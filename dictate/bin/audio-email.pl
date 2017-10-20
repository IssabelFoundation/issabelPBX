#!/usr/bin/perl -w

# Takes a slin (.raw) file, converts it to a user specified format, and emails it to the specified address.
#	License for all code of this IssabelPBX module can be found in the license file inside the module directory
#	Copyright 2013 Schmooze Com Inc.

use MIME::Base64;
use Net::SMTP;

# Default paramaters
my $to = "xrobau\@gmail.com";
my $from = "dictate\@";
my $subject = "Audio File has been sent";
my $file = undef;
my $attachment = undef;
my $format = "ogg";
my $ct = "audio/$format";

# Care about the hostname.
my $hostname = `/bin/hostname`;
chomp ($hostname);
if ($hostname =~ /localhost/) {
	$hostname = "set.your.hostname.com";
}
$from .= $hostname;

# Usage:
my $usage="Usage: --file filename [--attachment filename] [--format (gsm|wav|ogg)] [--to email_address] [--from email_address] [--type content/type] [--subject \"Subject Of Email\"]"; 

# Parse command line..
while (my $cmd = shift @ARGV) {
  chomp $cmd;
  # My kingdom for a 'switch'
  if ($cmd eq "--to") {
	my $tmp = shift @ARGV;
	$to = $tmp if (defined $tmp);
  } elsif ($cmd eq "--subject") {
	my $tmp = shift @ARGV;
	if ($tmp =~ /\^(\")|^(\')/) {
		# It's a quoted string
		my $delim = $+;   # $+ is 'last match', which is ' or "
		$tmp =~ s/\Q$delim\E//; # Strip out ' or "
		$subject = $tmp;
		while ($tmp = shift @ARGV) {
			if ($tmp =~ /\Q$delim\E/) {
				$tmp =~ s/\Q$delim\E//;
				last;
			}
		$subject .= $tmp;
		}
	} else {
		# It's a single word
		$subject = $tmp;
	}
  } elsif ($cmd eq "--type") {
	my $tmp = shift @ARGV;
	$ct = $tmp if (defined $tmp);
  } elsif ($cmd eq "--from") {
	my $tmp = shift @ARGV;
	$from = $tmp if (defined $tmp);
  } elsif ($cmd eq "--file") {
	my $tmp = shift @ARGV;
	$file = $tmp if (defined $tmp);
  } elsif ($cmd eq "--attachment") {
	my $tmp = shift @ARGV;
	$attachment = $tmp if (defined $tmp);
  } elsif ($cmd eq "--format") {
	my $tmp = shift @ARGV;
	$format = $tmp if (defined $tmp);
  } else {
	die "$cmd not understood\n$usage\n";
  }

}

# OK. All our variables are set up.
# Lets make sure that we know about a file...
die $usage unless $file;
# and that the file exists...
open FILE, $file or die "Error opening $file: $!"; 
# Oh, did we possibly not specify an attachment name?
$attachment = $file unless ($attachment);

my $encoded="";
# Create the file based on the format the user has specified 
open FILE, "sox -r 8000 -2 -c 1 -s $file -t $format - |";
$buf = "";
while (read(FILE, $buf, 60*57))  {
	$encoded .= encode_base64($buf);
}
close FILE;

# Now we have the file, we should ensure that there's no paths on the
# filename.. 
$attachment =~ s/\.\.//g;

# And that's pretty much all the hard work done. Now we just create the
# headers for the MIME encapsulation: 
my $boundary = '------ISSABELPBX_AUDIO_MAIL:'; 
my $dtime = `date`;
chomp $dtime;
my @chrs = ('0' .. '9', 'A' .. 'Z', 'a' .. 'z'); 
foreach (0..16) { $boundary .= $chrs[rand (scalar @chrs)]; } 

my $len = length $encoded;
# message body..
my $msg ="Content-Class: urn:content-classes:message
Content-Transfer-Encoding: 7bit
MIME-Version: 1.0
Content-Type: multipart/mixed; boundary=\"$boundary\"
From: $from
Date: $dtime
Reply-To: $from
X-Mailer: audiomail.pl
To: $to
Subject: $subject

This is a multi-part message in MIME format.

--$boundary 
Content-Type: text/plain; charset=\"us-ascii\"
Content-Transfer-Encoding: quoted-printable

An audio file has been sent to you, and is attached to this message.


--$boundary
Content-Type: $ct; name=\"$attachment\"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename=\"$attachment.$format\"

$encoded 
--$boundary-- 
";

#print "$msg";
# Now we just send it.
my $smtp = Net::SMTP-> new("127.0.0.1", Debug => 0) or
  die "Net::SMTP::new: $!";
$smtp-> mail($from);
$smtp-> recipient($to);
$smtp-> data();
$smtp-> datasend($msg);
$smtp-> dataend();

