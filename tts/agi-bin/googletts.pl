#!/usr/bin/env perl

#
# AGI script that uses Google's translate text to speech engine.
#
# Copyright (C) 2011 - 2015, Lefteris Zafiris <zaf@fastmail.com>
#
# This program is free software, distributed under the terms of
# the GNU General Public License Version 2. See the COPYING file
# at the top of the source tree.
#
# -----
# Usage
# -----
# agi(googletts.agi,"text",[language],[intkey],[speed]): This will invoke the Google TTS
# engine, render the text string to speech and play it back to the user.
# If 'intkey' is set the script will wait for user input. Any given interrupt keys will
# cause the playback to immediately terminate and the dialplan to proceed to the
# matching extension (this is mainly for use in IVR, see README for examples).
# If 'speed' is set the speech rate is altered by that factor (defaults to 1.2).
#
# The script contacts google's TTS service in order to get the voice data
# which then stores in a local cache (by default /tmp/) for future use.
#
# Parameters like default language, sample rate, caching and cache dir
# can be set up by altering the following variables:
# Default langeuage: $lang
# Sample rate:       $samplerate
# Speed factor:      $speed
# Chace:             $usecache
# Chache directory:  $cachedir
# SoX Version:       $sox_ver
#

use warnings;
use strict;
use utf8;
use Encode qw(decode encode);
use File::Temp qw(tempfile);
use File::Copy qw(move);
use File::Path qw(mkpath);
use Digest::MD5 qw(md5_hex);
use URI::Escape;
use LWP::UserAgent;
use LWP::ConnCache;

$| = 1;

# ----------------------------- #
#   User defined parameters:    #
# ----------------------------- #
# Default language              #
my $lang = "en";

# Output speed factor           #
my $speed = 1;

# Leave blank to auto-detect    #
my $samplerate = "16000";

# SoX Version                   #
# Leave blank to auto-detect    #
my $sox_ver = "";

# Verbose debugging messages    #
my $debug = 0;

my $destfilename;
# ----------------------------- #

my @text;
my $fh;
my $tmpname;
my %PARAM;
my $fexten;
my $tmpdir  = "/tmp";
my $maxlen  = 4096;
my $timeout = 10;
my $url     = "https://translate.google.com";
my $sox     = `/usr/bin/which sox`;
my $mpg123  = `/usr/bin/which mpg123`;


# Store AGI input #
($PARAM{arg_1}, $PARAM{arg_2}, $PARAM{arg_3}, $PARAM{arg_4}) = @ARGV;

# Abort if required programs not found. #
fatal_log("sox or mpg123 is missing. Aborting.") if (!$sox || !$mpg123);
chomp($sox, $mpg123);

# Sanitising input #
$PARAM{arg_1} = decode('utf8', $PARAM{arg_1});
for ($PARAM{arg_1}) {
	s/[\\|*~<>^\(\)\[\]\{\}[:cntrl:]]/ /g;
	s/\s+/ /g;
	s/^\s|\s$//g;
	fatal_log("No text passed for synthesis.") if (!length);
	# Split input to comply with google tts requirements #
	@text = /.{1,150}$|.{1,150}[.,?!:;]|.{1,150}\s/g;
}
my $lines = @text;

# Setting language, interrupt keys and speed rate #
if (length($PARAM{arg_2})) {
	if ($PARAM{arg_2} =~ /^[a-zA-Z]{2}(-[a-zA-Z]{2,6})?$/) {
		$lang = $PARAM{arg_2};
	} else {
		console_log("Invalid language setting. Using default.");
	}
}

if (length($PARAM{arg_3})) {
	$speed = $PARAM{arg_3} if ($PARAM{arg_3} =~ /^\d+(\.\d+)?$/);
}

if (length($PARAM{arg_4})) {
	$destfilename = $PARAM{arg_4};
}


$fexten="sln";
$samplerate = 8000;

# Initialise User angent #
my $ua = LWP::UserAgent->new(ssl_opts => { verify_hostname => 1 });
$ua->agent("Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:52.0) Gecko/20100101 Firefox/52.0");
$ua->env_proxy;
$ua->conn_cache(LWP::ConnCache->new());
$ua->timeout($timeout);

for (my $i = 0; $i < $lines; $i++) {
	my $res;
	my $len = length($text[$i]);
	my $line = encode('utf8', $text[$i]);
	$line =~ s/^\s+|\s+$//g;
	next if (length($line) == 0);
	if ($debug) {
		console_log("Text passed for synthesis: $line",
			"Language: $lang, Sample rate: $samplerate",
			"Speed: $speed "
		);
	}

	my $filename = md5_hex("$line.$lang.$speed");

	# Hnadle interrupts #
	$SIG{'INT'} = \&int_handler;
	$SIG{'HUP'} = \&int_handler;

	($fh, $tmpname) = tempfile("ggl_XXXXXXXX", DIR => $tmpdir, UNLINK => 1);
	my $token = make_token($line);
	$line = uri_escape($line);
	my $req   = "$url/translate_tts?ie=UTF-8&q=$line&tl=$lang&total=$lines&idx=$i&textlen=$len&client=tw-ob&tk=$token&prev=input";
	console_log("URL passed: $req") if ($debug);

	my $ua_request = HTTP::Request->new('GET' => $req);
	$ua_request->header(
		'Accept'          => '*/*',
		'Accept-Encoding' => 'identity;q=1, *;q=0',
		'Accept-Language' => 'en-US,en;q=0.5',
		'DNT'             => '1',
		'Range'           => 'bytes=0-',
		'Referer'         => 'https://translate.google.com/',
	);
	my $ua_response = $ua->request($ua_request, $tmpname);
	fatal_log("Failed to fetch file: ", $ua_response->code, $ua_response->message) unless ($ua_response->is_success);

	# Convert mp3 file to 16bit 8Khz or 16kHz mono raw #
	system($mpg123, "-q", "-w", "$tmpname.wav", $tmpname) == 0
		or fatal_log("$mpg123 failed: $?");

	# Detect sox version #
	if (!$sox_ver) {
		$sox_ver = (system("$sox --version > /dev/null 2>&1") == 0) ? 14 : 12;
		console_log("Found sox version $sox_ver in: $sox, mpg123 in: $mpg123") if ($debug);
	}
	my @soxargs = get_sox_args("$tmpname.wav", "$tmpname.$fexten");
	system(@soxargs) == 0 or fatal_log("$sox failed: $?");
	unlink "$tmpname.wav";

	# Playback and save file in cache #
	console_log("Saving file $filename to cache") if ($debug);
	move("$tmpname.$fexten", "$destfilename.$fexten");
}
exit;

sub checkresponse {
	my $input = <STDIN>;
	my @values;

	chomp $input;
	if ($input =~ /^200 result=(-?\d+)\s?(.*)$/) {
		@values = ("$1", "$2");
	} else {
		$input .= <STDIN> if ($input =~ /^520-Invalid/);
		warn "Unexpected result: $input\n";
		@values = (-1, -1);
	}
	return @values;
}

sub get_sox_args {
	# Set the appropiate sox cli arguments #
	my ($source_file, $dest_file) = @_;

	my @soxargs = ($sox, $source_file, "-q", "-r", $samplerate, "-t", "raw", $dest_file);
	if ($speed != 1) {
		if ($sox_ver >= 14) {
			push(@soxargs, ("tempo", "-s", $speed));
		} else {
			push(@soxargs, ("stretch", 1 / $speed, "80"));
		}
	}
	return @soxargs;
}

# Obfuscated crap straight from Google:
# https://translate.google.com/translate/releases/twsfe_w_20151214_RC03/r/js/desktop_module_main.js
sub make_token {
	my $text = shift;
	my $time = int(time() / 3600);
	my @chars = unpack('U*', $text);
	my $stamp = $time;

	foreach (@chars) {
		$stamp = make_rl($stamp + $_, '+-a^+6');
	}
	$stamp = make_rl($stamp, '+-3^+b+-f');
	if ($stamp < 0) {
		$stamp = ($stamp & 2147483647) + 2147483648;
	}
	$stamp %= 10**6;
	return ($stamp . '.' . ($stamp ^ $time));
}

sub make_rl {
	my ($num, $str) = @_;

	for (my $i = 0; $i < length($str) - 2 ; $i += 3) {
		my $d = substr($str, $i+2, 1);
		if (ord($d) >= ord('a')) {
			$d = ord($d) - 87;
		} else {
			$d = int($d);
		}
		if (substr($str, $i+1, 1) eq '+') {
			$d = $num >> $d;
		} else {
			$d = $num << $d;
		}
		if (substr($str, $i, 1) eq '+') {
			$num = $num + $d & 4294967295;
		} else {
			$num = $num ^ $d;
		}
	}
	return $num;
}

sub console_log {
	foreach my $message (@_) {
		warn "$message\n";
	}
}

sub fatal_log {
	console_log(@_);
	die;
}

sub int_handler {
	die "Interrupt signal received, terminating...\n";
}


sub get_data_dir {
    my $file = '/etc/amportal.conf';
    open my $info, $file or die "Could not open $file: $!";
    my $line;
    while( $line = <$info>)  {
	chomp($line);
	$line =~ tr/ //ds;
	if($line =~ m/ASTDATADIR/) { last; }
    }
    close $info;
    my @partes = split(/=/,$line);
    return $partes[1];
}


END {
	if ($tmpname) {
		warn "Cleaning temp files.\n" if ($debug);
		unlink glob "$tmpname*";
	}
}
