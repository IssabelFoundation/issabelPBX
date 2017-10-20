#!/usr/bin/perl

#  Flash Operator Panel.    http://www.asternic.org
#
#  Copyright (c) 2004 Nicolás Gudiño.  All rights reserved.
#
#  Nicolás Gudiño <nicolas@house.com.ar>
#
#  This program is free software, distributed under the terms of
#  the GNU General Public License.
#
#  THIS SOFTWARE IS PROVIDED BY THE CONTRIBUTORS ``AS IS'' AND ANY EXPRESS OR
#  IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
#  MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO
#  EVENT SHALL THE CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
#  SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
#  PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
#  OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
#  WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
#  OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
#  ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

use strict;
use warnings;
use integer;

use constant DEBUG         => 1;
use constant BYTES_TO_READ => 256;

use IO::Socket;
use IO::Select;
use Getopt::Long;
use Pod::Usage;
use Fcntl;
use POSIX qw(setsid EWOULDBLOCK);

my $FOP_VERSION    = "0.30";
my %datos          = ();
my %chanvar        = ();
my %monitoring     = ();
my %passvar        = ();
my %sesbot         = ();
my %linkbot        = ();
my %cache_hit      = ();
my %estadoboton    = ();
my %preestadoboton = ();

my %boton_paused               = ();
my %boton_agentready           = ();
my %boton_agentpaused          = ();
my %boton_agentbusy            = ();
my %boton_agentlogedof         = ();
my %botonled                   = ();
my %botonalpha                 = ();
my %botonledcolor              = ();
my %botonregistrado            = ();
my %boton_ip                   = ();
my %botonlabel                 = ();
my %botonlabelonly             = ();
my %botonsetlabel              = ();
my %botontimer                 = ();
my %botontimertype             = ();
my %botonpark                  = ();
my %botonmeetme                = ();
my %botonclid                  = ();
my %botonpermanenttext         = ();
my %botonqueue                 = ();
my %botonqueue_count           = ();
my %botonqueuemember           = ();
my %botonvoicemail             = ();
my %botonvoicemailcount        = ();
my %botonlinked                = ();
my %parked                     = ();
my %meetme_pos                 = ();
my %laststatus                 = ();
my %autenticado                = ();
my %auto_conference            = ();
my %attendant_transfer         = ();
my %attendant_pending          = ();
my %pending_uniqueid_attendant = ();
my %mute_other                 = ();
my %autosip                    = ();
my %cnt_auto_pos               = ();
my $cnt_autosip                = 0;
my %autosip_detail             = ();
my %buttons                    = ();
my %buttons_queue              = ();
my %buttons_queue_reverse      = ();
my %buttons_preserve_case      = ();
my %buttons_astdbkey           = ();
my %button_server              = ();
my %buttons_reverse            = ();
my %textos                     = ();
my %iconos                     = ();
my %urls                       = ();
my %alarms                     = ();
my %targets                    = ();
my %remote_callerid            = ();
my %remote_callerid_name       = ();
my %extension_transfer         = ();
my %extension_transfer_reverse = ();
my %max_queue_waiting_time_for = ();
my %flash_contexto             = ();
my %saved_clidnum              = ();
my %saved_clidname             = ();
my %keys_socket                = ();
my %manager_socket             = ();
my %start_muted                = ();
my %timeouts                   = ();
my %no_rectangle               = ();
my %background                 = ();
my %astdbcommands              = ();
my %client_queue               = ();
my %manager_queue              = ();
my %client_queue_nocrypt       = ();
my %ip_addy                    = ();
my %held_channel               = ();
my %agents_available_on_queue  = ();
my $queue_object               = {};
my %is_agent                   = ();
my %agents_on_queue            = ();
my %max_lastcall               = ();
my $config                     = {};
my $cola                       = {};
my $language                   = {};
my $global_verbose             = 1;
my $help                       = 0;
my $version                    = 0;
my $counter_servers            = -1;
my %bloque_completo;
my %buferbloque;
my $bloque_final;
my $todo;
my $reload_pending     = 0;
my $regexp_buttons     = 0;
my $auto_buttons       = 0;
my @auto_config        = ();
my $queueagent_buttons = 0;
my $defaultlanguage;
my @bloque;
my @respuestas;
my @all_flash_files;
my @masrespuestas;
my @fake_bloque;
my @flash_clients;
my @status_active;
my @panel_contexts;
my %mailbox;
my %tovoicemail;
my %tospy;
my %instancias;
my %agent_to_channel;
my %agent_label;
my %togle_action;
my %togle_response;
my %channel_to_agent;
my %reverse_agents;
my %agents_name;
my @p;
my $m;
my $O;
my @S;
my @key;
my @manager_host        = ();
my @manager_port        = ();
my @manager_user        = ();
my @manager_secret      = ();
my @event_mask          = ();
my @astmanproxy_servers = ();
my @manager_conectado   = ();
my %manager_desconectado;
my %mask_hash;
my $web_hostname;
my $listen_port;
my $park_exten;
my $parktimeout;
my $listen_addr;
my $security_code;
my $flash_dir;
my $astmanproxy_server = "";
my $restrict_channel   = "";
my $poll_interval;
my $poll_voicemail;
my $kill_zombies;
my $ren_agentlogin;
my $ren_cbacklogin;
my $ren_agentname;
my $agent_status;
my $ren_queuemember;
my $ren_wildcard;
my $clid_privacy;
my %clid_private;
my %group_count;
my $show_ip;
my $queue_hide;
my $enable_restart;
my $passvars = "";
my $change_led;
my $cdial_nosecure;
my $barge_muted;
my $debuglevel       = -1;
my $debuglevel_cache = "";
my $cont_debug_cache = 0;
my $flash_file;
my %barge_rooms;
my %barge_context;
my $first_room;
my $last_room;
my $clid_format;
my $directorio       = "";
my $auth_md5         = 1;
my $astmanproxy_host = "";
my $astmanproxy_port = "5038";
my $md5challenge;
my $reverse_transfer;
my %shapes;
my %legends;
my %images;
my %no_encryption = ();
my %total_shapes;
my %total_legends;
my %total_images;
my @serverinclude = ();
my @btninclude    = ();
my @styleinclude  = ();
my $command       = "";
my $daemonized    = 0;
my $pidfile       = "/var/run/op_panel.pid";
my $logdir        = "";
my $confdir       = "";
my $tab           = "";

my $PADDING = join(
    '',
    map( chr,
        (
            0x80, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
            0,    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
            0,    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
          ) )
);
my %a2b = (
    A   => 000,
    B   => 001,
    C   => 002,
    D   => 003,
    E   => 004,
    F   => 005,
    G   => 006,
    H   => 007,
    I   => 010,
    J   => 011,
    K   => 012,
    L   => 013,
    M   => 014,
    N   => 015,
    O   => 016,
    P   => 017,
    Q   => 020,
    R   => 021,
    S   => 022,
    T   => 023,
    U   => 024,
    V   => 025,
    W   => 026,
    X   => 027,
    Y   => 030,
    Z   => 031,
    a   => 032,
    b   => 033,
    c   => 034,
    d   => 035,
    e   => 036,
    f   => 037,
    g   => 040,
    h   => 041,
    i   => 042,
    j   => 043,
    k   => 044,
    l   => 045,
    m   => 046,
    n   => 047,
    o   => 050,
    p   => 051,
    q   => 052,
    r   => 053,
    s   => 054,
    t   => 055,
    u   => 056,
    v   => 057,
    w   => 060,
    x   => 061,
    y   => 062,
    z   => 063,
    '0' => 064,
    '1' => 065,
    '2' => 066,
    '3' => 067,
    '4' => 070,
    '5' => 071,
    '6' => 072,
    '7' => 073,
    '8' => 074,
    '9' => 075,
    '+' => 076,
    '_' => 077,
);
my %b2a                      = reverse %a2b;
my $rand_byte_already_called = 0;

$SIG{PIPE} = 'IGNORE';
$SIG{ALRM} = 'alarma_al_minuto';
$SIG{INT}  = 'close_all';
$SIG{HUP}  = 'generate_configs_onhup';
$SIG{USR1} = 'dump_internal_hashes_to_stdout';

GetOptions(
    'p|pidfile=s'    => \$pidfile,
    'l|logdir=s'     => \$logdir,
    'c|confdir=s'    => \$confdir,
    'd|daemon'       => \$daemonized,
    'V|version'      => \$version,
    'x|debuglevel=i' => \$debuglevel,
    'help|?'         => \$help
);

pod2usage(1) if $help;

if ( $version == 1 ) {
    print "op_server.pl version $FOP_VERSION\n";
    exit 0;
}

if ( $confdir eq "" ) {

    # if there is no config directory supplied at the command line
    # use the same directory where op_server.pl lives
    $directorio = $0;
    $directorio =~ s/(.*)\/(.*)/$1/g;
}
else {
    $directorio = $confdir;
}

if ( $logdir ne "" ) {
    open( STDOUT, ">>$logdir/output.log" )
      or die "Can't open output log $logdir/error.log";
    open( STDERR, ">>$logdir/error.log" )
      or die "Can't open output log $logdir/error.log";
}

if ( $daemonized == 1 ) {
    defined( my $pid = fork ) or die "Can't Fork: $!";
    exit if $pid;
    setsid or die "Can't start a new session: $!";
    open MYPIDFILE, ">$pidfile"
      or die "Failed to open PID file $pidfile for writing.";
    print MYPIDFILE $$;
    close MYPIDFILE;

    close(STDIN);
    if ( $logdir eq "" ) {
        close STDOUT;
        close STDERR;
    }
}

sub read_language_config() {
    $/ = "\n";

    # tries to read and parse every language file needed
    foreach my $ctx (@panel_contexts) {
        if ( !defined( $config->{$ctx}{language} ) ) {
            $config->{"$ctx"}{language} = $defaultlanguage;
        }

        my $lang = $config->{$ctx}{language};
        $lang =~ tr/A-Z/a-z/;
        $lang =~ s/\W//g;
        $config->{$ctx}{language} = $lang;

        open( CONFIG, "<$directorio/op_lang_$lang.cfg" )
          or die("Could not open $directorio/op_lang_$lang.cfg. Aborting...");

        while (<CONFIG>) {
            chop;
            $_ =~ s/^\s+//g;
            $_ =~ s/([^;]*)[;](.*)/$1/g;
            $_ =~ s/\s+$//g;
            next unless $_ ne "";
            my ( $variable_name, $value ) = split( /=/, $_ );
            $variable_name =~ tr/A-Z/a-z/;
            $variable_name =~ s/\s+//g;
            $value         =~ s/^\s+//g;
            $value         =~ s/\s+$//g;
            $value         =~ s/\"//g;
            $language->{$ctx}{$variable_name} = $value;
        }
        close(CONFIG);
    }
    $/ = "\0";
}

sub parse_amportal_config {
    my $filename = shift;
    my %ampconf;

    open( AMPCONF, $filename ) || die "Cannot open $filename";

    while (<AMPCONF>) {
        if ( $_ =~ /^\s*([a-zA-Z0-9]+)\s*=\s*(.*)\s*([;#].*)?/ ) {
            $ampconf{$1} = $2;
        }
    }

    close(AMPCONF);
    return %ampconf;
}

sub read_server_config() {
    my $context = "";
    my @distinct_files;
    $counter_servers = -1;

    $/ = "\n";

    @distinct_files = unique(@serverinclude);

    foreach my $archivo (@distinct_files) {

        open( CONFIG, "<$directorio/$archivo" )
          or die("Could not open op_server.cfg. Aborting...");

        while (<CONFIG>) {
            chomp;
            $_ =~ s/^\s+//g;
            $_ =~ s/([^;]*)[;](.*)/$1/g;
            $_ =~ s/\s+$//g;

            if ( /^#/ || /^;/ || /^$/ ) {
                next;
            }    # Ignores comments and empty lines

            if (/^\Q[\E/) {
                s/\[(.*)\]/$1/g;
                tr/a-z/A-Z/;
                $context = $_;
            }
            else {
                if ( $context ne "" ) {
                    my ( $variable_name, $value ) = split( /=/, $_ );
                    $variable_name =~ tr/A-Z/a-z/;
                    $variable_name =~ s/\s+//g;
                    $value         =~ s/^\s+//g;
                    $value         =~ s/\s+$//g;
                    $value         =~ s/\"//g;
                    $config->{$context}{$variable_name} = $value;

                    if ( $variable_name eq "manager_host" ) {
                        $counter_servers++;
                        $manager_host[$counter_servers] = $value;
                    }

                    if ( $variable_name eq "manager_user" ) {
                        $manager_user[$counter_servers] = $value;
                    }

                    if ( $variable_name eq "manager_secret" ) {
                        $manager_secret[$counter_servers] = $value;
                    }

                    if ( $variable_name eq "manager_port" ) {
                        $manager_port[$counter_servers] = $value;
                    }

                    if ( $variable_name eq "event_mask" ) {
                        $event_mask[$counter_servers] = $value;
                    }

                    if ( $variable_name eq "astmanproxy_server" ) {
                        push @astmanproxy_servers, $value;
                    }

                }
            }
        }
        close(CONFIG);
    }

    if ( defined( $config->{GENERAL}{use_amportal_conf} ) ) {
        if ( $config->{GENERAL}{use_amportal_conf} == 1 ) {
            my $issabelpbx_config = "/etc/amportal.conf";
            my %ampconf;

            if ( -e $issabelpbx_config ) {
                %ampconf                              = parse_amportal_config($issabelpbx_config);
                $config->{"GENERAL"}{"web_hostname"}  = $ampconf{"AMPWEBADDRESS"};
                $config->{"GENERAL"}{"security_code"} = $ampconf{"FOPPASSWORD"};
                $config->{"GENERAL"}{"flash_dir"}     = $ampconf{"FOPWEBROOT"};
                $manager_user[0]                      = $ampconf{"AMPMGRUSER"};
                $manager_secret[0]                    = $ampconf{"AMPMGRPASS"};
            }
        }
    }

    $web_hostname     = $config->{GENERAL}{web_hostname};
    $listen_port      = $config->{GENERAL}{listen_port};
    $listen_addr      = $config->{GENERAL}{listen_addr};
    $security_code    = $config->{GENERAL}{security_code};
    $flash_dir        = $config->{GENERAL}{flash_dir};
    $poll_interval    = $config->{GENERAL}{poll_interval};
    $poll_voicemail   = $config->{GENERAL}{poll_voicemail};
    $kill_zombies     = $config->{GENERAL}{kill_zombies};
    $reverse_transfer = $config->{GENERAL}{reverse_transfer};
    $auth_md5         = $config->{GENERAL}{auth_md5};
    $astmanproxy_host = $config->{GENERAL}{astmanproxy_host};
    $astmanproxy_port = $config->{GENERAL}{astmanproxy_port};
    $ren_agentlogin   = $config->{GENERAL}{rename_label_agentlogin};
    $ren_cbacklogin   = $config->{GENERAL}{rename_label_callbacklogin};
    $ren_wildcard     = $config->{GENERAL}{rename_label_wildcard};
    $ren_agentname    = $config->{GENERAL}{rename_to_agent_name};
    $agent_status     = $config->{GENERAL}{agent_status};
    $ren_queuemember  = $config->{GENERAL}{rename_queue_member};
    $change_led       = $config->{GENERAL}{change_led_agent};
    $cdial_nosecure   = $config->{GENERAL}{clicktodial_insecure};
    $barge_muted      = $config->{GENERAL}{barge_muted};
    $clid_privacy     = $config->{GENERAL}{clid_privacy};
    $show_ip          = $config->{GENERAL}{show_ip};
    $queue_hide       = $config->{GENERAL}{queue_hide};
    $enable_restart   = $config->{GENERAL}{enable_restart};
    $defaultlanguage  = $config->{GENERAL}{language};
    $passvars         = $config->{GENERAL}{passvars};
    $park_exten       = $config->{GENERAL}{parkexten};
    $parktimeout      = $config->{GENERAL}{parktimeout};

    if ( $debuglevel == -1 ) {
        $debuglevel = $config->{GENERAL}{debug};
    }

    my @todos_los_rooms;
    foreach my $val ($config) {
        while ( my ( $aa, $bb ) = each( %{$val} ) ) {
            while ( my ( $cc, $dd ) = each( %{$bb} ) ) {
                if ( $cc eq "barge_rooms" ) {
                    ( $first_room, $last_room ) = split( /-/, $dd );
                    if ( !defined($last_room) ) {
                        $last_room = $first_room;
                    }
                    my @arrayroom = $first_room .. $last_room;
                    foreach (@arrayroom) {
                        $barge_context{"$_"} = $aa;
                    }
                    push( @todos_los_rooms, @arrayroom );
                }
            }
        }
    }
    %barge_rooms = map { $todos_los_rooms[$_], 0 } 0 .. $#todos_los_rooms;

    $clid_format = $config->{GENERAL}{clid_format};
    if ( !defined($flash_dir) ) { $flash_dir = '/var/www/html' }
    $flash_file = $flash_dir . "/variables.txt";
    push @all_flash_files, $flash_file;

    if ( !defined $web_hostname ) {
        $web_hostname = "";
    }
    if ( !defined $listen_port ) {
        $listen_port = 4445;
    }
    if ( !defined $listen_addr ) {
        $listen_addr = "0.0.0.0";
    }
    if ( !defined $astmanproxy_host ) {
        $astmanproxy_host = "";
    }
    else {
        @manager_host   = ();
        @manager_user   = ();
        @manager_secret = ();
        push @manager_host,   $astmanproxy_host;
        push @manager_user,   "astmanproxy";
        push @manager_secret, "astmanproxy";
    }

    if ( defined $astmanproxy_port ) {
        @manager_port = ();
        push @manager_port, $astmanproxy_port;
    }

    if ( !defined $security_code ) {
        die("Missing security_code in op_server.cfg!");
    }

    if ( !defined $flash_dir ) { die("Missing flash_dir in op_server.cfg!"); }

    if ( !defined $poll_interval ) {
        die("Missing poll_interval in op_server.cfg!");
    }

    if ( !defined $ren_agentlogin ) {
        $ren_agentlogin = 0;
    }

    if ( !defined $defaultlanguage ) {
        $defaultlanguage = "en";
        $config->{DEFAULT}{language} = "en";
    }

    if ( !defined $config->{GENERAL}{monitor_filename} ) {
        $config->{GENERAL}{monitor_filename} = "\${UNIQUEID}";
    }

    if ( !defined $config->{GENERAL}{monitor_format} ) {
        $config->{GENERAL}{monitor_format} = "wav";
    }

    if ( !defined $clid_privacy ) {
        $clid_privacy = 0;
    }

    if ( !defined $show_ip ) {
        $show_ip = 0;
    }

    if ( !defined $queue_hide ) {
        $queue_hide = 0;
    }

    if ( !defined $ren_wildcard || $ren_wildcard eq "" ) {
        $ren_wildcard = 1;
    }

    if ( !defined $reverse_transfer || $reverse_transfer eq "" ) {
        $reverse_transfer = 0;
    }

    if ( !defined $barge_muted || $barge_muted eq "" ) {
        $barge_muted = 0;
    }

    if ( !defined $enable_restart || $enable_restart eq "" ) {
        $enable_restart = 0;
    }

    if ( !defined $cdial_nosecure || $cdial_nosecure eq "" ) {
        $cdial_nosecure = 0;
    }

    if ( !defined $agent_status || $agent_status eq "" ) {
        $agent_status = 0;
    }

    if ( !defined $ren_agentname || $ren_agentname eq "" ) {
        $ren_agentname = 0;
    }

    if ( !defined $ren_cbacklogin || $ren_cbacklogin eq "" ) {
        $ren_cbacklogin = 0;
    }

    if ( !defined $ren_queuemember || $ren_queuemember eq "" ) {
        $ren_queuemember = 0;
    }

    if ( !defined $change_led || $change_led eq "" ) {
        $change_led = 0;
    }

    if ( !defined $kill_zombies || $kill_zombies eq "" ) {
        $kill_zombies = 0;
    }

    if ( !defined $poll_voicemail || $poll_voicemail eq "" ) {
        $poll_voicemail = 0;
    }

    if ( !defined $clid_format ) {
        $clid_format = "(xxx) xxx-xxxx";
    }

    if ( !defined $passvars ) {
        $passvars = "";
    }

    if ( !defined $park_exten ) {
        $park_exten = "700";
    }

    if ( !defined $parktimeout ) {
        $parktimeout = 45000;
    }
    else {
        $parktimeout = $parktimeout * 1000;
    }

    if ( !defined $debuglevel ) {
        $debuglevel = 0;
    }
    else {
        if ( $daemonized == 1 && $logdir eq "" ) {
            $debuglevel = 0;
        }
    }
    $/ = "\0";
}

sub collect_includes {
    my $filename = shift;
    my $tipo     = shift;
    my $archivo  = $directorio . "/" . $filename;

    if ( !-r $archivo ) {
        log_debug( "** $archivo not readable... skipping", 16 ) if DEBUG;
        return;
    }

    if ( $tipo eq "buttons" ) {
        if ( !grep { $filename eq $_ } @btninclude ) {
            push( @btninclude, $filename );
        }
        else {
            log_debug( "** $filename already included", 16 ) if DEBUG;
            return;
        }
    }
    if ( $tipo eq "style" ) {
        if ( !grep { $filename eq $_ } @styleinclude ) {
            push( @styleinclude, $filename );
        }
        else {
            log_debug( "** $filename already included", 16 ) if DEBUG;
            return;
        }
    }
    if ( $tipo eq "server" ) {
        if ( !grep { $filename eq $_ } @serverinclude ) {
            push( @serverinclude, $filename );
        }
        else {
            log_debug( "** $filename already included", 16 ) if DEBUG;
            return;
        }
    }

    open( CONFIG, "< $archivo" )
      or die("Could not open $filename. Aborting...\n\n");

    my @lineas  = <CONFIG>;
    my $cuantos = @lineas;
    foreach my $linea (@lineas) {
        $linea =~ s/^\s+//g;
        $linea =~ s/([^;]*)[;](.*)/$1/g;
        $linea =~ s/\s+$//g;
        if ( $linea =~ /^include/ ) {

            # store include lines in an array so we can
            # process them later excluding duplicates
            $linea =~ s/^include//g;
            $linea =~ s/^\s+//g;
            $linea =~ s/^=>//g;
            $linea =~ s/^\s+//g;
            $linea =~ s/\s+$//g;
            collect_includes( $linea, $tipo );
        }
    }
    close CONFIG;
}

sub read_astdb_config() {
    $/ = "\n";
    if ( -e "$directorio/op_astdb.cfg" ) {
        open( ASTDB, "<$directorio/op_astdb.cfg" )
          or die("Could not open op_astdb.cfg. Aborting...");
        my $contador = 0;
        my $key      = "";
        while (<ASTDB>) {
            chomp;
            $_ =~ s/^\s+//g;
            $_ =~ s/([^;]*)[;](.*)/$1/g;
            $_ =~ s/\s+$//g;

            if ( /^#/ || /^;/ || /^$/ ) {
                next;
            }    # Ignores comments and empty lines

            if (/^\Q[\E/) {
                s/\[(.*)\]/$1/g;
                $key = $_;
            }
            else {
                push @{ $astdbcommands{$key} }, $_;
            }
        }
    }
    close(ASTDB);

    $/ = "\0";
}

sub read_buttons_config() {
    my @btn_cfg  = ();
    my $contador = -1;
    my @distinct_files;
    my $no_counter   = 0;
    my @contextos    = ();
    my %lastposition = ();

    $/ = "\n";

    @distinct_files = unique(@btninclude);

    foreach my $archivo (@distinct_files) {
        open( CONFIG, "< $directorio/$archivo" )
          or die("Could not open $directorio/$archivo. Aborting...");

        # Read op_buttons.cfg loading it into a hash for easier processing

        while (<CONFIG>) {
            chomp;
            $_ =~ s/^\s+//g;
            $_ =~ s/([^;]*)[;](.*)/$1/g;
            $_ =~ s/\s+$//g;
            if ( /^#/ || /^;/ || /^$/ ) {
                next;
            }    # Ignores comments and empty lines

            if (/^\Q[\E/) {
                $contador++;
                s/\[(.*)\]/$1/g;
                my $channel = $_;
                $btn_cfg[$contador]{'channel_preserve_case'} = $channel;
                $btn_cfg[$contador]{'channel'}               = $channel;

            }
            else {
                next unless ( $contador >= 0 );
                my ( $key, $val ) = split( /=/, $_, 2 );
                if ( !defined($val) ) { $val = ""; }
                $key =~ tr/A-Z/a-z/;
                $key =~ s/^\s+//g;
                $key =~ s/(.*)\s+/$1/g;
                if (   $key ne "label"
                    && $key ne "font_family"
                    && $key ne "text"
                    && $key ne "mailbox"
                    && $key ne "voicemail_context" )
                {
                    $val =~ s/^\s+//g;
                    $val =~ s/(.*)\s+/$1/g;
                }
                $btn_cfg[$contador]{$key} = $val;
                if ( $key eq "panel_context" ) {
                    push @contextos, $val;
                }
            }
        }

        close(CONFIG);
    }

    # Read now the auto_sip button config files
    foreach my $papi ( sort keys %autosip ) {
        if ( !defined( $autosip{$papi}{channel} ) ) { next; }

        $contador++;
        log_debug( "-----", 16 ) if DEBUG;
        $btn_cfg[$contador]{'channel_preserve_case'} = $autosip{$papi}{channel};
        $btn_cfg[$contador]{'channel'}               = $autosip{$papi}{channel};
        if ( defined( $cnt_auto_pos{ $autosip{$papi}{autonumber} } ) ) {
            $cnt_auto_pos{ $autosip{$papi}{autonumber} }++;
        }
        else {
            $cnt_auto_pos{ $autosip{$papi}{autonumber} } = 0;
        }

        my $pos = $autosip{$papi}{starting_position} + $cnt_auto_pos{ $autosip{$papi}{autonumber} };
        $btn_cfg[$contador]{'position'} = $pos;
        $btn_cfg[$contador]{'channel'}  = $autosip{$papi}{channel};
        my $logblock = "\n[" . $autosip{$papi}{channel} . "]\n";
        log_debug( $autosip{$papi}{channel} . " at position $pos", 16 ) if DEBUG;

        while ( my ( $key, $val ) = each( %{ $autosip{$papi} } ) ) {
            if ( $val eq "" ) {
                log_debug( "** Empty value for autosip for key $key, button $papi", 1 ) if DEBUG;
                next;
            }
            $btn_cfg[$contador]{$key} = $val;
            $logblock .= "$key=$val\n";
            if ( $key eq "panel_context" ) {
                push @contextos, $val;
            }
        }
        log_debug( "$logblock", 1 ) if DEBUG;
    }

    my @uniq2 = unique(@contextos);
    @contextos = @uniq2;
    @uniq2     = grep { !/\*/ } @contextos;
    @contextos = @uniq2;
    push @contextos, "DEFAULT";
    push @contextos, "GENERAL";

    # Convert every element to uppercase
    @panel_contexts = map { uc } @contextos;

    # Pass to replicate panel_context=* configuration
    my @copy_cfg = ();
    @copy_cfg = @btn_cfg;
    foreach (@copy_cfg) {
        my %tmphash = %$_;
        if ( defined( $tmphash{panel_context} ) ) {
            if ( $tmphash{panel_context} eq "*" ) {
                foreach my $contextoahora (@contextos) {
                    $contador++;
                    while ( my ( $key, $val ) = each(%tmphash) ) {
                        if ( $key eq "panel_context" ) {
                            $val = $contextoahora;
                        }
                        $btn_cfg[$contador]{$key} = $val;
                    }
                }
            }
        }
    }

    # We finished reading the file, now we populate our
    # structures with the relevant data
    my %rectangles_counter;
    my %legends_counter;
    my %images_counter;

    my $cont_auto = 0;
  CONFIG:
    foreach (@btn_cfg) {
        my @positions = ();
        my %tmphash   = %$_;

        if ( defined( $tmphash{panel_context} ) ) {
            if ( $tmphash{panel_context} eq "*" ) {

                # We skip the * panel_context because we already
                # expand them to every context possible before
                next CONFIG;
            }
        }
        if ( $tmphash{channel} =~ /^AUTO/i ) {
            $auto_buttons = 1;
            while ( my ( $key, $val ) = each(%tmphash) ) {
                $auto_config[$cont_auto]{$key} = $val;
            }
            $cont_auto++;
            next CONFIG;
        }

        if ( $tmphash{channel} =~ /^_/ ) {
            $regexp_buttons = 1;
        }
        elsif ( $tmphash{channel} =~ /^QUEUEAGENT\//i ) {
            $queueagent_buttons = 1;
        }
        elsif ( $tmphash{channel} =~ /^image$/i ) {

            # Image config primitive

            if ( defined( $tmphash{panel_context} ) ) {
                $tmphash{panel_context} =~ tr/a-z/A-Z/;
                $tmphash{panel_context} =~ s/^DEFAULT$//xms;
            }
            else {
                $tmphash{panel_context} = "";
            }
            my $conttemp = $tmphash{panel_context};
            if ( $conttemp eq "" ) { $conttemp = "GENERAL"; }

            if ( !defined( $tmphash{src} ) ) {
                next CONFIG;
            }
            if ( !defined( $tmphash{url} ) ) {
                $tmphash{url} = "no";
            }
            if ( !defined( $tmphash{target} ) ) {
                $tmphash{target} = "NONTARFOP";
            }
            $images_counter{$conttemp}++;
            if ( $images_counter{$conttemp} > 1 ) {
                $images{$conttemp} .= "&";
            }
            $total_images{$conttemp}++;
            $images{$conttemp} .= "image_$images_counter{$conttemp}=" . $tmphash{x} . ",";
            $images{$conttemp} .= $tmphash{y} . ",";
            $images{$conttemp} .= $tmphash{src} . ",";
            $images{$conttemp} .= $tmphash{url} . ",";
            $images{$conttemp} .= $tmphash{target};
            next CONFIG;

        }
        elsif ( $tmphash{channel} =~ /^legend$/i ) {

            # Legend config primitive

            if ( defined( $tmphash{panel_context} ) ) {
                $tmphash{panel_context} =~ tr/a-z/A-Z/;
                $tmphash{panel_context} =~ s/^DEFAULT$//xms;
            }
            else {
                $tmphash{panel_context} = "";
            }
            my $conttemp = $tmphash{panel_context};
            if ( $conttemp eq "" ) { $conttemp = "GENERAL"; }

            if ( !defined( $tmphash{text} ) ) {
                $tmphash{text} = "LEGEND";
            }
            if ( !defined( $tmphash{x} ) ) {
                $tmphash{x} = 1;
            }
            if ( !defined( $tmphash{y} ) ) {
                $tmphash{y} = 1;
            }
            if ( !defined( $tmphash{font_size} ) ) {
                $tmphash{font_size} = 16;
            }
            if ( !defined( $tmphash{font_color} ) ) {
                $tmphash{font_color} = "000000";
            }
            if ( !defined( $tmphash{use_embed_fonts} ) ) {
                $tmphash{use_embed_fonts} = 1;
            }
            if ( !defined( $tmphash{font_family} ) ) {
                $tmphash{font_family} = "Arial";
            }
            if ( !defined( $tmphash{no_base64} ) ) {
                $tmphash{no_base64} = 0;
            }
            if ( $tmphash{no_base64} == 0 ) {
                $tmphash{text} = encode_base64( $tmphash{text} );
            }
            if ( !defined( $tmphash{url} ) ) {
                $tmphash{url} = "no";
            }
            if ( !defined( $tmphash{target} ) ) {
                $tmphash{target} = "NONTARFOP";
            }
            $legends_counter{$conttemp}++;
            if ( $legends_counter{$conttemp} > 1 ) {
                $legends{$conttemp} .= "&";
            }
            $total_legends{$conttemp}++;
            $legends{$conttemp} .= "legend_$legends_counter{$conttemp}=" . $tmphash{x} . ",";
            $legends{$conttemp} .= $tmphash{y} . ",";
            $legends{$conttemp} .= $tmphash{text} . ",";
            $legends{$conttemp} .= $tmphash{font_size} . ",";
            $legends{$conttemp} .= $tmphash{font_family} . ",";
            $legends{$conttemp} .= $tmphash{font_color} . ",";
            $legends{$conttemp} .= $tmphash{use_embed_fonts} . ",";
            $legends{$conttemp} .= $tmphash{no_base64} . ",";
            $legends{$conttemp} .= $tmphash{url} . ",";
            $legends{$conttemp} .= $tmphash{target};
            next CONFIG;
        }
        elsif ( $tmphash{channel} =~ /^RECTANGLE$/i ) {

            # Rectangle config primitive
            if ( defined( $tmphash{panel_context} ) ) {
                $tmphash{panel_context} =~ tr/a-z/A-Z/;
                $tmphash{panel_context} =~ s/^DEFAULT$//;
            }
            else {
                $tmphash{panel_context} = "";
            }
            my $conttemp = $tmphash{panel_context};
            if ( $conttemp eq "" ) { $conttemp = "GENERAL"; }

            if ( !defined( $tmphash{x} ) ) {
                $tmphash{x} = 1;
            }
            if ( !defined( $tmphash{y} ) ) {
                $tmphash{y} = 1;
            }
            if ( !defined( $tmphash{width} ) ) {
                $tmphash{width} = 1;
            }
            if ( !defined( $tmphash{height} ) ) {
                $tmphash{height} = 1;
            }
            if ( !defined( $tmphash{line_width} ) ) {
                $tmphash{line_width} = 1;
            }
            if ( !defined( $tmphash{line_color} ) ) {
                $tmphash{line_color} = "0x000000";
            }
            if ( !defined( $tmphash{fade_color1} ) ) {
                $tmphash{fade_color1} = "0xd0d0d0";
            }
            if ( !defined( $tmphash{fade_color2} ) ) {
                $tmphash{fade_color2} = "0xd0d000";
            }
            if ( !defined( $tmphash{rnd_border} ) ) {
                $tmphash{rnd_border} = 3;
            }
            if ( !defined( $tmphash{alpha} ) ) {
                $tmphash{alpha} = 100;
            }
            if ( !defined( $tmphash{layer} ) ) {
                $tmphash{layer} = "bottom";
            }

            $rectangles_counter{$conttemp}++;
            if ( $rectangles_counter{$conttemp} > 1 ) {
                $shapes{$conttemp} .= "&";
            }
            $total_shapes{$conttemp}++;
            $shapes{$conttemp} .= "rect_$rectangles_counter{$conttemp}=" . $tmphash{x} . ",";
            $shapes{$conttemp} .= $tmphash{y} . ",";
            $shapes{$conttemp} .= $tmphash{width} . ",";
            $shapes{$conttemp} .= $tmphash{height} . ",";
            $shapes{$conttemp} .= $tmphash{line_width} . ",";
            $shapes{$conttemp} .= $tmphash{line_color} . ",";
            $shapes{$conttemp} .= $tmphash{fade_color1} . ",";
            $shapes{$conttemp} .= $tmphash{fade_color2} . ",";
            $shapes{$conttemp} .= $tmphash{rnd_border} . ",";
            $shapes{$conttemp} .= $tmphash{alpha} . ",";
            $shapes{$conttemp} .= $tmphash{layer};
            next CONFIG;
        }

        if ( !defined( $tmphash{position} ) ) {
            log_debug( "** Ignored button $tmphash{'channel'}, position?", 16 ) if DEBUG;
            next CONFIG;
        }

        if ( !defined( $tmphash{alarm} ) ) {
            $tmphash{alarm} = "0";

        }
        if ( !defined( $tmphash{url} ) ) {
            $tmphash{url} = "0";
        }

        if ( !defined( $tmphash{target} ) ) {
            $tmphash{target} = "0";
        }

        if ( !defined( $tmphash{server} ) ) {
            $tmphash{server} = 0;
        }
        else {
            if ( $tmphash{server} eq "*" ) { $tmphash{server} = 0; }
            $tmphash{server} = $tmphash{server} - 1;
        }

        if ( !defined( $tmphash{label} ) ) {
            $tmphash{label} = $tmphash{channel};
        }

        if ( !defined( $tmphash{icon} ) ) {
            $tmphash{icon} = "0";
        }

        # Local channels are case sensitive
        my $canal_key = "";

        if ( $tmphash{channel} =~ m/^local/i ) {
            $canal_key = $tmphash{channel};
        }
        else {
            $canal_key = uc( $tmphash{channel} );
        }

        my $canal_key_case = $tmphash{channel_preserve_case};

        if ( $canal_key =~ m/^PARK\d/ ) {

            # Change the PARKXXX tu PARK/XXX
            $canal_key      =~ s/PARK(.*)/PARK\/$1/g;
            $canal_key_case =~ s/PARK(.*)/PARK\/$1/gi;
        }

        if ( defined( $tmphash{panel_context} ) ) {
            $tmphash{panel_context} =~ tr/a-z/A-Z/;
            $tmphash{panel_context} =~ s/^DEFAULT$//;
        }
        else {
            $tmphash{panel_context} = "";
        }

        if ( $tmphash{panel_context} ne "" ) {

            # We want to add the context in case we have the same button
            # repeated in several panel_contexts. If we do not add it, then
            # only the last panel context will prevail.
            $canal_key      .= "&" . $tmphash{panel_context};
            $canal_key_case .= "&" . $tmphash{panel_context};
        }

        if (   ( $tmphash{position} !~ /,/ )
            && ( $tmphash{position} !~ /-/ )
            && ( $canal_key =~ /^_/ ) )
        {

            # If it's a regexp button with just one position
            # we fake the same position number to populate
            # the array and make the button work anyways.
            my $pos = $tmphash{position};
            $pos =~ s/(\d+),(\d+)/$1/g;
            my $countpos = 2;
            $tmphash{position} = "";
            if ( defined( $tmphash{count} ) ) {
                $countpos = $tmphash{count};
            }
            my $a = 0;
            for ( $a = 0 ; $a < $countpos ; $a++ ) {
                $tmphash{position} .= "$pos,";
            }
            $tmphash{position} = substr( $tmphash{position}, 0, -1 );
            $no_counter = 1;
        }
        else {
            $no_counter = 0;
        }

        if ( $tmphash{position} =~ /[,-]/ ) {

            my $canalidx = $tmphash{server} . "^" . $tmphash{channel};

            if ( defined( $tmphash{panel_context} )
                && $tmphash{panel_context} ne "" )
            {
                $canalidx .= "&" . $tmphash{panel_context};
            }
            $instancias{ uc($canalidx) }{""} = 0;

            my @ranges = split( /,/, $tmphash{position} );
            foreach my $valu (@ranges) {
                if ( $valu !~ m/-/ ) {
                    if ( $valu eq "n" ) {
                        my $lastpos = $lastposition{ $tmphash{panel_context} };
                        if ( is_number($lastpos) ) {
                            $lastpos++;
                            $lastposition{ $tmphash{panel_context} } = $lastpos;
                            $valu = $lastpos;
                            push @positions, $valu;
                            last;
                        }
                    }
                    push @positions, $valu;
                }
                else {
                    my @range2 = split( /-/, $valu );
                    my $menor = $range2[0] < $range2[1] ? $range2[0] : $range2[1];
                    my $mayor = $range2[0] > $range2[1] ? $range2[0] : $range2[1];
                    my @newrange = $menor .. $mayor;
                    foreach my $valevale (@newrange) {
                        push @positions, $valevale;
                    }
                }
            }

            my $count = 0;
            foreach my $pos (@positions) {
                $count++;
                my $indice_contexto = $pos;
                my $chan_trunk      = $tmphash{channel} . "=" . $count;
                my $chan_trunk_case = $tmphash{channel_preserve_case} . "=" . $count;
                if ( $tmphash{panel_context} ne "" ) {
                    $chan_trunk      .= "&" . $tmphash{panel_context};
                    $chan_trunk_case .= "&" . $tmphash{panel_context};
                    $indice_contexto .= "@" . $tmphash{panel_context};
                    $pos             .= "@" . $tmphash{panel_context};
                }
                if ( $chan_trunk =~ m/^QUEUE/i ) {
                    $buttons_queue{ uc("$tmphash{server}^$chan_trunk") } = $pos;
                }
                $buttons_preserve_case{"$tmphash{server}^$chan_trunk_case"} = $pos;
                if ( defined( $tmphash{astdbkey} ) ) {
                    $buttons_astdbkey{"$tmphash{server}^$chan_trunk_case"} = $tmphash{astdbkey};
                }
                $buttons{ uc("$tmphash{server}^$chan_trunk") } = $pos;
                $textos{$indice_contexto} = $tmphash{label};
                if ( !defined( $tmphash{no_label_counter} ) ) { $tmphash{no_label_counter} = 0; }
                if ( $no_counter == 0 && $tmphash{no_label_counter} == 0 ) {
                    $textos{$indice_contexto} .= " " . $count;
                }
                $iconos{$indice_contexto}  = $tmphash{icon};
                $urls{$indice_contexto}    = $tmphash{url};
                $alarms{$indice_contexto}  = $tmphash{alarm};
                $targets{$indice_contexto} = $tmphash{target};
                $button_server{$pos}       = $tmphash{server};

                # Saves last position for the button@context
                $lastposition{ $tmphash{panel_context} } = $pos;
                log_debug( qq[** $tmphash{server}^$chan_trunk in position $pos], 16 ) if DEBUG;
            }
        }
        else {
            my $lastpos = 0;
            $lastpos = $lastposition{ $tmphash{panel_context} }
              if defined( $lastposition{ $tmphash{panel_context} } );
            if ( $tmphash{position} eq "n" ) {
                if ( is_number($lastpos) ) {
                    $lastpos++;
                    $lastposition{ $tmphash{panel_context} } = $lastpos;
                }
            }
            else {
                $lastpos = $tmphash{position};
                $lastposition{ $tmphash{panel_context} } = $lastpos;
            }

            log_debug( qq[** $tmphash{channel} in next position $lastpos], 16 ) if DEBUG;

            if ( $tmphash{panel_context} ne "" ) {

                if ( $canal_key =~ m/^QUEUE/i ) {
                    $buttons_queue{ uc("$tmphash{server}^$canal_key") } = $lastpos . "\@" . $tmphash{panel_context};
                }
                $buttons{"$tmphash{server}^$canal_key"}                    = $lastpos . "\@" . $tmphash{panel_context};
                $buttons_preserve_case{"$tmphash{server}^$canal_key_case"} = $lastpos . "\@" . $tmphash{panel_context};
                if ( defined( $tmphash{astdbkey} ) ) {
                    $buttons_astdbkey{"$tmphash{server}^$canal_key_case"} = $tmphash{astdbkey};
                }

                $textos{"$lastpos\@$tmphash{panel_context}"}              = $tmphash{label};
                $iconos{"$lastpos\@$tmphash{panel_context}"}              = $tmphash{icon};
                $urls{"$lastpos\@$tmphash{panel_context}"}                = $tmphash{url};
                $alarms{"$lastpos\@$tmphash{panel_context}"}              = $tmphash{alarm};
                $targets{"$lastpos\@$tmphash{panel_context}"}             = $tmphash{target};
                $button_server{ $buttons{"$tmphash{server}^$canal_key"} } = $tmphash{server};
            }
            else {
                if ( $canal_key =~ /^_/ ) {
                    $canal_key .= "=1";
                }

                if ( $canal_key =~ m/^QUEUE/i ) {
                    $buttons_queue{ uc("$tmphash{server}^$canal_key") } = $lastpos;
                }
                $buttons{"$tmphash{server}^$canal_key"}                    = $lastpos;
                $buttons_preserve_case{"$tmphash{server}^$canal_key_case"} = $lastpos;
                if ( defined( $tmphash{astdbkey} ) ) {
                    $buttons_astdbkey{"$tmphash{server}^$canal_key_case"} = $tmphash{astdbkey};
                }
                $textos{$lastpos}                                         = $tmphash{label};
                $iconos{$lastpos}                                         = $tmphash{icon};
                $urls{$lastpos}                                           = $tmphash{url};
                $alarms{$lastpos}                                         = $tmphash{alarm};
                $targets{$lastpos}                                        = $tmphash{target};
                $button_server{ $buttons{"$tmphash{server}^$canal_key"} } = $tmphash{server};
            }
        }

        @positions = unique(@positions);

        if ( defined( $tmphash{groupcount} ) ) {
            my $count = @positions;
            if ( $count == 0 ) {
                push @positions, $lastposition{ $tmphash{panel_context} };
            }
            if ( $tmphash{groupcount} eq "true" || $tmphash{groupcount} eq "1" ) {
                my $agre_context = "";
                if ( $tmphash{panel_context} ne "" ) {
                    $agre_context = "\@" . $tmphash{panel_context};
                }
                foreach my $pos (@positions) {
                    $group_count{"$pos$agre_context"} = 1;
                }
            }
        }

        if ( defined( $tmphash{privacy} ) ) {
            my $count = @positions;
            if ( $count == 0 ) {
                push @positions, $lastposition{ $tmphash{panel_context} };
            }
            if ( $tmphash{privacy} eq "true" || $tmphash{privacy} eq "1" ) {
                my $agre_context = "";
                if ( $tmphash{panel_context} ne "" ) {
                    $agre_context = "\@" . $tmphash{panel_context};
                }
                foreach my $pos (@positions) {
                    $clid_private{"$pos$agre_context"} = 1;
                }
            }
        }

        if ( defined( $tmphash{no_rectangle} ) ) {
            my $count = @positions;
            if ( $count == 0 ) {
                push @positions, $lastposition{ $tmphash{panel_context} };
            }

            if ( $tmphash{no_rectangle} eq "true" || $tmphash{no_rectangle} eq "1" ) {
                my $pcont = $tmphash{panel_context};
                if ( $pcont eq "" ) { $pcont = "GENERAL"; }
                foreach my $pos (@positions) {
                    $pos =~ s/\@$pcont//g;
                    $no_rectangle{$pcont}{$pos} = 1;
                }
            }
        }

        if ( defined( $tmphash{background} ) ) {
            my $count = @positions;
            if ( $count == 0 ) {
                push @positions, $lastposition{ $tmphash{panel_context} };
            }

            my $pcont = $tmphash{panel_context};
            if ( $pcont eq "" ) { $pcont = "GENERAL"; }
            foreach my $pos (@positions) {
                $pos =~ s/\@$pcont//g;
                $background{$pcont} .= "&bg$pos=$tmphash{background}";
            }
        }

        if ( defined( $tmphash{extension} ) ) {
            if ( defined( $tmphash{context} ) ) {

                $extension_transfer{"$tmphash{server}^$canal_key"} = $tmphash{server} . "^" . $tmphash{extension} . "@" . $tmphash{context};

            }
            else {
                $extension_transfer{"$tmphash{server}^$canal_key"} = $tmphash{server} . "^" . $tmphash{extension};
            }
            if ( defined( $tmphash{voicemail_context} ) ) {
                $mailbox{"$tmphash{server}^$canal_key"} = $tmphash{extension} . "@" . $tmphash{voicemail_context};
            }
        }
        if ( defined( $tmphash{mailbox} ) ) {
            $mailbox{"$tmphash{server}^$canal_key"} = $tmphash{mailbox};
        }
        if ( defined( $tmphash{voicemailext} ) ) {
            my $indicevm = $lastposition{ $tmphash{panel_context} };
            if ( $tmphash{panel_context} ne "" ) {
                $indicevm .= "\@$tmphash{panel_context}";
            }
            $tovoicemail{$indicevm} = $tmphash{voicemailext};
        }
        if ( defined( $tmphash{spyext} ) ) {
            my $indicespy = $lastposition{ $tmphash{panel_context} };
            if ( $tmphash{panel_context} ne "" ) {
                $indicespy .= "\@$tmphash{panel_context}";
            }
            $tospy{$indicespy} = $tmphash{spyext};
        }

        $/ = "\0";
    }
    %extension_transfer_reverse = reverse %extension_transfer;
    %buttons_reverse            = reverse %buttons;
    %buttons_queue_reverse      = reverse %buttons_queue;
}

sub genera_config {

    # This sub generates the file variables.txt that is read by the
    # swf movie on load, with info about buttons, layout, etc.

    my @textsclients = (
        'detail_title',      'detail_from',        'detail_to',       'security_code_title',
        'btn_security_text', 'btn_restart_text',   'btn_reload_text', 'btn_debug_text',
        'btn_help_text',     'tab_call_text',      'tab_queue_text',  'calls_taken_text',
        'no_data_text',      'debug_window_title', 'detail_duration', 'clid_label',
        'version_mismatch'
    );

    $/ = "\n";
    my %style_variables;
    my @contextos        = ();
    my @unique_contexts  = ();
    my $contextoactual   = "";
    my $highest_position = 0;
    my @style_include    = ();

    foreach my $archi (@styleinclude) {
        open( STYLE, "<$directorio/$archi" )
          or die("Could not open $archi for reading");
        while (<STYLE>) {
            chomp($_);
            $_ =~ s/^\s+//g;
            $_ =~ s/([^;]*)[;](.*)/$1/g;
            $_ =~ s/\s+$//g;
            next unless $_ ne "";

            if (/^\Q[\E/) {
                s/\[(.*)\]/$1/g;
                $contextoactual = $_;
                $contextoactual =~ tr/A-Z/a-z/;
                next;
            }
            if (/^include/i) {

                # Skip include lines
                next;
            }
            $style_variables{$contextoactual} .= $_ . "&";
        }
        close(STYLE);
    }

    for ( keys %textos ) {
        if ( $_ =~ /\@/ ) {
            my @partes = split(/\@/);
            if ( $partes[1] ne "*" ) {
                push( @contextos, $partes[1] );
            }
        }
    }

    push @contextos, "GENERAL";
    @unique_contexts = unique(@contextos);

    # Writes variables.txt for each context defined
    foreach my $contexto_iterate (@unique_contexts) {

        my $directorio   = "";
        my $host_web     = "";
        my $contextlower = $contexto_iterate;
        $contextlower =~ tr/A-Z/a-z/;

        if ( defined( $config->{$contexto_iterate}{flash_dir} ) ) {
            $directorio = $config->{$contexto_iterate}{flash_dir};
        }
        else {
            $directorio = $config->{GENERAL}{flash_dir};
        }

        if ( defined( $config->{$contexto_iterate}{web_hostname} ) ) {
            $host_web = $config->{$contexto_iterate}{web_hostname};
        }
        else {
            $host_web = $web_hostname;
        }

        my $append_filename = "";
        if ( $contexto_iterate ne "GENERAL" ) {
            $append_filename = $contexto_iterate;
        }
        my $flash_context_file = $directorio . "/variables" . $append_filename . ".txt";
        push @all_flash_files, $flash_context_file;
        no warnings "io";
        open( VARIABLES, ">$flash_context_file" )
          or die("Could not write configuration data $flash_context_file.\nCheck your file permissions\n");

        if ( $host_web ne "" ) {
            print VARIABLES "server=$host_web&";
        }
        if ( $listen_port ne "4445" ) {
            print VARIABLES "port=$listen_port&";
        }
        print VARIABLES "restart=$enable_restart";

        if ( defined( $config->{$contexto_iterate}{security_code} ) ) {
            if ( $config->{"$contexto_iterate"}{security_code} eq "" ) {
                print VARIABLES "&nosecurity=1";
            }
        }

        if ( defined( $config->{"$contexto_iterate"}{transfer_timeout} ) ) {
            my @partes = split( /\|/, $config->{"$contexto_iterate"}{transfer_timeout} );
            my $cuantos = @partes;
            print VARIABLES "&totaltimes=$cuantos";
            my $contador = 1;
            foreach my $item_timeout (@partes) {
                print VARIABLES "&timeout_$contador=$item_timeout";
                $contador++;
            }
        }
        else {
            print VARIABLES "&totaltimes=0";
        }

        if ( $no_rectangle{$contexto_iterate} ) {
            my $pos_no_dibujar = "";
            while ( my ( $key, $val ) = each( %{ $no_rectangle{$contexto_iterate} } ) ) {
                $pos_no_dibujar .= "$key,";
            }
            $pos_no_dibujar = substr( $pos_no_dibujar, 0, -1 );
            print VARIABLES "&nodraw=$pos_no_dibujar";
        }

        if ( $background{$contexto_iterate} ) {
            my $pos_no_dibujar = $background{$contexto_iterate};
            print VARIABLES "$pos_no_dibujar";
        }

        while ( my ( $key, $val ) = each(%shapes) ) {
            if ( $key eq $contexto_iterate ) {
                print VARIABLES "&$val";
            }
        }
        while ( my ( $key, $val ) = each(%legends) ) {
            if ( $key eq $contexto_iterate ) {
                print VARIABLES "&$val";
            }
        }
        while ( my ( $key, $val ) = each(%images) ) {
            if ( $key eq $contexto_iterate ) {
                print VARIABLES "&$val";
            }
        }
        $highest_position = 0;
        while ( my ( $key, $val ) = each(%textos) ) {
            $val =~ s/\"(.*)\"/$1/g;
            my $contextoboton = $key;
            if ( $contextoboton =~ m/\@/ ) {
                ( undef, $contextoboton ) = split( /\@/, $contextoboton, 2 );
                $contextoboton =~ tr/a-z/A-Z/;
            }
            else {
                $contextoboton = "GENERAL";
            }
            if ( $contextoboton eq $contexto_iterate ) {
                $key =~ s/(\d+)\@.+/$1/g;
                print VARIABLES "&texto$key=$val";
                if ( $key > $highest_position ) { $highest_position = $key; }
            }
        }
        print VARIABLES "&highestpos=$highest_position";
        while ( my ( $key, $val ) = each(%iconos) ) {
            $val =~ s/\"(.*)\"/$1/g;

            my $contextoboton = $key;
            if ( $contextoboton =~ m/\@/ ) {
                ( undef, $contextoboton ) = split( /\@/, $contextoboton, 2 );
                $contextoboton =~ tr/a-z/A-Z/;
            }
            else {
                $contextoboton = "GENERAL";
            }

            if ( $contextoboton eq $contexto_iterate ) {
                $key =~ s/(\d+)\@.+/$1/g;
                print VARIABLES "&icono$key=$val";
            }
        }
        while ( my ( $key, $val ) = each(%urls) ) {
            $val =~ s/\"(.*)\"/$1/g;
            if ( $val ne "0" ) {
                my $base64_url    = encode_base64($val);
                my $contextoboton = $key;
                if ( $contextoboton =~ m/\@/ ) {
                    ($contextoboton) = split( /\@/, $contextoboton, 2 );
                    $contextoboton =~ tr/a-z/A-Z/;
                }
                else {
                    $contextoboton = "GENERAL";
                }

                if ( $contextoboton eq $contexto_iterate ) {
                    $key =~ s/(\d+)\@.+/$1/g;
                    print VARIABLES "&url$key=$base64_url";
                }
            }
        }
        while ( my ( $key, $val ) = each(%targets) ) {
            $val =~ s/\"(.*)\"/$1/g;

            if ( $val ne "0" ) {
                my $contextoboton = $key;
                if ( $contextoboton =~ m/\@/ ) {
                    ( undef, $contextoboton ) = split( /\@/, $contextoboton, 2 );
                    $contextoboton =~ tr/a-z/A-Z/;
                }
                else {
                    $contextoboton = "GENERAL";
                }

                if ( $contextoboton eq $contexto_iterate ) {
                    $key =~ s/(\d+)\@.+/$1/g;
                    print VARIABLES "&target$key=$val";
                }
            }
        }
        while ( my ( $key, $val ) = each(%alarms) ) {
            $val =~ s/\"(.*)\"/$1/g;
            if ( $val ne "0" ) {
                my $base64_url    = encode_base64($val);
                my $contextoboton = $key;
                if ( $contextoboton =~ m/\@/ ) {
                    ( undef, $contextoboton ) = split( /\@/, $contextoboton, 2 );
                    $contextoboton =~ tr/a-z/A-Z/;
                }
                else {
                    $contextoboton = "GENERAL";
                }

                if ( $contextoboton eq $contexto_iterate ) {
                    $key =~ s/(\d+)\@.+/$1/g;
                    print VARIABLES "&alarm$key=$base64_url\n";
                }
            }
        }

        if ( !defined( $style_variables{$contextlower} ) ) {
            $style_variables{$contextlower} = $style_variables{"general"};
        }
        print VARIABLES "&" . $style_variables{$contextlower};
        if ( !defined( $total_shapes{$contexto_iterate} ) ) {
            $total_shapes{$contexto_iterate} = 0;
        }
        print VARIABLES "total_rectangles=" . $total_shapes{$contexto_iterate};
        if ( !defined( $total_legends{$contexto_iterate} ) ) {
            $total_legends{$contexto_iterate} = 0;
        }
        if ( !defined( $total_images{$contexto_iterate} ) ) {
            $total_images{$contexto_iterate} = 0;
        }

        print VARIABLES "&total_legends=" . $total_legends{$contexto_iterate};
        print VARIABLES "&total_images=" . $total_images{$contexto_iterate};

        foreach my $val (@textsclients) {
            if ( defined( $language->{$contexto_iterate}{$val} ) ) {
                print VARIABLES "&$val=" . $language->{$contexto_iterate}{$val};
            }
            else {
                log_debug( "Language string $val in context $contexto_iterate does not exists", 64 ) if DEBUG;
                print("Language string $val in context $contexto_iterate does not exists\n");
            }
        }
        print VARIABLES "&lang=" . $config->{$contexto_iterate}{language};
        close(VARIABLES);
    }
    $/ = "\0";

}

sub send_reload_to_flash() {
    if ( $reload_pending == 1 ) {
        log_debug( "Pending Reload!", 32 ) if DEBUG;
        foreach my $socket ( keys %keys_socket ) {
            &sends_reload($socket);
        }
        $reload_pending = 0;
    }
}

sub dump_internal_hashes_to_stdout {

    &print_botones(1);

    &print_instancias(1);

    if ( keys(%datos) ) {
        &print_datos(1);
    }
    else {
        print "No data blocks in memory\n";
    }

    if ( keys(%sesbot) ) {
        &print_sesbot(1);
    }
    else {
        print "No data sesiones botones\n";
    }

    if ( keys(%linkbot) ) {
        &print_linkbot();
    }

    &print_cachehit();

    print "\n";
    while ( my ( $key, $val ) = each(%timeouts) ) {
        print "Timer($key)=$val\n";
    }
    print "\n";

    &print_status();

    &print_clients();

    &print_cola_write();

    &print_timers();

}

sub generate_configs_onhup {

    %autosip        = ();
    %autosip_detail = ();
    %cnt_auto_pos   = ();
    $cnt_autosip    = 0;

    %buttons            = ();
    %background         = ();
    %sesbot             = ();
    %linkbot            = ();
    %instancias         = ();
    %textos             = ();
    %iconos             = ();
    %urls               = ();
    %targets            = ();
    %extension_transfer = ();
    %shapes             = ();
    %legends            = ();
    %total_shapes       = ();
    %total_legends      = ();
    @all_flash_files    = ();
    %astdbcommands      = ();

    %estadoboton         = ();
    %botonled            = ();
    %botonvoicemail      = ();
    %botonvoicemailcount = ();
    %botonalpha          = ();
    %botonledcolor       = ();
    %botonqueue          = ();
    %botonqueuemember    = ();
    %botonpark           = ();
    %botonlinked         = ();
    %botonclid           = ();
    %botonmeetme         = ();
    %botontimer          = ();
    %botontimertype      = ();
    %botonlabel          = ();
    %botonsetlabel       = ();
    %botonregistrado     = ();
    @astmanproxy_servers = ();

    &read_buttons_config();
    &read_server_config();
    &read_language_config();
    &read_astdb_config();
    &genera_config();
    &send_initial_status();
}

sub get_next_trunk_button {
    my $canalid        = shift;
    my $contexto       = shift;
    my $server         = shift;
    my $canalsesion    = shift;
    my $canal_tipo_fop = "";
    my $canal;
    my $sesion;
    my $return = "";
    my @uniq;
    my $trunk_pos;
    my $heading = "** GET_NEXT_TRUNK";

    # This routine mantains and returns the position of each channel inside
    # a trunk button.

    log_debug( "$heading START SUB canalid $canalid contexto $contexto server $server canalsesion $canalsesion", 16 )
      if DEBUG;

    if ( $canalid eq "" ) {
        log_debug( "!! ERROR empty canalid ", 64 ) if DEBUG;
        return;
    }

    if ( $canalsesion =~ /</ ) {

        # We want to remove <ZOMBIE> or <MASQ>
        $canalsesion =~ s/([^<]*).*/$1/g;
    }

    if ( $canalid !~ /\^/ ) {
        $canal_tipo_fop = $server . "^" . $canalid;
    }
    else {
        $canal_tipo_fop = $canalid;
        $canalid =~ s/(.*)\^(.*)/$2/g;
    }

    if ( $canal_tipo_fop =~ /\QCAPI[\E/ ) {
        $canal_tipo_fop =~ tr/a-z/A-Z/;
        $canalid        =~ tr/a-z/A-Z/;
    }
    $canal_tipo_fop =~ s/(.*)<(.*)>/$1/g;
    $canal_tipo_fop =~ s/\s+//g;
    $canal_tipo_fop =~ s/(.*)[-\/](.*)/$1/g;
    if ( defined($2) ) {
        $sesion = $2;
    }
    else {
        $sesion = "XXXX";
    }
    $sesion         =~ s/(.*)\&(.*)/$1/g;                               # removes context if it has any
    $canal_tipo_fop =~ s/(\d+\^IAX2\/)([^@]*)(.*)/$1\U$2\E/g;
    $canal_tipo_fop =~ s/(\d+\^IAX2)\[(.*)?@?(.*)?\]?/$1\[\U$2\E\]/g;
    log_debug( "$heading canal_tipo_fop 1 $canal_tipo_fop", 64 ) if DEBUG;

    if ( $canalid =~ /^_.*/ ) {
        $canal_tipo_fop = $canalid;
        if ( $canal_tipo_fop =~ /=/ ) {
            $canal_tipo_fop =~ /([^=].*)(=\d+)(.*)/;
            $canal_tipo_fop = $1;
            if ( defined($3) ) {
                $contexto = $3;
            }
        }
        log_debug( "$heading contexto $contexto", 32 ) if DEBUG;
        my ( undef, $ses ) = separate_session_from_channel($canalsesion);
        $sesion = $ses;
    }
    $canal_tipo_fop =~ tr/a-z/A-Z/;
    log_debug( "$heading canal_tipo_fop 2 $canal_tipo_fop", 64 ) if DEBUG;

    my $canalconcontexto = "";
    if ( $contexto ne "" ) {
        $canalconcontexto = "$canal_tipo_fop$contexto";
    }
    else {
        $canalconcontexto = $canal_tipo_fop;
        $contexto         = "";
    }

    if ( $sesion eq "XXXX" ) {

        # Si la sesion es XXXX devuelve siempre el 1er boton
        log_debug( "$heading return $canal_tipo_fop=1$contexto (1st one)", 64 ) if DEBUG;
        return "$canal_tipo_fop=1$contexto";
    }

    if ( $canalconcontexto !~ /\^/ ) {
        $canalconcontexto = $server . "^" . $canalconcontexto;
    }
    if ( exists( $instancias{"$canalconcontexto"} ) ) {
        if ( exists( $instancias{"$canalconcontexto"}{"$server^$canalsesion"} ) ) {
            log_debug(
"$heading Found instancias($canalconcontexto)($server^$canalsesion)=$instancias{\"$canalconcontexto\"}{\"$server^$canalsesion\"}",
                64
              )
              if DEBUG;
            $trunk_pos = $instancias{"$canalconcontexto"}{"$server^$canalsesion"};
        }
        else {
            log_debug( "$heading Not Found instancias($canalconcontexto)($server^$canalsesion)", 64 ) if DEBUG;
            my %busy_slots = ();
            foreach my $key1 ( sort ( keys(%instancias) ) ) {
                if ( $key1 eq $canalconcontexto ) {
                    foreach my $key2 ( sort ( keys( %{ $instancias{$key1} } ) ) ) {
                        my $indice = $instancias{$key1}{$key2};
                        $busy_slots{$indice} = 1;
                    }
                }
            }
            for ( $trunk_pos = 1 ; ; $trunk_pos++ ) {
                last if ( !exists( $busy_slots{$trunk_pos} ) );
            }
            $instancias{"$canalconcontexto"}{"$server^$canalsesion"} = $trunk_pos;
        }
        $return = "$canal_tipo_fop=${trunk_pos}$contexto";
    }
    return $return;
}

sub separate_session_from_channel {
    my $elemento = shift;
    my $heading  = "** SEPARATE_SESSION_FROM_CHAN";
    log_debug( "$heading elemento1 $elemento", 32 ) if DEBUG;
    if ( $elemento !~ /.*[-\/].*[-\/].+$/ ) {
        if ( $elemento =~ /^OH323/ || $elemento =~ /^\QMODEM[I4l]\E/i ) {
            $elemento =~ s/(.*)\/(.*)/\U$1\E\/${2}-${2}/g;
        }
        elsif ( $elemento =~ m/^\d+/ ) {

            # If the channel is a meetme, do nothing;
        }
        else {
            $elemento .= "-XXXX";
        }
    }
    if ( $elemento =~ /^(?i:mISDN)(?!.*XXXY)/ ) {
        $elemento .= "-XXXY";
    }
    elsif ( $elemento =~ /^SRX/i ) {
        $elemento =~ s/(.*)\/(.*)/\U$1\E\/${2}-1/g;
    }
    elsif ( $elemento =~ /^CAPI\//i ) {
        $elemento =~ s/(CAPI\/)(.*)\/.*-(.*)/$1$2-$3/g;
    }

    $elemento =~ s/^asyncgoto\///gi;
    $elemento =~ s/(.*)[-\/](.*)/$1\t$2/g;
    log_debug( "$heading elemento2 $elemento", 32 ) if DEBUG;
    my $canal  = $1;
    my $sesion = $2;

    if ( defined($canal) && defined($sesion) ) {
        $canal =~ tr/a-z/A-Z/;
        $elemento = $canal . "\t" . $sesion;
        log_debug( "$heading canal $canal sesion $sesion", 32 ) if DEBUG;
    }
    $elemento =~ s/IAX2\[(.*)@(.*)\]\t(.*)/IAX2\[$1\]\t$3/;
    $elemento =~ s/IAX2\/(.*)@(.*)\t(.*)/IAX2\/$1\t$3/;

    return split( /\t/, $elemento );
}

sub peerinfo {
    my $sock  = shift;
    my $short = shift;
    if ( $sock eq "" ) {
        return "";
    }
    if ( defined( $sock->peeraddr ) ) {
        if ( defined($short) ) {
            return $sock->peerhost;
        }
        else {
            return sprintf( "%s:%s", $sock->peerhost, $sock->peerport );
        }

    }
    else {
        return "";
    }
}

sub erase_instances_for_trunk_buttons {

    my $canalid = shift;
    my $canal   = shift;
    my $server  = shift;
    my $canalglobal;
    my $heading = "** ERASE_INSTANCE_TRUNK";

    my $solocanal = $canalid;
    $solocanal =~ s/[^\w]//g;

    $canalid = "$server^$canalid";
    $canalid =~ s/(.*)<(.*)>/$1/g;    #discards ZOMBIE or MASQ

    log_debug( "$heading canalid $canalid canal $canal", 16 ) if DEBUG;

    $canalglobal = $canalid;
    $canalglobal =~ s/(.*)[-\/](.*)/$1/g;
    $canalglobal =~ s/IAX2\/(.*)@(.*)/IAX2\/$1/g;
    $canalglobal =~ s/IAX2\[(.*)@(.*)\]/IAX2\[$1\]/g;

    my ( undef, $contexto ) = split( /\&/, $canal );
    $contexto = $contexto ? "&$contexto" : "";

    my $sesiontemp = $canalid;

    log_debug( "$heading looking for $canalid on instancias to erase it", 128 ) if DEBUG;

    if ( $canal =~ m/^DID|^CLID/ ) {
        $canal =~ s/(.*)=.*/$1/g;
        $canalid = $server . "^" . $canal . "-" . $solocanal;
    }
    foreach my $key1 ( keys(%instancias) ) {
        delete $instancias{$key1}{$canalid};
        log_debug( "$heading Erasing $canalid from instanacias!", 128 ) if DEBUG;
    }
}

sub generate_linked_buttons_list {
    my $nroboton     = shift;
    my $server       = shift;
    my @botonas      = ();
    my $listabotones = "";
    my $heading      = "** GEN_LINK_LIST ";

    log_debug( "$heading canal $nroboton server $server", 16 ) if DEBUG;

    if ( $nroboton !~ /\^/ ) {
        $nroboton = "$server^$nroboton";
    }

    my ( undef, $contexto1 ) = split( /\&/, $nroboton );
    if ( !defined($contexto1) ) { $contexto1 = ""; }

    if ( defined( @{ $linkbot{"$nroboton"} } ) ) {
        log_debug( "$heading Esta definido linkbot {$nroboton}", 32 ) if DEBUG;
        foreach ( @{ $linkbot{"$nroboton"} } ) {
            log_debug( "$heading y contiene $_", 32 ) if DEBUG;
            my ( $canal1, $sesion1 ) = separate_session_from_channel($_);
            log_debug( "$heading luego de separate canal1 = $canal1 y sesion1 = $sesion1", 128 ) if DEBUG;
            my $canalsesion = $_;
            if ( !defined($sesion1) ) {
                $canalsesion = $canal1 . "-XXXX";
            }
            log_debug( "$heading canal1 = $canal1 y sesion1 = $sesion1 canalsesion=$canalsesion", 128 ) if DEBUG;
            my @linkbotones = find_panel_buttons( $canal1, $canalsesion, $server );
            foreach my $cual (@linkbotones) {
                my ( undef, $contexto2 ) = split( /\&/, $cual );
                if ( !defined($contexto2) ) { $contexto2 = ""; }
                if ( $contexto1 eq $contexto2 ) {
                    if ( defined( $buttons{"$server^$cual"} ) ) {
                        my $botinro = $buttons{"$server^$cual"};
                        push @botonas, $botinro;
                        log_debug( "$heading Agrego $botinro", 64 ) if DEBUG;
                    }
                }
            }
        }

        #my %seen2 = ();
        #my @uniq2 = grep { !$seen2{$_}++ } @botonas;
        #@botonas = \@uniq2;
        @botonas = unique(@botonas);

        foreach my $val (@botonas) {
            if ( defined($val) ) {
                $listabotones .= "$val,";
                log_debug( "$heading devuelve $val", 128 ) if DEBUG;
            }
        }
        $listabotones = substr( $listabotones, 0, -1 );
    }
    else {
        log_debug( "$heading NO ESTA DEFINIDO linkbot {$nroboton}", 32 ) if DEBUG;
    }
    return $listabotones;
}

sub erase_all_sessions_from_queues {
    my $canalid     = shift;
    my $canal       = shift;
    my $server      = shift;
    my $canalsesion = $canalid;
    my $heading     = "** ERASE SESSION QUEUE ";
    log_debug( "$heading erase queue $canalid $canal $server", 64 ) if DEBUG;

    for my $mnroboton ( keys %sesbot ) {
        if ( !exists( $buttons_queue_reverse{$mnroboton} ) ) {
            next;
        }
        my @final = ();
        foreach my $msesion ( @{ $sesbot{$mnroboton} } ) {
            log_debug( "$heading $msesion ne $canalsesion?", 64 ) if DEBUG;
            if ( $msesion ne $canalsesion ) {
                log_debug( "$heading sesbot es distinto dejo $msesion en sesbot($mnroboton)", 64 ) if DEBUG;
                push @final, $msesion;
            }
        }
        $sesbot{$mnroboton} = [@final];
    }
}

sub erase_all_sessions_from_channel {
    my $canalid     = shift;
    my $canal       = shift;
    my $server      = shift;
    my $canalsesion = $canalid;
    my @final;
    my @return;
    my $heading = "** ERASE_ALL_SESS_FROM";
    log_debug( "$heading canal $canal canalid $canalid server $server", 16 ) if DEBUG;

    my $indice_cache = $canalid . "-" . $canal . "-" . $server;
    log_debug( "$heading borro cache_hit($indice_cache)", 128 ) if DEBUG;
    delete $cache_hit{$indice_cache};
    delete $monitoring{$canalsesion};
    if ( keys(%cache_hit) ) {
        for ( keys %cache_hit ) {
            if ( defined( @{ $cache_hit{$_} } ) ) {
                foreach my $val ( @{ $cache_hit{$_} } ) {
                    if ( $val eq $canal ) {
                        log_debug( "$heading borro cache $_", 128 ) if DEBUG;
                        delete $cache_hit{$_};
                    }
                }
            }
        }
    }

    if ( $canal =~ /^QUEUE/ ) {

        # QUEUE buttons have a special treatment with sessions (sesbot).
        # we dont want to remove a session (sesbot) from a real button when
        # the channel leaves the queue
        erase_all_sessions_from_queues( $canalid, $canal, $server );
        return;
    }
    else {

        if ( $canal =~ /=/ ) {

            # If its a trunk button, erase instances
            erase_instances_for_trunk_buttons( $canalsesion, $canal, $server );
        }
        $canalsesion =~ s/\t/-/g;
        $canalid     =~ s/(.*)<(.*)>/$1/g;    # Removes <zombie><masq>

        for my $mnroboton ( keys %sesbot ) {
            @final = ();
            foreach my $msesion ( @{ $sesbot{$mnroboton} } ) {
                log_debug( "$heading $msesion ne $canalsesion?", 64 ) if DEBUG;
                if ( $msesion ne $canalsesion ) {
                    log_debug( "$heading sesbot es distinto dejo $msesion a \@final", 64 ) if DEBUG;
                    push @final, $msesion;
                }
            }
            $sesbot{$mnroboton} = [@final];
        }

        if ( keys(%linkbot) ) {
            for ( keys %linkbot ) {
                if ( defined( @{ $linkbot{$_} } ) ) {
                    my @final = ();
                    foreach my $val ( @{ $linkbot{$_} } ) {
                        log_debug( "$heading linkbot($_) ne $val ?", 64 ) if DEBUG;
                        if ( $val ne $canalsesion ) {
                            push @final, $val;
                            log_debug( "$heading No es igual lo dejo $_", 64 ) if DEBUG;
                        }
                        else {
                            push @return, $_;
                            log_debug( "$heading Es igual lo AGREGO RETURN $_", 64 ) if DEBUG;
                        }
                    }

                    log_debug( "$heading delete linkbot($_)", 64 ) if DEBUG;
                    delete $linkbot{$_};
                    $linkbot{$_} = [@final];
                }
            }
        }

        my $quehay = "";
        for $quehay ( keys %datos ) {
            while ( my ( $key, $val ) = each( %{ $datos{$quehay} } ) ) {
                if ( $key eq "Channel" ) {
                    $val =~ s/(.*)[-\/](.*)/$1\t$2/g;
                    $val =~ tr/a-z/A-Z/;
                    if ( $canalid eq $val ) {
                        log_debug( "** Found a match $canalid=$val ($quehay) - Cleared!", 16 ) if DEBUG;
                        delete $datos{$quehay};
                        delete $chanvar{$quehay};
                        delete $passvar{$quehay};
                    }
                }
            }
        }
        for my $valores (@return) {
            log_debug( "$heading devuleve $valores", 64 ) if DEBUG;
        }
        return @return;
    }
}

sub extraer_todas_las_sesiones_de_un_canal {
    my $canal         = shift;
    my $canalbase     = "";
    my $sesion_numero = "";
    my $sesion        = "";
    my $key           = "";
    my $val           = "";
    my $quehay        = "";
    my @result        = ();
    my $heading       = "** EXTRAER_TODAS ";
    log_debug( "$heading from the channel $canal", 16 ) if DEBUG;

    # Removes the context if its set

    ($canal) = split( /&/, $canal );

    # Checks if the channel name has an equal sign
    # (its a trunk button channel)

    if ( $canal =~ /(.*)=(\d+)/ ) {
        ( $canalbase, $sesion_numero ) = split( /\=/, $canal );
        log_debug( "** Its a trunk $canalbase button number $sesion_numero!", 16 ) if DEBUG;

        foreach my $key1 ( sort ( keys(%instancias) ) ) {
            foreach my $key2 ( sort ( keys( %{ $instancias{$key1} } ) ) ) {
                if ( $key2 eq $canalbase ) {
                    push @result, $key2;
                    log_debug( "$heading encontro sesion $canalbase", 16 ) if DEBUG;
                }
            }
        }
    }

    my $cuantos = @result;
    if ( $cuantos == 0 ) {

        # If there is no results for a trunk button, look into the %datos
        # hash.

        for $quehay ( keys %datos ) {
            while ( ( $key, $val ) = each( %{ $datos{$quehay} } ) ) {
                if ( defined($val) ) {
                    my $vel = $val;
                    if ( $vel =~ /^IAX2/ ) {
                        $vel =~ s/IAX2\/(.*)@(.*)\/(.*)/IAX2\/$1\/$3/g;
                        $vel =~ s/IAX2\[(.*)@(.*)\](.*)/IAX2\[$1\]$3/g;
                    }
                    if ( $vel =~ /^\Q$canal\E[-\/]/i && $key eq "Channel" ) {
                        push( @result, $val );
                        log_debug( "** Sesion: $val", 16 ) if DEBUG;
                    }
                }
            }
        }
    }
    return @result;
}

sub extracts_exten_from_active_channel {
    my $canal   = shift;
    my $quehay  = "";
    my @result  = ();
    my $heading = "** EXTRACT_EXTEN ";
    my $server  = "";

    ($canal) = split( /&/, $canal );

    if ( $canal =~ /\^/ ) {
        ( $server, $canal ) = split( /\^/, $canal );
    }

    for $quehay ( keys %datos ) {
        log_debug( "$heading turno de $quehay", 64 ) if DEBUG;
        my $canalaqui  = 0;
        my $serveraqui = 0;
        my $linkeado   = "";
        while ( my ( $key, $val ) = each( %{ $datos{$quehay} } ) ) {

            if ( $val =~ /^$canal-/i && ( $key =~ /^Chan/i || $key =~ /^Link/i ) ) {
                $canalaqui = 1;
            }
            if ( $key =~ /^Server/i && $val == $server ) {
                $serveraqui = 1;
            }
            if ( $key =~ /^Exten/i ) {
                $linkeado = $val;
            }
        }
        if ( $canalaqui == 1 && $linkeado ne "" && $serveraqui == 1 ) {
            log_debug( "$heading devuelvo $linkeado\n", 64 ) if DEBUG;
            push( @result, $linkeado );
        }
    }
    return @result;
}

sub extraer_todos_los_enlaces_de_un_canal {
    my $canal   = shift;
    my $server  = shift;
    my $quehay  = "";
    my @result  = ();
    my $heading = "** EXTRACT_LINKS_CHAN";

    print_datos(1);

    ($canal) = split( /&/, $canal );

    if ( $canal =~ /\^/ ) {
        ( $server, $canal ) = split( /\^/, $canal );
    }

    log_debug( "$heading canal $canal server $server", 1 ) if DEBUG;

    for $quehay ( keys %datos ) {
        my $canalaqui  = 0;
        my $serveraqui = 0;
        my $linkeado   = "";
        my $todo       = "";
        my $eventob    = "";

        log_debug( "$heading turno de $quehay", 128 ) if DEBUG;
        log_debug( "",                          128 ) if DEBUG;
        while ( my ( $key, $val ) = each( %{ $datos{$quehay} } ) ) {
            $todo .= "$key = $val\n";
            log_debug( "$heading buscando $canal en $key=$val", 128 ) if DEBUG;
            if ( ( $val =~ /^$canal-/i || $val =~ /^$canal$/i ) && $key =~ /^Chan/i ) {
                log_debug( "$heading canal coincide $canal = $val\n", 16 ) if DEBUG;
                $canalaqui = 1;
            }
            if ( $key =~ /^Server/i && ( $val eq $server || $server eq "-1" ) ) {
                $serveraqui = 1;
                log_debug( "$heading server coincide $server = $val\n", 16 ) if DEBUG;
            }
            if ( $key =~ /^Link/i ) {
                $linkeado = $val;
            }
            if ( $key =~ /^Event/i ) {
                $eventob = $val;
                log_debug( "$heading eventob = $val\n", 16 ) if DEBUG;
            }
        }
        if ( $canalaqui == 1 && $linkeado ne "" && $serveraqui == 1 && $eventob !~ /agent/i ) {
            push( @result, $linkeado );
            log_debug( "$heading Agrego $linkeado a la lista", 16 ) if DEBUG;
            log_debug( $todo, 16 ) if DEBUG;
        }
    }
    return @result;
}

sub find_panel_buttons {

    # *****************************************************************
    # Based on a CHANNEL name returned by Asterisk, we try to match
    # one or more of our buttons to show status. Returns array with list
    # of channel names as set in op_buttons.cfg

    my $canal       = shift;
    my $canalsesion = shift;
    my $server      = shift;
    my $pos         = 0;
    my $sesion      = "";
    my @canales     = ();
    my @multicanal;
    my $quehay        = "";
    my $canalfinal    = "";
    my $contextoindex = "";
    my $server_boton  = 0;
    my $heading       = "** FIND_PANEL_BUT";
    my $calleridnum   = "noexiste";
    my $didnum        = "noexiste";
    my %trunk_matched = ();

    $tab = $tab . "\t" if DEBUG;
    log_debug( "$heading canal $canal canalsesion $canalsesion server $server", 32 ) if DEBUG;

    if ( $canal eq "" ) {
        $tab = substr( $tab, 0, -1 ) if DEBUG;
        return;
    }

    my $uniqueid = find_uniqueid( $canalsesion, $server );
    if ($uniqueid) {
        if ( defined( $datos{$uniqueid}{CallerID} ) ) {
            $calleridnum = $datos{$uniqueid}{CallerID};
        }
        if ( defined( $datos{$uniqueid}{Extension} )
            && defined( $datos{$uniqueid}{AppData} )
            && $datos{$uniqueid}{AppData} =~ m/^FROM_DID/ )
        {
            $didnum = $datos{$uniqueid}{"Extension"};
        }
    }

    # XXXXX We have to try hard to find a match for the channel
    # There are several posibilities:
    #
    # Exact match:      SIP/jo       (no panel context, not trunk, no wildcard)
    # Panel Ctxt match: SIP/jo&SIP   (exact name, not trunk, no wildcard, panel context)
    # Trunk match:      SIP/jo=1     (exact name, trunk, no wildcard, no panel context)
    # Ctxt&Trunk match: SIP/jo=1&SIP (exact name, trunk, no wildcard, panel context)
    # Wildcard          SIP/*=1      (wildcard name, trunk)
    #
    # The key to match syntax is server^[chan_name|wildcard](=trunk_position)(&panel_context)
    #
    # Here I first will try to match any $buttons that might match the given channel name

    if ( $canalsesion =~ /</ ) {

        # "<" Is an invalid character for a channel name, unless its a zombie
        # or masq, in that case we should discard them
        log_debug( "$heading canalsesion $canalsesion (Se supone que no debo tratar zombies?)", 32 ) if DEBUG;
    }

    $canal = uc($canal);

    # Pass for QUEUEAGENT buttons
    # We should check it no matter the cache because we match also on queue, not only channel
    push @multicanal, $canal;

    if ( defined( $channel_to_agent{"$server^$canal"} ) ) {
        $canal = uc( $channel_to_agent{"$server^$canal"} );
    }

    if ( $queueagent_buttons == 1 ) {

        my $canalsindumy = $canal;
        $canalsindumy =~ s/-FOPdummy$//g;

        log_debug( "$heading trying with QUEUEAGENT buttons", 64 ) if DEBUG;

        if ( keys(%agents_on_queue) ) {
            foreach my $valor ( keys(%agents_on_queue) ) {
                my $cont = 0;
                foreach my $vvalor ( @{ $agents_on_queue{$valor} } ) {
                    $cont++;
                    log_debug( "$heading in queue $valor is '$vvalor' equal to '$server^$canalsindumy'?", 64 ) if DEBUG;

                    if ( uc("$server^$canalsindumy") eq uc($vvalor) ) {
                        my $queuename = $valor;
                        $queuename =~ s/(\d+)\^(.*)/$2/g;
                        my $fake_channel_agent = uc("QUEUEAGENT/$queuename=$cont");

                        foreach my $ctx (@panel_contexts) {
                            my $ctxp = "";
                            if ( $ctx eq "DEFAULT" ) {
                                $ctxp = "";
                            }
                            else {
                                $ctxp = "&$ctx";
                            }
                            my $idx = "$server^$fake_channel_agent" . $ctxp;
                            if ( exists( $buttons{$idx} ) ) {

                                # We have a match! add to return canales
                                push @canales, "$fake_channel_agent$ctxp";
                                log_debug( "$heading we have a winner! $fake_channel_agent $ctxp", 64 ) if DEBUG;
                            }
                        }
                    }
                }
            }
        }
    }

    push @multicanal, $canal;

    if ( $canal =~ /^AGENT/i ) {
        my $canalag = $canal;
        $canalag =~ s/^AGENT/Agent/g;
        if ( defined( $agent_to_channel{"$server^$canalag"} ) ) {
            push @multicanal, uc( $agent_to_channel{"$server^$canalag"} );
            log_debug( "$heading HAY UN AGENTE " . $agent_to_channel{"$server^$canalag"}, 32 );
        }
        else {
            if ( $canal =~ /-FOPdummy$/ ) {
                $canal =~ s/-FOPdummy$//g;
                @multicanal = ($canal);
            }
        }
    }
    else {
        if ( defined( $channel_to_agent{"$server^$canal"} ) ) {
            push @multicanal, $channel_to_agent{"$server^$canal"};
        }
    }

    if ( $canal =~ m/^Local/i ) {
        my $canalsinlocal = $canal;
        $canalsinlocal =~ s/^LOCAL\///gi;
        $canalsinlocal =~ s/(,*)\/n$/$1/gi;
        while ( my ( $key, $val ) = each(%extension_transfer_reverse) ) {
            if ( uc($key) eq "$server^$canalsinlocal" ) {
                my $canalfin = $val;
                $canalfin =~ s/-?\d+\^(.*)/$1/g;
                push @multicanal, $canalfin;
            }
        }
    }

    my $indice_cache = "";
    @multicanal = unique(@multicanal);
    my $server_original = $server;

    foreach my $canal (@multicanal) {

        # Attemp to match a button from cache
        $indice_cache = $canalsesion . "-" . $canal . "-" . $server;
        if ( !defined( $cache_hit{$indice_cache} ) || $didnum ne "noexiste" ) {
            log_debug( "$heading CACHE MISS $indice_cache", 32 ) if DEBUG;
            for ( keys %buttons ) {
                $server     = $server_original;
                $canalfinal = "";
                my ( undef, $contexto ) = split( "\&", $_ );
                if ( !defined($contexto) ) { $contexto = ""; }
                if ( $contexto ne "" ) { $contexto = "&" . $contexto; }
                log_debug( "$heading trying $_", 32 ) if DEBUG;
                if ( $_ =~ /^\Q-1^\E/ ) {
                    log_debug( "$heading IGNORE SERVER! $_", 32 ) if DEBUG;
                    $server = "-1";
                }
                if ( $_ =~ /^\Q$server^$canal\E$/i ) {

                    log_debug( "$heading exact match buttons ( $_ )  $canal $contexto", 32 ) if DEBUG;
                    $canalfinal = $canal;
                }
                elsif ( $_ =~ /^\Q$server^$canal\E\&/i ) {

                    log_debug( "$heading context match buttons ( $_ )  $canal $contexto", 32 ) if DEBUG;
                    $canalfinal = $canal;
                }
                elsif ( $_ =~ /^\Q$server^$canal\E=/i ) {
                    if ( !exists( $trunk_matched{"$server^$canal"} ) ) {
                        $canalfinal = get_next_trunk_button( $canalsesion, $contexto, $server, $canalsesion );
                        if ( $canalfinal ne "" ) {
                            log_debug( "$heading trunk match ( $_ )  $canal $contexto", 32 ) if DEBUG;
                            $trunk_matched{"$server^$canal"} = 1;
                            $canalfinal =~ s/(.*)\^(.*)/$2/g;
                        }
                    }
                }
                elsif ( $_ =~ /^$server\^CLID\/\Q$calleridnum\E=/ ) {
                    my $solocanal = $canalsesion;
                    $solocanal =~ s/[^\w]//g;
                    my $tcanal = "CLID/" . $calleridnum . "-" . $solocanal;
                    if ( !exists( $trunk_matched{"$server^CLID/$calleridnum"} ) ) {
                        $canalfinal = get_next_trunk_button( $tcanal, $contexto, $server, $tcanal );
                        if ( $canalfinal ne "" ) {
                            log_debug( "$heading clid match trunk ( $_ )  $canal $contexto", 32 ) if DEBUG;
                            $trunk_matched{"$server^CLID/$calleridnum"} = 1;
                            $canalfinal =~ s/(.*)\^(.*)/$2/g;
                        }
                    }
                }
                elsif ( $_ =~ /^$server\^CLID\/\Q$calleridnum\E\&?/ ) {
                    log_debug( "$heading clid match ( $_ )  $canal $contexto", 32 ) if DEBUG;
                    $canalfinal = "CLID/$calleridnum";
                }
                elsif ( $_ =~ /^$server\^DID\/\Q$didnum\E=/ ) {
                    my $solocanal = $canalsesion;
                    $solocanal =~ s/[^\w]//g;
                    my $tcanal = "DID/" . $didnum . "-" . $solocanal;
                    if ( !exists( $trunk_matched{"$server^DID/$didnum"} ) ) {
                        $canalfinal = get_next_trunk_button( $tcanal, $contexto, $server, $tcanal );
                        if ( $canalfinal ne "" ) {
                            log_debug( "$heading did match trunk ( $_ )  $canal $contexto", 32 ) if DEBUG;
                            $trunk_matched{"$server^DID/$didnum"} = 1;
                            $canalfinal =~ s/(.*)\^(.*)/$2/g;
                        }
                    }
                }
                elsif ( $_ =~ /^$server\^DID\/\Q$didnum\E\&?/ ) {
                    log_debug( "$heading did match ( $_ )  $canal $contexto", 32 ) if DEBUG;
                    $canalfinal = "DID/$didnum";
                }

                if ( $canalfinal ne "" ) {
                    my $indicefin             = "";
                    my $canalfinalconcontexto = $canalfinal;

                    if ( $canalfinal =~ /\^/ ) {
                        $indicefin = "${canalfinal}";
                    }
                    else {
                        $indicefin = "$server^${canalfinal}";
                    }
                    if ( $indicefin !~ /(.*)\&(.*)$/ ) {
                        $indicefin             = "$indicefin$contexto";
                        $canalfinalconcontexto = "${canalfinal}${contexto}";
                    }

                    if ( exists( $buttons{$indicefin} ) ) {
                        my $posicion = $buttons{$indicefin};
                        $server_boton = $button_server{$posicion};
                        log_debug( "$heading server para $canalfinal = $server_boton", 64 ) if DEBUG;
                    }
                    push @canales, "${canalfinalconcontexto}";
                }
            }
            $canalfinal = "";

            my $contextemp = "";
            my %contextosencontrados;
            for my $val (@canales) {
                ( undef, $contextemp ) = split( "&", $val );
                if ( !defined($contextemp) ) { $contextemp = ""; }
                $contextosencontrados{"&$contextemp"} = 1;
            }

            # Pass for REGEXP buttons
            my $canal_sin_server_ni_contexto = $canal;
            $canal_sin_server_ni_contexto =~ s/(^\d+\^)(.*)(\&.*)?(=\d+)?/$2/;

            if ( $regexp_buttons == 1 ) {
                for ( keys %buttons_preserve_case ) {
                    my $regexp = "";
                    if ( $_ =~ /^\d+\^_/ ) {
                        $regexp = $_;
                        $regexp =~ /^(\d+)\^_([^=&]*)(=[^&]*)?(\&.*)?/;
                        my $serverb = $1;
                        $regexp = $2;
                        my $posicion = $3;
                        my $contexto = "";
                        if ( defined($4) ) {
                            $contexto = $4;
                            if ( defined( $contextosencontrados{$contexto} ) ) {
                                next;
                            }
                        }

                        if ( $canal_sin_server_ni_contexto =~ m/$regexp/i
                            && !exists( $trunk_matched{"$canal_sin_server_ni_contexto^$regexp"} ) )
                        {
                            if ( defined($posicion) ) {

                                # Es un trunk
                                $canalfinal = get_next_trunk_button( $_, $contexto, $server, $canalsesion );
                            }
                            else {

                                # No es un trunk
                                $canalfinal = $_;
                            }
                            if ( $canalfinal ne "" ) {
                                $trunk_matched{"$canal_sin_server_ni_contexto^$regexp"} = 1;
                                push @canales, $canalfinal;
                            }
                        }
                    }
                }
            }
        }
        else {

            # We have a cache match, retrieve the buttons from cache
            my @canales2 = @{ $cache_hit{$indice_cache} };

            # and add the matches from queueagents we might have
            push @canales, @canales2;
            log_debug( "$heading CACHE HIT Retrieving buttons from cache ($indice_cache)", 32 ) if DEBUG;
        }
    }    # end foreach multicanal
    @canales = unique(@canales);

    my $cuantoscanales = @canales;
    if ( $cuantoscanales > 0 ) {
        $cache_hit{$indice_cache} = [@canales];
    }

    my $cuantos = $#canales + 1;
    log_debug( "$heading returns $cuantos", 16 ) if DEBUG;
    foreach (@canales) {
        log_debug( "$heading cache button $_", 32 ) if DEBUG;
    }
    $tab = substr( $tab, 0, -1 );
    return @canales;
}

sub procesa_bloque {
    my $blaque             = shift;
    my $socket             = shift;
    my $astmanproxy_server = shift;
    my %bloque             = %$blaque if defined(%$blaque);

    my %hash_temporal = ();
    my $evento        = "";
    my $canal         = "";
    my $sesion        = "";
    my $texto         = "";
    my $estado_final  = "";
    my $unico_id      = "";
    my $exten         = "";
    my $clid          = "";
    my $clidnum       = "";
    my $clidname      = "";
    my $canalid       = "";
    my $key           = "";
    my $val           = "";
    my @return        = ();
    my $conquien      = "";
    my $enlazado      = "";
    my $viejo_nombre  = "";
    my $nuevo_nombre  = "";
    my $quehay        = "";
    my $elemento      = "";
    my $state         = "";
    my $exists        = 0;
    my $fakecounter   = 1;
    my $fill_datos    = 0;
    my $server        = 0;
    my $timeout       = 0;
    my $heading       = "** PROCESA_BLOQUE";

    $tab = $tab . "\t";
    $hash_temporal{"Event"} = "";

    while ( my ( $key, $val ) = each(%bloque) ) {
        if ( defined($val) ) {
            $val =~ s/\s+$//;
        }
        else {
            $val = "";
        }
        $hash_temporal{$key} = $val;
    }

    if ( defined( $hash_temporal{Application} ) ) {

        # Chanvar hash stores the complete list of channel variables
        # that are "SET" in the dialplan for a given Uniqueid
        if ( $hash_temporal{Application} eq "Set" ) {
            my @vardata = split( /\|/, $hash_temporal{AppData} );
            foreach my $vara (@vardata) {
                my ( $vari, $valu ) = split( /=/, $vara );
                $vari =~ s/^_.?//g;
                $chanvar{ $hash_temporal{Uniqueid} }{$vari} = $valu;
            }
        }
    }
    if ( defined( $hash_temporal{Channel} ) ) {
        if ( $hash_temporal{Channel} =~ /^Agent/ ) {

            # If the channel is Agent/XXXX and we have a real channel
            # in memory, duplicate the event using the real channel
            my $uniagentid = "YYYY";

            my $canalcompara = $hash_temporal{"Channel"};
            $canalcompara =~ s/Agent\/(.*)/$1/g;
            for $quehay ( keys %agent_to_channel ) {
                my $conque = $quehay;
                $conque =~ s/(\d+)\^(.*)/$2/g;
                if ( $canalcompara eq $conque ) {
                    while ( my ( $key, $val ) = each(%hash_temporal) ) {
                        if ( $key ne "Channel" ) {
                            $fake_bloque[$fakecounter]{$key} = $val;
                        }
                    }
                    $fake_bloque[$fakecounter]{"Channel"} = $agent_to_channel{$quehay} . "-XXXX";    # We dont want to add a sesbot
                    $fakecounter++;
                }
            }
        }
    }

    if ( $hash_temporal{"Event"} =~ /^UserEvent/ ) {

        if ( $hash_temporal{"Event"} eq "UserEvent" ) {

            # Asterisk 1.4 Issues a separate header for UserEvent, merge it
            # to make it compatible with the way older versions worked
            $hash_temporal{"Event"} = "UserEvent" . $hash_temporal{UserEvent};
        }

        # This blocks checks if we have an UserEvent
        # and splits every key value pair if it haves
        # a caret as a delimiter
        while ( my ( $key, $val ) = each(%hash_temporal) ) {
            if ( defined($val) && $val =~ /\^/ ) {

                #my @partes = split( /\^/, $val, 2 );
                #$hash_temporal{$key} = $partes[0];
                #my $resto_de_parametros = $partes[1];
                ( $hash_temporal{$key}, my $resto ) = split( /\^/, $val, 2 );
                my @partes = split( /\^/, $resto );
                foreach my $value (@partes) {
                    my @partes2 = split( /: /, $value );
                    if ( !defined( $partes2[0] ) ) { next; }
                    if ( !defined( $partes2[1] ) ) { $partes2[1] = ""; }
                    $hash_temporal{ $partes2[0] } = $partes2[1];
                }
            }
        }
    }

    # Asterisk 1.6 to 1.4 translations
    if ( defined( $hash_temporal{BridgedChannel} ) ) {
        $hash_temporal{Link} = $hash_temporal{BridgedChannel};
    }
    if ( defined( $hash_temporal{ChannelStateDesc} ) ) {
        $hash_temporal{State} = $hash_temporal{ChannelStateDesc};
    }
    if ( defined( $hash_temporal{CallerIDNum} ) ) {
        $hash_temporal{CallerID} = $hash_temporal{CallerIDNum};
    }
    if ( $hash_temporal{Event} eq "Bridge" ) {
        $hash_temporal{Event} = "Link";
    }

    # ********************************
    $canalid = "";
    $canalid = $hash_temporal{Channel}
      if defined( $hash_temporal{Channel} );

    $server = 0;
    $server = $hash_temporal{Server}
      if defined( $hash_temporal{Server} );

    if ( defined( $hash_temporal{Uniqueid} ) ) {
        $unico_id = $hash_temporal{Uniqueid};
        if ( $hash_temporal{Event} !~ /^Originate/ ) {
            $fill_datos = 1;
        }
    }
    else {
        $unico_id = "YYYY";
    }

    $enlazado = "";
    if ( exists( $datos{$unico_id} ) ) {

        if ( exists( $datos{$unico_id}{Link} ) ) {
            $enlazado = $datos{$unico_id}{Link};
        }

        if ( exists( $datos{$unico_id}{Application} ) ) {
            $enlazado .= " - " . $datos{$unico_id}{Application};
        }

        if ( exists( $datos{$unico_id}{AppData} ) ) {
            $enlazado .= ":" . $datos{$unico_id}{AppData};
        }

    }

    if ( $unico_id !~ /-\d+$/ ) {

        # Add the server at the end of the uniqueid
        # if its not already there
        $unico_id .= "-" . $server;
    }

    $evento = "";
    if ( defined( $hash_temporal{"Event"} ) ) {
        $evento = $hash_temporal{"Event"};
    }

    if ( defined( $hash_temporal{"ActionID"} ) ) {
        if ( $hash_temporal{"ActionID"} =~ /^timeout/i ) {
            ( $canalid, $timeout ) = split( /\|/, $hash_temporal{"ActionID"} );
            $evento   = "Timeout";
            $unico_id = "YYYY-$server";
        }
    }

    log_debug( "$heading canalid $canalid unico_id $unico_id evento $evento enlazado $enlazado", 128 ) if DEBUG;

    # Populates a global hash to keep track of
    # 'active' channels, the ones that are not
    # state down.
    if ( defined($unico_id) ) {
        if ( $unico_id !~ /^YYYY/ ) {

            if ($fill_datos) {    # Ignores blocks without Uniqueid
                log_debug( "$heading LLENANDO el global datos $unico_id", 64 ) if DEBUG;
                delete $datos{$unico_id}{State};
                while ( my ( $key, $val ) = each(%hash_temporal) ) {
                    if ( $key eq "Uniqueid" ) {
                        if ( $val !~ /-/ ) {
                            $val .= "-" . $server;
                        }
                    }
                    if ( !defined($val) ) {
                        $val = "";
                    }
                    $datos{$unico_id}{$key} = $val;
                    log_debug( "$heading POPULATES datos($unico_id){ $key } = $val", 128 ) if DEBUG;
                }
            }
        }
        else {
            log_debug( "$heading NO LLENO el global datos $unico_id", 64 ) if DEBUG;
        }
    }

    # Convert Asterisk 1.4 Originate responses to 1.2 format so attendant transfers work
    if ( $evento eq "OriginateResponse" ) {
        if ( $hash_temporal{Response} eq "Success" ) {
            $evento = "OriginateSuccess";
        }
        else {
            $evento = "OriginateFailure";
        }
    }

    # Convert Asterisk 1.4 ParkedCalltimeout to UnParkedCall
    if ( $evento eq "ParkedCallTimeOut" ) {
        $evento = "UnParkedCall";
    }

    $evento =~ s/UserEvent//g;
    if    ( $evento =~ /Newchannel/ )                  { $evento = "newchannel"; }
    elsif ( $evento =~ /Newcallerid/ )                 { $evento = "newcallerid"; }
    elsif ( $evento =~ /^Status$/ )                    { $evento = "status"; }
    elsif ( $evento =~ /^StatusComplete/ )             { $evento = "statuscomplete"; }
    elsif ( $evento =~ /Newexten/ )                    { $evento = "newexten"; }
    elsif ( $evento =~ /^ParkedCall$/ )                { $evento = "parkedcall"; }
    elsif ( $evento =~ /^UnParkedCall$/ )              { $evento = "unparkedcall"; }
    elsif ( $evento =~ /^virtualvaletparkedcall/i )    { $evento = "parkedcall"; }
    elsif ( $evento =~ /^virtualvaletunparkedcall$/i ) { $evento = "unparkedcall"; }
    elsif ( $evento =~ /Newstate/ )                    { $evento = "newstate"; }
    elsif ( $evento =~ /Hangup/ )                      { $evento = "hangup"; }
    elsif ( $evento =~ /Rename/ )                      { $evento = "rename"; }
    elsif ( $evento =~ /MessageWaiting/ )              { $evento = "voicemail"; }
    elsif ( $evento =~ /Regstatus/ )                   { $evento = "regstatus"; }
    elsif ( $evento =~ /^Unlink/ )                     { $evento = "unlink"; }
    elsif ( $evento =~ /QueueParams/ )                 { $evento = "queueparams"; }
    elsif ( $evento =~ /PeerEntry/ )                   { $evento = "peerentry"; }
    elsif ( $evento =~ /QueueEntry/ )                  { $evento = "queueentry"; }
    elsif ( $evento =~ /^QueueMember$/ )               { $evento = "queuemember"; }
    elsif ( $evento =~ /^QueueMemberStatus$/ )         { $evento = "queuememberstatus"; }
    elsif ( $evento =~ /QueueMemberAdded/ )            { $evento = "queuememberadded"; }
    elsif ( $evento =~ /QueueMemberRemoved/ )          { $evento = "queuememberremoved"; }
    elsif ( $evento =~ /QueueMemberPaused/ )           { $evento = "queuememberpaused"; }
    elsif ( $evento =~ /QueueStatus$/ )                { $evento = "queuestatus"; }
    elsif ( $evento =~ /QueueStatusComplete/ )         { $evento = "queuestatuscomplete"; }
    elsif ( $evento =~ /^Link/ )                       { $evento = "link"; }
    elsif ( $evento =~ /^Join/ )                       { $evento = "join"; }
    elsif ( $evento =~ /^MeetmeJoin/ )                 { $evento = "meetmejoin"; }
    elsif ( $evento =~ /^MeetmeLeave/ )                { $evento = "meetmeleave"; }
    elsif ( $evento =~ /^meetmemute/ )                 { $evento = "meetmemute"; }
    elsif ( $evento =~ /^meetmeunmute/ )               { $evento = "meetmeunmute"; }
    elsif ( $evento =~ /^Agentlogin/ )                 { $evento = "agentlogin"; }
    elsif ( $evento =~ /^Agents$/ )                    { $evento = "agents"; }
    elsif ( $evento =~ /^RefreshQueue/ )               { $evento = "refreshqueue"; }
    elsif ( $evento =~ /^Timeout/ )                    { $evento = "timeout"; }
    elsif ( $evento =~ /^AgentCalled/ )                { $evento = "agentcalled"; }
    elsif ( $evento =~ /^AgentConnect/ )               { $evento = "agentconnect"; }
    elsif ( $evento =~ /^AgentComplete/ )              { $evento = "agentcomplete"; }
    elsif ( $evento =~ /^Agentcallbacklogin/ )         { $evento = "agentcblogin"; }
    elsif ( $evento =~ /^Agentcallbacklogoff/ )        { $evento = "agentlogoff"; }
    elsif ( $evento =~ /^Agentlogoff/ )                { $evento = "agentlogoff"; }
    elsif ( $evento =~ /^IsMeetmeMember/ )             { $evento = "fakeismeetmemember"; }
    elsif ( $evento =~ /^PeerStatus/ )                 { $evento = "peerstatus"; }
    elsif ( $evento =~ /^Leave/ )                      { $evento = "leave"; }
    elsif ( $evento =~ /^FOP_Popup/i )                 { $evento = "foppopup"; }
    elsif ( $evento =~ /^FOP_LedColor/i )              { $evento = "fopledcolor"; }
    elsif ( $evento =~ /^Dial/ )                       { $evento = "dial"; }
    elsif ( $evento =~ /^ASTDB/ )                      { $evento = "astdb"; }
    elsif ( $evento =~ /^DNDState/ )                   { $evento = "zapdndstate"; }
    elsif ( $evento =~ /^ZapShowChannels$/ )           { $evento = "zapdndstate"; }
    elsif ( $evento =~ /^ExtensionStatus$/ )           { $evento = "extensionstatus"; }
    elsif ( $evento =~ /^OriginateSuccess$/ )          { $evento = "originatesuccess"; }
    elsif ( $evento =~ /^OriginateFailure$/ )          { $evento = "originatefailure"; }
    elsif ( $evento =~ /^ChannelReload$/ )             { $evento = "channelreload"; }
    elsif ( $evento =~ /^Hold$/ )                      { $evento = "hold"; }
    elsif ( $evento =~ /^Unhold$/ )                    { $evento = "unhold"; }
    elsif ( $evento =~ /^response-/ )                  { $evento = "monitor"; }
    else { log_debug( "$heading No event match ($evento)", 32 ); }

    if ( defined( $hash_temporal{Link} ) ) {
        if ( defined( $hash_temporal{Seconds} ) ) {
            my $unid = "";
            if ( defined( $hash_temporal{BridgedUniqueid} ) ) {
                $unid = $hash_temporal{BridgedUniqueid};
            }
            else {
                $unid = find_uniqueid( $hash_temporal{Link}, $server );
            }
            $fake_bloque[$fakecounter]{Event}    = "Newexten";
            $fake_bloque[$fakecounter]{Channel}  = $hash_temporal{Link};
            $fake_bloque[$fakecounter]{State}    = "Up";
            $fake_bloque[$fakecounter]{Seconds}  = $hash_temporal{Seconds};
            $fake_bloque[$fakecounter]{CallerID} = $hash_temporal{CallerID};
            $fake_bloque[$fakecounter]{Uniqueid} = $unid;
            $fake_bloque[$fakecounter]{Server}   = $hash_temporal{Server};
            $fakecounter++;
            log_debug( "$heading Fake bloque canal $hash_temporal{Link} con seconds $hash_temporal{Seconds}", 128 )
              if DEBUG;
        }
    }

    if ( $evento eq "monitor" ) {
        my $botinro = "";
        ( undef, $estado_final, undef ) = split( /-/, $hash_temporal{ActionID}, 3 );
        ( $canal, undef ) = separate_session_from_channel( $hash_temporal{Channel} );
        $canalid = $hash_temporal{Channel};
        $estado_final =~ tr/A-Z/a-z/;
    }
    elsif ( $evento eq "channelreload" ) {

        # Event: ChannelReload
        # Privilege: system,all
        # Channel: SIP
        # ReloadReason: RELOAD (Channel module reload)
        # Registry_Count: 1
        # Peer_Count: 19
        # User_Count: 7
        $reload_pending = 1;
        &generate_configs_onhup;

    }
    elsif ( $evento eq "originatesuccess" ) {
        if ( defined( $hash_temporal{ActionID} ) ) {
            if ( $hash_temporal{ActionID} =~ /^attendant/ ) {

                # Store the uniqueid for OriginateSuccess for attendant transfers
                # we need it to find the channel in the next newexten event with
                # the same uniqueid
                my $indice = $hash_temporal{Uniqueid} . "-" . $hash_temporal{Server};
                if ( exists( $datos{"$indice"} ) ) {
                    log_debug( "** ATTENDANT we had that uniqueid before, extract the channel from there", 16 )
                      if DEBUG;
                    if ( defined( $datos{$indice}{Extension} ) ) {

                        # if Extension is defined we received the Newexten event before the OriginateSuccess
                        # if not, then we only had a Ringing state without extension/context, so use the one from
                        # this event.
                        $attendant_pending{ $datos{$indice}{Channel} } = $datos{$indice}{Extension} . "@" . $datos{$indice}{Context};
                    }
                    else {
                        $attendant_pending{ $datos{$indice}{Channel} } = $hash_temporal{Exten} . "@" . $hash_temporal{Context};
                    }
                }
                else {
                    log_debug( "** ATTENDANT we do not have any event with that uniqueid, save for later", 16 )
                      if DEBUG;
                    print_datos(99);
                    $pending_uniqueid_attendant{ $hash_temporal{Uniqueid} } = $hash_temporal{Exten} . "@" . $hash_temporal{Context};
                }
            }
        }
        $evento = "";
    }
    elsif ( $evento eq "originatefailure" ) {

        my $contexto = $hash_temporal{ActionID};

        $contexto =~ s/(.*)-(.*)/$2/g;
        $contexto = uc($contexto);
        $canal    = $hash_temporal{Channel};

        my $ext_transf = $extension_transfer{"$server^$canal"};
        $ext_transf =~ s/-?\d+\^(.*)/$1/g;
        my @part_ext = split( /\@/, $ext_transf );

        my $dst_exten   = $hash_temporal{Exten};
        my $dst_context = $hash_temporal{Context};

        if ( defined( $config->{$contexto}{'attendant_failure_redirect_to'} ) ) {

            # If we define a new extension/context, then disengage the originating button
            # from the conference

            my @borrar = ();
            while ( ( $key, $val ) = each(%attendant_pending) ) {
                if ( $val eq "$dst_exten\@$dst_context" ) {
                    push @borrar, $key;
                    log_debug( "Hangup pending attendant channel $key on originate failure", 16 ) if DEBUG;
                    my $comando = "Action: Hangup\r\n";
                    $comando .= "Channel: $key\r\n\r\n";
                    send_command_to_manager( $comando, $socket, 0, $astmanproxy_server );
                }
            }
            foreach (@borrar) {
                delete $attendant_pending{$_};
            }

            if (DEBUG) {
                while ( ( $key, $val ) = each(%mute_other) ) {
                    log_debug( "Mute other after originate failure: $key = $val", 16 );
                }
                while ( ( $key, $val ) = each(%pending_uniqueid_attendant) ) {
                    log_debug( "Pending uniqueid attendant after originate failure: $key = $val", 16 );
                }
            }

            my $tempval = $config->{$contexto}{'attendant_failure_redirect_to'};
            $tempval =~ s/\${CHANNEL}/$hash_temporal{Channel}/g;
            $tempval =~ s/\${EXTEN}/$part_ext[0]/g;
            $tempval =~ s/\${CONTEXT}/$part_ext[1]/g;

            if ( $tempval =~ m/\@/ ) {
                ( $dst_exten, $dst_context ) = split( /\@/, $tempval, 2 );
            }
            else {
                $dst_exten   = $tempval;
                $dst_context = "default";
            }
        }

        log_debug( "Transfer to $dst_exten @ $dst_context after originate failure", 16 ) if DEBUG;

        # Event: OriginateFailure
        # Privilege: call,all
        # ActionID: attendant-general
        # Channel: SIP/17
        # Context: conferences
        # Exten: 901
        # Reason: 5
        # Uniqueid: <null>
        # Server: 0

        if ( $hash_temporal{ActionID} =~ /^attendant/ ) {
            my $room = $hash_temporal{Exten} . "@" . $hash_temporal{Context};
            print "Failure pero tengo mute_other($room) = " . $mute_other{$room} . "\n";
            my $comando = "Action: Redirect\r\n";
            $comando .= "Channel: " . $mute_other{$room} . "\r\n";
            $comando .= "Exten: " . $dst_exten . "\r\n";
            $comando .= "Context: " . $dst_context . "\r\n";
            $comando .= "ActionID: 1234attendant\r\n";
            $comando .= "Priority: 1\r\n\r\n";
            send_command_to_manager( $comando, $socket, 0, $astmanproxy_server );
        }
        $evento = "";

    }
    elsif ( $evento eq "agentcomplete" ) {

        # Hook for queue statistics?
        #Event: AgentComplete
        #Privilege: agent,all
        #Queue: soporte
        #Uniqueid: 1130872017.1364
        #Channel: SIP/16-6b1b
        #HoldTime: 17
        #TalkTime: 557
        #Reason: agent
        my ( $canal, undef ) = separate_session_from_channel( $hash_temporal{Channel} );
        request_queue_status( $socket, $canal );
        my @respuestas = set_queueobject( $server, $canal, "status", 1 );
        foreach (@respuestas) {
            push @return, $_;
        }
    }
    elsif ( $evento eq "PeerlistComplete" ) {

        foreach (@auto_config) {
            my %tmphash = %$_;
            my $srv     = 1;
            if ( defined( $tmphash{server} ) ) {
                $srv = $tmphash{server};
            }
            $srv--;

            if ( $srv == $hash_temporal{Server} ) {
                my $match = $tmphash{channel_preserve_case};
                $match =~ s/^AUTO\///g;
                while ( ( $key, $val ) = each(%autosip) ) {
                    my ( $server, $sipp ) = split( /\^/, $key, 2 );
                    if ( "SIP/$sipp" =~ m/$match/ && $server == $srv ) {
                        $cnt_autosip++;
                        log_debug( "AUTO sip de server $srv $key coincide con $match, envio query detallado autosipentry-$cnt_autosip", 16 )
                          if DEBUG;
                        my $comando = "Action: SIPShowPeer\r\nPeer: $sipp\r\nActionID: autosipentry-$cnt_autosip\r\n\r\n";
                        send_command_to_manager( $comando, $socket, 0, $server );
                    }
                    else {
                        log_debug( "No autosip match $srv $key con $match", 32 ) if DEBUG;
                    }
                }
            }
        }
        $autosip_detail{"autosipentry-$cnt_autosip"} = 1;
        send_reload_to_flash();
    }
    elsif ( $evento eq "sippeerentrylong" ) {

        if ( defined( $hash_temporal{ObjectName} ) ) {

            my $calid               = $hash_temporal{Callerid};
            my $ctx                 = $hash_temporal{Context};
            my $chan_name           = $hash_temporal{ObjectName};
            my $acid                = $hash_temporal{ActionID};
            my $voicemailbox        = $hash_temporal{VoiceMailbox} ? $hash_temporal{VoiceMailbox} : "";
            my $accode              = $hash_temporal{Accountcode} ? $hash_temporal{Accountcode} : "";
            my $voicemailboxnum     = "";
            my $voicemailboxcontext = "";

            my $calidnum  = $calid;
            my $calidname = $calid;

            $calidnum  =~ s/([^<]*)<([^>]*)>/$2/g;
            $calidname =~ s/([^<]*)<([^>]*)>/$1/g;

            if ( $voicemailbox =~ m/\@/ ) {
                ( $voicemailboxnum, $voicemailboxcontext ) = split( /\@/, $voicemailbox, 2 );
            }
            else {
                $voicemailboxnum     = $voicemailbox;
                $voicemailboxcontext = "";
            }
            my $cnt = 0;

            foreach my $auto (@auto_config) {
                my %tmphash = %$auto;
                if ( !defined( $tmphash{server} ) ) { $tmphash{server} = 1; }
                my $srv = $tmphash{server};
                $srv--;
                my $match = $tmphash{channel_preserve_case};
                $match =~ s/^AUTO\///g;
                if ( !defined( $hash_temporal{Server} ) ) {
                    $hash_temporal{Server} = 1;
                }

                if ( $srv == $hash_temporal{Server} && "SIP/$chan_name" =~ m/$match/ ) {
                    while ( ( $key, $val ) = each(%tmphash) ) {
                        $val =~ s/\${CONTEXT}/$ctx/g;
                        $val =~ s/\${CALLERID}/$calid/g;
                        $val =~ s/\${CLIDNUM}/$calidnum/g;
                        $val =~ s/\${CLIDNAME}/$calidname/g;
                        $val =~ s/\${VOICEMAILBOX}/$voicemailbox/g;
                        $val =~ s/\${VOICEMAILBOXNUM}/$voicemailboxnum/g;
                        $val =~ s/\${VOICEMAILBOXCONTEXT}/$voicemailboxcontext/g;
                        $val =~ s/\${ACCOUNTCODE}/$accode/g;
                        $val =~ s/\${CHANNEL}/$chan_name/g;
                        $autosip{"$server^$chan_name"}{$key} = $val;
                    }
                    $autosip{"$server^$chan_name"}{channel}    = "SIP/$chan_name";
                    $autosip{"$server^$chan_name"}{autonumber} = $cnt;
                    delete( $autosip{"$server^$chan_name"}{channel_preserve_case} );

                    delete( $autosip_detail{$acid} );
                    my @quedan = keys(%autosip_detail);

                    # AUTOSIP SUTOSIP
                    my $cuantos_quedan = @quedan;
                    if ( $cuantos_quedan == 0 ) {

                        # Last autosip for each server, it is time to write variables
                        # for this kind
                        read_buttons_config();
                        genera_config();
                        send_initial_status( '', 1 );
                    }

                }
                $cnt++;
            }
        }

    }
    elsif ( $evento eq "peerentry" ) {

        #Event: PeerEntry
        #ActionID: autosip
        #Channeltype: SIP
        #ObjectName: nicocasa
        #ChanObjectType: peer
        #IPaddress: 1.2.3.4
        #IPport: 15540
        #Dynamic: yes
        #Natsupport: yes
        #ACL: no
        #Status: UNREACHABLE

        if ( $hash_temporal{ActionID} eq "autosip" ) {
            my $peer = $hash_temporal{ObjectName};
            $autosip{"$server^$peer"}{server} = $server;
        }
    }
    elsif ( $evento eq "agentcalled" ) {

        # We use this event to send the ringing state for an Agent
        $estado_final = "ringing";
        $canal        = $hash_temporal{"AgentCalled"};
        my $unique = find_uniqueid( $hash_temporal{ChannelCalling}, $server );
        $unique =~ s/(.*)-.*/$1/g;
        $canal  =~ tr/a-z/A-Z/;
        $canalid  = $canal . "-XXXX";
        $clidnum  = $hash_temporal{"CallerID"};
        $clidname = $hash_temporal{"CallerIDName"};
        $texto    = "&incoming,[" . format_clid( $clidnum, $clidname, $clid_format ) . "]";
        my $base64_clidnum  = encode_base64( $clidnum . " " );
        my $base64_clidname = encode_base64( $clidname . " " );

        foreach my $var ( split( /\|/, $passvars ) ) {
            if ( defined( $chanvar{$unique}{$var} ) ) {
                my $base64_var = encode_base64( $chanvar{$unique}{$var} );
                push @return, "$canal|setvar|$var=$base64_var|$canalid-$server|$canalid";
            }
        }
        push @return, "$canal|clidnum|$base64_clidnum|$canalid-$server|$canalid";
        push @return, "$canal|clidname|$base64_clidname|$canalid-$server|$canalid";
        push @return, "$canal|$estado_final|$texto|$canalid-$server|$canalid";
        $evento = "";
    }
    elsif ( $evento eq "agentconnect" ) {

        # We use this event to fake the ringing state
        $estado_final = "ocupado";
        $texto        = "Taking call from " . $hash_temporal{Queue};
        $canal        = $hash_temporal{"Channel"};
        $canal =~ tr/a-z/A-Z/;
        $canalid = $canal . "-XXXX";
        push @return, "$canal|$estado_final|$texto|$canalid-$server|$canalid";                     #NEW
        push @return, "$canal|agentconnect|$hash_temporal{Uniqueid}|$canalid-$server|$canalid";    #NEW

        my $member     = $hash_temporal{Member};
        my $queue      = $hash_temporal{Queue};
        my @respuestas = set_queueobject( $server, $member, "status", 2 );
        foreach (@respuestas) {
            push @return, $_;
        }

        $evento = "";
    }
    elsif ( $evento eq "dial" ) {

        # We use this hashes to store the remote callerid for CVS-HEAD
        my $dorigen  = "";
        my $ddestino = "";

        if ( $hash_temporal{Destination} ) {
            my $key = "$server^$hash_temporal{Destination}";
        }
        $remote_callerid{$key}      = $hash_temporal{CallerID};
        $remote_callerid_name{$key} = $hash_temporal{CallerIDName};

        if ( $hash_temporal{SrcUniqueID} ) {
            if ( $hash_temporal{DestUniqueID} ) { 
               foreach my $var ( split( /\|/, $passvars ) ) {
                 $passvar{$hash_temporal{DestUniqueID}}{$var} = $chanvar{$hash_temporal{SrcUniqueID}}{$var};
               }
            }
            $datos{"$hash_temporal{SrcUniqueID}-$server"}{Origin} = "true";
        }

        if (($hash_temporal{Source}) && ($hash_temporal{Source} =~ m/^Local/i)) {

            # We also look for Dial from Local/XX@context to TECH/XX for
            # matching agentcallbacklogins exten@context to real channels
            # so we can map outgoing calls to Agent buttons
            # It will only work after the agent receives at least one call
            ( $dorigen,  undef ) = separate_session_from_channel( $hash_temporal{Source} );
            ( $ddestino, undef ) = separate_session_from_channel( $hash_temporal{Destination} );
            if ( exists( $channel_to_agent{"$server^$dorigen"} ) ) {
                my $agente = $channel_to_agent{"$server^$dorigen"};

                # delete $channel_to_agent{$dorigen};
                $channel_to_agent{"$server^$ddestino"} = $agente;
            }
        }
    }
    elsif ( $evento eq "zapdndstate" ) {

        $canal = $hash_temporal{"Channel"};
        my $zstatus = "";
        if ( $canal !~ m/Zap/i ) {
            $canal = "Zap/$canal";
        }
        if ( defined( $hash_temporal{Status} ) ) {
            $zstatus = $hash_temporal{Status};
        }
        if ( defined( $hash_temporal{DND} ) ) {
            $zstatus = $hash_temporal{DND};
        }
        if ( $zstatus =~ /disabled/i ) {
            $zstatus = "";
        }

        # If we receive a zap dnd state, we fake the ASTDB
        # event with family 'dnd'. So it will execute the
        # actions listed on op_astdb.cfg inside [dnd]
        $fake_bloque[$fakecounter]{Event}   = "ASTDB";
        $fake_bloque[$fakecounter]{Channel} = $canal;
        $fake_bloque[$fakecounter]{Family}  = "dnd";
        $fake_bloque[$fakecounter]{Server}  = $hash_temporal{Server};
        $fake_bloque[$fakecounter]{Value}   = $zstatus;
        $fakecounter++;

        $evento = "";

    }
    elsif ( $evento eq "astdb" ) {

        my $valor = "";
        $estado_final = "astdb";
        ( $canal, undef ) = separate_session_from_channel( $hash_temporal{"Channel"} );
        $canalid = $hash_temporal{"Channel"} . "-XXXX";
        my $clave = $hash_temporal{"Family"};
        if ( !defined( $hash_temporal{"Value"} ) ) {
            $valor = "";
        }
        else {
            $valor = $hash_temporal{"Value"};
        }

        foreach my $item ( @{ $astdbcommands{$clave} } ) {
            my $item_temp = $item;
            my $datoon    = "";
            my $datooff   = "";
            $item_temp =~ s/\${value}/$valor/g;
            my ( $comando, $datos ) = split( /=/, $item_temp );
            if ( $datos =~ /\|/ ) {
                ( $datoon, $datooff ) = split( /\|/, $datos );
            }
            else {
                $datoon  = $datos;
                $datooff = "";
            }

            if ( $valor ne "" ) {
                push @return, "$canal|$comando|$datoon|$canalid-$server|$canalid";
            }
            else {
                push @return, "$canal|$comando|$datooff|$canalid-$server|$canalid";
            }
        }
        $evento = "";
    }
    elsif ( $evento eq "timeout" ) {
        $estado_final = "timeout";
        $texto        = $timeout;
        my $ahora = time();
        my $unique = find_uniqueid( $canalid, $server );
        $datos{$unique}{"Timeout"} = $ahora + $timeout;
        $timeouts{$canalid} = $ahora + $timeout;
        push @return, "$canal|$estado_final|$texto|$unique|$canalid";    #NEW
        $evento = "";
    }
    elsif ( $evento eq "regstatus" ) {

        # Sends the IP address of the peer to the flash client
        # XXXX It will have to store this value internally in future version
        # to avoid polling asterisk every time
        ( $canal, undef ) = separate_session_from_channel( $hash_temporal{"Channel"} );
        $texto = $hash_temporal{"IP"};
        my $serv = $hash_temporal{"Server"};
        if ($show_ip) {

            # $estado_final = "ip";
            $estado_final = "settext";
            $boton_ip{$canalid} = $texto;
            push @return, "$canal|$estado_final|$texto|$unico_id|$canalid";

            # $evento = "";
        }
    }
    elsif ( $evento eq "fopledcolor" ) {
        my $color = "";
        my $state = "";
        ( $canal, undef ) = separate_session_from_channel( $hash_temporal{"Channel"} );
        $color        = $hash_temporal{Color};
        $state        = $hash_temporal{State};
        $estado_final = "fopledcolor";
        push @return, "$canal|$estado_final|$color^$state|$unico_id|$canalid";
        $evento = "";
    }
    elsif ( $evento eq "foppopup" ) {
        ( $canal, undef ) = separate_session_from_channel( $hash_temporal{"Channel"} );
        my $url    = $hash_temporal{"URL"};
        my $target = $hash_temporal{"Target"};
        my $button = $hash_temporal{"Button"};
        if ( !defined($button) ) { $button = ""; }
        my $data = "$url^$target^$button";
        $estado_final = "foppopup";
        push @return, "$canal|$estado_final|$data|$unico_id|$canalid";
        $evento = "";
    }
    elsif ( $evento eq "refreshqueue" ) {
        ( $canal, undef ) = separate_session_from_channel( $hash_temporal{"Channel"} );

        # Turns off led of the agent that generated the refresh
        if ( $change_led == 1 ) {
            $estado_final = "changelabel" . $change_led;
            push @return, "$canal|$estado_final|original|$unico_id|$canalid";
        }
        request_queue_status( $socket, $hash_temporal{"Channel"} );
        $evento = "";
    }
    elsif ( $evento eq "agentcblogin" ) {
        my $canal      = "";
        my $canallocal = "";
        my $labeltext  = ".";
        my $texto      = $hash_temporal{"Agent"};

        if ( $canalid eq "" ) {
            $canalid = "Agent/$texto-XXXX";
        }
        my @respuestas = set_queueobject( $server, "AGENT/$texto", "status", 1 );
        foreach (@respuestas) {
            push @return, $_;
        }

        if ( defined( $agent_to_channel{"$server^Agent/$texto"} ) ) {

            # The agent was already logged in, fake a logout event

            if ( $ren_agentlogin == 1 || $ren_cbacklogin == 1 || $change_led == 1 ) {

                $canal        = $agent_to_channel{"$server^Agent/$texto"};
                $estado_final = "changelabel" . $change_led;
                if ( $canal =~ /^AGENT/i ) {
                    push @return, "AGENT/$texto|setalpha|100|$unico_id|$canalid";
                }
                else {
                    push @return, "$canal|$estado_final|original|$unico_id|$canalid";

                    # Change back the label to the old localtion/button
                }
                delete $agent_to_channel{"$server^Agent/$texto"};
                delete $channel_to_agent{"$server^$canal"};
            }
        }

        if ( defined( $datos{$unico_id}{"Channel"} ) ) {

            # If we have this defined, its a REAL and LIVE event!
            # so we populate some internal structures. If not, its a fake
            # callbacklogin from the show agents cli command
            ( $canal, undef ) = separate_session_from_channel( $datos{$unico_id}{Channel} );
            $canal =~ tr/a-z/A-Z/;
        }
        else {

            # It is a fake login, probably from a first run (show agents)
            # or a queuemember event. We have the extension@context but not
            # the real channel. In order to monitor OUTBOUND calls, we need
            # to find out the real channel. There is no easy way around this
            # problem. We have to make some assumptions.

            my $ext_transf_key = $hash_temporal{"Server"} . "^" . $hash_temporal{"Loginchan"};

            if ( defined( $extension_transfer_reverse{$ext_transf_key} ) ) {

                # Our first assumption would be to look for a button definition
                # that has the same extension@context and extract the channel name
                # from there. Drawback: there has to be a butotn with the same exten@context
                # in op_buttons.cfg.
                $canal = $extension_transfer_reverse{$ext_transf_key};
                ( $canal, undef ) = split( /&/, $canal );
                if ( $canal =~ /\^/ ) {
                    ( undef, $canal ) = split( /\^/, $canal );
                }
            }
        }

        # We also add a Local channel just in case. It will be used to match but
        # not to keep track of sessions. We skip the agent_to_channel, we dont
        # mind on local channels as monitoring buttons.

        $canallocal = "Local/$hash_temporal{'Loginchan'}";
        $canallocal =~ tr/a-z/A-Z/;
        $channel_to_agent{"$server^$canallocal"} = "Agent/$texto";

        if ( $canal ne "" ) {

            # So, we find a channel. Lets send the responses and fill the hashes

            $channel_to_agent{"$server^$canal"}       = "Agent/$texto";
            $agent_to_channel{"$server^Agent/$texto"} = $canal;

            $estado_final = "changelabel" . $change_led;
            if ( $ren_cbacklogin == 1 ) {
                $labeltext = "Agent/$texto";
                if ( $ren_agentname == 1 ) {
                    if ( defined( $agents_name{"$server^$texto"} ) ) {
                        $labeltext = $agents_name{"$server^$texto"};
                    }
                }
                push @return, "$canal|$estado_final|$labeltext|$unico_id|$canalid";
            }
            if ( $agent_status == 1 ) {
                push @return, "$canal|isagent|0|$unico_id|$canalid";
                push @return, "$canal|settimer|0\@IDLE|$unico_id|$canalid";
                push @return, "$canal|settext|Idle|$unico_id|$canalid";
            }
        }

        # Now send AGENT/ events
        if ( $ren_cbacklogin == 1 ) {
            $labeltext = "Agent/$texto";
            if ( $ren_agentname == 1 ) {
                if ( defined( $agents_name{"$server^$texto"} ) ) {
                    $labeltext = $agents_name{"$server^$texto"};
                }
            }
        }
        push @return, "AGENT/$texto|changelabel$change_led|$labeltext|$unico_id|$canalid";

        if ( $agent_status == 1 ) {
            push @return, "AGENT/$texto|isagent|0|$unico_id|$canalid";
        }

        $evento = "";
    }
    elsif ( $evento eq "queuememberpaused" && $agent_status == 1 ) {
        my $canal   = $hash_temporal{Location};
        my $canalid = $canal . "-XXXX";

        # Paused
        #  Event: QueueMemberPaused
        #  Privilege: agent,all
        #  Queue: soporte
        #  Location: SIP/17
        #  Paused: 1
        #  Server: 0

        # Unpaused
        #  Event: QueueMemberPaused
        #  Privilege: agent,all
        #  Queue: soporte
        #  Location: SIP/17
        #  Paused: 0
        #  Server: 0

        my $color = "";
        if ( $hash_temporal{Paused} ne "0" ) {
            my ( $text, $textriginal, $buttontext ) = translate( $canal, "&paused", "", "" );
            $texto = $text;
            $texto = "&paused";
            $color = "ledcolor_paused";
        }
        else {
            my ( $text, $textriginal, $buttontext ) = translate( $canal, "&idle", "", "" );
            $texto = $text;
            $texto = "&idle";
            $color = "ledcolor_agent";
        }
        my $canset = "";
        $canset = uc($canal);
        my @respuestas = set_queueobject( $server, $canset, "paused", $hash_temporal{Paused} );
        foreach my $item (@respuestas) {
            push @return, $item;
        }
        $boton_ip{$canalid} = $texto;
        push @return, "$canal|settext|$texto|$unico_id|$canalid";
        push @return, "$canal|settimer|1\@UP|$unico_id|$canalid";
        push @return, "$canal|settimer|0\@IDLE|$unico_id|$canalid";
        push @return, "$canal|fopledcolor|$color^2|$unico_id|$canalid";
        push @return, "$canal|state|free|$unico_id|$canalid";
        push @return, "$canal|paused|$hash_temporal{Paused}|$unico_id|$canalid";
        $evento = "";

    }
    elsif ( $evento eq "queuememberremoved" ) {
        my $colar      = $hash_temporal{Queue};
        my $canal      = $hash_temporal{Location};
        my @respuestas = delete_queueobject( $server, $colar, $canal );
        foreach (@respuestas) {
            push @return, $_;
        }

        $fake_bloque[$fakecounter]{Event}   = "Agentlogoff";
        $fake_bloque[$fakecounter]{Channel} = $canal . "-XXXX";
        $fake_bloque[$fakecounter]{Agent}   = $canal;
        $fake_bloque[$fakecounter]{Queue}   = $colar;
        $fake_bloque[$fakecounter]{Fake}    = "removed";
        $fake_bloque[$fakecounter]{Server}  = $hash_temporal{Server};
        $fakecounter++;
        $evento = "";
    }
    elsif ( $evento eq "queuememberadded" ) {

        my $colar      = $hash_temporal{Queue};
        my $canal      = $hash_temporal{Location};
        my @respuestas = add_queueobject( $server, $colar, $canal );
        foreach (@respuestas) {
            push @return, $_;
        }

        $fake_bloque[$fakecounter]{Event}   = "Agentlogin";
        $fake_bloque[$fakecounter]{Channel} = $canal . "-XXXX";
        $fake_bloque[$fakecounter]{Agent}   = $canal;
        $fake_bloque[$fakecounter]{Queue}   = $colar;
        $fake_bloque[$fakecounter]{Server}  = $hash_temporal{Server};
        $fake_bloque[$fakecounter]{Addhash} = 1;
        $fakecounter++;

        $evento = "";

        # Add the channel to the agents_on_queue hash
        ( $server, $canal ) = local_channels_are_driving_me_mad( $server, $canal );
        reserve_next_available_agent_button( $server, $canal, $hash_temporal{Queue} );

        # Remove cache hit to force find panel buttons to look for new positions
        foreach my $kkey ( keys %cache_hit ) {
            if ( $kkey =~ /^$canal/ ) {
                delete $cache_hit{$kkey};
            }
        }

    }
    elsif ( $evento eq "agents" ) {

        #Event: Agents
        #Agent: 609
        #Name: Nicolas
        #Status: AGENT_LOGGEDOFF
        #LoggedInChan: n/a
        #LoggedInTime: 0
        #TalkingTo: n/a
        my $agent_number  = $hash_temporal{Agent};
        my $agent_name    = $hash_temporal{Name};
        my $agent_status  = $hash_temporal{Status};
        my $agent_logchan = $hash_temporal{LoggedInChan};

        $agents_name{"$server^$agent_number"} = $agent_name;
        set_queueobject( $server, "AGENT/$agent_number", "name", $agent_name );

        if ( $agent_status eq "AGENT_IDLE" ) {

            # Agent callback login, idle
            my $agevent = "";
            if ( $agent_logchan =~ /.*\/.*-.*/ ) {
                $fake_bloque[$fakecounter]{"Event"}   = "Agentlogin";
                $fake_bloque[$fakecounter]{"Channel"} = $agent_logchan;
            }
            else {
                $fake_bloque[$fakecounter]{"Event"}     = "Agentcallbacklogin";
                $fake_bloque[$fakecounter]{"Loginchan"} = $agent_logchan;
            }
            $fake_bloque[$fakecounter]{"Agent"}  = $agent_number;
            $fake_bloque[$fakecounter]{"Name"}   = $agent_name;
            $fake_bloque[$fakecounter]{"Server"} = "$server";
            $fakecounter++;
        }
        elsif ( $agent_status eq "AGENT_ONCALL" ) {

            # Agent login
            $fake_bloque[$fakecounter]{Event}   = "Agentlogin";
            $fake_bloque[$fakecounter]{Channel} = $agent_logchan;
            $fake_bloque[$fakecounter]{Agent}   = $agent_number;
            $fake_bloque[$fakecounter]{Server}  = $server;
            $fakecounter++;
        }
        elsif ( $agent_status eq "AGENT_LOGEDOFF" ) {
            $fake_bloque[$fakecounter]{Event}  = "Agentlogoff";
            $fake_bloque[$fakecounter]{Agent}  = $agent_number;
            $fake_bloque[$fakecounter]{Server} = $server;
            $fake_bloque[$fakecounter]{Fake}   = 1;
            $fakecounter++;
        }

    }
    elsif ( $evento eq "agentlogin" ) {

        my $labeltext = ".";
        my $texto     = $hash_temporal{"Agent"};

        if ( defined( $datos{$unico_id}{Channel} ) ) {

            # This catches a live real Agentlogin event
            ( my $canalreal, undef ) = separate_session_from_channel( $datos{$unico_id}{Channel} );
            $canalreal =~ tr/a-z/A-Z/;
            $channel_to_agent{"$server^$canalreal"}   = "Agent/$texto";
            $agent_to_channel{"$server^Agent/$texto"} = $canalreal;
            log_debug( "channel_to_agent($server^$canalreal) = " . $channel_to_agent{"$server^$canalreal"}, 64 )
              if DEBUG;
            if ( !defined( $hash_temporal{Fake} ) || $hash_temporal{Fake} ne "init" ) {
                my @respuestas = set_queueobject( $server, "AGENT/$texto", "status", 1 );
                foreach (@respuestas) {
                    push @return, $_;
                }
            }
        }

        ( $canal, my $sess ) = separate_session_from_channel( $hash_temporal{Channel} );
        $estado_final = "changelabel" . $change_led;

        if ( $sess ne "XXXX" ) {

            # If we have a real session, its an agentlogin on op_server startup (from show agents)
            $channel_to_agent{"$server^$canal"}       = "Agent/$texto";
            $agent_to_channel{"$server^Agent/$texto"} = $canal;
        }

        if ( defined( $agent_label{$canal} ) && ( $ren_agentlogin == 1 || $ren_cbacklogin == 1 ) ) {
            $labeltext = $agent_label{$canal};
        }

        if ( $canalid eq "" ) {
            $canalid = "AGENT/$texto-XXXX";
        }

        if ( $ren_agentlogin == 1 && !defined( $hash_temporal{'Fake'} ) ) {
            if ( $texto !~ /\// ) {
                $labeltext = "Agent/$texto";
            }
            else {
                $labeltext = $texto;
            }
            if ( $ren_agentname == 1 ) {
                if ( defined( $agents_name{"$server^$texto"} ) ) {
                    $labeltext = $agents_name{"$server^$texto"};
                }
            }
        }

        if ( $ren_queuemember == 1 ) {
            if ( $texto !~ /\// ) {
                $labeltext = "Agent/$texto";
            }
            else {
                $labeltext = $texto;
            }
            if ( $ren_agentname == 1 ) {
                if ( defined( $agents_name{"$server^$texto"} ) ) {
                    $labeltext = $agents_name{"$server^$texto"};
                }
            }
        }

        if ( $labeltext eq "original" ) {
            $labeltext = ".";
        }

        if ( $canal =~ m/^AGENT/i ) {
            push @return, "$canal-FOPdummy|setalpha|100|$unico_id|$canalid";

            my $agent_num = $texto;
            $agent_num =~ s/^Agent\///gi;
            if ( $ren_agentname == 1 ) {
                if ( defined( $agents_name{"$server^$agent_num"} ) ) {
                    $labeltext = $agents_name{"$server^$agent_num"};
                }
                else {
                    $labeltext = $texto;
                }
            }
            else {

                # If its an Agent channel, and rename to agent name is not
                # set, rename it anyways to Agent/XXXX
                $labeltext = "Agent/$agent_num";
            }

            # push @return, "$canal-FOPdummy|setlabel|$labeltext|$unico_id|$canalid";

        }
        else {
            push @return, "$canal|$estado_final|$labeltext|$unico_id|$canalid";
            push @return, "$canal|corto||$unico_id|$canalid";
        }

        if ( $agent_status == 1 ) {
            my $textopaused = "&idle";
            if ( defined( $hash_temporal{Paused} ) ) {
                $textopaused = $hash_temporal{Paused};
            }
            if ( defined( $hash_temporal{LastCall} ) ) {
                if ( $hash_temporal{LastCall} > 0 ) {

                    # Max_lastcall saves the maximum epoch time, so I can
                    # show only the lowest lastcall time if an agent is member
                    # or more than one queue
                    if ( !defined( $max_lastcall{$canal} ) ) {
                        $max_lastcall{$canal} = $hash_temporal{LastCall};
                    }
                    if ( $hash_temporal{LastCall} > $max_lastcall{$canal} ) {
                        $max_lastcall{$canal} = $hash_temporal{LastCall} + 0;
                    }
                }
            }
            push @return, "$canal|isagent|0|$unico_id|$canalid";
            push @return, "$canal|settext|$textopaused|$unico_id|$canalid";
        }

        $evento = "";

        $is_agent{ uc("$server^$texto") } = 1;

        if ( defined( $hash_temporal{Queue} ) ) {
            if ( keys(%agents_available_on_queue) ) {
                my $contaconta = 0;
                my %temp_queue = ();
                my $valor      = "$server^$hash_temporal{Queue}";

                push @{ $agents_available_on_queue{$valor} }, "$server^$texto";

                my %count;
                my @unique_queues = grep { ++$count{$_} < 2 } @{ $agents_available_on_queue{$valor} };
                @{ $agents_available_on_queue{$valor} } = @unique_queues;

                if ( exists( $agents_available_on_queue{$valor} ) && $agents_available_on_queue{$valor} ne "" ) {
                    my $texto3 = "";
                    foreach my $qmem ( @{ $agents_available_on_queue{$valor} } ) {
                        $texto3 .= "$qmem\n";
                    }
                    $contaconta = @{ $agents_available_on_queue{$valor} };
                    my $texto2 = "Agents Logged: $contaconta\n" . $texto3 . "  ";
                    $texto2 = encode_base64($texto2);
                    push @return, "QUEUE/" . uc( $hash_temporal{Queue} ) . "|infoqstat2|$texto2|$unico_id|$canalid";
                    print_countqueue("final de agentlogin tenia queue");
                }
            }
        }
    }
    elsif ( $evento eq "agentlogoff" ) {

        $canal = "Agent/" . $hash_temporal{Agent};
        if ( $hash_temporal{Agent} !~ /^Agent/ ) {
            $canal = $hash_temporal{Agent};
        }

        if ( !defined( $hash_temporal{Fake} ) || $hash_temporal{Fake} ne "init" ) {
            my @respuestas = set_queueobject( $server, "AGENT/$canal", "status", 5 );
            foreach (@respuestas) {
                push @return, $_;
            }
        }

        my $texto = $hash_temporal{Agent};
        $canalid = $canal . "-XXXX";

        if ( $ren_agentlogin == 1 || $ren_cbacklogin == 1 || $change_led == 1 ) {
            $estado_final = "changelabel" . $change_led;

            if (   defined( $agent_to_channel{"$server^Agent/$canal"} )
                || defined( $channel_to_agent{"$server^$canal"} ) )
            {
                if ( defined( $agent_to_channel{"$server^Agent/$canal"} ) ) {
                    $canal = $agent_to_channel{"$server^Agent/$canal"};
                }
                else {
                    $canal = $channel_to_agent{"$server^$canal"};
                }

                if ( defined( $agent_label{$canal} ) ) {
                    delete $agent_label{$canal};
                }

                if ( $canalid eq "" ) {
                    $canalid = "AGENT/$texto-XXXX";
                }

                delete $reverse_agents{$texto};
                delete $reverse_agents{$canal};

                push @return, "$canal|$estado_final|original|$unico_id|$canalid";
                push @return, "$canal|agentlogoff|original|$unico_id|$canalid";
            }
            else {
                log_debug( "No esta definido agente $server^$canal", 32 ) if DEBUG;
                push @return, "$canal|$estado_final|original|$unico_id|$canalid";
                push @return, "$canal|agentlogoff|original|$unico_id|$canalid";
            }

            #    if ( defined( $hash_temporal{Fake} ) ) {

            # We dont want queueagent buttons to be renamed back to the orignal label
            #       push @return, "AGENT/$texto|$estado_final|original|$unico_id|$canalid";
            #  }

            if ( $agent_status == 1 ) {
                push @return, "$canal|isagent|-1|$unico_id|$canalid" if ( $canal ne "" );
                push @return, "AGENT/$texto|isagent|-1|$unico_id|$canalid";
            }

            # Its form a removequeuemember, we have to mantain the agents_on_queue hash
            # after finding buttons in digest_event_block, so the led and label go back
            # to normal. If we do it here, we will always have the AGENTQUEUE button marked
            # as an agent even if its removed
            if ( defined( $hash_temporal{Fake} ) && $canal ne "" ) {
                if ( $hash_temporal{Fake} eq "removed" ) {
                    push @return, "$canal|queueremoved|$hash_temporal{Queue}|$unico_id|$canalid";
                }
            }

            $evento = "";
        }

        delete( $is_agent{ uc("$server^$texto") } );

        if ( keys(%agents_available_on_queue) ) {
            print_countqueue("agentlogoff principio agents_available_on_queue");
            my $contaconta = 0;
            my %temp_queue = ();
            my $valor      = "";
            foreach $valor ( sort ( keys(%agents_available_on_queue) ) ) {
                foreach my $vvalor ( @{ $agents_available_on_queue{$valor} } ) {
                    if ( $vvalor !~ /^$server\^$canal$/i && $vvalor !~ /^$server\^AGENT\/$texto$/i ) {
                        push @{ $temp_queue{$valor} }, $vvalor;
                    }
                    my %count;
                    my @unique_queues = grep { ++$count{$_} < 2 } @{ $temp_queue{$valor} };
                    @{ $temp_queue{$valor} } = @unique_queues;
                }
            }
            %agents_available_on_queue = %temp_queue;
            if ( defined( $hash_temporal{Queue} ) ) {
                $valor = $hash_temporal{Queue};
                if ( exists( $agents_available_on_queue{"$server^$valor"} )
                    && $agents_available_on_queue{"$server^$valor"} ne "" )
                {
                    my $texto3 = "";
                    foreach my $qmem ( @{ $agents_available_on_queue{"$server^$valor"} } ) {
                        $texto3 .= "$qmem\n";
                    }
                    $contaconta = @{ $agents_available_on_queue{"$server^$valor"} };
                    my $texto2 = "Agents Logged: $contaconta\n" . $texto3 . "  ";
                    $texto2 = encode_base64($texto2);
                    push @return, "QUEUE/" . uc( $hash_temporal{Queue} ) . "|infoqstat2|$texto2|$unico_id|$canalid";
                    print_countqueue("al final");
                }
            }
        }

    }
    elsif ( $evento eq "queueentry" ) {

        if ( defined( $max_queue_waiting_time_for{"$hash_temporal{Queue}-$hash_temporal{Server}"} ) ) {
            if ( $hash_temporal{Wait} > $max_queue_waiting_time_for{"$hash_temporal{Queue}-$hash_temporal{Server}"} ) {
                $max_queue_waiting_time_for{"$hash_temporal{Queue}-$hash_temporal{Server}"} = $hash_temporal{Wait};
            }
        }
        else {
            $max_queue_waiting_time_for{"$hash_temporal{Queue}-$hash_temporal{Server}"} = $hash_temporal{Wait};
        }
        foreach my $keyh ( keys(%hash_temporal) ) {
            if ( $keyh eq "Event" ) {
                $fake_bloque[$fakecounter]{$keyh} = "Join";
            }
            elsif ( $keyh eq "Position" ) {
                $fake_bloque[$fakecounter]{Count}    = $hash_temporal{$keyh};
                $fake_bloque[$fakecounter]{Position} = $hash_temporal{$keyh};
            }
            else {
                $fake_bloque[$fakecounter]{$keyh} = $hash_temporal{$keyh};
            }
        }
        $fakecounter++;
    }
    elsif ( $evento eq "queuestatuscomplete" ) {
        for my $cola_server ( keys %max_queue_waiting_time_for ) {
            my ( $cola, $server ) = ( $cola_server =~ m/(.*)-(.*)/ );
            push @return, "QUEUE/$cola|settimer|" . $max_queue_waiting_time_for{"$cola-$server"} . "|$cola-$server|QUEUE/$cola-XXXX";
        }

        # Sends the lowest lastcall time of all possible queues
        for my $canala ( keys %max_lastcall ) {
            my $idleseconds = time() - $max_lastcall{$canala};
            push @return, "$canala|settimer|$idleseconds\@IDLE|$unico_id|$canala-XXXX";
        }
        $evento = "";
        my @respuestas = compute_queueobject($server);
        foreach (@respuestas) {
            push @return, $_;
        }
    }
    elsif ( $evento eq "queuemember" || $evento eq "queuememberstatus" ) {

        my $canalag = $hash_temporal{"Location"};
        $canalag =~ tr/a-z/A-Z/;
        my $canalagid  = $canalag . "-XXXX";
        my $unicoag_id = "$canalag-$server";
        $canal = $hash_temporal{"Location"};

        # $agent_status{$hash_temporal{Queue}}{$canalag}=$hash_temporal{Status}; XXXXXXXX NUEVO

        ( $server, $canal ) = local_channels_are_driving_me_mad( $server, $canal );

        if ( $canal =~ /^AGENT/i ) {
            my $temp = $canal;
            $temp =~ s/^AGENT\///gi;

            if ( defined( $hash_temporal{Status} ) && $evento eq "queuemember" ) {
                if ( $hash_temporal{Status} == 5 ) {

                    # If its logged off, fake the event, but only for queuemember
                    $fake_bloque[$fakecounter]{Event}  = "Agentlogoff";
                    $fake_bloque[$fakecounter]{Agent}  = $temp;
                    $fake_bloque[$fakecounter]{Server} = $server;
                    $fake_bloque[$fakecounter]{Fake}   = "init";
                    $fakecounter++;

                    push @return, "$canalag|changelabel$change_led|original|$unicoag_id|$canal-XXXX";
                }
                else {

                    # Generates Fake Agent Login to change led color and label renaming
                    $fake_bloque[$fakecounter]{Event}   = "Agentlogin";
                    $fake_bloque[$fakecounter]{Channel} = $canal . "-XXXX";
                    $fake_bloque[$fakecounter]{Agent}   = $canal;
                    $fake_bloque[$fakecounter]{Fake}    = "init";
                    $fake_bloque[$fakecounter]{Server}  = $server;
                    $fakecounter++;

                    #    push @return, "$canalag|changelabel$change_led|.|$unicoag_id|$canal-XXXX";
                }
            }

            if ( defined( $hash_temporal{Paused} ) ) {
                if ( $evento eq "queuemember" ) {
                    push @return, "$canalag|paused|$hash_temporal{Paused}|$unicoag_id|$canal-XXXX";
                }
            }

        }

        if ( $evento eq "queuemember" ) {

            # We only want to reserve positions on queuemember (initial query status) and
            # to AGENT channels
            reserve_next_available_agent_button( $server, $canal, $hash_temporal{Queue} );
            $is_agent{ uc("$server^$canalag") } = 1;
        }
        if ( $canal !~ /^Local/ ) {
            $canal =~ tr/a-z/A-Z/;
        }
        $estado_final = "info";
        $texto        = "";

        my $contaconta        = 0;
        my $vval              = $hash_temporal{Queue};
        my $has_status_ast_12 = 0;

        while ( ( $key, $val ) = each(%hash_temporal) ) {
            if ( !defined($val) ) { $val = " "; }

            # Status line changes from 1 to 0 on each ring and not taken call, generating
            # an event that we do not really need
            $texto .= "$key = $val\n" if ( $key ne "Status" );
            if ( $key eq "Status" && $val != 5 ) {
                $estado_final .= $vval;
                push @{ $agents_available_on_queue{"$server^$vval"} }, "$server^$canal";
                $has_status_ast_12 = 1;
            }
            if ( $key eq "Status" ) {
                $queue_object->{$server}->{$vval}->{$canal}->{status} = $val;
            }
            if ( $key eq "Paused" ) {
                $queue_object->{$server}->{$vval}->{$canal}->{paused} = $val;
            }
        }
        if ( $has_status_ast_12 == 0 ) {

            # If there is no status on the events, is asterisk stable, count the agent in
            $estado_final .= $vval;
            push @{ $agents_available_on_queue{"$server^$vval"} }, "$server^$canal";
        }
        my %count;
        my @unique_queues = grep { ++$count{$_} < 2 } @{ $agents_available_on_queue{"$server^$vval"} };
        @{ $agents_available_on_queue{"$server^$vval"} } = @unique_queues;
        $contaconta = @{ $agents_available_on_queue{"$server^$vval"} };

        my $texto3 = "";
        foreach my $qmem ( @{ $agents_available_on_queue{"$server^$vval"} } ) {
            $texto3 .= "$qmem\n";
        }
        $unico_id = "$canal-$server";
        my $texto2 = "Agents Logged: $contaconta\n" . $texto3 . "  ";
        $texto .= " ";
        $texto = encode_base64($texto);
        print_countqueue("en queuemember");
        $texto2  = encode_base64($texto2);
        $canalid = $canal . "-XXXX";
        push @return, "$canal|$estado_final|$texto|$unico_id|$canalid";

        if ( $canal !~ /$canalag/i ) {
            push @return, "$canalag|$estado_final|$texto|$unicoag_id|$canalagid";
        }
        if ( $evento eq "queuememberstatus" ) {

            # for each member we sent a message to the queue, on initial status it would make
            # more sense to send the status at the end and not for  every queue member
            push @return, "QUEUE/" . uc( $hash_temporal{Queue} ) . "|infoqstat2|$texto2|$unico_id|$canalid";

            # XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
        }

        if ( $canal !~ /^Local/i && $canal !~ /^Agent/i && $evento eq "queuemember" ) {

            #if (!exists($is_agent{"$server^$canal"})) {
            # If we have a queuemember event and is not recorded as agent, fake login
            my $pausetext = "";
            if ( $hash_temporal{Paused} eq "1" ) {
                $pausetext = "&paused";
            }
            else {
                $pausetext = "&idle";
            }

            # Generates Fake Agent Login to change led color and label renaming
            $fake_bloque[$fakecounter]{Event}   = "Agentlogin";
            $fake_bloque[$fakecounter]{Channel} = $canal . "-XXXX";
            $fake_bloque[$fakecounter]{Agent}   = $canal;
            $fake_bloque[$fakecounter]{Fake}    = "1";
            $fake_bloque[$fakecounter]{Server}  = $server;

            # we sent the folowing two headers (paused,lastcall) to the fake agentlogin
            $fake_bloque[$fakecounter]{Paused}   = $pausetext;
            $fake_bloque[$fakecounter]{LastCall} = $hash_temporal{LastCall};
            $fakecounter++;
        }

        #}
        $evento = "";
    }
    elsif ( $evento eq "queuestatus" ) {
        $canal   = $hash_temporal{Queue};
        $canalid = $canal . "-XXXX";
        print_countqueue("en queuestatus");
        $canal =~ tr/a-z/A-Z/;
        $canal        = "QUEUE/$canal";
        $estado_final = "infoqstat";
        $texto        = "";
        while ( ( $key, $val ) = each(%hash_temporal) ) {
            $texto .= "$key = $val\n";
        }
        $unico_id = "$canal-$server";
        $texto .= " ";
        $texto = encode_base64($texto);
        push @return, "$canal|$estado_final|$texto|$unico_id|$canalid";
        $evento = "";
    }
    elsif ( $evento eq "meetmemute" || $evento eq "meetmeunmute" ) {
        my ( $canal, undef ) = separate_session_from_channel($canalid);
        $estado_final = $evento;
        push @return, "$canal|$evento||$unico_id|$canalid";
        $evento = "";
    }
    elsif ( $evento eq "queueparams" ) {
        $canal = $hash_temporal{Queue};
        $canal =~ tr/a-z/A-Z/;
        $estado_final = "ocupado";
        my $plural = "";
        if ( $hash_temporal{Calls} > 0 ) {
            if ( $hash_temporal{Calls} > 1 ) { $plural = "s"; }
            $texto    = "&waitingonqueue," . $hash_temporal{Calls} . ",$plural&";
            $unico_id = "$canal-$server";
            push @return, "$canal|$estado_final|$texto|$unico_id|$canalid";
        }
        else {

            # If the queue has no users waiting, delete the hash element
            delete $max_queue_waiting_time_for{"$hash_temporal{Queue}-$hash_temporal{Server}"};
        }

        # Generates a Fake Block/Event for sending info status to queues
        while ( ( $key, $val ) = each(%hash_temporal) ) {
            $fake_bloque[$fakecounter]{$key} = $val;
        }
        $fake_bloque[$fakecounter]{Event} = "QueueStatus";
        $fakecounter++;
        $evento = "";
    }
    elsif ( $evento eq "join" ) {
        $canal = "QUEUE/" . $hash_temporal{Queue};
        my $position = $hash_temporal{Position};
        $canal =~ tr/a-z/A-Z/;
        $estado_final = "ocupado1";
        my $plural = "";
        if ( $hash_temporal{Count} > 1 ) { $plural = "s"; }
        $texto    = "&waitingonqueue," . $hash_temporal{Count} . ",$plural&";
        $unico_id = "$canal-$server";

        push @return, "$canal|$estado_final|$texto|$unico_id|$canalid";
        my @queue_events = recompute_queues_onjoin( \%hash_temporal, $server, $canalid );
        foreach my $valor (@queue_events) {
            push @return, $valor;
        }
        $evento = "";
    }
    elsif ( $evento eq "leave" ) {
        $canal = "QUEUE/" . $hash_temporal{"Queue"};
        $canal =~ tr/a-z/A-Z/;
        $estado_final = "ocupado";
        my $plural = "";
        if ( $hash_temporal{"Count"} > 1 )  { $plural       = "s"; }
        if ( $hash_temporal{"Count"} == 0 ) { $estado_final = "corto"; }
        $texto    = "&waitingonqueue," . $hash_temporal{"Count"} . ",$plural&";
        $unico_id = "$canal-$server";
        push @return, "$canal|$estado_final|$texto|$unico_id|$canalid";
        $evento = "";

        print_sesbot(1);
        my @queue_events = recompute_queues_onleave($canalid);
        foreach my $valor (@queue_events) {
            push @return, $valor;
        }
        print_sesbot(2);
    }
    elsif ( $evento eq "meetmejoin" ) {
        my $originate        = "no";
        my $mute_other_party = "no";
        my $contexto         = "";

        if ( $hash_temporal{Channel} =~ /^Local/ ) {

            # We have to ignore Local channels when counting users
            $hash_temporal{Fake} = 1;
        }

        $canal = $hash_temporal{Meetme};
        my $uni_id = $hash_temporal{Uniqueid} . "-" . $server;
        log_debug( "$heading MEETMEJOIN uni_id = $uni_id y canal = $canal", 128 ) if DEBUG;
        $datos{$uni_id}{Extension} = $canal;
        log_debug( "$heading 2 BORRO datos $uni_id { link }", 128 ) if DEBUG;
        delete $datos{$uni_id}{Link};

        $canal =~ tr/a-z/A-Z/;

        for $quehay ( keys %auto_conference ) {
            if ( $quehay eq $hash_temporal{Channel} ) {
                $originate = $auto_conference{"$quehay"};
                $contexto  = $barge_context{$canal};
            }
        }

        for $quehay ( keys %attendant_transfer ) {
            if ( $quehay eq $hash_temporal{Channel} ) {
                $originate        = $attendant_transfer{$quehay};
                $mute_other_party = $mute_other{$quehay};
                $contexto         = $barge_context{$canal};

                # delete mute_other($channel) and set it to mute_other(room)
                # so we can use that for redirecting when attendant_pending
                # hangs up.
                delete $mute_other{$quehay};
                my $indice = $canal . "@" . $config->{$contexto}{'conference_context'};
                $mute_other{$indice} = $mute_other_party;
            }
        }

        my $callerid_attendant   = "";
        my $callerid_attendant_n = "";
        if ( $mute_other_party ne "no" ) {
            my $extendest = $canal;
            my $contedest = $config->{$contexto}{'conference_context'};

            if ( defined( $config->{$contexto}{'attendant_hold_extension'} ) ) {
                $extendest = $config->{$contexto}{'attendant_hold_extension'};
            }

            if ( defined( $config->{$contexto}{'attendant_hold_context'} ) ) {
                $contedest = $config->{$contexto}{'attendant_hold_context'};
            }

            log_debug( "** ATTENDANT Redirect $mute_other_party to MUSIC on HOLD extension", 16 ) if DEBUG;
            for $quehay ( keys %datos ) {
                if ( defined( $datos{$quehay}{Channel} ) ) {
                    if ( $datos{$quehay}{Channel} eq $mute_other_party ) {
                        $callerid_attendant   = $datos{$quehay}{CallerID};
                        $callerid_attendant_n = $datos{$quehay}{CallerIDName};
                    }
                }
            }
            my $comando = "Action: Redirect\r\n";
            $comando .= "Channel: $mute_other_party\r\n";
            $comando .= "Exten: " . $extendest . "\r\n";
            $comando .= "Context: " . $contedest . "\r\n";
            $comando .= "ActionID: 1234attendant\r\n";
            $comando .= "Priority: 1\r\n\r\n";
            send_command_to_manager( $comando, $socket, 0, $astmanproxy_server );
        }

        if ( $originate ne "no" ) {
            log_debug( "$heading origino a meetme en el contexto $contexto!", 128 ) if DEBUG;
            my $comando = "Action: Originate\r\n";
            $comando .= "Channel: $originate\r\n";
            $comando .= "Exten: $canal\r\n";
            $comando .= "Context: " . $config->{$contexto}{'conference_context'} . "\r\n";
            $comando .= "Priority: 1\r\n";
            if ( $callerid_attendant ne "" ) {
                $comando .= "CallerID: $callerid_attendant_n <$callerid_attendant>\r\n";
            }
            if ( $mute_other_party ne "no" ) {
                $comando .= "Async: True\r\n";
                $comando .= "ActionID: attendant-$contexto\r\n";
            }
            $comando .= "\r\n";
            send_command_to_manager( $comando, $socket, 0, $astmanproxy_server );
            if ( $config->{$contexto}{barge_muted} && $mute_other_party eq "no" ) {
                $start_muted{"$server^$originate"} = 1;
            }
        }

        $estado_final = "ocupado9";    # 9 for conference
        my $plural = "";

        if ( !defined( $hash_temporal{Fake} ) ) {
            if ( !defined( $datos{"$canal-$server"}{Count} ) ) {
                $datos{"$canal-$server"}{Count} = 0;
                log_debug( "$heading POPULATES datos($canal-$server){ count } = 0", 64 ) if DEBUG;
            }
            if ( exists( $meetme_pos{"$server^$canal"}{ $hash_temporal{Usernum} } ) ) {

                # Already logged in, buggy channed driver! (SCCP?)
                log_debug( "$heading ignoring already joined channel", 16 );
            }
            else {
                $datos{"$canal-$server"}{Count}++;
            }
            log_debug( "$heading pongo DATOS ($canal-$server) {count} en $datos{\"$canal-$server\"}{Count}", 16 )
              if DEBUG;
        }

        # Its a fake meetmejoin generated from the meetme status at startup
        my ( $canalsinses, $pedses ) = separate_session_from_channel( $hash_temporal{Channel} );
        push @return, "$hash_temporal{Meetme}|setlink|$hash_temporal{Channel}|YYYY-$server|$hash_temporal{Channel}";
        push @return, "$canalsinses|setlink|$hash_temporal{Meetme}|$hash_temporal{Meetme}-$server|$hash_temporal{Channel}";
        push @return, "$canalsinses|meetmeuser|$hash_temporal{Usernum},$hash_temporal{Meetme}|YYYY-$server|$hash_temporal{Channel}";

        if ( defined( $hash_temporal{Total} ) ) {
            $datos{"$canal-$server"}{Count} = $hash_temporal{Total};
            log_debug( "$heading pongo DATOS de ($canal-$server) {count} en $hash_temporal{Total}", 64 ) if DEBUG;
        }

        $barge_rooms{"$canal"} = $datos{"$canal-$server"}{"Count"};

        if ( defined( $datos{"$canal-$server"}{"Count"} ) ) {
            if ( $datos{"$canal-$server"}{"Count"} > 1 ) { $plural = "s"; }
            $texto = "&memberonconference," . $datos{"$canal-$server"}{"Count"} . ",$plural&";
        }

        if ( exists( $start_muted{"$server^$canalsinses"} ) ) {
            my $boton_con_contexto = $buttons{"$server^$canalsinses"};
            my $comando            = "Action: Command\r\n";
            $comando .= "ActionID: meetmemute$boton_con_contexto\r\n";
            $comando .= "Command: meetme mute $hash_temporal{Meetme} $hash_temporal{Usernum}\r\n\r\n";
            send_command_to_manager( $comando, $socket, 0, $astmanproxy_server );
            delete $start_muted{"$server^$canalsinses"};
        }

        $unico_id = $canal . "-" . $server;
        push @return, "$canal|$estado_final|$texto|$unico_id|$canalid";
        $evento = "";

        # From here we send MEETME=POS button responses
        my $position   = $hash_temporal{Usernum};
        my $realunique = "";
        if ( $hash_temporal{Uniqueid} =~ m/(.*)-\d+/ ) {
            $realunique = $hash_temporal{Uniqueid};
        }
        else {
            $realunique = $hash_temporal{Uniqueid} . "-" . $server;
        }
        my $qclidnum  = "";
        my $qclidname = "";
        if ( defined( $datos{$realunique}{CallerIDName} ) ) {
            $qclidname = $datos{$realunique}{CallerIDName};
            $qclidnum  = $datos{$realunique}{CallerID};
        }
        elsif ( defined( $datos{$realunique}{CalleridName} ) ) {
            $qclidname = $datos{$realunique}{CalleridName};
            $qclidnum  = $datos{$realunique}{Callerid};
        }
        else {
            print_datos(99);
            if ( defined( $datos{$realunique}{CallerID} ) ) {
                ( $qclidnum, $qclidname ) = split_callerid( $datos{$realunique}{CallerID} );
            }
            else {
                $qclidnum  = "";
                $qclidname = "";
            }
        }
        my $texto_pos = "[$qclidnum]";
        if ( $qclidnum ne $qclidname ) {
            $texto_pos = "[$qclidname $qclidnum]";
        }
        my $canalfin = get_meetme_pos( $server, $canal, $position );
        my ( $ca1, $se1 ) = separate_session_from_channel( $hash_temporal{Channel} );
        push @return, "$ca1|$estado_final|$texto_pos|YYYY-$server|$hash_temporal{Channel}";
        push @return, "$canalfin|$estado_final|$texto_pos|YYYY-$server|$hash_temporal{Channel}";
        push @return, "$canalfin|meetmeuser|$hash_temporal{Usernum},$hash_temporal{Meetme}|YYYY-$server|$hash_temporal{Channel}";
    }
    elsif ( $evento eq "meetmeleave" ) {
        $canal = $hash_temporal{Meetme};
        $canal =~ tr/a-z/A-Z/;
        $estado_final = "ocupado9";    # 9 for meetme
        my $plural = "";
        $datos{"$canal-$server"}{"Count"}--;
        log_debug( "$heading pongo DATOS ( $canal-$server) (count) en $datos{\"$canal-$server\"}{'Count'} leave", 64 )
          if DEBUG;
        $barge_rooms{$canal} = $datos{"$canal-$server"}{Count};
        if ( $datos{"$canal-$server"}{Count} > 1 )  { $plural       = "s"; }
        if ( $datos{"$canal-$server"}{Count} <= 0 ) { $estado_final = "corto"; }
        $texto    = "&memberonconference," . $datos{"$canal-$server"}{Count} . ",$plural&";
        $unico_id = $canal . "-" . $server;
        push @return, "$canal|$estado_final|$texto|$unico_id|$canalid";
        my $canaleja = $hash_temporal{Channel};
        delete $auto_conference{$canaleja};
        log_debug( "$heading Erasing auto_conference $canaleja", 64 ) if DEBUG;

        for $quehay ( keys %auto_conference ) {
            log_debug( "$heading Remaining conferences: $quehay", 64 ) if DEBUG;
        }

        my ( $canal1, undef ) = separate_session_from_channel($canaleja);
        push @return, "$canal1|unsetlink|$canal|$unico_id|$canalid";
        $evento = "";

        my $canalfin = get_meetme_pos( $server, $canal, $hash_temporal{Usernum} );
        delete $meetme_pos{"$server^$canal"}{ $hash_temporal{Usernum} };
        push @return, "$canalfin|corto||$hash_temporal{Uniqueid}-$server|$canaleja";
    }
    elsif ( $evento eq "voicemail" ) {
        my @canalesvoicemail = ();

        while ( my ( $ecanal, $eextension ) = each(%mailbox) ) {
            if ( $eextension eq $hash_temporal{"Mailbox"} ) {
                $canal = $ecanal;
                $canal =~ s/(.*)\&(.*)/$1/g;    # Remove &context
                $canal =~ s/(.*)\^(.*)/$2/g;    # Remove Server
                push @canalesvoicemail, $canal;
            }
        }
        foreach my $canal (@canalesvoicemail) {
            $unico_id = $canal;
            $canalid  = $canal . "-XXXX";
            if ( defined( $hash_temporal{Waiting} ) ) {

                $estado_final = "voicemail";
                $texto        = $hash_temporal{Waiting};

                if ( $texto eq "0" ) {

                    # If it does not have new voicemail, ask for mailboxcount to get old mails
                    send_command_to_manager( "Action: MailboxCount\r\nMailbox: $hash_temporal{Mailbox}\r\n\r\n",
                        $socket, 0, $astmanproxy_server );
                }
                else {

                    # If it has new voicemail, look for the New and Old headers
                    if ( defined( $hash_temporal{New} ) ) {
                        my $nuevos = $hash_temporal{"New"};
                        my $viejos = $hash_temporal{"Old"};
                        push @return, "$canal|voicemailcount|&newold,$nuevos,$viejos|$unico_id-$server|$canal-XXXX";
                    }
                    else {
                        send_command_to_manager( "Action: MailboxCount\r\nMailbox: $hash_temporal{Mailbox}\r\n\r\n",
                            $socket, 0, $astmanproxy_server );
                    }
                }
            }
            else {

                # This is the actual message count event
                $estado_final = "voicemailcount";
                my $nuevos = $hash_temporal{"NewMessages"};
                my $viejos = $hash_temporal{"OldMessages"};
                $texto   = "&newold,$nuevos,$viejos";
                $canalid = $canal . "-XXXX";
            }
            push @return, "$canal|$estado_final|$texto|$unico_id-$server|$canalid";
        }
        $evento = "";
    }
    elsif ( $evento eq "link" ) {
        my $uniqueid1 = $hash_temporal{"Uniqueid1"};
        my $uniqueid2 = $hash_temporal{"Uniqueid2"};
        if ( $uniqueid1 !~ /-\d+$/ ) {
            $uniqueid1 .= "-" . $server;
        }
        if ( $uniqueid2 !~ /-\d+$/ ) {
            $uniqueid2 .= "-" . $server;
        }
        my $channel1 = $hash_temporal{"Channel1"};
        my $channel2 = $hash_temporal{"Channel2"};

        log_debug( "$heading DATOS de $uniqueid1 { link } en $channel2", 64 ) if DEBUG;
        log_debug( "$heading DATOS de $uniqueid2 { link } en $channel1", 64 ) if DEBUG;
        $datos{$uniqueid1}{"Link"} = $channel2;
        $datos{$uniqueid2}{"Link"} = $channel1;
        my ( $canal1, $sesion1 ) = separate_session_from_channel($channel1);
        my ( $canal2, $sesion2 ) = separate_session_from_channel($channel2);

        my $channel1conses = $channel1;
        if ( $channel1 !~ /${sesion1}$/ && $channel1 !~ /^mISDN/i ) {
            $channel1conses = "$canal1-$sesion1";
        }

        my $channel2conses = $channel2;
        if ( $channel2 !~ /${sesion2}$/ && $channel2 !~ /^mISDN/i ) {
            my $channel2conses = "$canal2-$sesion2";
        }

        # push @return, "$canal1|link|$canal2|$uniqueid2|${canal1}-XXXX";
        # delete $datos{$unico_id};
        # print "3 BORRO datos $unico_id \n";
        $evento       = "";
        $canal        = $canal1;
        $estado_final = "ocupado7";    # 7 for linked channel, start billing

        if ( exists( $parked{"$server^$channel1"} ) ) {
            log_debug( "$heading EXISTE parked{$server^$channel1} ", 128 ) if DEBUG;
            my $parkexten = $parked{"$server^$channel1"};
            delete $parked{"$server^$channel1"};
            push @return, "PARK/$parkexten|corto||YYYY-$server|$channel1conses";
            push @return, "$canal1|ocupado5||$uniqueid1|$channel1conses";
        }
        else {
            log_debug( "$heading NO EXISTE parked{$server^$channel1}", 128 ) if DEBUG;
        }

        if ( exists( $parked{"$server^$channel2"} ) ) {
            log_debug( "$heading EXISTE parked{$server^$channel2} ", 128 ) if DEBUG;
            log_debug( "$heading SI EXISTE!",                        128 ) if DEBUG;
            my $parkexten = $parked{"$server^$channel2"};
            delete $parked{"$server^$channel2"};
            push @return, "PARK/$parkexten|corto||YYYY-$server|$channel2conses";
            push @return, "$canal2|ocupado5||$uniqueid2|$channel2conses";
        }
        else {
            log_debug( "$heading NO EXISTE parked{$server^$channel2}", 128 ) if DEBUG;
        }
        my $clid1 = "";
        my $clid2 = "";

        if ( defined( $datos{$uniqueid2}{"Callerid"} ) ) {
            $clid1 = $datos{$uniqueid2}{"Callerid"};
        }
        if ( defined( $datos{$uniqueid2}{"CallerID"} ) ) {
            $clid1 = $datos{$uniqueid2}{"CallerID"};
        }
        if ( defined( $datos{$uniqueid1}{"Callerid"} ) ) {
            $clid2 = $datos{$uniqueid1}{"Callerid"};
        }
        if ( defined( $datos{$uniqueid1}{"CallerID"} ) ) {
            $clid2 = $datos{$uniqueid1}{"CallerID"};
        }
        my $sclidname1 = "";
        my $sclidname2 = "";

        if ( defined( $hash_temporal{Privilege} ) ) {

            # Fake events do not have the Privilege set, so, do not send the settext
            # with callerid on fake link events.
            if ( $clid1 ne "" && $channel2 ne "" ) {
                $sclidname1 = $saved_clidname{"$server^$channel2"};
                my $clid_with_format = format_clid( $clid1, $sclidname1, $clid_format );
                push @return, "$canal1|setclid|$clid_with_format|$uniqueid1|$channel1conses";
            }
            if ( $clid2 ne "" && $channel1 ne "" ) {
                $sclidname2 = $saved_clidname{"$server^$channel1"};
                my $clid_with_format = format_clid( $clid2, $sclidname2, $clid_format );
                push @return, "$canal2|setclid|$clid_with_format|$uniqueid2|$channel2conses";
            }
        }

        push @return, "$canal1|setlink|$channel2|$uniqueid1|$channel1conses";
        push @return, "$canal2|setlink|$channel1|$uniqueid2|$channel2conses";
        $evento = "";    #NEW
    }
    elsif ( $evento eq "unlink" ) {
        my $uniqueid1 = $hash_temporal{Uniqueid1};
        my $uniqueid2 = $hash_temporal{Uniqueid2};
        my $channel1  = $hash_temporal{Channel1};
        my $channel2  = $hash_temporal{Channel2};
        my ( $canal1, $sesion1 ) = separate_session_from_channel($channel1);
        my ( $canal2, $sesion2 ) = separate_session_from_channel($channel2);

        my $channel1conses = $channel1;
        if ( $channel1 !~ /${sesion1}$/ ) {
            my $channel1conses = "$canal1-$sesion1";
        }

        my $channel2conses = $channel2;
        if ( $channel2 !~ /${sesion2}$/ ) {
            my $channel2conses = "$canal2-$sesion2";
        }

        log_debug( "$heading Unlink $canal1 and $canal2", 64 ) if DEBUG;
        $evento = "";

        $estado_final = "unsetlink";
        $canal        = $canal1;

        my $boton1 = 0;
        my $boton2 = 0;

        for my $mnroboton ( keys %sesbot ) {
            foreach my $msesion ( @{ $sesbot{$mnroboton} } ) {
                if ( $msesion eq $channel1 ) {
                    $boton1 = $mnroboton;
                }
                if ( $msesion eq $channel2 ) {
                    $boton2 = $mnroboton;
                }
            }
        }

        #push @return, "$boton1|unsetlink|$boton2|$uniqueid1|$channel2";
        #push @return, "$boton2|unsetlink|$boton1|$uniqueid2|$channel1";
        push @return, "$canal1|unsetlink|$channel2|$uniqueid1-$server|$channel1conses";
        push @return, "$canal2|unsetlink|$channel1|$uniqueid2-$server|$channel2conses";
        $evento = "";    #NEW
    }
    elsif ( $evento eq "rename" ) {
        my $nuevo_nombre = "";
        my $viejo_nombre = "";
        log_debug( "$heading RENAME Event", 32 ) if DEBUG;
        $evento = "";
        while ( ( $key, $val ) = each(%hash_temporal) ) {
            if ( $key =~ /newname/i ) {
                $nuevo_nombre = $val;
            }
            if ( $key =~ /oldname/i ) {
                $viejo_nombre = $val;
            }
        }
        log_debug( "$heading RENAME $viejo_nombre por $nuevo_nombre (id $unico_id)", 64 ) if DEBUG;

        if ( $nuevo_nombre =~ /<ZOMBIE>/ ) {
            log_debug( "$heading $nuevo_nombre, asterisk bug, sometimes misses the hangup, so we fake it", 64 )
              if DEBUG;
            my ( $canalnuevo, undef ) = separate_session_from_channel($nuevo_nombre);
            push @return, "$canalnuevo|corto||$unico_id|$nuevo_nombre";
        }

        my @final = ();

        # A rename means that the channel was masqueraded, or going somewhere else
        # we dont want to fiddle with sesbot variables. Because the new/old names
        # maybe we want to REMOVE the old name from sesbot

        for my $mnroboton ( keys %sesbot ) {
            @final = ();
            foreach my $msesion ( @{ $sesbot{$mnroboton} } ) {
                if ( $msesion ne $viejo_nombre ) {
                    push @final, $msesion;
                }
            }
            $sesbot{$mnroboton} = [@final];
        }

        # We need to remove the channel from the cola hash
        $cola->{$nuevo_nombre} = $cola->{$viejo_nombre};
        delete $cola->{$viejo_nombre};

        for my $mnroboton ( keys %linkbot ) {
            @final = ();
            foreach my $msesion ( @{ $linkbot{$mnroboton} } ) {
                log_debug( "$heading RENAME iteracion cada linkbot($mnroboton)", 32 ) if DEBUG;
                if ( $msesion ne $viejo_nombre ) {
                    push @final, $msesion;
                    log_debug( "$heading RENAME dejo $msesion en linkbot($mnroboton)", 32 ) if DEBUG;
                }
                else {
                    &print_sesbot(20);
                    log_debug( "$heading RENAME viejo $viejo_nombre no va, en realidad va $nuevo_nombre", 32 )
                      if DEBUG;
                    push @final, $nuevo_nombre;
                    $estado_final = "setlink";
                    my $botoncambiado = $buttons{"$mnroboton"};
                    log_debug( "$heading RENAME buttons($mnroboton)",       32 ) if DEBUG;
                    log_debug( "$heading RENAME sesbot($botoncambiado)[0]", 32 ) if DEBUG;
                    my $canalcambiado = $sesbot{$botoncambiado}[0];

                    if ( defined($canalcambiado) ) {
                        my ( $canalito, undef ) = separate_session_from_channel($canalcambiado);
                        push @return, "$canalito|$estado_final|$nuevo_nombre|$unico_id|$canalcambiado";
                        ( $canalito, undef ) = separate_session_from_channel($nuevo_nombre);
                        push @return, "$canalito|$estado_final|$canalcambiado|$unico_id|$nuevo_nombre";
                        $canal = $canalito;
                    }
                }
            }
            $linkbot{$mnroboton} = [@final];
        }

        for $quehay ( keys %datos ) {
            while ( ( $key, $val ) = each( %{ $datos{$quehay} } ) ) {
                if ( ( $key eq "Channel" ) && ( $val eq $viejo_nombre ) ) {
                    $datos{"$quehay"}{"$key"} = $nuevo_nombre;
                    log_debug( "$heading POPULATES datos($quehay){ $key } = $nuevo_nombre", 32 ) if DEBUG;
                }
            }
        }
        for $quehay ( keys %parked ) {
            if ( $quehay eq "$server^$viejo_nombre" ) {
                $parked{"$server^$nuevo_nombre"} = $parked{"$quehay"};
                delete $parked{"$quehay"};
                my ( $rcanal, $rses ) = separate_session_from_channel($nuevo_nombre);
                $texto = "&parked," . $parked{"$server^$nuevo_nombre"} . "&";
                push @return, "$rcanal|ocupado3|$texto|$unico_id|$nuevo_nombre";
            }
        }
        $evento = "";    #NEW
    }
    elsif ( $evento eq "peerstatus" ) {
        my $tiempo = 0;
        $canal = $hash_temporal{Peer};
        $canal =~ tr/a-z/A-Z/;
        $state = $hash_temporal{PeerStatus};

        if ( defined $hash_temporal{Time} ) {
            $tiempo = $hash_temporal{Time};
        }

        if ( $state eq "Registered" ) {
            $estado_final = "registrado";
            $texto        = "&registered";
        }
        elsif ( $state eq "Reachable" ) {
            $estado_final = "registrado";
            $texto        = "&reachable,$tiempo";
        }
        elsif ( $state eq "Unreachable" ) {
            $estado_final = "unreachable";
            $texto        = "&unreachable,$tiempo";
        }
        elsif ( $state eq "Unregistered" ) {
            $estado_final = "noregistrado";
            $texto        = "&notregistered";
        }
        elsif ( $state eq "Lagged" ) {
            $estado_final = "noregistrado";
            $texto        = "&lagged,$tiempo";
        }
        $canalid = $canal . "-XXXX";
        push @return, "$canal|$estado_final|$texto|$unico_id|$canalid";
        $evento = "";
    }
    elsif ( $evento eq "hold" ) {
        $held_channel{ $hash_temporal{"Channel"} } = 1;
    }
    elsif ( $evento eq "unhold" ) {
        delete $held_channel{ $hash_temporal{"Channel"} };
    }
    elsif ( $evento eq "extensionstatus" ) {
        $canal = $hash_temporal{"Exten"};
        $canal =~ s/(\d+)/SCCP\/$1/g;
        $state = $hash_temporal{"Status"};
        if ( $hash_temporal{"Status"} == 0 ) {
            $estado_final = "registrado";
            $texto        = "&registered";
        }
        elsif ( $hash_temporal{"Status"} == 4 ) {
            $estado_final = "unreachable";
            $texto        = "&unreachable";
        }
        push @return, "$canal|$estado_final|$texto|$unico_id|$canalid";
        $evento = "";
    }
    elsif ( $evento eq "status" ) {
        $evento = "";
    }
    elsif ( $evento eq "statuscomplete" ) {

        # When done with the status retrieval, generate events to send to
        # flash clients. Do it only when finishing receiving status from
        # all asterisk servers monitored

        my @ids = ();
        if ( keys(%datos) ) {
            my $hay_activos = 0;

            foreach my $dkey ( keys %datos ) {
                my $ignorame = 0;
                my ( undef, $current_server ) = split( /-/, $dkey );
                if ( "$server" ne "$current_server" ) {
                    next;
                }

                log_debug( "$heading STATUSCOMPLETE datos { $dkey }", 128 ) if DEBUG;
                push @ids, $dkey;
                my $myevent = "Newexten";
                while ( my ( $key, $val ) = each( %{ $datos{$dkey} } ) ) {
                    log_debug( "$heading STATUSCOMPLETE datos { $key } = $val", 128 ) if DEBUG;
                    if ( defined($val) ) {
                        $hay_activos = 1;
                        $fake_bloque[$fakecounter]{$key} = $val;
                        if ( $key eq "Extension" ) {
                            $myevent = "Ring";
                            $fake_bloque[$fakecounter]{Origin} = "true";
                        }
                        if ( $key eq "Channel" ) {
                            if ( $timeouts{$val} ) {
                                $datos{$_}{Timeout} = $timeouts{$val};
                            }
                        }
                        if ( $key eq "Link" && $val =~ /^Agent/ ) {
                            $ignorame = 1;
                        }
                    }
                }
                if ( $hay_activos == 1 ) {
                    $fake_bloque[$fakecounter]{"Event"} = $myevent;
                    if ( $ignorame == 1 ) {
                        $fake_bloque[$fakecounter]{Event} = "Ignoreme-Bad-CLID";
                        $fake_bloque[$fakecounter]{State} = "Ignoreme";
                    }
                    log_debug( "$heading fake bloque de $fakecounter (evento) lo pongo en $myevent", 128 ) if DEBUG;
                    $fakecounter++;
                }
            }
        }
        else {
            log_debug( "$heading En statuscomplete datos esta vacio!", 64 ) if DEBUG;
        }

        foreach my $valor (@ids) {
            log_debug( "$heading foreach statuscomplete $valor", 32 ) if DEBUG;
            if ( exists( $datos{"$valor"} ) ) {
                if ( exists( $datos{"$valor"}{Link} ) ) {
                    if ( $datos{"$valor"}{Link} !~ /^Agent/ ) {
                        log_debug( "$heading datos de $valor tiene defined Link genero un Fake bloque", 128 ) if DEBUG;

                        my $channel1 = $datos{$valor}{"Channel"};
                        my $channel2 = $datos{$valor}{"Link"};
                        my $unique1  = $datos{$valor}{"Uniqueid"};
                        my $unique2  = "";
                        if ( defined( $datos{$valor}{BridgedUniqueid} ) ) {
                            $unique2 = $hash_temporal{BridgedUniqueid};
                        }
                        else {
                            $unique2 = find_uniqueid( $channel2, $server );
                        }

                        $fake_bloque[$fakecounter]{"Event"}     = "Link";
                        $fake_bloque[$fakecounter]{"Channel1"}  = $channel1;
                        $fake_bloque[$fakecounter]{"Channel2"}  = $channel2;
                        $fake_bloque[$fakecounter]{"Uniqueid1"} = $unique1;
                        $fake_bloque[$fakecounter]{"Uniqueid2"} = $unique2;

                        while ( my ( $quey, $vail ) = each( %{ $fake_bloque[$fakecounter] } ) ) {
                            log_debug( "$heading FAKEBLOQUE contiene $quey = $vail", 128 ) if DEBUG;
                        }

                        $fakecounter++;
                    }
                    else {
                        log_debug( "$heading datos de $valor linked to Agent, IGNORE", 128 ) if DEBUG;
                    }
                }
                if ( exists( $datos{"$valor"}{"Timeout"} ) ) {
                    my $calltimeout = $datos{"$valor"}{"Timeout"} - time();
                    $fake_bloque[$fakecounter]{"ActionID"} = "timeout|$datos{$valor}{'Channel'}|$calltimeout";
                    $fakecounter++;
                }
            }
        }

        $evento = "";    #NEW (estaba comentado)
    }
    elsif ( $evento eq "fakeismeetmemember" ) {
        my @bot1 = ();
        my $bot2 = 0;
        $estado_final = "meetmeuser";
        $texto        = $hash_temporal{Usernum} . "," . $hash_temporal{Meetme};
        my ( $chan1, undef ) = separate_session_from_channel( $hash_temporal{Channel} );
        push @return, "$hash_temporal{Meetme}|setlink|$hash_temporal{Channel}||$hash_temporal{Channel}";
        push @return, "$chan1|setlink|$hash_temporal{Meetme}||$hash_temporal{Channel}";
        $evento = "";

    }
    elsif ( $evento eq "newexten" ) {

        # If its a new extension without state and priority 1, defaults to 'Up' and set setlid
        if ( !defined( $hash_temporal{'Priority'} ) ) {
            $hash_temporal{'Priority'} = 1;
        }
        if ( !defined( $datos{$unico_id}{State} ) && $hash_temporal{Priority} == 1 ) {
            $datos{$unico_id}{State} = "Up";
            log_debug( "$heading POPULATES datos($unico_id){ State } = Up", 128 ) if DEBUG;
            ( $canal, $sesion ) = separate_session_from_channel( $hash_temporal{Channel} );
            if ( $hash_temporal{Extension} ) {
                $texto = $hash_temporal{Extension};
            }
            if ( $texto ne "s" ) {
                $estado_final = "setclid";
            }
        }

        # If its a parked channel, set the PARK button to 'Down'
        if ( exists( $parked{"$server^$canalid"} ) ) {
            log_debug( "$heading EXISTE parked{$server^$canalid}", 128 ) if DEBUG;
            my $parkexten = $parked{"$server^$canalid"};
            delete $parked{"$server^$canalid"};
            push @return, "PARK/$parkexten|corto||YYYY-$server|$canalid";
        }
        else {
            log_debug( "$heading NO EXISTE parked{$server^$canalid}", 128 ) if DEBUG;
        }

        if ( exists( $pending_uniqueid_attendant{ $hash_temporal{Uniqueid} } ) ) {

            # This was from an originatesuccess for attendant transfers
            # so remove the hash element and add it to attendant_pending hash
            # that is used for look for hangups and perform redirects

            $attendant_pending{ $hash_temporal{Channel} } = $pending_uniqueid_attendant{ $hash_temporal{Uniqueid} };
            delete $pending_uniqueid_attendant{ $hash_temporal{Uniqueid} };
            log_debug( "Save " . $hash_temporal{Channel} . " as pending", 16 ) if DEBUG;
        }

    }
    elsif ( $evento eq "hangup" ) {

        # Look for attendant_pending and perform a redirect of the
        # hold channel to the same meetme room to complete the transfer
        my $canalid = $hash_temporal{Channel};
        my $room    = "";
        my @aborrar = ();
        foreach my $key ( keys %attendant_pending ) {
            if ( $canalid eq $key ) {
                $room = $attendant_pending{$key};
                log_debug( "** ATTENDANT corto y pending coincide con $canalid! es = $room", 16 ) if DEBUG;
                last;
            }
        }
        if ( $room ne "" ) {
            push @aborrar, $canalid;
            while ( my ( $key, $val ) = each(%attendant_pending) ) {
                if ( $val eq $room ) {
                    push @aborrar, $key;
                }
            }
            foreach my $item (@aborrar) {
                log_debug( "** ATTENDANT borrando pending $item", 32 ) if DEBUG;
                delete $attendant_pending{$item};
            }
            log_debug( "transfer " . $mute_other{$room} . " to meetme $room", 32 ) if DEBUG;
            my $eext = $room;
            my $ccnt = $room;
            $eext =~ s/(.*)\@(.*)/$1/g;
            $ccnt =~ s/(.*)\@(.*)/$2/g;
            my $comando = "Action: Redirect\r\n";
            $comando .= "Channel: $mute_other{$room}\r\n";
            $comando .= "Exten: $eext\r\n";
            $comando .= "Context: $ccnt\r\n";
            $comando .= "Priority: 10\r\n\r\n";
            send_command_to_manager( $comando, $socket, 0, $astmanproxy_server );
        }
        else {
            log_debug( "** ATTENDANT no room for pending", 32 ) if DEBUG;
        }

        # End block for attendant pending

        if ( exists( $datos{$unico_id} ) ) {
            $datos{$unico_id}{State} = "Down";
            $hash_temporal{State} = "Down";
            log_debug( "$heading POPULATES datos($unico_id){ State } = down", 128 ) if DEBUG;
        }

        # Look if the channel was parked and clear that button too
        if ( exists( $parked{"$server^$canalid"} ) ) {
            log_debug( "$heading 2 EXISTE parked{$server^$canalid}", 128 ) if DEBUG;
            my $parkexten = $parked{"$server^$canalid"};
            delete $parked{"$server^$canalid"};
            push @return, "PARK/$parkexten|corto||YYYY-$server|$canalid";
        }
        else {
            log_debug( "$heading NO EXISTE parked{$server^$canalid}", 128 ) if DEBUG;
        }
    }
    elsif ( $evento eq "unparkedcall" ) {
        my $channel1 = $hash_temporal{"Channel"};

        if ( exists( $parked{"$server^$channel1"} ) ) {
            my $unidchan = find_uniqueid( $hash_temporal{'Channel'}, $server );
            log_debug( "$heading EXISTE parked{$server^$channel1} ", 128 ) if DEBUG;
            my $parkexten = $parked{"$server^$channel1"};
            delete $parked{"$server^$channel1"};
            push @return, "PARK/$parkexten|corto||YYYY-$server|$channel1";
            my ( $canal1, $sesion1 ) = separate_session_from_channel($channel1);
            push @return, "$canal1|ocupado5||$unidchan|$channel1";
        }
    }
    elsif ( $evento eq "parkedcall" ) {
        my $parksl = "";
        if ( defined( $hash_temporal{LotName} ) ) {
            $parksl = $hash_temporal{LotName} . "-" . $hash_temporal{Exten};
        }
        else {
            $parksl = $hash_temporal{Exten};
        }
        $texto        = "&parked," . $parksl . "&";
        $estado_final = "ocupado3";
        my ( $canal, undef ) = separate_session_from_channel( $hash_temporal{'Channel'} );
        my $textid   = "";
        my $timeout  = "";
        my $unidchan = find_uniqueid( $hash_temporal{'Channel'}, $server );
        if ( defined( $hash_temporal{CallerID} ) ) {
            $textid = $hash_temporal{CallerID} . " " . $hash_temporal{CallerIDName};
        }
        else {
            $textid = $datos{$unidchan}{Callerid}
              if ( defined( $datos{$unidchan}{Callerid} ) );
            $textid = $datos{$unidchan}{CallerID}
              if ( defined( $datos{$unidchan}{CallerID} ) );
        }
        $timeout = "(" . $hash_temporal{Timeout} . ")";
        $textid =~ s/\"//g;
        $textid =~ s/\<//g;
        $textid =~ s/\>//g;
        push @return, "$canal|ocupado3|$texto|$unidchan|$canalid";
        push @return, "PARK/$parksl|park|[$textid]$timeout|$hash_temporal{'Timeout'}-$server|$hash_temporal{'Channel'}";

        log_debug( "$heading pongo parked($server^$hash_temporal{'Channel'}) en $parksl", 64 ) if DEBUG;
        $parked{"$server^$hash_temporal{'Channel'}"} = $parksl;
        $evento = "";    #NEW
    }
    elsif ( $evento eq "newcallerid" ) {
        $estado_final = "setstatus";
        $state        = "Newcallerid";
        my $save_clidnum  = "";
        my $save_clidname = "";
        if ( defined( $hash_temporal{'CallerIDName'} ) ) {
            $save_clidnum  = $hash_temporal{'CallerID'};
            $save_clidname = $hash_temporal{'CallerIDName'};
        }
        elsif ( defined( $hash_temporal{'CalleridName'} ) ) {
            $save_clidnum  = $hash_temporal{'Callerid'};
            $save_clidname = $hash_temporal{'CalleridName'};
        }
        else {
            ( $save_clidnum, $save_clidname ) = split_callerid( $hash_temporal{'CallerID'} );
        }
        $saved_clidnum{"$server^$hash_temporal{'Channel'}"}  = $save_clidnum;
        $saved_clidname{"$server^$hash_temporal{'Channel'}"} = $save_clidname;
    }

    # From now on, we only look for the State of the datos block
    # Dont check for $evento bellow this line!

    if ( $evento ne "" ) {
        log_debug( "$heading Event $evento, canal '$canal', estadofinal $estado_final", 32 ) if DEBUG;

        # De acuerdo a los datos de la extension genera
        # la linea con info para el flash

        $elemento = $canalid;

        if ( exists( $datos{$unico_id} ) ) {
            if ( exists( $datos{$unico_id}{'Channel'} ) ) {
                $elemento = $datos{$unico_id}{'Channel'};

                # Old IAX naming convention
                if ( $elemento =~ /^IAX2\[/ && $elemento =~ /\@/ ) {

                    # The channel is IAX2 and has the @context
                    # I will remove the @context/host because it varies
                    $elemento =~ s/IAX2\[(.*)@(.*)\](.*)/IAX2\[$1\]$3/g;
                }

                if ( $elemento =~ /^IAX2\// && $elemento =~ /\@/ ) {
                    $elemento =~ s/IAX2\/(.*)@(.*)\/(.*)/IAX2\/$1\/$3/g;
                }
            }
        }
        ( $canal, $sesion ) = separate_session_from_channel($elemento);

        if ( defined($canal) ) {
            $canal =~ tr/a-z/A-Z/;
        }
        else {
            log_debug( "$heading canal not defined!! END $elemento", 32 ) if DEBUG;
            while ( my ( $key, $val ) = each(%hash_temporal) ) {
                log_debug( "$heading hash_temporal $key = $val", 128 ) if DEBUG;
            }
            return;
        }

        if ( $canal =~ m/^Local/i ) {    # ZZ nico
                                         # return;
        }

        if ( defined($sesion) ) {
            log_debug( "$heading canal $canal sesion $sesion", 128 ) if DEBUG;
        }

        if ( exists( $datos{$unico_id} ) ) {

            log_debug( "$heading EXISTE datos($unico_id) ", 32 ) if DEBUG;

            if ( exists( $datos{$unico_id}{'Extension'} ) ) {
                $exten = $datos{$unico_id}{'Extension'};
            }

            if ( exists( $datos{$unico_id}{'Callerid'} ) ) {
                $clid = $datos{$unico_id}{'Callerid'};
            }

            if ( exists( $datos{$unico_id}{'CallerID'} ) ) {
                $clid = $datos{$unico_id}{'CallerID'};
            }
            if ( $clid ne "" ) {
                ( $clidnum, $clidname ) = split_callerid($clid);
            }
            if ( exists( $datos{$unico_id}{'CallerIDName'} ) ) {
                $clidname = $datos{$unico_id}{'CallerIDName'};
            }

            # The ones below are for catching the callerid from the
            # Dial event on CVS-HEAD
            if ( exists( $remote_callerid{"$server^$canalid"} ) ) {
                $clidnum = $remote_callerid{"$server^$canalid"};
            }
            if ( exists( $remote_callerid_name{"$server^$canalid"} ) ) {
                $clidname = $remote_callerid_name{"$server^$canalid"};
            }
        }
        else {
            log_debug( "$heading NO EXISTE datos($unico_id)", 32 ) if DEBUG;
        }

        # print "clidnum $clidnum clidname $clidname\n";

        if ( $state eq "" ) {
            if ( defined( $hash_temporal{State} ) ) {
                $state = $hash_temporal{State};
            }
            else {
                $state = "";
            }
        }

        my $clid_with_format = format_clid( $clidnum, $clidname, $clid_format );

        if ( $state eq "Ringing" ) {
            my $ret = "";
            if ( $clidnum ne "" ) {
                my $base64_clidnum = encode_base64( $clidnum . " " );
                $ret = "$canal|clidnum|$base64_clidnum|$unico_id|$hash_temporal{Channel}";
                push @return, $ret;
            }
            if ( defined($clidname) ) {
                my $base64_clidname = encode_base64( $clidname . " " );
                $ret = "$canal|clidname|$base64_clidname|$unico_id|$hash_temporal{Channel}";
                push @return, $ret;
            }
            foreach my $var ( keys %{ $passvar{ $hash_temporal{Uniqueid} } } ) {
                my $base64_var = encode_base64( $passvar{ $hash_temporal{Uniqueid} }{$var} );
                $ret = "$canal|setvar|$var=$base64_var|$unico_id|$hash_temporal{Channel}";
                push @return, $ret;
            }
            delete $passvar{ $hash_temporal{Uniqueid} };
        }

        if ( $state eq "Rsrvd" ) {
            $state = "Ring";
        }

        if ( $state eq "Ring" ) {
            $texto        = $canalid;
            $estado_final = "ring";

            #$datos{$unico_id}{'Origin'} = "true";
            #log_debug( "$heading POPULATES datos($unico_id){ Origin } = true", 128 ) if DEBUG;
        }

        if ( $state eq "Dialing" ) {
            $texto        = "&dialing";
            $estado_final = "ring";
            if ( $canal =~ /^OH323/ ) {

                # OH323 use a random channel name when Dialing
                # that later changes its name. So we just discard
                # this event/name to avoid getting Dialing channels
                # foerever (because we never receive a Down status)
                $estado_final = "";
            }
        }

        if ( $state eq "OffHook" ) {
            $texto                      = "&calling";
            $estado_final               = "ring";
            $datos{$unico_id}{'Origin'} = "true";
            log_debug( "$heading POPULATES datos($unico_id){ Origin } = true", 128 );
        }

        if ( $state =~ /^UNK/ ) {
            $texto        = "&notregistered";
            $estado_final = "noregistrado";
            $unico_id     = "YYYY-$server";
            if ( $canalid !~ /(.*)-XXXX$/ ) {
                $canalid = $canalid .= "-XXXX";
            }
        }

        if ( $state =~ /^UNR/ ) {
            $texto        = "&unreachable";
            $estado_final = "unreachable";
            $unico_id     = "YYYY-$server";
            if ( $canalid !~ /(.*)-XXXX$/ ) {
                $canalid = $canalid .= "-XXXX";
            }
        }

        if ( $state =~ /^Unm/ ) {
            $texto        = "&registered";
            $estado_final = "registrado";
            $unico_id     = "YYYY-$server";
            if ( $canalid !~ /(.*)-XXXX$/ ) {
                $canalid = $canalid .= "-XXXX";
            }
        }

        if ( $state =~ /^OK/ ) {
            $texto        = "&registered";
            $estado_final = "registrado";
            $unico_id     = "YYYY-$server";
            if ( $canalid !~ /(.*)-XXXX$/ ) {
                $canalid = $canalid .= "-XXXX";
            }
        }

        if ( $state eq "Newcallerid" ) {
            $texto = "&incoming,[" . $clid_with_format . "]";
        }

        if ( $state eq "Ringing" ) {
            $texto        = "&incoming,[" . $clid_with_format . "]";
            $estado_final = "ringing";
        }

        if ( $state eq "Down" ) {

            if ( $evento eq "hangup" ) {

                # Solo nos interesa mandar a flash los down por hangup
                # para evitar enviar eventos redundantes
                $estado_final = "corto";
                if ( $agent_status == 1 ) {
                    if ( exists( $agent_to_channel{"$server^$canal"} ) || exists( $reverse_agents{$canal} ) ) {
                        push @return, "$canal|isagent|0|$unico_id|$canalid";
                    }
                }
            }
            delete $timeouts{$canalid};
            delete $remote_callerid{"$server^$canalid"};
            delete $remote_callerid_name{"$server^$canalid"};
            delete $saved_clidname{"$server^$canalid"};
            delete $saved_clidnum{"$server^$canalid"};

            #erase_instances_for_trunk_buttons($canalsesion);
        }

        my $exclude    = 0;
        my $exten_clid = "";
        if ( $state eq "Up" ) {

            if ( defined( $hash_temporal{CallerIDName} ) ) {
                $saved_clidname{"$server^$hash_temporal{'Channel'}"} = $hash_temporal{CallerIDName};
            }

            if ( $exten ne "" ) {
                if ( is_number($exten) ) {
                    $exten_clid = format_clid( $exten, '', $clid_format );
                    if ( defined( $saved_clidnum{"$server^$canalid"} ) ) {
                        $exten_clid = format_clid( $saved_clidnum{"$server^$canalid"}, $saved_clidname{"$server^$canalid"}, $clid_format );
                    }
                    if ($clid_privacy) {
                        $exten_clid = "n/a";
                    }
                    $conquien = "[" . $exten_clid . "]";
                }
                else {
                    if ( defined( $saved_clidname{"$server^$hash_temporal{Channel}"} ) ) {
                        $clidname = $saved_clidname{"$server^$hash_temporal{Channel}"};
                    }
                    if ( defined( $saved_clidnum{"$server^$hash_temporal{Channel}"} ) ) {
                        $clidnum = $saved_clidnum{"$server^$hash_temporal{Channel}"};
                    }
                    $conquien = format_clid( $clidnum, $clidname, $clid_format );

                    #if ( length($exten) == 1 ) {
                    #    $conquien = $exten;
                    #    $exclude  = 1;        # We ignore events that have letter extensions 's', 'h', etc
                    #    $exclude  = 0;        # Do we? UserEvent fake events dont work if we ignore them
                    #    log_debug( "$heading CLID is not a number! $exten", 32 ) if DEBUG;
                    #}
                    #else {
                    #    $conquien = $exten;
                    #}
                }
            }
            else {
                $conquien = $clid_with_format;
            }

            if ( defined( $hash_temporal{'Seconds'} ) ) {

                # $conquien .= " (" . $hash_temporal{'Seconds'} . ")";
                push @return, "$canal|settimer|$hash_temporal{'Seconds'}\@UP|$unico_id|$canalid";
                push @return, "$canal|state|busy|$unico_id|$canalid";

            }

            if ( defined( $datos{$unico_id}{'Origin'} ) ) {
                if ( $datos{$unico_id}{'Origin'} eq "true" ) {
                    if ( $exclude == 1 ) {
                        $texto = "skip";
                    }
                    else {
                        if ( $exten_clid eq "" ) { $exten_clid = $conquien; }
                        $texto = "&calling,[$exten_clid]";
                    }
                    $estado_final = "ocupado2";    # 2 for origin button
                }
            }
            else {

                $texto        = "&incoming,[$conquien]";
                $estado_final = "ocupado1";                # 1 for destination button
            }
        }

        # Remove special character from Caller ID string
        $texto =~ s/\"/'/g;
        $texto =~ s/</[/g;
        $texto =~ s/>/]/g;
        $texto =~ s/\|/ /g;

        push @return, "$canal|$estado_final|$texto|$unico_id|$canalid";

    }

    @return = unique(@return);

    my $cuantos = $#return + 1;
    if (DEBUG) {
        log_debug( "$heading returns $cuantos", 16 );
        log_debug( "$heading END SUB returns $_", 32 ) foreach (@return);
    }
    $tab = substr( $tab, 0, -1 );
    return @return;
}

sub local_channels_are_driving_me_mad {

    # This tidbit checks if the channel is Local and we happen to have a regular
    # channel button with the same ext @ context, if so, we replace the Local
    # !$##@ channel to the regular/basic channel.

    my $server = shift;
    my $canal  = shift;
    my @return = ();

    log_debug( "** LOCAL_CHANNELS entra server $server canal $canal", 32 );

    if ( $canal =~ /^Local/i ) {
        my $local_channel = $canal;
        $local_channel =~ s/Local\///gi;
        if ( $local_channel =~ m/\/n$/ ) {
            $local_channel = substr( $local_channel, 0, -2 );
        }

        if ( defined( $extension_transfer_reverse{"$server^$local_channel"} ) ) {
            if ( $extension_transfer_reverse{"$server^$local_channel"} !~ /-?\d+\^CLID/ ) {

                # We dont want to return CLID buttons
                $canal = $extension_transfer_reverse{"$server^$local_channel"};
                if ( $canal =~ /(\d+)\^(.*)/ ) {
                    $server = $1;
                    $canal  = $2;
                }
            }
            else {
                log_debug( "** LOCAL_CHANNELS ignoro el CLID button $canal", 32 ) if DEBUG;
            }
        }
    }

    # Remove &context from $canal
    $canal =~ s/(.*)&(.*)/$1/g;

    log_debug( "** LOCAL_CHANNELS devuelvo $server canal $canal", 32 ) if DEBUG;

    # If channel has a pipe due to REGEXP button, replace it with something else because
    # procesa_bloque use pipes to return data to digest_event_block
    $canal =~ s/\|/~/g;
    push @return, $server;
    push @return, $canal;
    return @return;
}

sub digest_event_block {
    my $bleque             = shift;
    my $tipo               = shift;
    my $socket             = shift;
    my $astmanproxy_server = shift;
    my @blique             = @$bleque;
    my @respuestas         = ();
    my $canal              = "";
    my $quehace            = "";
    my $dos                = "";
    my $uniqueid           = "";
    my $canalid            = "";
    my $quehay             = "";
    my @mensajes           = ();
    my $interno            = "";
    my $toda               = "";
    my @mensajefinal;
    my $cuantas;
    my $server = 0;
    my %cambiaron;
    my $heading = "** DIGEST_EVENT:";

    $tab = $tab . "\t" if DEBUG;

    log_debug( "$heading start", 256 ) if DEBUG;

    @fake_bloque = ();

    foreach my $blaque (@blique) {

        if (DEBUG) {
            if ( $tipo eq "fake" ) {
                $tab = substr( $tab, 0, -1 );
                my $totdebug = "";
                while ( my ( $key, $val ) = each( %{$blaque} ) ) {
                    if ( $key ne "" ) {
                        $totdebug .= sprintf( "%-15s <- %s\n", "fake", "$key: $val" );
                    }
                }
                log_debug( $totdebug, 1 );
                $tab = $tab . "\t";
            }
        }
        @mensajes = procesa_bloque( $blaque, $socket, $astmanproxy_server );

        foreach my $mensaje (@mensajes) {
            if ( defined($mensaje) && $mensaje ne "" ) {
                log_debug( "$heading GOT $mensaje", 256 ) if DEBUG;
                delete $datos{""};    # Erase the hash with no uniqueid
                ( $canal, $quehace, $dos, $uniqueid, $canalid ) = split( /\|/, $mensaje );

                # change back newflo into pipes
                $canal   =~ s/~/\|/g;
                $canalid =~ s/~/\|/g;
                $canalid =~ s/(.*),(\d)/$1/g;    # discard ,2 on Local channels

                if ( !defined($dos) )     { $dos     = ""; }
                if ( !defined($quehace) ) { $quehace = ""; }

                if ( $canal =~ /\/PSEUDO/ ) {
                    log_debug( "$heading Ignoring pseudo channel $canal", 256 ) if DEBUG;
                    next;
                }

                if ( $dos eq "skip" ) {
                    log_debug( "$heading skipping $canal $quehace (has skip)", 256 ) if DEBUG;
                    next;
                }

                if ( $quehace eq "" ) {
                    log_debug( "$heading skipping $canal (empty quehace)", 256 ) if DEBUG;
                    next;
                }

                log_debug( "$heading canal:    $canal",    256 ) if DEBUG;
                log_debug( "$heading quehace:  $quehace",  256 ) if DEBUG;
                log_debug( "$heading dos:      $dos",      256 ) if DEBUG;
                log_debug( "$heading uniqueid: $uniqueid", 256 ) if DEBUG;
                log_debug( "$heading canalid:  $canalid",  256 ) if DEBUG;

                $canalid =~ s/\s+//g;             # Removes whitespace from CHANNEL-ID
                $canalid =~ s/(.*)<(.*)>/$1/g;    # discards ZOMBIE or MASQ

                if ( $canal =~ /^vpb\//i ) {

                    # For vpb channels, we fake a session number
                    $canal = $canalid;
                    $canal =~ tr/a-z/A-Z/;
                    $canalid = $canalid .= "-VPB1";
                }

                $server = $uniqueid;
                $server =~ s/(.*)-(.*)/$2/g;

                my $buttontext = $dos;
                if ( $buttontext =~ /\Q[\E/ ) {
                    $buttontext =~ s/.*\Q[\E(.*)\Q]\E.*/$1/g;
                }
                else {
                    $buttontext = "";
                }

                my @canaleja = find_panel_buttons( $canal, $canalid, $server );
                my $cuantos  = @canaleja;

                # Perform some pre processing...

                if ( $quehace eq "corto" || $quehace eq "info" ) {

                    # We collect the last state of the channel on hangup
                    $toda = "";
                    while ( my ( $key, $val ) = each( %{ $datos{$uniqueid} } ) ) {
                        $toda .= "$key = $val\n"
                          if ( $key ne "E" ) && ( defined($val) );
                        log_debug( "$heading \tAgrego $key = $val", 256 ) if DEBUG;
                    }
                    $toda .= " ";
                    $toda = encode_base64($toda);

                    if ( $quehace eq "corto" ) {
                        log_debug( "$heading erasing datos{$uniqueid}", 256 ) if DEBUG;
                        delete $held_channel{$canalid};
                        delete $datos{$uniqueid};
                        delete $chanvar{$uniqueid};
                        delete $passvar{$uniqueid};

                        if ( $cuantos == 0 ) {

                            # We need to call it here because a channel with no buttons defined
                            # might count for other button that exists, like meetmes, queues, etc.
                            # So we only call it when there is NO match for buttons (because
                            # we will call it again for existing matches)
                            erase_all_sessions_from_channel( $canalid, $canal, $server );
                        }
                    }
                }
                elsif ( $quehace eq "queueremoved" ) {

                    # Remove the agent from the agents_on_queue hash
                    my $colita    = $dos;
                    my @elementos = ();

                    if ( keys(%agents_on_queue) ) {

                        # remove from agents_on_queue hash only if it has anything on it
                        foreach my $vvalor ( @{ $agents_on_queue{"$server^$colita"} } ) {
                            if ( $vvalor ne "$server^$canal" ) {
                                push @elementos, $vvalor;
                            }
                            else {
                                push @elementos, "!" . $vvalor;
                            }
                        }
                        @{ $agents_on_queue{"$server^$colita"} } = @elementos;
                    }

                    #Remove cache hit to force find panel buttons to look for new positions
                    foreach my $kkey ( keys %cache_hit ) {
                        if ( $kkey =~ /^$canal/ ) {
                            delete $cache_hit{$kkey};
                        }
                    }
                }

                my $dosoriginal = "";

                foreach $canal (@canaleja) {
                    log_debug( "",                                       256 ) if DEBUG;
                    log_debug( "$heading canaleja LOOP; is $canal turn", 256 ) if DEBUG;

                    if ( $dosoriginal ne "" ) {
                        $dos = $dosoriginal;
                    }

                    # Try to find the text string in the lang conf hash
                    if ( $dos =~ m/^\&/ ) {
                        ( $dos, $dosoriginal, $buttontext ) = translate( $canal, $dos, $dosoriginal, $buttontext );
                    }

                    if ( !defined( $buttons{"$server^$canal"} ) && !defined( $buttons{"-1^$canal"} ) ) {
                        log_debug( "$heading \tThere are no buttons for $server^$canal, skipping...", 256 ) if DEBUG;
                        if (DEBUG) {
                            for ( keys %buttons ) {
                                log_debug( "$heading \t\tKey $_", 256 );
                            }
                        }
                        next;
                    }

                    # If its a REGEXP button, we have to ignore all events
                    # except ocupado*, corto, setlink and unsetlink
                    if ( $canal =~ /^_/ ) {
                        log_debug( "$heading canal $canal is regexp, quehace value = $quehace", 256 ) if DEBUG;

                        if (   $quehace =~ /registr/
                            || $quehace =~ /reacha/
                            || $quehace =~ /^inf/ )
                        {
                            log_debug( "$heading IGNORING $quehace because it is a regexp match", 256 ) if DEBUG;
                            next;
                        }

                        if (   $quehace !~ /^ocupado/
                            && $quehace !~ /^corto/
                            && $quehace !~ /^state/
                            && $quehace !~ /^settext/
                            && $quehace !~ /^setclid/
                            && $quehace !~ /^setlabel/
                            && $quehace !~ /^setlink/
                            && $quehace !~ /^meetme/
                            && $quehace !~ /^ring/
                            && $quehace !~ /^settimer/
                            && $quehace !~ /^clidnum/
                            && $quehace !~ /^clidname/
                            && $quehace !~ /^setstatus/
                            && $quehace !~ /^unsetlink/ )
                        {
                            my ( undef, $elcontexto ) = split( /\&/, $canal );
                            if ( !defined($elcontexto) ) { $elcontexto = ""; }
                            if ( $elcontexto ne "" ) {
                                $elcontexto = "&" . $elcontexto;
                            }
                            my ( $canalsolo, $nrotrunk ) = split( /=/, $canal );
                            $canal = $canalsolo . "=1" . $elcontexto;
                            log_debug( "$heading quehace=$quehace, select 1st from trunk $canal", 256 ) if DEBUG;

                            #next;
                        }

                        # If we have a regexp button with changelabel
                        # and change led_color (the 1 after changelabel)
                        # change it so to not change the led color.
                        if ( $quehace =~ /changelabel1/ ) {
                            log_debug( "$heading regexp has changelabel1, lets change it to changelabel0!", 256 )
                              if DEBUG;
                            $quehace = "changelabel0";
                        }
                    }

                    my $serverindex = $server;    # Save the server in another var

                    if ( $canal eq "" ) {

                        # No channel? continue...
                        log_debug( "$heading There is no command defined", 256 ) if DEBUG;

                    }
                    else {

                        $interno = $buttons{"$server^$canal"};
                        if ( !defined($interno) ) {
                            $interno     = $buttons{"-1^$canal"};
                            $serverindex = -1;
                            if ( $quehace !~ /regist/ ) {
                                $button_server{$interno} = $server;
                            }
                        }

                        $interno = "" if ( !defined($interno) );

                        if ( $interno ne "" ) {
                            if ( defined( $clid_private{$interno} ) && $clid_private{$interno} == 1 ) {
                                if ( $dos =~ m/\[/ ) {
                                    $dos =~ s/([^\[]*)?(\[.*\])/$1 \[n\/a\]/g;
                                }
                            }
                        }

                        # The following block cleans internal op_server states. no matter if we
                        # have a button defined or not.

                        if ( $quehace eq 'corto' || $quehace eq 'info' ) {
                            my @linked = erase_all_sessions_from_channel( $canalid, $canal, $server );

                            push @linked, $canal;
                            my $btnorinum = "";
                            foreach my $canaleje (@linked) {
                                if ( $canaleje =~ /\^/ ) {
                                    $btnorinum = $buttons{$canaleje};
                                }
                                else {
                                    $btnorinum = $buttons{"$serverindex^$canaleje"};
                                }
                                log_debug( "$heading call GEN_LINKED 1", 256 ) if DEBUG;
                                my $listabotones = generate_linked_buttons_list( $canaleje, $server );
                                push @respuestas, "$btnorinum|linked|$listabotones";
                            }

                            delete $datos{$uniqueid};
                            delete $chanvar{$uniqueid};
                            delete $passvar{$uniqueid};
                            log_debug( "$heading REMOVING datos { $uniqueid }", 256 ) if DEBUG;

                        }
                        elsif ( $quehace eq "setlink" ) {

                            log_debug( "$heading IF quehace = SETLINK", 256 ) if DEBUG;
                            my ( undef, $contexto1 ) = split( /\&/, $canal );
                            if ( !defined($contexto1) ) { $contexto1 = ""; }
                            my $listabotones = "";

                            if ( !defined( @{ $linkbot{"$server^$canal"} } ) ) {
                                push @{ $linkbot{"$server^$canal"} }, "";
                                pop @{ $linkbot{"$server^$canal"} };
                                log_debug( "$heading DEFINIENDO linkbot ($server^$canal)", 256 ) if DEBUG;
                            }

                            my ( $canal1, $sesion1 ) = separate_session_from_channel($dos);
                            my @linkbotones = find_panel_buttons( $canal1, $dos, $server );
                            foreach (@linkbotones) {
                                my ( undef, $contexto2 ) = split( /\&/, $_ );
                                if ( !defined($contexto2) ) { $contexto2 = ""; }
                                if ( $contexto1 eq $contexto2 ) {
                                    push @{ $linkbot{"$server^$canal"} }, $dos;
                                    log_debug( "$heading AGREGO a linkbot{ $server^$canal} el valor $dos", 256 )
                                      if DEBUG;
                                }
                            }

                            my @uniq = unique( @{ $linkbot{"$server^$canal"} } );
                            $linkbot{"$server^$canal"} = \@uniq;

                            foreach my $valorad (@uniq) {
                                log_debug( "$heading linkbot ($server^$canal) = $valorad", 256 ) if DEBUG;
                            }
                            my $btnorinum = $buttons{"$serverindex^$canal"};
                            log_debug( "$heading llamo a GENERATE_LINKED", 256 ) if DEBUG;
                            $listabotones = generate_linked_buttons_list( $canal, $server );
                            push @respuestas, "$btnorinum|linked|$listabotones";
                            push @respuestas, "$btnorinum|bridgepeer|$dos";
                            $botonlinked{$btnorinum} = $listabotones;
                            log_debug( "$heading linkeado con $listabotones", 256 ) if DEBUG;
                            log_debug( "$heading ENDIF quehace = SETLINK",    256 ) if DEBUG;

                        }
                        elsif ( $quehace eq "unsetlink" ) {
                            log_debug( "$heading IF quehace = UNSETLINK", 256 ) if DEBUG;
                            my @final = ();
                            foreach my $msesion ( @{ $linkbot{"$server^$canal"} } ) {
                                if ( $msesion ne $dos ) {
                                    push @final, $msesion;
                                }
                            }
                            $linkbot{"$server^$canal"} = [@final];
                            log_debug( "$heading ENDIF quehace = UNSETLINK", 256 ) if DEBUG;

                        }
                        elsif ( $quehace eq "queueremoved" ) {

                            delete $botonvoicemail{$interno};
                            delete $botonvoicemailcount{$interno};
                            delete $botonpark{$interno};
                            delete $botonmeetme{$interno};
                            delete $botonclid{$interno};
                            delete $botonlinked{$interno};
                            delete $botontimer{$interno};
                            delete $botontimertype{$interno};
                            delete $botonqueue_count{$interno};
                            delete $botonqueue{$interno};
                            delete $botonled{$interno};
                            push @mensajefinal, "$interno|voicemail|0";
                        }

                        # Continue after cleaning internal state...
                        if ( $interno eq "" ) {
                            log_debug( "$heading MISSING buttons($server^$canal), skipping...", 256 ) if DEBUG;
                            next;
                        }
                        else {
                            log_debug( "$heading INTERNO = $interno", 256 ) if DEBUG;
                        }

                        if ( !defined( $laststatus{$interno} ) ) {
                            $laststatus{$interno} = "";
                        }
                        if ( !defined( $estadoboton{$interno} ) ) {
                            $estadoboton{$interno} = "";
                        }

                        # Mantains hash of arrays with sessions for each button number
                        # %sesbot{key}=value where:
                        #
                        # key is the button number (anything after '@' is the panel context)
                        # value is an array containing the sessions Eg: SIP/mary-43xZ
                        #
                        # The rename manager event also modifies this hash
                        #
                        # There are other hashes to maintain a 'view' of the status:
                        #
                        # %estadoboton{key}   = shows busy, free or ringing
                        #
                        if ( $canalid eq "" || $canalid =~ /zombie/i || $canalid =~ /(.*)-XXXX$/ ) {
                            log_debug( "$heading ATENTION canalid = '$canalid', skipping...", 256 ) if DEBUG;
                            if ( $quehace eq "registrado" || $quehace eq "noregistrado" || $quehace eq "unreachable" ) {
                                if ( defined( $botonregistrado{$interno} ) ) {
                                    if ( $botonregistrado{$interno} eq "$quehace|$dos" ) {
                                        $cambiaron{$interno} = 0;
                                    }
                                }
                                else {
                                    $botonregistrado{$interno} = "$quehace|$dos";
                                    $cambiaron{$interno}       = 1;
                                }
                            }
                        }
                        else {

                            if ( $quehace eq "corto" ) {

                                log_debug( "$heading CORTO interno $interno canal $canal", 256 ) if DEBUG;
                                $dos = $canalid;

                                delete $botonpark{$interno};
                                delete $botonmeetme{$interno};
                                delete $botonclid{$interno};
                                delete $botonlinked{$interno};
                                delete $botontimer{$interno};
                                delete $botontimertype{$interno};
                                delete $togle_action{$interno};
                                delete $togle_response{$interno};

                                my $canalbotonreverse = $buttons_reverse{$interno};

                                if ( $canal =~ /^_/ && $ren_wildcard == 1 ) {
                                    push @respuestas, "$interno|changelabel0|labeloriginal";
                                }

                                delete $linkbot{$interno};
                                delete $linkbot{$canalbotonreverse};

                                if ( !defined( $sesbot{$interno} ) ) {
                                    push @{ $sesbot{$interno} }, "";
                                    pop @{ $sesbot{$interno} };
                                }
                                my $cuantos = @{ $sesbot{$interno} };
                                if ( $cuantos == 0 ) {
                                    log_debug( "$heading CORTO y SE DESOCUPO estadoboton($interno) = free, sesbot($interno) esta vacio",
                                        256 )
                                      if DEBUG;
                                    $cambiaron{$interno}   = 1;
                                    $estadoboton{$interno} = "free";
                                }
                                else {
                                    log_debug( "$heading CORTO y SIGUE OCUPADO estadoboton($interno) = busy, sesbot($interno) tiene algo",
                                        256 )
                                      if DEBUG;
                                    &print_sesbot(3);
                                    &print_datos(1);

                                    if ( $laststatus{$interno} ne "busy|${buttontext}" ) {
                                        $cambiaron{$interno} = 1;

                                        push @respuestas, "$interno|state|busy";

                                        $laststatus{$interno} = "busy|${buttontext}";

                                        log_debug(
                                            "$heading Y es distinto al ultimo estado $laststatus{$interno} ne $estadoboton{$interno}", 256 )
                                          if DEBUG;
                                    }

                                    # Conserva el callerid anterior
                                    if ( defined( $preestadoboton{$interno} ) ) {
                                        $estadoboton{$interno} = $preestadoboton{$interno};
                                        delete $preestadoboton{$interno};
                                    }
                                    else {
                                        $estadoboton{$interno} = "busy|${buttontext}";
                                    }
                                    my $stringy = $estadoboton{$interno};
                                    $stringy =~ s/(.*)\|(.*)/$2/g;
                                    push @respuestas, "$interno|settext|$stringy";
                                }

                            }
                            else {

                                # quehace no es "corto"

                                # MAINTAINS SESBOT HASH
                                if ( $canalid !~ /^Agent/ ) {

                                    # settimer agent needs to be discarded
                                    if ( !defined( @{ $sesbot{$interno} } ) ) {
                                        push @{ $sesbot{$interno} }, "";
                                        pop @{ $sesbot{$interno} };
                                    }

                                    push @{ $sesbot{$interno} }, "$canalid";

                                    log_debug( "$heading AGREGO a sesbot($interno) el valor $canalid", 256 ) if DEBUG;

                                    my @uniq = unique( @{ $sesbot{$interno} } );
                                    $sesbot{$interno} = [@uniq];

                                    foreach my $vavi ( @{ $sesbot{$interno} } ) {
                                        log_debug( "$heading sesbot($interno) tiene $vavi", 256 ) if DEBUG;
                                        log_debug( "$heading --------------------",         256 ) if DEBUG;
                                    }
                                }

                                if ( $canal =~ /^_/ && $quehace =~ /^ring/ ) {
                                    log_debug( "$heading have a regexp originating a call $canal $quehace $canalid, rename label", 256 )
                                      if DEBUG;

                                    if ( $ren_wildcard == 1 ) {
                                        push @respuestas, "$interno|changelabel0|$canalid";
                                        $botonlabel{$interno} = $canalid;
                                    }
                                }
                                if ( $quehace eq "ringing" ) {
                                    if ( $laststatus{$interno} ne "ringing|${buttontext}" ) {
                                        $cambiaron{$interno} = 1;
                                    }
                                    if ( $estadoboton{$interno} =~ /^busy/ ) {

                                        # If we were busy before the ringing, save the callerid so we can restore it
                                        # if this call is not answered, discarded, or whatever.
                                        $preestadoboton{$interno} = $estadoboton{$interno};
                                    }
                                    $estadoboton{$interno} = "ringing|${buttontext}";
                                    if ( $dos =~ m/(.*)?\[(.*)\].*?/ ) {
                                        my $clidtext = $2;
                                        $botonclid{$interno} = $clidtext;
                                    }

                                    # We dont want a timer when ringing - Local channels
                                    # generate a previous state and timer
                                    ## push @mensajefinal, "$interno|settimer|0\@STOP";

                                }
                                elsif ( $quehace =~ /^ocupado/ || $quehace eq 'ring' ) {

                                    if ( defined( $group_count{$interno} ) ) {
                                        if ( $group_count{$interno} == 1 ) {
                                            $buttontext = group_count( $interno, $canal );
                                            push @respuestas, $buttontext;
                                        }
                                    }
                                    if ( $laststatus{$interno} ne "busy|${buttontext}" ) {
                                        $cambiaron{$interno} = 1;
                                    }
                                    $estadoboton{$interno} = "busy|${buttontext}";
                                }
                            }
                        }

                        log_debug( "$heading Continuo proceso...", 256 ) if DEBUG;

                        if ( $quehace =~ /changelabel/ ) {
                            log_debug( "$heading quehace = changelabel", 256 ) if DEBUG;

                            # Mantains state of label and led
                            my $cambia_el_led = $quehace;
                            $cambia_el_led =~ s/changelabel//g;
                            my $labdos = "";
                            if ( $canal =~ /^QUEUEAGENT/ ) {
                                $labdos = $canalid;
                                $labdos = substr( $labdos, 0, -5 );
                                $labdos =~ s/(.*)\&(.*)/$1/g;

                                if ( $labdos =~ m/^Agent/i && $ren_agentname == 1 ) {
                                    $labdos =~ s/^Agent\///g;
                                    if ( defined( $agents_name{"$server^$labdos"} ) ) {
                                        $labdos = $agents_name{"$server^$labdos"};
                                    }
                                }

                                $botonled{$interno} = $cambia_el_led;
                                if ( defined( $botonlabelonly{$interno} ) ) {
                                    $labdos = $botonlabelonly{$interno};
                                }
                                $botonlabel{$interno}     = $dos;
                                $botonlabelonly{$interno} = $labdos;
                                $agent_label{$canal}      = $dos;
                                push @mensajefinal, "$interno|setlabel|$labdos";
                            }
                            else {
                                $botonled{$interno} = $cambia_el_led;
                                if ( $ren_agentlogin || $ren_cbacklogin ) {
                                    $botonlabel{$interno} = $dos;
                                }
                                else {
                                    $botonlabel{$interno} = ".";
                                }

                                $agent_label{$canal} = $dos;
                            }

                        }
                        elsif ( $quehace eq "park" ) {

                            log_debug( "$heading quehace = park", 256 ) if DEBUG;
                            $dos =~ m/(.*)\((.*)\)/;
                            my $texto   = $1;
                            my $timeout = $2;
                            $timeout = time() + $timeout;
                            $botonpark{$interno} = "$texto|$timeout";
                        }
                        elsif ( $quehace eq "meetmeuser" ) {
                            $botonmeetme{$interno} = $dos;
                        }
                        elsif ( $quehace eq "infoqstat" ) {
                            $botonqueue{$interno} = $dos;
                        }
                        elsif ( $quehace eq "infoqstat2" ) {
                            $botonqueue_count{$interno} = $dos;
                        }
                        elsif ( $quehace =~ /info/ ) {
                            my $mcola = $quehace;
                            $mcola =~ s/^info//g;
                            my $estaba   = 0;
                            my @newarray = ();
                            foreach my $val ( @{ $botonqueuemember{$interno} } ) {
                                if ( $val =~ /^$mcola\|/ ) {
                                    if ( $val eq "$mcola|$dos" ) {
                                        $estaba  = 1;
                                        $quehace = "";
                                    }
                                }
                                else {
                                    push @newarray, $val;
                                }
                            }
                            @{ $botonqueuemember{$interno} } = @newarray;
                            if ( $estaba == 0 ) {
                                push @{ $botonqueuemember{$interno} }, "$mcola|$dos";
                            }

                        }
                        elsif ( $quehace eq "setclid" ) {
                            if ( !defined( $group_count{$interno} ) ) {
                                push @respuestas, "$interno|settext|$dos";
                            }
                            if ( $estadoboton{$interno} eq "" ) {
                                push @respuestas, "$interno|state|busy";
                                $estadoboton{$interno} = "busy";
                            }
                        }
                        elsif ( $quehace eq "settext" ) {
                            if ( !defined( $group_count{$interno} ) ) {
                                $botonpermanenttext{$interno} = $dos;
                                push @respuestas, "$interno|settext|$dos";
                            }
                        }
                        elsif ( $quehace eq "settextnopermanente" ) {
                            if ( !defined( $group_count{$interno} ) ) {
                                push @respuestas, "$interno|settext|$dos";
                            }
                        }
                        elsif ( $quehace eq "fopledcolor" ) {
                            $botonledcolor{$interno} = $dos;
                        }
                        elsif ( $quehace eq "setalpha" ) {
                            $botonalpha{$interno} = $dos;
                            push @respuestas, "$interno|setalpha|$dos";
                        }
                        elsif ( $quehace eq "flip" ) {
                            push @respuestas, "$interno|flip|$dos";
                        }
                        elsif ( $quehace eq "setlabel" ) {
                            if (   $dos ne "."
                                && $dos ne "original"
                                && $dos ne "labeloriginal" )
                            {
                                $botonsetlabel{$interno} = $dos;
                                push @respuestas, "$interno|setlabel|$dos";
                            }
                        }
                        elsif ( $quehace eq "voicemail" ) {
                            $botonvoicemail{$interno} = $dos;
                        }
                        elsif ( $quehace eq "voicemailcount" ) {
                            $botonvoicemailcount{$interno} = $dos;
                        }
                        elsif ( $quehace =~ "^voicemail" ) {

                            # This block is for the voicemail client
                            my $canalsincontexto = $canal;
                            $canalsincontexto =~ s/(.*)&(.*)/$1/g;
                            push @mensajefinal, "$canalsincontexto\@$canalsincontexto|$quehace|$dos";
                        }
                        elsif ( $quehace =~ "^ringing" ) {

                            # This block is for the voicemail client, popups
                            my $canalsincontexto = $canal;
                            $canalsincontexto =~ s/(.*)&(.*)/$1/g;
                            my $calleridpop = $dos;
                            $calleridpop =~ s/(.*)\Q[\E(.*)/$2/g;
                            $calleridpop =~ s/\]//g;
                            $calleridpop =~ s/\s+//g;
                            push @mensajefinal, "$canalsincontexto\@$canalsincontexto|$quehace|$calleridpop";
                            if ( defined( $group_count{$interno} ) && $group_count{$interno} == 1 ) {

                                # Ignore ringing because of groupcount
                                $quehace = "";
                            }
                        }

                        # linkbot{key} hash mantains the list of linked channels
                        # for a button. key is the button number, the value is the
                        # channel-session, like SIP/jose-AxiD

                        if (   ( $quehace !~ /^corto/ )
                            && ( $quehace !~ /^ocupado/ )
                            && ( $quehace !~ /link/ ) )
                        {
                            if ( !defined( $cambiaron{$interno} ) ) {
                                $cambiaron{$interno} = 1;
                                log_debug( "$heading es distinto de corto,ocupado,link pongo cambiaron=1", 256 )
                                  if DEBUG;
                            }
                        }

                        if ( !defined( $sesbot{$interno} ) ) {
                            push @{ $sesbot{$interno} }, "";
                            pop @{ $sesbot{$interno} };
                        }

                        if ( @{ $sesbot{$interno} } > 0 && $quehace eq 'corto' ) {
                            log_debug( "$heading Still busy...sesbot($interno) is not empty, ignore hangup", 256 )
                              if DEBUG;
                            if ( defined( $group_count{$interno} ) ) {
                                if ( $group_count{$interno} == 1 ) {
                                    $buttontext = group_count( $interno, $canal );
                                    push @respuestas, $buttontext;
                                }
                            }
                        }
                        else {

                            if ( $quehace eq "corto" ) {
                                my $canalsincontexto = $canal;
                                $canalsincontexto =~ s/(.*)&(.*)/$1/g;
                                push @mensajefinal, "$canalsincontexto\@$canalsincontexto|$quehace|$canalid";
                                if ( defined( $group_count{$interno} ) ) {
                                    if ( $group_count{$interno} == 1 ) {
                                        $buttontext = group_count( $interno, $canal );
                                        push @respuestas, $buttontext;
                                    }
                                }
                            }

                            my $quehace2 = $quehace;

                            next unless ( $quehace2 ne "setlink" );
                            next unless ( $quehace2 ne "unsetlink" );

                            log_debug( "$heading sigo quehace quehace2", 256 ) if DEBUG;

                            if ( $quehace2 eq "isagent" && $dos == -1 ) {
                                log_debug( "$heading quehace2 = isagent", 256 ) if DEBUG;
                                push @mensajefinal, "$interno|changelabel1|original";
                                push @mensajefinal, "$interno|settimer|0\@STOP";
                                push @mensajefinal, "$interno|settext|";
                                push @mensajefinal, "$interno|corto|$canalid";
                            }

                            if ( $quehace2 eq "agentlogoff" ) {

                                # clear the agent helper hashes. We do it here because we first need to map
                                log_debug( "$heading quehace2 = agentlogoff", 256 ) if DEBUG;
                                my $canalag = $canalid;
                                $canalag = substr( $canalag, 0, -5 );
                                if ( $canalag !~ /^Agent/ ) {
                                    $canalag = "Agent/" . $canalag;
                                }

                                my %temp_channel_to_agent = %channel_to_agent;
                                while ( my ( $key, $val ) = each(%temp_channel_to_agent) ) {
                                    if ( $val eq $canalag ) {
                                        delete $channel_to_agent{$key};
                                        log_debug( "$heading borro channel_to_agent($key)", 256 ) if DEBUG;
                                    }
                                }
                                undef %temp_channel_to_agent;

                                if ( defined( $agent_to_channel{"$server^$canalag"} ) ) {
                                    delete $agent_to_channel{"$server^$canalag"};
                                    log_debug( "$heading borro agent_to_channel($server^$canalag)", 256 ) if DEBUG;
                                }

                                $quehace2 = "corto";
                                delete $estadoboton{$interno};
                                delete $botonpark{$interno};
                                delete $botonmeetme{$interno};
                                delete $botonclid{$interno};
                                delete $botonlinked{$interno};
                                delete $botontimer{$interno};
                                delete $botontimertype{$interno};
                                delete $sesbot{$interno};    # Delete all sessions for agentlogoff XXXXXXX ????
                            }

                            if ( $quehace2 !~ /isagent/ && $quehace2 !~ /^agentlogoff/ && $quehace2 !~ /^setclid/ ) {

                                if ( defined( $cambiaron{$interno} ) && $cambiaron{$interno} == 1 ) {

                                    # Discard events that we dont want to send
                                    # to flash clients
                                    # "isagent". "agentlogoff"
                                    # everything else is pushed
                                    if ( defined( $group_count{$interno} ) && $quehace2 eq "setclid" ) {
                                        log_debug( "$heading skip settext because groupcount is set", 256 ) if DEBUG;
                                    }
                                    else {
                                        if ( $quehace2 ne "" ) {
                                            if ( defined( $group_count{$interno} ) && $group_count{$interno} == 1 ) {

                                                # $dos = "";
                                            }
                                            else {
                                                push @respuestas, "$interno|$quehace2|$dos";
                                                log_debug( "$heading pushing respuestas $interno|$quehace2|$dos", 256 )
                                                  if DEBUG;
                                            }
                                        }
                                    }
                                }
                            }

                            if ( $quehace2 =~ /ocupado/ ) {
                                if ( defined( $group_count{$interno} ) && $group_count{$interno} == 1 ) {
                                    $cambiaron{$interno} = 0;
                                    $quehace2 = "";
                                    next;
                                }
                                else {
                                    if ( $dos =~ m/(.*)?\[(.*)\].*?/ ) {
                                        my $clidtext = $2;
                                        $botonclid{$interno} = $clidtext;
                                    }

                                    #push @mensajefinal, "$interno|state|busy";
                                    push @mensajefinal, "$interno|settimer|0\@UP";
                                }
                            }
                            if ( $quehace2 eq "settimer" ) {
                                if ( !defined($dos) ) { $dos = 1; }
                                my $tiempo     = $dos;
                                my $timer_type = "";
                                if ( $tiempo =~ /\@/ ) {
                                    ( $tiempo, $timer_type ) = split( /\@/, $tiempo );
                                }

                                if ( $tiempo eq "" ) { $tiempo = 0; }

                                if ( $timer_type ne "" ) {
                                    $botontimertype{$interno} = $timer_type;
                                    $timer_type = "\@" . $timer_type;
                                }
                                $botontimer{$interno} = time() - $tiempo;
                                push @mensajefinal, "$interno|settimer|$tiempo$timer_type";
                            }

                            if ( $quehace eq "ring" || $quehace eq "ocupado1" || $quehace eq "ocupado9" ) {
                                push @mensajefinal, "$interno|settimer|0\@UP";
                                if ( !defined( $botontimer{$interno} ) ) {
                                    $botontimer{$interno}     = time();
                                    $botontimertype{$interno} = "UP";
                                }
                            }
                            if ( $quehace eq "ocupado1" ) {
                                push @mensajefinal, "$interno|channel|$canalid";
                            }
                            if ( $quehace2 eq "ring" ) {
                                push @mensajefinal, "$interno|state|busy";
                            }
                            if ( $quehace2 =~ /corto/ ) {
                                log_debug( "$heading quehace2 corto", 256 ) if DEBUG;

                                my $canalsincontexto = $canal;
                                $canalsincontexto =~ s/(.*)&(.*)/$1/g;
                                $canalsincontexto =~ s/^AGENT/Agent/g;
                                if (
                                    (
                                           exists( $agent_to_channel{"$server^$canalsincontexto"} )
                                        || exists( $channel_to_agent{"$server^$canalsincontexto"} )
                                        || exists( $is_agent{"$server^$canalsincontexto"} )

                                        #  || ( $canalsincontexto =~ /^QUEUEAGENT/i )
                                    )
                                    && $agent_status == 1
                                  )
                                {
                                    my $valip    = "";
                                    my $iniagent = "";
                                    if ( defined( $agent_to_channel{"$server^$canalsincontexto"} ) ) {
                                        $iniagent = $agent_to_channel{"$server^$canalsincontexto"};
                                    }
                                    elsif ( defined( $channel_to_agent{"$server^$canalsincontexto"} ) ) {
                                        $iniagent = $channel_to_agent{"$server^$canalsincontexto"};
                                    }
                                    log_debug( "$heading quehace2 corto y es agente, pushing settimer y settext to idle", 256 )
                                      if DEBUG;
                                    $botontimer{$interno}     = time();
                                    $botontimertype{$interno} = "IDLE";
                                    push @mensajefinal, "$interno|settimer|0\@IDLE";
                                    if ( exists( $boton_ip{"$iniagent-XXXX"} ) ) {
                                        $valip = $boton_ip{"$iniagent-XXXX"};
                                    }
                                    if ( $valip eq "" ) { $valip = "Idle"; }
                                    $botonclid{$interno} = $valip;
                                    push @mensajefinal, "$interno|settext|$valip";
                                }
                                else {
                                    my $valip = "";
                                    log_debug( "$heading quehace2 corto, no es agente, pongo timer en cero", 256 )
                                      if DEBUG;

                                    push @mensajefinal, "$interno|settimer|0\@STOP";
                                    if ( defined( $boton_ip{"$canal-XXXX"} ) ) {
                                        $valip = $boton_ip{"$canal-XXXX"};
                                        push @mensajefinal, "$interno|settext|$valip";
                                        $botonclid{$interno} = $valip;
                                    }
                                    else {
                                        if ( defined( $botonpermanenttext{$interno} ) ) {
                                            push @mensajefinal, "$interno|settext|$botonpermanenttext{$interno}";
                                            log_debug( "interno $interno tenia permanente $botonpermanenttext{$interno}", 128 )
                                              if DEBUG;
                                        }
                                        $botonclid{$interno} = "";

                                    }

                                    if ( defined( $botonalpha{$interno} ) ) {
                                        if ( $botonalpha{$interno} ne "" ) {
                                            push @mensajefinal, "$interno|setalpha|$botonalpha{$interno}";
                                        }
                                    }
                                }
                            }

                            if ( $quehace eq "registrado" || $quehace eq "noregistrado" || $quehace eq "unreachable" ) {
                                if ( $botonregistrado{$interno} ne "$quehace|$dos" ) {

                                    # changed registration state, do something
                                    if ( $quehace eq "registrado" ) {
                                        if ( defined( $botonalpha{$interno} ) ) {
                                            if ( $botonalpha{$interno} ne "" ) {
                                                push @mensajefinal, "$interno|setalpha|50";
                                            }
                                            else {
                                                push @mensajefinal, "$interno|setalpha|100";
                                            }
                                        }
                                    }
                                    $botonregistrado{$interno} = "$quehace|$dos";
                                }
                            }

                            if ( $quehace eq "paused" ) {
                                $boton_paused{$interno} = $dos;
                            }
                            elsif ( $quehace eq "agents_paused" ) {
                                $boton_agentpaused{$interno} = $dos;
                            }
                            elsif ( $quehace eq "agents_ready" ) {
                                $boton_agentready{$interno} = $dos;
                            }
                            elsif ( $quehace eq "agents_busy" ) {
                                $boton_agentbusy{$interno} = $dos;
                            }
                            elsif ( $quehace eq "agents_logedof" ) {
                                $boton_agentlogedof{$interno} = $dos;
                            }

                            log_debug( "$heading Agrego mensaje final $interno|$quehace2|$dos", 256 ) if DEBUG;

                            #if (defined($mensajefinal) && $interno ne "")
                            $cuantas = @mensajefinal;
                            if ( $cuantas > 0 && $interno ne "" ) {
                                if ( exists $cambiaron{$interno} ) {

                                    log_debug( "$heading Existe cambiaron($interno) = $cambiaron{$interno}", 256 )
                                      if DEBUG;

                                    if ( $cambiaron{$interno} == 1 ) {
                                        foreach (@mensajefinal) {
                                            log_debug( "$heading pushing respuestas $_ because cambiaron($interno)=1", 256 )
                                              if DEBUG;
                                            push @respuestas, $_;
                                        }
                                    }
                                }
                                else {
                                    log_debug( "$heading No existe cambiaron($interno)", 256 ) if DEBUG;
                                }
                                if ( $toda ne "" ) {
                                    my $otromensajefinal = "$interno|info|$toda";
                                    push( @respuestas, $otromensajefinal );
                                    $toda = "";
                                }
                            }
                        }

                        $laststatus{$interno} = $estadoboton{$interno};
                    }
                }
            }
        }
    }

    @respuestas = unique(@respuestas);
    $cuantas    = $#respuestas + 1;
    log_debug( "$heading end, return $cuantas", 256 ) if DEBUG;
    foreach my $valor (@respuestas) {
        log_debug( "$heading END SUB: returns $valor", 256 ) if DEBUG;
    }
    $tab = substr( $tab, 0, -1 ) if DEBUG;
    return @respuestas;
}

sub group_count {
    my $interno = shift;
    my $canal   = shift;
    my $plural  = "";
    my $return  = "";
    if ( @{ $sesbot{$interno} } > 1 ) {
        $plural = "s";
    }
    if ( @{ $sesbot{$interno} } > 0 ) {
        my $cuantos = @{ $sesbot{$interno} };
        my ( $text, $textriginal, $buttontext ) = translate( $canal, "&channels,$cuantos,$plural", "", "" );
        $return = "$interno|settext|$text";
        $botonpermanenttext{$interno} = $text;
    }
    else {
        $botonpermanenttext{$interno} = "";
        $return = "$interno|corto|";
    }
    return $return;
}

sub translate {

    my $canal       = shift;
    my $dos         = shift;
    my $dosoriginal = shift;
    my $buttontext  = shift;

    my @return;

    # Try to find the text string in the lang conf hash
    my $ctx = "";
    if ( $canal =~ m/.*\&(.*)/ ) {
        $ctx = $1;
    }
    else {
        $ctx = "DEFAULT";
    }
    my ( $partstring, $varsub ) = split( /,/, $dos, 2 );
    $partstring =~ s/&(.*)/$1/g;
    if ( !defined($varsub) ) { $varsub = ""; }
    my $traduc = $language->{$ctx}{$partstring};
    if ( $varsub =~ m/.*&/ ) {

        # Horrible hack, if variable ends in &, set the text
        # between brackets so it will be displayed as a whole in the
        # button's clid area
        $varsub = substr( $varsub, 0, -1 );
        my ( $var1, $var2 ) = split( /,/, $varsub );
        $traduc =~ s/\$1/$var1/g if defined $var1;
        $traduc =~ s/\$2/$var2/g if defined $var2;
        $traduc =~ s/\$1//g;
        $traduc =~ s/\$2//g;
        $dosoriginal = $dos;
        $dos         = "[" . $traduc . "]";
        $buttontext  = $dos;
    }
    else {
        my ( $var1, $var2 ) = split( /,/, $varsub );
        $traduc =~ s/\$1/$var1/g if defined $var1 && $var1 ne "";
        $traduc =~ s/\$2/$var2/g if defined $var2 && $var1 ne "";
        $traduc =~ s/\$1//g;
        $traduc =~ s/\$2//g;
        $dosoriginal = $dos;
        $dos         = $traduc;
    }

    push @return, $dos;
    push @return, $dosoriginal;
    push @return, $buttontext;

    return @return;
}

sub clean_inmemory_state_for_server {

    my $server = shift;
    my @botones_a_limpiar;
    log_debug( "CLEAN_INMEMORY from server $server)", 16 ) if DEBUG;
    foreach ( keys(%buttons) ) {
        my $btn_server = $_;
        $btn_server =~ s/^(\d+)\^.*/$1/g;
        if ( $btn_server eq $server ) {
            push @botones_a_limpiar, $buttons{$_};
        }
    }

    foreach (@botones_a_limpiar) {
        delete $estadoboton{$_};
        delete $botonled{$_};
        delete $botonlabelonly{$_};
        delete $botonvoicemail{$_};
        delete $botonvoicemailcount{$_};
        delete $botonalpha{$_};
        delete $botonledcolor{$_};
        delete $botonqueue{$_};
        delete $botonqueuemember{$_};
        delete $botonqueue_count{$_};
        delete $botonpark{$_};
    }
}

sub manager_connection {
    my $host       = "";
    my $user       = "";
    my $pass       = "";
    my $heading    = "** MANAGER CONNECTION";
    my $temphandle = "";
    my $contador   = 0;
    my $port       = "5038";

    foreach my $mhost (@manager_host) {
        if ( defined($mhost) ) {
            $host = $mhost;
            $user = $manager_user[$contador];
            $pass = $manager_secret[$contador];
            $port = $manager_port[$contador] if defined( $manager_port[$contador] );

            if ( defined( $manager_conectado[$contador] ) ) {
                if ( $manager_conectado[$contador] == 1 ) {
                    $contador++;
                    next;
                }
            }

            log_debug( "$heading Connecting to $mhost:$port (Server $contador)", 1 ) if DEBUG;

            $p[$contador] = new IO::Socket::INET->new(
                PeerAddr => $manager_host[$contador],
                PeerPort => $port,
                Proto    => "tcp",
                Timeout  => '2',
                Type     => SOCK_STREAM
            );

            if ( !$p[$contador] ) {
                log_debug( "$heading Couldn't connect to $mhost:$port (Server $contador)", 1 ) if DEBUG;
                $p[$contador]                    = "";
                $manager_conectado[$contador]    = 0;
                $manager_desconectado{$contador} = time();
                $contador++;
                next;
            }
            else {
                log_debug( "$heading Connected  to $mhost:$port (Server $contador)", 1 ) if DEBUG;
                $manager_conectado[$contador] = 1;
                if ( defined( $manager_desconectado{$contador} ) ) {
                    delete $manager_desconectado{$contador};
                }
                $p[$contador]->autoflush(1);
                $ip_addy{ $p[$contador] } = peerinfo( $p[$contador], 1 );
                clean_inmemory_state_for_server($contador);
            }

            my $mask = "";
            if ( defined( $event_mask[$contador] ) ) {
                $mask_hash{ $p[$contador] } = $event_mask[$contador];
            }

            $manager_socket{ $p[$contador] } = $manager_host[$contador] . "|" . $manager_user[$contador] . "|" . $manager_secret[$contador];
            my $command = "";

            # If using astmanproxy, override authentication
            if ( $astmanproxy_host ne "" ) {
                $autenticado{ $p[$contador] } = 1;
                send_eventmask( $p[$contador] );
                send_initial_status( $p[$contador] );
            }
            else {
                if ( $auth_md5 == 1 ) {
                    $command = "Action: Challenge\r\n";
                    $command .= "AuthType: MD5\r\n\r\n";
                }
                else {
                    $command = "Action: Login\r\n";
                    $command .= "Username: $user\r\n";
                    $command .= "Secret: $pass\r\n\r\n";
                }
                send_command_to_manager( $command, $p[$contador], 1 );
            }

        }
        $contador++;
    }

    # Add AMI handles into IO::Select
    foreach (@p) {
        if ( defined($_) ) {
            $O->add($_);
        }
    }

}

sub send_eventmask {
    my $socket = shift;
    if ( defined( $mask_hash{$socket} ) ) {
        my $comando = "Action: Events\r\n";
        $comando .= "EventMask: " . $mask_hash{$socket} . "\r\n\r\n";
        send_command_to_manager( $comando, $socket );
    }
}

sub clean_socket {
    my $socket  = shift;
    my $heading = "** CLEAN SOCKET ";
    log_debug( "$heading connection lost removing socket $socket", 1 ) if DEBUG;

    $O->remove($socket);
    $socket->close;
    delete $ip_addy{$socket};
    if ( exists( $manager_socket{$socket} ) ) {
        delete $manager_queue{$socket};

        # The closed connections belong to an asterisk manager port
        my @pp      = ();
        my $counter = 0;
        foreach my $cual (@p) {
            if ( defined($cual) ) {
                if ( $cual eq $_ ) {
                    log_debug( "$heading Connection lost to server $counter", 1 ) if DEBUG;
                    $manager_conectado[$counter] = 0;
                    $manager_desconectado{$counter} = time();
                    delete $autenticado{$_};
                    push @pp, "";
                }
                else {
                    log_debug( "$heading still connected to $ip_addy{$cual}", 16 ) if DEBUG;
                    push @pp, $cual;
                }
            }
            $counter++;
        }
        @p = @pp;
    }
    else {

        # The closed socket was from a client
        log_debug( "$heading flash client connection lost", 1 ) if DEBUG;
        delete $client_queue{$socket};
        delete $client_queue_nocrypt{$socket};
        my $cualborrar = $socket;
        my @temp = grep( !/\Q$cualborrar\E/, @flash_clients );
        @flash_clients = @temp;
        delete $keys_socket{$socket};
        &print_clients();
    }
}

collect_includes( "op_buttons.cfg", "buttons" );
collect_includes( "op_style.cfg",   "style" );
collect_includes( "op_server.cfg",  "server" );
read_buttons_config();
read_server_config();
read_language_config();
read_astdb_config();
genera_config();

# Tries to open the listening socket
$m = new IO::Socket::INET(
    Listen    => 1,
    LocalAddr => $listen_addr,
    LocalPort => $listen_port,
    ReuseAddr => 1,
    Blocking  => 0
  )
  or die "\nCan't listen to port $listen_port\n";
$O = new IO::Select();
$O->add($m);

# Connects to the asterisk boxes
manager_connection();

$/ = "\0";

# MAIN
# Endless loop
while (1) {
    my $heading = "** MAIN";

    # Attempt reconnections to dead asterisk servers  every 10 seconds
    if (%manager_desconectado) {
        while ( my ( $key, $val ) = each(%manager_desconectado) ) {
            my $seconds_to_go = ( time() - $val ) % 10;
            if ( $seconds_to_go == 9 ) {
                manager_connection();
            }
        }
    }

    while ( @S = $O->can_read(0.1) )    # profile
    {
        foreach (@S) {
            my $handle = $_;

            if ( $_ == $m ) {

                # New client connection
                my $C = $m->accept;
                $ip_addy{$C} = peerinfo( $C, 1 );
                log_debug( "$heading New client connection $ip_addy{$C}", 1 ) if DEBUG;
                push( @flash_clients, $C );
                $O->add($C);
                $C->blocking(0);
            }
            else {

                # Its not a new client connection
                my %i;
                my $R;
                $R = sysread( $_, $i{$handle}, BYTES_TO_READ );    # profile
                if ( defined($R) && $R == 0 ) {

                    # Could not read.. close the socket
                    log_debug( "$heading closing $ip_addy{$_}", 1 ) if DEBUG;
                    clean_socket($_);
                }
                else {
                    $bloque_completo{$handle} = ""
                      if ( !defined( $bloque_completo{$handle} ) );
                    $buferbloque{$handle} = ""
                      if ( !defined( $buferbloque{$handle} ) );
                    if ( $buferbloque{$handle} ne "" ) {
                        $bloque_completo{$handle} = $buferbloque{$handle};
                        $buferbloque{$handle}     = "";
                    }
                    $bloque_completo{$handle} .= $i{$handle};

                    next
                      if ( $bloque_completo{$handle} !~ /\r\n\r\n/
                        && $bloque_completo{$handle} !~ /\0/ );

                    # From here we have one or more Event block
                    # to process. The last one might be incomplete
                    # so we have to buffer it for the next can_read

                    my @event_blocks = ();
                    if ( $bloque_completo{$handle} =~ /\0/ ) {
                        $bloque_completo{$handle} =~ s/\0/\$\$\$\0/g;
                        @event_blocks = split /\0/, $bloque_completo{$handle};
                    }
                    else {
                        $bloque_completo{$handle} =~ s/\r\n\r\n/\$\$\$\r\n\r\n/g;
                        @event_blocks = split /\r\n\r\n/, $bloque_completo{$handle};
                    }

                    foreach my $block (@event_blocks) {

                        if ( $block !~ /\$\$\$/ ) {

                            # if there is no end, buffer the block
                            $buferbloque{$handle} = $block;
                        }
                        else {
                            $block =~ s/\$\$\$/\n/g;

                            # process an individual manager block stored in $block
                            if ( exists( $manager_socket{$handle} ) ) {
                                my @part = split( /\|/, $manager_socket{$handle} );
                                log_debug( "$heading End of block from $part[0]", 16 ) if DEBUG;
                            }

                            $bloque_final = $block;
                            $bloque_final =~ s/([^\r])\n/$1\r\n/g;    # Reemplaza \n solo por \r\n
                            $bloque_final =~ s/\r\n\r\n/\r\n/g;
                            $bloque_completo{$handle} = "";

                            ##################################################
                            # If we have a Server header, asume this is comming from astmanproxy
                            # and replace the server address to the server index number that FOP uses
                            # and that is specified in op_server.cfg file with the astmanproxy_server
                            # keyword
                            if ( $bloque_final =~ /.*?^Server: .*$/xms ) {
                                $astmanproxy_server = $bloque_final;
                                $astmanproxy_server =~ s/.*?^Server: \s ([\.\w]*) .*? \z/$1/xms;
                                my $que_manager = 0;
                                foreach my $address (@astmanproxy_servers) {
                                    if ( $address eq $astmanproxy_server ) {
                                        $bloque_final =~ s/(.*?^Server: \s)([\.\w]*)( .*? \z)/${1}${que_manager}${3}/xms;
                                    }
                                    $que_manager++;
                                }
                            }
                            else {

                                # Add the asterisk server number as a part of the event block
                                my $que_manager = 0;
                                foreach my $handle_manager_connected (@p) {
                                    if ( $handle_manager_connected eq $handle ) {

                                        $bloque_final = $bloque_final . "Server: $que_manager";
                                    }
                                    $que_manager++;
                                }
                            }

                            ####################################################
                            # This block is just for logging in the event
                            # to stdout
                            if ( ( $debuglevel & 1 ) && DEBUG ) {
                                my @lineas = split( "\r\n", $bloque_final );
                                foreach my $linea (@lineas) {
                                    if ( exists( $manager_socket{$handle} ) ) {
                                        my $linea_formato = sprintf( "%-15s <- %s", $ip_addy{$handle}, $linea );
                                        log_debug( $linea_formato, 1 ) if DEBUG;
                                    }
                                }
                                $global_verbose = 'separator';
                            }
                            ##################################################

                            foreach my $C ( $O->handles ) {
                                if ( $C == $handle ) {
                                    log_debug( "$heading AST event received...", 16 ) if DEBUG;

                                    # Asterisk event received
                                    # Read the info and arrange it into blocks
                                    # for processing in 'procesa_bloque'
                                    if (   $bloque_final =~ /Event:/
                                        || $bloque_final =~ /Message: Mailbox/
                                        || $bloque_final =~ /SIP-CanReinvite/
                                        || $bloque_final =~ /ActionID: monitor-/
                                        || $bloque_final =~ /Message: Timeout/ )
                                    {
                                        log_debug( "$heading There's an 'Event' in the event block", 32 ) if DEBUG;
                                        my @lineas = split( /\r\n/, $bloque_final );
                                        @bloque = ();
                                        my $block_count = -1;
                                        foreach my $p (@lineas) {
                                            if ( $p =~ /ActionID: autosipentry/ ) {
                                                $block_count++;
                                                $bloque[$block_count]{Event} = "sippeerentrylong";
                                            }
                                            elsif ( $p =~ /ActionID: monitor-/ ) {
                                                my ( undef, $quemonitor, $quecana ) = split( /-/, $p, 3 );
                                                $block_count++;
                                                $bloque[$block_count]{Event}   = "response-$quemonitor";
                                                $bloque[$block_count]{Channel} = $quecana;
                                            }
                                            my $my_event = "";
                                            if ( $p =~ /^Event:/ ) {
                                                $block_count++;
                                                log_debug( "$heading Event detected block_count = $block_count", 128 )
                                                  if DEBUG;
                                            }
                                            elsif ( $p =~ /Message: Mailbox/ ) {
                                                $my_event = "MessageWaiting";    # Fake event
                                                $block_count++;
                                                log_debug( "$heading Event mailbox detected block_count = $block_count", 128 )
                                                  if DEBUG;
                                            }
                                            my ( $atributo, $valor ) = split( /: /, $p, 2 );
                                            if ( defined $atributo && $atributo ne "" ) {
                                                if ( $my_event ne "" ) {
                                                    $atributo = "Event";
                                                    $valor    = $my_event;
                                                    log_debug( "$heading Fake event generated $atributo=$valor", 128 )
                                                      if DEBUG;
                                                }
                                                if ( length($atributo) >= 1 ) {
                                                    if ( $block_count < 0 ) {
                                                        $block_count = 0;
                                                    }
                                                    $bloque[$block_count]{"$atributo"} = $valor;
                                                }
                                            }
                                        }
                                        log_debug( "$heading There are $block_count blocks for processing", 128 )
                                          if DEBUG;
                                        @respuestas = ();
                                        log_debug( "$heading Answer block cleared", 32 ) if DEBUG;
                                        @respuestas = digest_event_block( \@bloque, "real", $C, $astmanproxy_server );
                                        @masrespuestas = ();
                                        while (@fake_bloque) {
                                            my @respi = digest_event_block( \@fake_bloque, "fake", $C, $astmanproxy_server );
                                            foreach (@respi) {
                                                push @masrespuestas, $_;
                                            }
                                        }
                                    }
                                    elsif ( $bloque_final =~ /--END COMMAND--/ ) {
                                        log_debug( "$heading There's an 'END' in the event block", 32 ) if DEBUG;
                                        $todo .= $bloque_final;
                                        process_cli_command($todo);
                                        my $cuantos = @bloque;
                                        log_debug( "$heading There are $cuantos blocks for processing", 128 ) if DEBUG;
                                        @respuestas = digest_event_block( \@bloque, "real", $C, $astmanproxy_server );
                                        @masrespuestas = ();
                                        while (@fake_bloque) {
                                            my @respi = digest_event_block( \@fake_bloque, "fake", $C, $astmanproxy_server );
                                            foreach (@respi) {
                                                push @masrespuestas, $_;
                                            }
                                        }
                                        $todo = "";
                                    }
                                    elsif ( $bloque_final =~ /<msg/ ) {
                                        $bloque_final =~ s/\n//g;
                                        log_debug( "$heading Processing command received from flash clients...", 32 )
                                          if DEBUG;
                                        process_flash_command( $bloque_final, $_ );
                                        @respuestas   = ();
                                        $bloque_final = "";
                                        $todo         = "";
                                    }
                                    elsif ( $bloque_final =~ /Challenge:/ ) {
                                        my @lineas = split( /\r\n/, $bloque_final );
                                        foreach my $p (@lineas) {
                                            if ( $p =~ /Challenge:/ ) {
                                                $p =~ s/^Challenge: (.*)/$1/g;
                                                $md5challenge = $p;
                                            }
                                        }
                                        manager_login_md5( $md5challenge, $C );
                                    }
                                    elsif ( $bloque_final =~ /Message: Authentication ac/i ) {

                                        # Authentication Accepted, enable sending commands
                                        $autenticado{$C} = 1;
                                        send_eventmask($C);
                                        send_initial_status($C);
                                    }
                                    elsif ( $bloque_final =~ /<policy-file-request\/>/ ) {
                                        send_policy_to_flash($C);
                                    }
                                    else {
                                        log_debug( "$heading No 'Event' nor 'End'. Erasing block...", 32 )  if DEBUG;
                                        log_debug( "$bloque_final",                                   255 ) if DEBUG;

                                        # No Event in the block. Lets clear it up...
                                        @bloque = ();
                                        $todo .= $bloque_final;
                                    }
                                }
                                else {

                                    # Send messages to Flash clients
                                    @respuestas    = ( @respuestas, @masrespuestas );
                                    @masrespuestas = ();
                                    @respuestas    = unique(@respuestas);

                                    if ( !defined( $autenticado{$C} ) ) {    # try to exclude manager connections
                                        foreach my $valor (@respuestas) {
                                            if ( defined( $flash_contexto{$C} ) ) {
                                                send_status_to_flash( $C, $valor, 0 );
                                            }
                                        }                                    # end foreach respuestas
                                    }
                                }
                            }    # end foreach handles

                        }
                    }

                }    # end else the handle is readable
            }    # end else for active connections
        }    # end foreach @S -> can read
    }    # while can read

    foreach my $sacket ( $O->can_write(0.1) )    # profile
    {
        if ( defined( $client_queue{$sacket} ) ) {

            # Loop through command buffer to send to  clients
            while ( my $comd = shift @{ $client_queue{$sacket} } ) {
                my $tolog = shift @{ $client_queue_nocrypt{$sacket} };
                my $ret = actual_syswrite( $sacket, $comd, "isclient", $tolog );
                if ( $ret == -1 ) {
                    log_debug( "Partial syswrite, buffering for client $ip_addy{$sacket}", 1 ) if DEBUG;
                    last;
                }

            }
        }
        if ( defined( $manager_queue{$sacket} ) ) {

            # Loop through command buffer to send to managers
            while ( my $comd = shift @{ $manager_queue{$sacket} } ) {
                my $cuantos = @{ $manager_queue{$sacket} };

                my $ret = actual_syswrite( $sacket, $comd, "ismanager", $comd );
                if ( $ret == -1 ) {
                    log_debug( "Partial syswrite, buffering for server $ip_addy{$sacket}", 1 ) if DEBUG;
                    last;
                }

            }
        }
    }
}    # endless loop

sub actual_syswrite {
    my ( $socket, $encriptadofinal, $whom, $log ) = @_;
    my $largo = length($encriptadofinal);
    my $res   = syswrite( $socket, $encriptadofinal, $largo );

    # select( undef, undef, undef, 0.01 );

    if ( defined $res && $res > 0 ) {
        if ( $res != $largo ) {

            # Could not write the whole command, buffer
            # the rest for later
            my $offset = $largo - $res;
            $offset = $offset * -1;
            my $buf = substr( $encriptadofinal, $offset );
            unshift( @{ $client_queue{$socket} },         $buf );
            unshift( @{ $client_queue_nocrypt{$socket} }, $log );
            log_debug( "Partial syswrite, len $res but $largo written", 1 ) if DEBUG;

            my $cuantos = @{ $client_queue{$socket} };
            if ( $cuantos > 200 ) {
                &clean_socket($socket);
            }
            return -1;
        }
        else {

            # Write succesfull, log to stdout
            $log = substr( $log, 0, -1 );
            if ( $debuglevel > 0 ) {
                if ( $whom eq "isclient" ) {
                    my $linea_formato = sprintf( "%-15s => %s", $ip_addy{$socket}, $log );
                    log_debug( "$linea_formato", 8 ) if DEBUG;
                    $global_verbose = "separador";
                }
                else {
                    $log =~ s/\r//g;
                    $log =~ s/\n//g;
                    if ( $log ne "" ) {
                        my $linea_formato = sprintf( "%-15s -> %s", $ip_addy{$socket}, $log );
                        log_debug( "$linea_formato", 2 ) if DEBUG;
                    }
                    else {
                        $global_verbose = "separador";
                    }
                }
            }
        }
    }
    elsif ( $! == EWOULDBLOCK ) {

        # would block: not an error
        # handle blocking, by trying again later
        log_debug( "Write wouldblock, buffering for $ip_addy{$socket}", 1 ) if DEBUG;
        push( @{ $client_queue{$socket} },         $encriptadofinal );
        push( @{ $client_queue_nocrypt{$socket} }, $log );
        my $cuantos = @{ $client_queue{$socket} };
        if ( $cuantos > 200 ) {
            &clean_socket($socket);
        }
    }
    else {
        log_debug( "Write error on $ip_addy{$socket}", 1 ) if DEBUG;
        &clean_socket($socket);
    }
    return 0;
}

sub get_transfer_channel {

    my $origin_channel = shift;
    my $datosflash     = shift;

    my @cuales_transferir = ();

    my $local_reverse = $reverse_transfer;

    if ( $origin_channel =~ m/^PARK/i || $origin_channel =~ m/^QUEUE/i || $origin_channel =~ m/^\d/ ) {
        $local_reverse = 0;
        log_debug( "** GET TRANSFER Disable reverse transfer for $origin_channel!", 16 ) if DEBUG;
    }

    if ( $local_reverse == 1 ) {
        log_debug( "** !! REVERSE TRANSFER", 16 ) if DEBUG;

        # Transfer the session from the *other* button
        @cuales_transferir = extraer_todos_los_enlaces_de_un_canal( $origin_channel, $button_server{$datosflash} );
        if ( @cuales_transferir == 0 ) {
            log_debug( "** !! REVERSE TRANSFER No reverse available, using regular sesbot to find the linked channels", 16 )
              if DEBUG;
            if ( $sesbot{$datosflash} ) {
                if ( @{ $sesbot{$datosflash} } ) {
                    @cuales_transferir = extraer_todos_los_enlaces_de_un_canal( $cuales_transferir[0], $button_server{$datosflash} );
                }
            }
        }
    }
    else {
        log_debug( "** !! NORMAL TRANSFER", 16 ) if DEBUG;

        # Transfer the session from the same button
        if ( $sesbot{$datosflash} ) {
            if ( @{ $sesbot{$datosflash} } ) {
                @cuales_transferir = @{ $sesbot{$datosflash} };
            }
            else {
                @cuales_transferir = ();
            }
        }
        else {
            @cuales_transferir = ();
        }
    }

    return @cuales_transferir;

}

sub process_flash_command {

    # This function process a command received from a Flash client
    # Including request of transfers, hangups, etc
    my $comando        = shift;
    my $socket         = shift;
    my $datosflash     = "";
    my $accion         = "";
    my $password       = "";
    my $valor          = "";
    my $origin_channel = "";
    my $origin_server  = "";
    my $canal_destino  = "";
    my $destin_server  = "";
    my $contexto       = "";
    my $btn_destino    = "0";
    my $extension_destino;
    my $origin_context = "";
    my $canal;
    my $nroboton;
    my $destino;
    my $sesion;
    my @partes;
    my $ultimo;
    my $clid;
    my $myclave;
    my $md5clave;
    my @pedazos;
    my $panelcontext;
    my $auto_conf_exten;
    my $conference_context;
    my $bargerooms;
    my $found_room;
    my $servidor_dial = "";
    my $heading       = "-- PROCESS_FLASH_COMMAND";
    my $calltimeout   = 0;

    my $linea_formato = sprintf( "%-15s <= %s", $ip_addy{$socket}, $comando );
    log_debug( "$linea_formato", 4 ) if DEBUG;

    $tab = $tab . "\t" if DEBUG;

    $comando =~ s/<msg data=\"(.*)\"\s?\/>/$1/g;    # Removes XML markup
    ( $datosflash, $accion, $password ) = split( /\|/, $comando );
    chop $password;
    log_debug( "$heading datosflash $datosflash accion $accion password $password", 16 ) if DEBUG;

    if ( $accion =~ /\+/ ) {

        # The command has a timeout for the call
        $accion =~ s/(.*)\+(.*)\+(.*)/$1$3/g;
        $calltimeout = $2;
    }

    my $elementname = $datosflash;
    $elementname =~ s/(.*)\.(.*)/$2/g;
    $elementname =~ s/([^\@]*)(.*)/$1/g;
    $elementname =~ s/\d//g;

    $datosflash =~ s/_level0\.casilla(\d+)/$1/g;
    $datosflash =~ s/_level0\.rectangulo(\d+).*/$1/g;

    log_debug( "$heading datosflash before context $datosflash", 128 ) if DEBUG;

    # Appends context if defined because my crappy regexp only extracts digits
    # FIXME make a regexp that extract digits and digits@context
    if ( defined( $flash_contexto{$socket} ) ) {
        if ( $flash_contexto{$socket} ne "" ) {
            if ( $datosflash =~ /\@/ ) {

                # No need to append context
            }
            else {
                $datosflash .= "\@" . $flash_contexto{$socket};
            }
        }
    }

    log_debug( "$heading datosflash after context $datosflash", 128 ) if DEBUG;

    undef $origin_channel;

    # Flash clients send a "contexto" command on connect indicating
    # the panel context they want to receive. We populate a hash with
    # sockets/contexts in order to send only the events they want
    # And because this is an initial connection, it triggers a status
    # request to Asterisk

    if ( $accion =~ /^contexto\d+/ ) {

        my ( undef, $contextoenviado ) = split( /\@/, $datosflash );

        if ( defined($contextoenviado) ) {
            $flash_contexto{$socket} = $contextoenviado;
        }
        else {
            $flash_contexto{$socket} = "";
        }
        if ( $datosflash =~ /^1/ ) {
            $no_encryption{$socket} = 1;
        }
        else {
            $no_encryption{$socket} = 0;
        }

        sends_key($socket);
        sends_version($socket);

        first_client_status($socket);
        $tab = substr( $tab, 0, -1 ) if DEBUG;
        return;
    }
    if ( defined( $flash_contexto{$socket} ) ) {
        $panelcontext = $flash_contexto{$socket};
    }
    else {
        $panelcontext = "";
    }
    if ( $panelcontext eq "" ) { $panelcontext = "GENERAL"; }

    if ( defined( $config->{$panelcontext}{conference_context} ) ) {
        $conference_context = $config->{$panelcontext}{conference_context};
    }
    else {
        if ( defined( $config->{GENERAL}{conference_context} ) ) {
            $conference_context = $config->{GENERAL}{conference_context};
        }
        else {
            $conference_context = "";
        }
    }

    if ( defined( $config->{$panelcontext}{barge_rooms} ) ) {
        $bargerooms = $config->{$panelcontext}{barge_rooms};
        ( $first_room, $last_room ) = split( /-/, $bargerooms );
    }
    else {
        if ( defined( $config->{GENERAL}{barge_rooms} ) ) {
            $bargerooms = $config->{GENERAL}{barge_rooms};
            ( $first_room, $last_room ) = split( /-/, $bargerooms );
        }
        else {
            $bargerooms = "";
        }
    }

    # We have the origin button number from the drag&drop in the 'datos'
    # variable. We need to traverse the %buttons hash in order to extract
    # the channel name and the panel context, used to find the destination
    # button of the command if any
    if (   $accion =~ /^meetmemute/
        || $accion =~ /^meetmeunmute/
        || $accion =~ /^bogus/
        || $accion =~ /^restart/ )
    {
        $origin_channel = "bogus";
    }
    else {
        my $datosflash_sincontexto = $datosflash;
        $datosflash_sincontexto =~ s/(.*)\@(.*)/$1/g;

        if ( is_number($datosflash_sincontexto) ) {

            # If the originator is a number, assume button position
            # on fop, extract the channel name from the button_reverse hash

            $canal = $buttons_reverse{$datosflash};

            # A button key with an & is for a context channel
            # A button key with an = is for a trunk   channel
            # This bit of code just cleans the channel name and context
            if ( $canal =~ m/&/ ) {
                @pedazos = split( /&/, $canal );
                $origin_context = $pedazos[1];
                ( $origin_server, $origin_channel ) = split( /\^/, $pedazos[0] );
            }
            else {
                ( $origin_server, $origin_channel ) = split( /\^/, $canal );
            }
        }
        else {

            # The origin has letters, assume its a channel name
            # with a possible extensions.conf context after '@'
            # (We have already removed the '@fop_context')

            if ( $datosflash_sincontexto =~ /\@/ ) {
                $contexto = $datosflash_sincontexto;
                $datosflash_sincontexto =~ s/(.*)\@(.*)/$1/g;
                $contexto               =~ s/(.*)\@(.*)/$2/g;
            }

            $origin_channel = $datosflash_sincontexto;

            # If we receive a channel name for the dial command
            # we default to server number 1 to send the command
            $servidor_dial = "default";

        }
    }

    if ( $origin_channel =~ m/^clid/i ) {
        $contexto = $datosflash;

        if ( $contexto =~ m/\@/ ) {
            $contexto =~ s/(.*)\@(.*)/$2/g;
            if ( defined($contexto) && $contexto ne "" ) {
                $contexto = "&" . $contexto;
            }
        }
        else {
            $contexto = "";
        }

        my $local_channel_for_clid_buttons = $extension_transfer{"$origin_server^$origin_channel$contexto"};
        $local_channel_for_clid_buttons =~ s/-?\d+\^(.*)/$1/g;
        $origin_channel = "Local/" . $local_channel_for_clid_buttons;

        # Add the reverse transfer extension to make the callerid in originate work
        $extension_transfer{"$origin_server^$origin_channel$contexto"} = "Local/$local_channel_for_clid_buttons";
    }

    if ( $accion =~ /^restrict/ && defined($origin_channel) ) {
        my $contextoaagregar = "";
        if ( $panelcontext ne "GENERAL" ) {
            $contextoaagregar = "&$panelcontext";
        }
        $restrict_channel = $origin_channel;
        log_debug( "$heading RESTRICT commands to channel $restrict_channel", 32 ) if DEBUG;
        my $indice = "0^$restrict_channel$contextoaagregar";
        log_debug( "$heading RESTRICT indice $indice", 32 ) if DEBUG;
        my $btn_num = "0";
        if ( defined( $buttons{$indice} ) ) {
            $btn_num = $buttons{$indice};

            # $btn_num =~ s/(.*)\@(.*)/$1/g;
        }
        if ( $btn_num ne "0" ) {
            log_debug( "$heading RESTRICT btn_num $btn_num", 32 ) if DEBUG;
            my $manda = "$btn_num|restrict|0";
            send_status_to_flash( $socket, $manda, 0 );
        }
        else {
            log_debug( "$heading RESTRICT channel not found $indice!", 32 ) if DEBUG;
        }
        $tab = substr( $tab, 0, -1 ) if DEBUG;
        return;
    }

    if ( defined($origin_channel) ) {
        log_debug( "$heading origin_channel = $origin_channel", 64 ) if DEBUG;

        my $no_security_code = "";
        if ( defined( $config->{$panelcontext}{security_code} ) ) {
            $myclave = $config->{$panelcontext}{security_code} . $keys_socket{$socket};
            log_debug( "$heading usando key " . $keys_socket{$socket}, 16 ) if DEBUG;
            $no_security_code = $config->{$panelcontext}{security_code};
        }
        else {
            $myclave = "";
            $myclave = $config->{GENERAL}{security_code} . $keys_socket{$socket};
            log_debug( "$heading usando key " . $keys_socket{$socket}, 16 ) if DEBUG;
            $no_security_code = $config->{GENERAL}{security_code};
        }

        if ( $myclave ne "" ) {
            $md5clave = MD5HexDigest($myclave);
        }

        if (   ( "$password" eq "$md5clave" )
            || ( $accion =~ /^dial/ && $cdial_nosecure == 1 )
            || ( $no_security_code eq "" ) )
        {
            sends_correct($socket);
            log_debug( "** The channel selected is $origin_channel and the security code matches", 16 ) if DEBUG;
            sends_key($socket);
            if ( $accion =~ /^restart/ ) {

                $comando = "Action: Command\r\n";
                $comando .= "Command: restart when convenient\r\n\r\n";
                log_debug( "!! Command received: restart when convenient", 32 ) if DEBUG;
                send_command_to_manager( $comando, $p[0], 0, $astmanproxy_servers[ $button_server{$datosflash} ] );

                # FIXME restart only works for the 1st server defined
                alarm(10);
                $tab = substr( $tab, 0, -1 ) if DEBUG;
                return;
            }

            if ( $accion =~ /-/ ) {

                #if action has an "-" the command has clid text to pass
                @partes = split( /-/, $accion );
                $ultimo = @partes;
                $ultimo--;
                $btn_destino = $partes[$ultimo];
                $ultimo--;
                $clid = $partes[$ultimo];

                if ( defined($origin_context) ) {

                    if ( length($origin_context) > 0 ) {
                        $btn_destino = $btn_destino . "@" . $origin_context;
                    }
                }
            }
            else {

                #strips the destination button (number at the end)
                $btn_destino = $accion;
                $btn_destino =~ s/[A-Za-z- ]//g;
                if ( $btn_destino eq "" ) { $btn_destino = "0"; }

                if ( defined($origin_context) ) {
                    if ( length($origin_context) > 0 ) {
                        $btn_destino = $btn_destino . "@" . $origin_context;
                    }
                }
            }
            if ( $btn_destino eq "" ) { $btn_destino = "0"; }
            if ( $btn_destino eq "0" ) {
                log_debug( "$heading btn_destino es igual a cero!", 32 ) if DEBUG;
            }
            else {

                log_debug( "$heading btn_destino = $btn_destino", 32 ) if DEBUG;
                if ( defined( $buttons_reverse{$btn_destino} ) ) {
                    $canal = $buttons_reverse{$btn_destino};
                    $canal =~ s/(.*)=(.*)/$1/g;
                }
                else {
                    $canal = "";
                }
                $destino = $canal;
            }

            if ( defined($destino) && $destino ne "" ) {
                if ( $destino ne "0" ) {
                    log_debug( "$heading destino es igual a $destino", 32 ) if DEBUG;
                    ( $destin_server, $destino ) = split( /\^/, $destino );
                    ($destino) = split( /\&/, $destino );
                    log_debug( "$heading El boton de destino es $destino en el server $destin_server", 64 ) if DEBUG;
                }
            }

            if ( $accion =~ /^tospy/ ) {
                my @cuales_transferir = get_transfer_channel( $origin_channel, $datosflash );
                my $cuantos           = @cuales_transferir;

                if ( !defined( $tospy{$btn_destino} ) ) {

                    # If there is no spy extension defined, change it to a standard
                    # trasnfer
                    if ( $cuantos > 0 ) {
                        $accion = "transferir";
                    }
                    else {
                        $accion = "originate";
                    }
                }
                else {
                    my $keyext  = "$origin_server^$origin_channel";
                    my $exttran = $tospy{$btn_destino};
                    my ( $extx, $contextx ) = split( /\@/, $exttran, 2 );

                    if ( $cuantos > 0 ) {
                        $comando = "Action: Redirect\r\n";
                        $comando .= "Channel: $cuales_transferir[0]\r\n";
                        $comando .= "Exten: $extx\r\n";
                        $comando .= "ActionID: 1234\r\n";
                        $comando .= "Context: $contextx\r\n";
                        $comando .= "Priority: 1\r\n\r\n";
                    }
                    else {
                        $comando = "Action: Originate\r\n";
                        $comando .= "Channel: $origin_channel\r\n";
                        $comando .= "Exten: $extx\r\n";
                        $comando .= "ActionID: 1234\r\n";
                        $comando .= "Context: $contextx\r\n";
                        $comando .= "Priority: 1\r\n\r\n";
                    }
                    if ( $button_server{$datosflash} == -1 ) {
                        send_command_to_managers($comando);
                    }
                    else {
                        send_command_to_manager( $comando, $p[ $button_server{$datosflash} ],
                            0, $astmanproxy_servers[ $button_server{$datosflash} ] );
                    }

                    $tab = substr( $tab, 0, -1 ) if DEBUG;
                    return;

                }

            }
            elsif ( $accion =~ /^tovoicemail/ ) {

                my @cuales_transferir = get_transfer_channel( $origin_channel, $datosflash );
                my $cuantos           = @cuales_transferir;

                if ( !defined( $tovoicemail{$btn_destino} ) ) {

                    # If there is no voicemail extension defined, change it to a standard
                    # trasnfer
                    if ( $cuantos > 0 ) {
                        $accion = "transferir";
                    }
                    else {
                        $accion = "originate";
                    }
                }
                else {

                    my $keyext  = "$origin_server^$origin_channel";
                    my $exttran = $tovoicemail{$btn_destino};
                    my ( $extx, $contextx ) = split( /\@/, $exttran, 2 );

                    if ( $cuantos > 0 ) {
                        $comando = "Action: Redirect\r\n";
                        $comando .= "Channel: $cuales_transferir[0]\r\n";
                        $comando .= "Exten: $extx\r\n";
                        $comando .= "ActionID: 1234\r\n";
                        $comando .= "Context: $contextx\r\n";
                        $comando .= "Priority: 1\r\n\r\n";
                    }
                    else {
                        $comando = "Action: Originate\r\n";
                        $comando .= "Channel: $origin_channel\r\n";
                        $comando .= "Exten: $extx\r\n";
                        $comando .= "ActionID: 1234\r\n";
                        $comando .= "Context: $contextx\r\n";
                        $comando .= "Priority: 1\r\n\r\n";
                    }
                    if ( $button_server{$datosflash} == -1 ) {
                        send_command_to_managers($comando);
                    }
                    else {
                        send_command_to_manager( $comando, $p[ $button_server{$datosflash} ],
                            0, $astmanproxy_servers[ $button_server{$datosflash} ] );
                    }

                    $tab = substr( $tab, 0, -1 ) if DEBUG;
                    return;
                }
            }
            elsif ( $accion =~ /^voicemail/ ) {
                my $vext       = "";
                my $vmext      = "";
                my $vcontext   = "";
                my $orichannel = "";

                if ( defined( $config->{$panelcontext}{voicemail_extension} ) ) {
                    my $voicemailext = $config->{$panelcontext}{voicemail_extension};
                    ( $vmext, $orichannel ) = split( /\^/, $voicemailext );
                    ( $vext,  $vcontext )   = split( /\@/, $vmext );
                }
                else {
                    log_debug( "There is no voicemail_extension defined in op_server.cfg!", 32 ) if DEBUG;
                    $tab = substr( $tab, 0, -1 ) if DEBUG;
                    return;
                }

                if ( !defined($orichannel) ) {
                    $orichannel = $origin_channel;
                }

                my $keyext = "$origin_server^$origin_channel";
                if ( $contexto ne "" ) { $keyext .= "\&$contexto"; }
                my $vclid = $extension_transfer{$keyext};
                $vclid =~ s/-?\d+\^(.*)/$1/g;
                $vclid =~ s/^Local\///g;
                $vclid =~ s/(.*)\@(.*)/$1/g;

                $comando = "Action: Originate\r\n";
                $comando .= "Channel: $orichannel\r\n";
                $comando .= "Callerid: $vclid <$vclid>\r\n";
                $comando .= "Async: True\r\n";
                $comando .= "Exten: $vext\r\n";
                if ( defined($vcontext) ) {
                    $comando .= "Context: $vcontext\r\n";
                }
                $comando .= "Priority: 1\r\n";
                $comando .= "\r\n";

                if ( $button_server{$datosflash} == -1 ) {
                    send_command_to_managers($comando);
                }
                else {
                    send_command_to_manager( $comando, $p[ $button_server{$datosflash} ],
                        0, $astmanproxy_servers[ $button_server{$datosflash} ] );
                }
                $tab = substr( $tab, 0, -1 ) if DEBUG;
                return;
            }

            if ( is_number($destino) ) {

                # If the selected channel name is only digits, its a
                # conference. So treat a conference command as a regular
                # transfer or redirect. (We do not want to send into a
                # meetme conference another ongoing meetme conference)
                my @sesiones_del_canal = extraer_todas_las_sesiones_de_un_canal($origin_channel);
                my $cuantos            = @sesiones_del_canal;

                if ( $accion =~ /^conference/ ) {
                    if ( $cuantos == 0 ) {
                        $accion =~ s/conference/originate/g;
                    }
                    elsif ( $cuantos > 0 ) {
                        $accion =~ s/conference/transferir/g;
                    }
                }
            }

            if ( $accion =~ /^startmonitor/ ) {
                my $buton_number = $datosflash;
                foreach my $ses ( @{ $sesbot{$buton_number} } ) {
                    my $monaction   = "";
                    my $extracomand = "";
                    if ( !defined( $monitoring{$ses} ) ) {
                        log_debug( "$heading START MONITOR $ses -- ($origin_server $origin_channel)", 1 );
                        my $unique = find_uniqueid( $ses, $origin_server );
                        my ( $filename, $format ) = compute_monitoring_filename( $unique, $panelcontext );
                        $monaction = "Monitor";
                        $monitoring{$ses} = 1;
                        $extracomand .= "File: $filename\r\n";
                        $extracomand .= "Format: $format\r\n";
                        $extracomand .= "Mix: 1\r\n";
                    }
                    else {
                        log_debug( "$heading STOP MONITOR $ses", 1 );
                        $monaction = "StopMonitor";
                        delete $monitoring{$ses};
                    }
                    $comando = "Action: $monaction\r\n";
                    $comando .= "Channel: $ses\r\n";
                    $comando .= "ActionID: monitor-$monaction-$ses\r\n";
                    $comando .= $extracomand . "\r\n";
                    if ( $button_server{$datosflash} == -1 ) {
                        send_command_to_managers($comando);
                    }
                    else {
                        send_command_to_manager( $comando, $p[ $button_server{$datosflash} ],
                            0, $astmanproxy_servers[ $button_server{$datosflash} ] );
                    }
                }
            }
            elsif ( $accion eq "cortar" ) {
                my $buton_number = $datosflash;
                log_debug( "$heading Will try to hangup channel para el boton $buton_number", 16 ) if DEBUG;

                foreach ( @{ $sesbot{$buton_number} } ) {
                    log_debug( "$heading hanging up channel $_", 16 ) if DEBUG;
                    $comando = "Action: Hangup\r\n";
                    $comando .= "Channel: $_\r\n\r\n";
                    log_debug( "-- Command received: $accion chan $_", 32 ) if DEBUG;

                    if ( $button_server{$datosflash} == -1 ) {
                        send_command_to_managers($comando);
                    }
                    else {
                        send_command_to_manager( $comando, $p[ $button_server{$datosflash} ],
                            0, $astmanproxy_servers[ $button_server{$datosflash} ] );
                    }
                }
            }
            elsif ( $accion =~ /^meetmemute/ ) {
                my $conference   = $btn_destino;
                my $meetmemember = $datosflash;
                $conference   =~ s/(.*)\@(.*)/$1/g;
                $meetmemember =~ s/(.*)\@(.*)/$1/g;
                my $boton_con_contexto = $clid;
                $boton_con_contexto =~ s/^meetmemute//g;
                $comando = "Action: Command\r\n";
                $comando .= "ActionID: meetmemute$boton_con_contexto\r\n";
                $comando .= "Command: meetme mute $conference $meetmemember\r\n\r\n";

                if ( $button_server{$boton_con_contexto} == -1 ) {
                    send_command_to_managers($comando);
                }
                else {
                    send_command_to_manager( $comando, $p[ $button_server{$boton_con_contexto} ],
                        0, $astmanproxy_servers[ $button_server{$boton_con_contexto} ] );
                }
            }
            elsif ( $accion =~ /^meetmeunmute/ ) {
                my $conference   = $btn_destino;
                my $meetmemember = $datosflash;
                $conference   =~ s/(.*)\@(.*)/$1/g;
                $meetmemember =~ s/(.*)\@(.*)/$1/g;
                my $boton_con_contexto = $clid;
                $boton_con_contexto =~ s/^meetmeunmute//g;
                $comando = "Action: Command\r\n";
                $comando .= "ActionID: meetmeunmute$boton_con_contexto\r\n";
                $comando .= "Command: meetme unmute $conference $meetmemember\r\n\r\n";

                if ( $button_server{$boton_con_contexto} == -1 ) {
                    send_command_to_managers($comando);
                }
                else {
                    send_command_to_manager( $comando, $p[ $button_server{$boton_con_contexto} ],
                        0, $astmanproxy_servers[ $button_server{$boton_con_contexto} ] );
                }
            }

            elsif ( $accion =~ /^atxfer/ ) {
                log_debug( "$heading ATXFER extension_transfer($origin_channel)", 1 ) if DEBUG;
                my $nro_boton_destino = $accion;
                $nro_boton_destino =~ s/^atxfer//g;
                my $contextoaagregar = "";
                if ( $panelcontext ne "GENERAL" ) {
                    $contextoaagregar = "&$panelcontext";
                }
                my $indice                = $origin_server . "^" . $origin_channel . $contextoaagregar;
                my $originate             = $extension_transfer{$indice};
                my $transfiero_finalmente = "";

                $originate =~ s/-?\d+\^(.*)/$1/;
                foreach ( keys(%buttons) ) {
                    log_debug( "$heading comparing $buttons{$_} with btn_destino $btn_destino", 1 ) if DEBUG;
                    if ( $buttons{$_} eq $btn_destino ) {
                        if ( $canal =~ /^_/ ) {
                            my @canalarray = @{ $sesbot{$btn_destino} };
                            my $canalses   = $canalarray[0];
                            my ( $newcanal, $newses ) = separate_session_from_channel($canalses);
                            $canal = $newcanal;
                        }
                        $canal =~ s/(.*)=(.*)/$1/g;
                        log_debug( "$heading match for $btn_destino the channel is $canal", 1 )
                          if DEBUG;
                        my @links            = extraer_todos_los_enlaces_de_un_canal( $canal, $button_server{$datosflash} );
                        my @canal_transferir = @{ $sesbot{$btn_destino} };

                        foreach my $testchan (@canal_transferir) {
                            if ( defined( $held_channel{$testchan} ) ) {
                                log_debug( "$heading skip $testchan because it is on hold", 1 );
                            }
                            else {
                                $transfiero_finalmente = $testchan;
                            }
                        }

                        my $cuantos = @links;
                        if ( $cuantos <= 0 ) {

                            $canal_destino = retrieve_extension( $origin_server . "^" . $origin_channel );
                            if ( $canal_destino ne "-1" ) {
                                if ( $canal_destino =~ /\@/ ) {
                                    ( $canal_destino, $contexto ) = split( /\@/, $canal_destino );
                                }
                            }
                            $comando = "Action: Originate\r\n";
                            $comando .= "Channel: $destino\r\n";
                            $comando .= "Exten: $canal_destino\r\n";
                            $comando .= "Context: $contexto\r\n";
                            $comando .= "Priority: 1\r\n\r\n";
                        }
                        else {

                            if ( $transfiero_finalmente eq "" ) {
                                $transfiero_finalmente = $canal_transferir[0];
                            }

                            log_debug( "** $canal_transferir[0] $links[0] will be transferred to $origin_channel ($originate)", 1 )
                              if DEBUG;

                            my ( $vmext, $vmctx ) = split( /@/, $originate );
                            $comando = "Action: Atxfer\r\n";
                            $comando .= "Channel: " . $transfiero_finalmente . "\r\n";
                            $comando .= "Exten: $vmext\r\n";
                            $comando .= "Context: $vmctx\r\n";
                            $comando .= "ActionID: atxfer\r\n";
                        }
                        if ( $button_server{$nro_boton_destino} == -1 ) {
                            send_command_to_managers($comando);
                        }
                        else {
                            send_command_to_manager( $comando, $p[ $button_server{$nro_boton_destino} ],
                                0, $astmanproxy_servers[ $button_server{$nro_boton_destino} ] );
                        }
                        last;
                    }
                }
            }
            elsif ( $accion =~ /^conference/ ) {
                log_debug( "$heading CONFERENCE extension_transfer($origin_channel)", 1 ) if DEBUG;

                my $indice    = $origin_server . "^" . $origin_channel;
                my $originate = $extension_transfer{$indice};
                $originate =~ s/-?\d+\^(.*)/$1/;
                foreach ( keys(%buttons) ) {
                    log_debug( "$heading comparo $buttons{$_} con btn_destino $btn_destino", 1 ) if DEBUG;
                    if ( $buttons{$_} eq $btn_destino ) {
                        if ( $canal =~ /^_/ ) {
                            my @canalarray = @{ $sesbot{$btn_destino} };
                            my $canalses   = $canalarray[0];
                            my ( $newcanal, $newses ) = separate_session_from_channel($canalses);
                            $canal = $newcanal;
                        }
                        $canal =~ s/(.*)=(.*)/$1/g;
                        log_debug( "$heading coincidencia para btn_destino $btn_destino el canal es $canal", 1 )
                          if DEBUG;
                        my @links            = extraer_todos_los_enlaces_de_un_canal( $canal, $button_server{$datosflash} );
                        my @canal_transferir = @{ $sesbot{$btn_destino} };

                        my $cuantos = @links;
                        if ( $cuantos <= 0 ) {
                            my @extensiondialed = extracts_exten_from_active_channel($canal);
                            $comando = "Action: Originate\r\n";
                            $comando .= "Channel: $origin_channel\r\n";
                            $comando .= "Exten: $extensiondialed[0]\r\n";
                            $comando .= "Priority: 1\r\n\r\n";
                        }
                        else {

                            log_debug( "** $canal_transferir[0] $links[0] will be conferenced together with $origin_channel ($originate)",
                                16 )
                              if DEBUG;

                            # Try to find an empty conference
                            my $empty_room = $first_room;
                            for ( my $at = $first_room ; $at <= $last_room ; $at++ ) {
                                log_debug( "room $at = " . $barge_rooms{"$at"}, 128 ) if DEBUG;
                                if ( $barge_rooms{"$at"} == 0 ) {
                                    $found_room = 1;
                                    $empty_room = $at;
                                    last;
                                }
                            }

                            if ( $found_room == 1 ) {
                                $comando = "Action: Redirect\r\n";
                                $comando .= "Channel: $canal_transferir[0]\r\n";
                                $comando .= "ExtraChannel: $links[0]\r\n";
                                $comando .= "Exten: $empty_room\r\n";
                                $comando .= "ActionID: 1234\r\n";
                                $comando .= "Context: $conference_context\r\n";
                                $comando .= "Priority: 1\r\n\r\n";

                                if ( defined( $config->{$panelcontext}{'attendant_hold_extension'} ) ) {
                                    $attendant_transfer{ $canal_transferir[0] } = $origin_channel;
                                    $mute_other{ $canal_transferir[0] }         = $links[0];
                                    $attendant_pending{ $canal_transferir[0] }  = $empty_room . "@" . $conference_context;
                                }
                                else {
                                    $auto_conference{ $canal_transferir[0] } = $origin_channel;
                                }
                            }
                            else {
                                log_debug( "$heading No hay meetme vacio!", 64 ) if DEBUG;
                                $comando = "";
                            }
                        }
                        if ( $button_server{$datosflash} == -1 ) {
                            send_command_to_managers($comando);
                        }
                        else {
                            send_command_to_manager( $comando, $p[ $button_server{$datosflash} ],
                                0, $astmanproxy_servers[ $button_server{$datosflash} ] );
                        }
                        last;
                    }
                }
            }
            elsif ( $accion =~ /transferir/ ) {
                if ( $accion =~ /^ctransferir/ ) {

                    # Sets db variable to set callerid on dialplan
                    $comando = "Action: Command\r\n";
                    $comando .= "Command: database put clid $destino ";
                    $comando .= "\"$clid\"\r\n\r\n";
                    if ( $button_server{$datosflash} == -1 ) {
                        send_command_to_managers($comando);
                    }
                    else {
                        send_command_to_manager( $comando, $p[ $button_server{$datosflash} ],
                            0, $astmanproxy_servers[ $button_server{$datosflash} ] );
                    }
                }

                $canal_destino = retrieve_extension($btn_destino);

                if ( $origin_channel =~ /\*/ ) {
                    my @canalarray = @{ $sesbot{$datosflash} };
                    my $canalses   = $canalarray[0];
                    my ( $newcanal, $newses ) = separate_session_from_channel($canalses);
                    $origin_channel = $newcanal;
                }

                if ( $canal_destino ne "-1" ) {
                    if ( $canal_destino =~ /\@/ ) {
                        ( $canal_destino, $contexto ) = split( /\@/, $canal_destino );
                    }

                    my @cuales_transferir = get_transfer_channel( $origin_channel, $datosflash );
                    if ( $canal_destino eq $park_exten && $contexto eq "parkedcalls" ) {
                        log_debug( "$heading Will try to park $valor with announce and bounce to $canal_destino!", 16 )
                          if DEBUG;
                        my @links;
                        my @canal1;
                        if ( $reverse_transfer == 1 ) {
                            @links = @{ $sesbot{$datosflash} };
                            @canal1 = extraer_todos_los_enlaces_de_un_canal( $links[0], $button_server{$datosflash} );
                        }
                        else {
                            @canal1 = @{ $sesbot{$datosflash} };
                            @links = extraer_todos_los_enlaces_de_un_canal( $canal1[0], $button_server{$datosflash} );
                        }
                        $comando = "Action: Park\r\n";
                        $comando .= "Channel: $canal1[0]\r\n";
                        $comando .= "Channel2: $links[0]\r\n";
                        $comando .= "Timeout: $parktimeout\r\n\r\n";
                        if ( $button_server{$datosflash} == -1 ) {
                            send_command_to_managers($comando);
                        }
                        else {
                            send_command_to_manager( $comando, $p[ $button_server{$datosflash} ],
                                0, $astmanproxy_servers[ $button_server{$datosflash} ] );
                        }
                    }
                    else {
                        foreach my $valor (@cuales_transferir) {
                            log_debug( "$heading Will try to transfer $valor to extension number $canal_destino!", 16 )
                              if DEBUG;
                            $comando = "Action: Redirect\r\n";
                            $comando .= "Channel: $valor\r\n";
                            $comando .= "Exten: $canal_destino\r\n";
                            if ( $contexto ne "" ) {
                                $comando .= "Context: $contexto\r\n";
                            }
                            $comando .= "Priority: 1\r\n\r\n";
                            if ( $button_server{$datosflash} == -1 ) {
                                send_command_to_managers($comando);
                            }
                            else {
                                send_command_to_manager( $comando, $p[ $button_server{$datosflash} ],
                                    0, $astmanproxy_servers[ $button_server{$datosflash} ] );
                            }

                            if ( $calltimeout > 0 ) {
                                $comando = "Action: AbsoluteTimeout\r\n";
                                $comando .= "Channel: $valor\r\n";
                                $comando .= "Timeout: $calltimeout\r\n";
                                $comando .= "ActionID: timeout|$valor|$calltimeout\r\n";
                                $comando .= "\r\n";
                                if ( $button_server{$datosflash} == -1 ) {
                                    send_command_to_managers($comando);
                                }
                                else {
                                    send_command_to_manager( $comando, $p[ $button_server{$datosflash} ],
                                        , 0, $astmanproxy_servers[ $button_server{$datosflash} ] );
                                }
                            }

                        }
                    }
                }
                else {
                    log_debug( "** Untransferable destination! ($origin_channel)", 16 ) if DEBUG;
                }
            }
            elsif ( $accion =~ /originate/ ) {

                if ( $origin_channel =~ /\*/ ) {
                    log_debug( "** Cannot originate from wildcard buttons ($origin_channel)", 16 ) if DEBUG;
                    $tab = substr( $tab, 0, -1 ) if DEBUG;
                    return;
                }

                if ( $accion =~ /^coriginate/ ) {
                    $comando = "Action: Command\r\n";
                    $comando .= "Command: database put clid $destino ";
                    $comando .= "\"$clid\"\r\n\r\n";
                    if ( $button_server{$datosflash} == -1 ) {
                        send_command_to_managers($comando);
                    }
                    else {
                        send_command_to_manager( $comando, $p[ $button_server{$datosflash} ],
                            0, $astmanproxy_servers[ $button_server{$datosflash} ] );
                    }
                }

                $extension_destino = retrieve_extension($btn_destino);

                $destino = "";
                while ( my ( $canloop, $nrobotonloop ) = each(%buttons_preserve_case) ) {
                    if ( $nrobotonloop eq $btn_destino ) {
                        $destino = $canloop;
                    }
                }
                if ( $destino ne "" ) {
                    $destino =~ s/(.*)=(.*)/$1/g;
                    $destino =~ s/^\d+\^(.*)/$1/g;
                }

                if ( $destino =~ m/QUEUE\//i ) {
                    $destino =~ s/^QUEUE\/(.*)/$1/g;
                    $destino =~ s/(.*)&(.*)/$1/g;

                    my $member = 0;
                    while ( my ( $key, $val ) = each(%agents_available_on_queue) ) {
                        if ( $key eq "$button_server{$datosflash}^$destino" ) {
                            foreach my $qmember (@$val) {
                                my $canal_compara = "$button_server{$datosflash}^$origin_channel";
                                if ( uc($qmember) eq uc($canal_compara) ) {
                                    $member++;
                                }
                            }
                        }
                    }
                    if ( $origin_channel !~ /^QUEUEAGENT/ ) {
                        if ( $member > 0 ) {
                            $comando = "Action: QueueRemove\r\n";
                            $comando .= "Queue: $destino\r\n";
                            $comando .= "Interface: $origin_channel\r\n";
                            $comando .= "\r\n";
                        }
                        else {
                            $comando = "Action: QueueAdd\r\n";
                            $comando .= "Queue: $destino\r\n";
                            $comando .= "Interface: $origin_channel\r\n";
                            $comando .= "\r\n";
                        }
                    }
                }
                else {

                    if ( $extension_destino =~ /\@/ ) {
                        ( $extension_destino, $contexto ) = split( /\@/, $extension_destino );
                    }

                    log_debug( "$heading Originate from $origin_channel to extension $extension_destino!", 16 )
                      if DEBUG;
                    my $keyext = "$origin_server^$origin_channel";

                    if ( $panelcontext ne "" && $panelcontext ne "GENERAL" ) { $keyext .= "\&$panelcontext"; }

                    my $dclid = $extension_transfer{$keyext};
                    $dclid =~ s/-?\d+\^(.*)/$1/g;
                    $dclid =~ s/^Local\///g;
                    $dclid =~ s/(.*)\@(.*)/$1/g;

                    $clid = $textos{"$datosflash"} . " <$dclid>";

                    if ( $origin_channel =~ /^IAX2\[/ ) {
                        $origin_channel =~ s/^IAX2\[(.*)\]/IAX2\/$1/g;
                    }
                    $comando = "Action: Originate\r\n";
                    $comando .= "Channel: $origin_channel\r\n";
                    $comando .= "Async: True\r\n";
                    $comando .= "Callerid: $clid\r\n";
                    $comando .= "Exten: $extension_destino\r\n";

                    if ( $contexto ne "" ) {
                        $comando .= "Context: $contexto\r\n";
                    }
                    $comando .= "Priority: 1\r\n";
                    $comando .= "\r\n";
                }
                if ( $button_server{$datosflash} == -1 ) {
                    send_command_to_managers($comando);
                }
                else {
                    send_command_to_manager( $comando, $p[ $button_server{$datosflash} ],
                        0, $astmanproxy_servers[ $button_server{$datosflash} ] );
                }
            }
            elsif ( $accion =~ /^dial/ ) {
                if ( $servidor_dial eq "default" ) {
                    $servidor_dial = $p[0];
                }
                else {
                    $servidor_dial = $p[ $button_server{$datosflash} ];
                }
                my $numero_a_discar = $accion;
                $numero_a_discar =~ s/^dial//g;
                $comando = "Action: Originate\r\n";
                $comando .= "Channel: $origin_channel\r\n";
                $comando .= "Async: True\r\n";
                $comando .= "Exten: $numero_a_discar\r\n";
                if ( $contexto ne "" ) {
                    $comando .= "Context: $contexto\r\n";
                }
                $comando .= "Priority: 1\r\n";
                $comando .= "\r\n";
                if ( $button_server{$datosflash} == -1 ) {
                    send_command_to_managers($comando);
                }
                else {
                    send_command_to_manager( $comando, $servidor_dial, 0, $astmanproxy_servers[ $button_server{$datosflash} ] );
                }
            }
        }
        else {
            log_debug( "$heading Password mismatch -$password-$md5clave-!", 1 ) if DEBUG;
            sends_key($socket);
            sends_incorrect($socket);
        }
    }
    else {
        log_debug( "$heading There is no channel selected ?", 16 ) if DEBUG;
    }
    $tab = substr( $tab, 0, -1 ) if DEBUG;
}

sub compute_monitoring_filename {
    my $unique       = shift;
    my $panelcontext = shift;
    my $filename     = "";
    my $format       = "";
    my @return;
    my %hasha;

    if ( !defined( $config->{$panelcontext}{monitor_filename} ) ) {
        $filename = $config->{GENERAL}{monitor_filename};
    }
    else {
        $filename = $config->{$panelcontext}{monitor_filename};
    }
    if ( !defined( $config->{$panelcontext}{monitor_format} ) ) {
        $format = $config->{GENERAL}{monitor_format};
    }
    else {
        $format = $config->{$panelcontext}{monitor_format};
    }

    while ( my ( $key, $val ) = each( %{ $datos{$unique} } ) ) {
        $key =~ tr/a-z/A-Z/;
        if ( $key eq "CALLERID" ) {
            $key = "CLID";
        }
        elsif ( $key eq "CALLERIDNUM" ) {
            $key = "CLIDNUM";
        }
        elsif ( $key eq "CALLERIDNAME" ) {
            $key = "CLIDNAME";
        }
        elsif ( $key eq "UNIQUEID" ) {
            my ( $realunique, undef ) = split( /-/, $val, 2 );
            $val = $realunique;
        }
        $hasha{$key} = $val;
    }
    $filename =~ s/\${CLIDNAME}/$hasha{CLIDNAME}/gi;
    $filename =~ s/\${CLIDNUM}/$hasha{CLIDNUM}/gi;
    $filename =~ s/\${CLID}/$hasha{CLID}/gi;
    $filename =~ s/\${UNIQUEID}/$hasha{UNIQUEID}/gi;
    $filename =~ s/\${CHANNEL}/$hasha{CHANNEL}/gi;
    $filename =~ s/\${LINK}/$hasha{LINK}/gi;
    $filename = formatdate($filename);
    $filename =~ s/\//-/gi;
    push @return, $filename;
    push @return, $format;
    return @return;
}

sub retrieve_extension {
    my $param         = shift;
    my $canal         = "";
    my $canal_destino = "";
    my $heading       = "** RETRIEVE_EXTEN";
    my $contexto      = "";

    my $param_sin_contexto = $param;
    $param_sin_contexto =~ s/(.*)(\@.*)/$1/g;
    if ( defined($2) ) {
        $contexto = $2;
        $contexto =~ s/\@/&/g;
    }

    log_debug( "$heading param $param param_sin_con $param_sin_contexto", 32 ) if DEBUG;

    if ( is_number($param_sin_contexto) ) {
        log_debug( "$heading I guess its a button number", 32 ) if DEBUG;

        # If the parameter is a number, assume button number
        foreach ( keys(%buttons) ) {
            if ( $buttons{$_} eq $param ) {
                log_debug( "$heading coincide con $param", 64 ) if DEBUG;
                $canal = $_;
                $canal =~ s/(.*)=(.*)/$1/g;
                $canal =~ s/(.*)&(.*)/$1/g;
                log_debug( "$heading canal $canal contexto $contexto", 64 ) if DEBUG;
                $canal_destino = $extension_transfer{"$canal$contexto"};
                last;
            }
        }
    }
    else {
        log_debug( "$heading I guess its a channel name", 32 ) if DEBUG;

        # If its not a number, asume channel name (technology/name)
        foreach ( keys(%buttons) ) {
            my $linealog = sprintf( "%-20s %-10s", $_, $buttons{$_} );

            # log_debug("$heading $linealog",64) if DEBUG;
            if ( $_ eq $param ) {
                log_debug( "$heading coincide con $param", 64 ) if DEBUG;
                $canal = $_;
                $canal =~ s/(.*)=(.*)/$1/g;
                $canal_destino = $extension_transfer{"$canal"};
                last;
            }
        }
    }

    log_debug( "$heading canal_destino =  $canal_destino", 32 ) if DEBUG;
    $canal_destino =~ s/-?\d+\^(.*)/$1/g;
    $canal_destino =~ s/^-//g;
    log_debug( "$heading La extension para $param es $canal_destino", 32 ) if DEBUG;
    return $canal_destino;
}

sub request_astdb_status {

    for my $key ( keys %astdbcommands ) {
        my $nro_servidor = 0;
        foreach my $socket (@p) {
            if ( defined($socket) && $socket ne "" ) {
                for ( keys %buttons_preserve_case ) {
                    my $canal = $_;
                    $canal =~ m/(\d+)\^([^&]*)(.*)/g;
                    my $servidor = $1;
                    my $canalito = $2;
                    if ( $canalito !~ m/^_/ && $nro_servidor == $servidor && $canalito !~ m/=/ ) {
                        if ( defined( $buttons_astdbkey{$canal} ) ) {
                            my $astdbkey = $buttons_astdbkey{$canal};
                            my $comando  = "Action: Command\r\n";
                            $comando .= "ActionID: astdb-$key-$canalito\r\n";
                            $comando .= "Command: database get $key $astdbkey\r\n\r\n";
                            if ( defined( $autenticado{$socket} ) ) {
                                if ( $autenticado{$socket} == 1 ) {
                                    send_command_to_manager( $comando, $socket );
                                }
                            }
                        }
                    }
                }
            }
            $nro_servidor++;
        }
    }
}

sub request_queue_status {
    my $socket     = shift;
    my $canalid    = shift;
    my $member     = "";
    my $showagents = 0;

    if ( defined($canalid) ) {
        if ( $canalid eq "initialrequest" ) {

            # We only ask for agents on startup
            $showagents = 1;
        }
        else {
            ( $member, undef ) = separate_session_from_channel($canalid);
        }
    }

    my @todos = ();

    if ( $socket eq "all" ) {
        @todos = @p;
    }
    else {
        push @todos, $socket;
    }

    foreach my $socket2 (@todos) {
        if ( defined($socket2) && $socket2 ne "" ) {

            if ( $showagents == 1 ) {

                #send_command_to_manager( "Action: Command\r\nActionId: agents\r\nCommand: show agents\r\n\r\n", $socket2 );
                send_command_to_manager( "Action: Agents\r\n\r\n", $socket2 );
            }
            if ( defined($member) ) {
                my @agentes = ();
                push @agentes, $member;
                if ( exists( $reverse_agents{$member} ) ) {
                    push @agentes, "Agent/" . $reverse_agents{$member};
                }
                foreach my $cual (@agentes) {
                    send_command_to_manager( "Action: QueueStatus\r\nMember: $cual\r\n\r\n", $socket2 );

                }
            }
            else {
                send_command_to_manager( "Action: QueueStatus\r\nActionID: QueueStatus\r\n", $socket2 );
            }
        }
    }
}

sub first_client_status {

    # This functions traverses all FOP internal hashes and send the proper
    # commands to the flash client to reflect the status of each button.

    my $socket  = shift;
    my $interno = "";

    if ( $queue_hide == 1 ) {

        # If queue_hide is set, hide queue positions
        for my $key ( keys %buttons ) {
            if ( $key =~ m/-?\d+\^QUEUE\/[^=]*=\d/i ) {
                $interno = $buttons{$key};
                send_status_to_flash( $socket, "$interno|setalpha|00", 0 );
            }
        }
    }

    if ( keys(%estadoboton) ) {
        for $interno ( keys %botonled ) {
            if ( $botonled{$interno} == 1 ) {
                send_status_to_flash( $socket, "$interno|changelabel1|$botonlabel{$interno}", 0 );
            }
        }
        for $interno ( keys %botonledcolor ) {
            if ( $botonledcolor{$interno} ne "" ) {
                send_status_to_flash( $socket, "$interno|fopledcolor|$botonledcolor{$interno}", 0 );
                if ( $estadoboton{$interno} =~ /^free/ || $estadoboton{$interno} eq "" ) {
                    send_status_to_flash( $socket, "$interno|state|free", 0 );
                }
            }
        }
        for $interno ( keys %botonlabelonly ) {
            send_status_to_flash( $socket, "$interno|setlabel|$botonlabelonly{$interno}", 0 );
        }
        for $interno ( keys %botonvoicemail ) {
            if ( $botonvoicemail{$interno} >= 0 ) {
                send_status_to_flash( $socket, "$interno|voicemail|$botonvoicemail{$interno}", 0 );
            }
        }
        for $interno ( keys %botonvoicemailcount ) {
            send_status_to_flash( $socket, "$interno|voicemailcount|$botonvoicemailcount{$interno}", 0 );
        }
        for $interno ( keys %botonalpha ) {
            if ( $botonalpha{$interno} ne "" ) {
                send_status_to_flash( $socket, "$interno|setalpha|$botonalpha{$interno}", 0 );
            }
        }
        for $interno ( keys %botonqueue ) {
            send_status_to_flash( $socket, "$interno|infoqstat|$botonqueue{$interno}", 0 );
        }
        for $interno ( keys %botonqueue_count ) {
            send_status_to_flash( $socket, "$interno|infoqstat2|$botonqueue_count{$interno}", 0 );
        }
        if ( keys(%botonqueuemember) ) {
            for $interno ( keys %botonqueuemember ) {
                if ( defined( @{ $botonqueuemember{$interno} } ) ) {
                    my %temphash = ();
                    foreach my $val ( @{ $botonqueuemember{$interno} } ) {
                        my @datos = split( /\|/, $val );
                        $temphash{ $datos[0] } = $datos[1];

                        #                        send_status_to_flash($socket, "$interno|info$datos[0]|$datos[1]", 0);
                    }
                    while ( my ( $key, $val ) = each(%temphash) ) {
                        send_status_to_flash( $socket, "$interno|info$key|$val", 0 );
                    }
                }
            }
        }

        if ( keys(%boton_paused) ) {
            for $interno ( keys %boton_paused ) {
                send_status_to_flash( $socket, "$interno|paused|$boton_paused{$interno}", 0 );
            }
        }
        if ( keys(%boton_agentpaused) ) {
            for $interno ( keys %boton_agentready ) {
                send_status_to_flash( $socket, "$interno|agents_paused|$boton_agentpaused{$interno}", 0 );
            }
        }
        if ( keys(%boton_agentready) ) {
            for $interno ( keys %boton_agentready ) {
                send_status_to_flash( $socket, "$interno|agents_ready|$boton_agentready{$interno}", 0 );
            }
        }
        if ( keys(%boton_agentbusy) ) {
            for $interno ( keys %boton_agentbusy ) {
                send_status_to_flash( $socket, "$interno|agents_busy|$boton_agentbusy{$interno}", 0 );
            }
        }
        if ( keys(%boton_agentlogedof) ) {
            for $interno ( keys %boton_agentlogedof ) {
                send_status_to_flash( $socket, "$interno|agents_logedof|$boton_agentlogedof{$interno}", 0 );
            }
        }

        if ( keys(%botonpark) ) {
            for $interno ( keys %botonpark ) {
                $botonpark{$interno} =~ m/(.*)\|(.*)/;
                my $texto   = $1;
                my $timeout = $2;
                my $diftime = $timeout - time();
                if ( $diftime > 0 ) {
                    send_status_to_flash( $socket, "$interno|park|$texto($diftime)", 0 );
                }
            }
        }
        if ( keys(%botonpermanenttext) ) {
            for $interno ( keys %botonpermanenttext ) {
                if ( $botonpermanenttext{$interno} ne "" ) {
                    send_status_to_flash( $socket, "$interno|settext|$botonpermanenttext{$interno}", 0 );
                }
            }
        }
        for $interno ( keys %estadoboton ) {

            if ( $estadoboton{$interno} =~ /^busy/ ) {
                send_status_to_flash( $socket, "$interno|state|busy", 0 );
                if ( defined( $botonlabel{$interno} ) ) {
                    send_status_to_flash( $socket, "$interno|changelabel0|$botonlabel{$interno}", 0 );
                }
            }
            elsif ( $estadoboton{$interno} =~ /ringi/ ) {
                send_status_to_flash( $socket, "$interno|state|ringing", 0 );
            }
            if ( defined( $botonclid{$interno} ) ) {
                my $texti = "";
                if ( $botonclid{$interno} ne "" ) {
                    send_status_to_flash( $socket, "$interno|settext|$botonclid{$interno}", 0 );
                }
            }

        }
        if ( keys(%botonlinked) ) {
            for $interno ( keys %botonlinked ) {
                if ( $botonlinked{$interno} ne "" ) {
                    send_status_to_flash( $socket, "$interno|linked|$botonlinked{$interno}", 0 );
                }
            }
        }
        if ( keys(%botonmeetme) ) {
            for $interno ( keys %botonmeetme ) {
                if ( $botonmeetme{$interno} ne "" ) {
                    send_status_to_flash( $socket, "$interno|meetmeuser|$botonmeetme{$interno}", 0 );
                }
            }
        }
        if ( keys(%botontimer) ) {
            for $interno ( keys %botontimer ) {
                if ( $botontimer{$interno} ne "" ) {
                    my $diftime = time() - $botontimer{$interno};
                    my $type    = "";
                    if ( defined( $botontimertype{$interno} ) ) {
                        $type = "\@" . $botontimertype{$interno};
                    }
                    send_status_to_flash( $socket, "$interno|settimer|$diftime$type", 0 );
                    if ( $type eq "\@UP" ) {
                        send_status_to_flash( $socket, "$interno|state|busy", 0 );
                    }
                }
            }
        }
        if ( keys(%botonsetlabel) ) {
            for $interno ( keys %botonsetlabel ) {
                if (   $botonsetlabel{$interno} ne ""
                    && $botonsetlabel{$interno} ne "."
                    && $botonsetlabel{$interno} ne "original"
                    && $botonsetlabel{$interno} ne "labeloriginal" )
                {
                    send_status_to_flash( $socket, "$interno|setlabel|$botonsetlabel{$interno}", 0 );
                }
            }
        }
        if ( keys(%botonregistrado) ) {
            for $interno ( keys %botonregistrado ) {
                if ( $botonregistrado{$interno} ne "" ) {
                    my ( $quehace, $dos ) = split( /\|/, $botonregistrado{$interno} );
                    send_status_to_flash( $socket, "$interno|$quehace|$dos", 0 );
                }
            }
        }
    }
}

sub send_initial_status {
    %datos = ();
    my $nro_servidor = 0;
    my $heading      = "** SEND INITIAL STATUS";
    my $cual         = shift;
    my $skip_autosip = shift;
    my @socket_manager;

    if ( !defined($skip_autosip) ) { $skip_autosip = 0; }

    if ( defined($cual) && $cual ne "" ) {
        push @socket_manager, $cual;
    }
    else {
        @socket_manager = @p;
    }

    log_debug( "$heading START SUB", 16 ) if DEBUG;

    foreach my $socket (@socket_manager) {

        if ( defined($socket) && $socket ne "" ) {
            my ($ipactual) = split( /\|/, $manager_socket{$socket} );
            if ( $ipactual eq $ip_addy{$socket} ) {
                my $contador = 0;
                foreach my $valor (@manager_host) {
                    if ( $valor eq $ipactual ) {
                        $nro_servidor = $contador;
                    }
                    $contador++;
                }
            }

            # If we send the channel status after the queue status, the agentlogin will be displayed as busy
            # when they are actually waiting for a call, trying to put it at the end
            request_queue_status( $socket, "initialrequest" );

            send_command_to_manager( "Action: Status\r\n\r\n", $socket );

            send_command_to_manager( "Action: ZapShowChannels\r\n\r\n", $socket );

            if ( $skip_autosip == 0 ) {
                send_command_to_manager( "Action: SIPPeers\r\nActionID: autosip\r\n\r\n", $socket );
            }

            send_command_to_manager( "Action: Command\r\nActionID: parkedcalls\r\nCommand: show parkedcalls\r\n\r\n", $socket );

            # request_queue_status( $socket, "initialrequest" );
            # Send commands to check the mailbox status for each mailbox defined
            while ( my ( $key, $val ) = each(%mailbox) ) {
                my ($servidormbox) = split( /\^/, $key );
                if ( "$servidormbox" eq "$nro_servidor" ) {
                    log_debug( "$heading mailbox $ip_addy{$socket} $key $val", 32 ) if DEBUG;
                    send_command_to_manager( "Action: MailboxStatus\r\nMailbox: $val\r\n\r\n", $socket );
                }
            }
            my @all_meetme_rooms = ();

            # generates an array with all meetme rooms to check on init
            for my $valor ( keys %barge_rooms ) {
                push( @all_meetme_rooms, $valor );
            }

            for my $key ( keys %buttons ) {
                if ( $key =~ /^\d+\^\d+$/ ) {
                    push( @all_meetme_rooms, $key );
                }
            }

            my %count               = ();
            my @unique_meetme_rooms =
              grep { ++$count{$_} < 2 } @all_meetme_rooms;

            foreach my $valor (@unique_meetme_rooms) {
                my $servidormeetme = 0;
                my $meetmeroom     = "";

                if ( $valor =~ /\^/ ) {
                    ( $servidormeetme, $meetmeroom ) = split( /\^/, $valor );
                }
                else {

                    # If there is no server defined (its a barge_room)
                    # we will query all servers - quick hack FIX IT or
                    # try to figure out a way to have barge-rooms separated
                    # in panel_contexts (as it is now) and also asterisk
                    # servers.
                    $servidormeetme = $nro_servidor;
                    $meetmeroom     = $valor;
                }

                if ( "$servidormeetme" eq "$nro_servidor" ) {
                    send_command_to_manager( "Action: Command\r\nActionID: meetme_$meetmeroom\r\nCommand: meetme list $meetmeroom\r\n\r\n",
                        $socket );
                }
            }
        }
    }
    request_astdb_status();
    alarm(2);
}

sub process_cli_command {

    # This subroutine process the output for a manager "Command"
    # sent, as 'sip show peers'

    my $texto = shift;
    @bloque = ();
    my @lineas     = split( "\r\n", $texto );
    my $contador   = 0;
    my $interno    = "";
    my $estado     = "";
    my $conference = 0;
    my $usernum    = 0;
    my $canal      = "";
    my $sesion     = "";
    my $heading    = "** PROCESS_CLI";
    my $server     = 0;

    foreach my $valor (@lineas) {
        if ( $valor =~ /^Server/ ) {
            $server = $valor;
            $server =~ s/Server: (.*)/$1/g;
        }
    }

    if ( $texto =~ /ActionID: meetme_/ ) {
        log_debug( "$heading process meetme", 16 ) if DEBUG;

        # Its a meetme status report
        foreach my $valor (@lineas) {
            $valor =~ s/\s+/ /g;
            my ( $key, $value ) = split( /: /, $valor, 2 );

            if ( defined($key) ) {

                if ( $key eq "ActionID" ) {
                    $value =~ s/meetme_(\d+)$/$1/g;
                    $conference = $value;
                }
                if ( $key eq "User #" ) {
                    my @partes = split( /Channel:/, $value );
                    $usernum = $partes[0];
                    $usernum =~ s/(\d+)(.*)/$1/g;
                    $usernum = $usernum * 1;
                    $usernum =~ s/\s+//g;
                    $canal = $partes[1];
                    $canal =~ s/^\s+//g;
                    $canal =~ s/(.*?)\((.*)/$1/g;
                    my $uniqueid = find_uniqueid( $canal, $server );
                    $bloque[$contador]{"Event"}    = "MeetmeJoin";
                    $bloque[$contador]{"Meetme"}   = $conference;
                    $bloque[$contador]{"Count"}    = $contador;
                    $bloque[$contador]{"Channel"}  = $canal;
                    $bloque[$contador]{"Usernum"}  = $usernum;
                    $bloque[$contador]{"Fake"}     = "hola";
                    $bloque[$contador]{"Server"}   = "$server";
                    $bloque[$contador]{"Uniqueid"} = $uniqueid;
                    $contador++;
                }
            }
        }
        my $cuentamenos = $contador - 1;
        if ( $cuentamenos >= 0 ) {
            $bloque[$cuentamenos]{"Total"} = $contador;
        }
    }

    #elsif ( $texto =~ "ActionID: agents" ) {
    #    log_debug( "$heading process agents", 16 ) if DEBUG;
    #    my $agent_number;
    #    my $agent_state;
    #    my $agent_name;
    #
    #        # Show Agents CLI command, generates fake events
    #
    #        foreach (@lineas) {
    #            $_ =~ s/\s+/ /g;
    #            /(\d+) \((.*)\) (.*) (\(.*\))/;
    #            if ( defined($1) ) {
    #                $agent_number                         = $1;
    #                $agent_name                           = $2;
    #                $agent_state                          = $3;
    #                $agents_name{"$server^$agent_number"} = $agent_name;
    #                set_queueobject( $server, "AGENT/$agent_number", "name", $agent_name );
    #            }
    #
    #            if ( defined($3) ) {
    #                if ( $agent_state =~ /available at/ ) {
    #
    #                    # Agent callback login
    #                    $agent_state =~ s/.*'(.*)'.*/$1/g;
    #                    $bloque[$contador]{"Event"}     = "Agentcallbacklogin";
    #                    $bloque[$contador]{"Loginchan"} = $agent_state;
    #                    $bloque[$contador]{"Agent"}     = $agent_number;
    #                    $bloque[$contador]{"Server"}    = "$server";
    #                    $contador++;
    #                }
    #
    #                if ( $agent_state =~ /logged in on/ ) {
    #
    #                    # Agent login
    #                    $agent_state =~ s/\s+/ /g;
    #                    $agent_state =~ s/logged in on //g;
    #                    $agent_state =~ s/([^ ]*).*/$1/g;
    #
    #                    $bloque[$contador]{Event}   = "Agentlogin";
    #                    $bloque[$contador]{Channel} = $agent_state;
    #                    $bloque[$contador]{Agent}   = $agent_number;
    #                    $bloque[$contador]{Server}  = $server;
    #                    $contador++;
    #                }
    #                if ( $agent_state =~ /not logged in/ ) {
    #                    $bloque[$contador]{Event}  = "Agentlogoff";
    #                    $bloque[$contador]{Agent}  = $agent_number;
    #                    $bloque[$contador]{Server} = $server;
    #                    $bloque[$contador]{Fake}   = 1;
    #                    $contador++;
    #                }
    #            }
    #        }
    #    }
    elsif ( $texto =~ /ActionID: astdb-/ ) {
        log_debug( "$heading process astdb", 16 ) if DEBUG;
        my $astdbk = "";
        my $canalk = "";
        my $valork = "";
        foreach (@lineas) {
            if (/^ActionID/) {
                $_ =~ m/ActionID: astdb-([^-]*)-(.*)/;
                $astdbk = $1;
                $canalk = $2;
            }
            if (/^Value:/) {
                $valork = $_;
                $valork =~ s/Value: //g;
            }
        }
        if ( $valork ne "" ) {
            $bloque[$contador]{"Event"}   = "ASTDB";
            $bloque[$contador]{"Channel"} = $canalk;
            $bloque[$contador]{"Value"}   = $valork;
            $bloque[$contador]{"Family"}  = $astdbk;
            $contador++;
        }

    }
    elsif ( $texto =~ /ActionID: meetmeun/ || $texto =~ /ActionID: meetmemute/ ) {
        log_debug( "$heading process meetmemute/meetmeunmute", 16 ) if DEBUG;
        my $quecomando = "";
        my $quecanal   = "";
        foreach my $valor (@lineas) {
            if ( $valor =~ /^ActionID:/ ) {
                $quecomando = $valor;
                $quecomando =~ s/^ActionID: //g;
                if ( $quecomando =~ /meetmemute/ ) {
                    $quecanal = $quecomando;
                    $quecanal =~ s/meetmemute//g;
                    $quecomando = "meetmemute";
                }
                else {
                    $quecanal = $quecomando;
                    $quecanal =~ s/meetmeunmute//g;
                    $quecomando = "meetmeunmute";
                }
            }
        }
        my $canal_a_mutear = $buttons_reverse{$quecanal};
        ( undef, $canal_a_mutear ) = split /\^/, $canal_a_mutear;
        $canal_a_mutear =~ s/(.*)\&(.*)/$1/g;
        $bloque[$contador]{"Event"}   = $quecomando;
        $bloque[$contador]{"Channel"} = $canal_a_mutear . "-XXXX";
        $bloque[$contador]{"Server"}  = "$server";
        $contador++;
    }
    elsif ( $texto =~ "ActionID: iaxpeers" ) {
        log_debug( "$heading process iaxpeers", 16 ) if DEBUG;
        my $info    = 0;
        my $statPos = 74;
        foreach my $valor (@lineas) {
            log_debug( "$heading Line iaxpeers: $valor", 32 ) if DEBUG;
            if ( $valor =~ /^Name\/User/i ) {
                $statPos = index( $valor, "Status" );
                $info = 1;
                next;
            }
            last if $valor =~ /^--End/i;
            next unless $info;
            next unless ( length($valor) > $statPos );
            my $estado = substr( $valor, $statPos );
            $valor =~ s/\s+/ /g;
            my ( $interno, $dirip ) = split( " ", $valor );

            if ( $interno =~ /\// ) {
                ($interno) = split( /\//, $interno );
            }

            if ( defined($estado) && $estado ne "" ) {
                $interno = "IAX2/" . $interno . "-XXXX";
                log_debug( "$heading State: $estado Extension: $interno", 32 ) if DEBUG;
                $bloque[$contador]{Event}   = "Regstatus";
                $bloque[$contador]{Channel} = $interno;
                $bloque[$contador]{State}   = $estado;
                $bloque[$contador]{IP}      = $dirip;
                $bloque[$contador]{Server}  = "$server";
                $contador++;
            }
        }
    }
    elsif ( $texto =~ "ActionID: sccppeers" ) {
        log_debug( "$heading process sccppeers", 16 ) if DEBUG;
        my $info = 0;
        foreach my $valor (@lineas) {
            log_debug( "$heading Line sccppeers: $valor", 32 ) if DEBUG;
            last if $valor =~ /^--END/i;
            next unless ( $valor =~ /(.*?)\t+|\s+(.*?)\t+|\s+.*?O.*/ );
            $valor =~ s/\s+/ /g;
            my @parametros = split( " ", $valor );
            my $status = 0;
            if ( $parametros[1] eq "--" ) {
                $status = 4;
            }
            log_debug( "$heading State: 4 Extension: $interno", 32 ) if DEBUG;
            $bloque[$contador]{"Event"}  = "ExtensionStatus";
            $bloque[$contador]{"Exten"}  = $parametros[0];
            $bloque[$contador]{"Status"} = $status;
            $bloque[$contador]{"Server"} = "$server";
            $contador++;
        }
    }
    elsif ( $texto =~ "ActionID: parkedcalls" ) {
        log_debug( "$heading process parkedcalls", 16 ) if DEBUG;
        my $info = 0;
        foreach my $valor (@lineas) {
            log_debug( "$heading Line parkedcalls: $valor", 32 ) if DEBUG;
            if ( $valor =~ /Timeout/ ) {
                $info = 1;
                next;
            }
            last if $valor =~ /^--End/i;
            last if $valor =~ /^\d+ parked/i;
            next unless $info;
            $valor =~ s/\s+/ /g;
            my @parametros = split( " ", $valor );
            my $timeout = $parametros[6];
            $timeout =~ s/(\d+)s/$1/;

            $bloque[$contador]{"Event"}   = "ParkedCall";
            $bloque[$contador]{"Channel"} = $parametros[1];
            $bloque[$contador]{"Exten"}   = $parametros[0];
            $bloque[$contador]{"Timeout"} = $timeout;
            $bloque[$contador]{"Server"}  = "$server";
            $contador++;

        }
    }
    else {
        my $info    = 0;
        my $statPos = 74;

        log_debug( "$heading process sip peers", 16 ) if DEBUG;

        # Its a sip show peers report
        foreach my $valor (@lineas) {
            if ( $valor =~ /^Name\/User/i ) {
                $statPos = index( $valor, "Status" );
                $info = 1;
                next;
            }
            last if $valor =~ /^--End/i;
            next unless $info;
            next unless ( length($valor) > $statPos );
            log_debug( "$heading Line: $valor", 32 ) if DEBUG;

            if ( length($valor) < $statPos ) {
                log_debug( "$heading SIP PEER line $valor does not match $statPos!", 32 ) if DEBUG;
                next;
            }

            my $estado = substr( $valor, $statPos );
            $valor =~ s/\s+/ /g;
            if ( $valor eq "" ) { next; }
            my ( $interno, $dirip ) = split( " ", $valor );
            ( $interno, undef ) = split( /\//, $interno );

            if ( defined($interno) ) {

                if ( $interno =~ /(.*)\/(.*)/ ) {
                    if ( $1 eq $2 ) {
                        $interno = $1 . "-XXXX";
                    }
                    else {
                        $interno .= "-XXXX";
                    }
                }
            }
            if ( defined($estado)
                && $estado ne "" )    # If set, is the status of 'sip show peers'
            {
                $interno = "SIP/" . $interno;
                log_debug( "$heading State: $estado Extension: $interno", 32 ) if DEBUG;
                $bloque[$contador]{"Event"}   = "Regstatus";
                $bloque[$contador]{"Channel"} = $interno . "-XXXX";
                $bloque[$contador]{"State"}   = $estado;
                $bloque[$contador]{"IP"}      = $dirip;
                $bloque[$contador]{"Server"}  = "$server";
                $contador++;
            }
        }
    }
}

sub get_meetme_pos {
    my $server     = shift;
    my $meetmeroom = shift;
    my $userpos    = shift;

    my $trunk_pos = 1;
    my $heading   = "** GET MEETME ";

    # This routine gets the usernum for a meetmejoin/meetmeleave event
    # and coverts it to a button position for meetme=1 channels.
    # Meetme's usernum starts from one, but if there are already members
    # in the conference, then it counts for the last members number up.
    # (no matter if some participants left the room)

    if ( exists( $meetme_pos{"$server^$meetmeroom"}{"$userpos"} ) ) {
        log_debug( "$heading Found meetme_pos($server^$meetmeroom)($userpos)", 64 ) if DEBUG;
        $trunk_pos = $meetme_pos{"$server^$meetmeroom"}{"$userpos"};
    }
    else {
        log_debug( "$heading Not Found meetme_pos($server^$meetmeroom)($userpos)", 64 ) if DEBUG;
        my %busy_slots = ();
        foreach my $key1 ( sort ( keys(%meetme_pos) ) ) {
            if ( $key1 eq "$server^$meetmeroom" ) {
                foreach my $key2 ( sort ( keys( %{ $meetme_pos{$key1} } ) ) ) {
                    my $indice = $meetme_pos{$key1}{$key2};
                    $busy_slots{$indice} = 1;
                }
            }
        }
        for ( $trunk_pos = 1 ; ; $trunk_pos++ ) {
            last if ( !exists( $busy_slots{$trunk_pos} ) );
        }
        $meetme_pos{"$server^$meetmeroom"}{"$userpos"} = $trunk_pos;
    }
    log_debug( "$heading devuelve $meetmeroom=$trunk_pos", 32 ) if DEBUG;
    return "$meetmeroom=$trunk_pos";
}

sub reserve_next_available_agent_button {
    my $server = shift;
    my $canal  = shift;
    my $queue  = shift;

    my @temparray = ();
    my $done      = 0;

    if ( $queueagent_buttons != 1 ) {

        # Do not waste memory or cpu cicles if we do not have queueagent buttons
        return;
    }

    # agents_on_queue stores every agent that is member of that queue
    # no matter if its logged in or not. We only need to add/remove items
    # from here when queuememberadded or queuememberremoved events

    if ( $canal =~ m/^CLID/ ) {
        my $extr = $extension_transfer{"$server^$canal"};
        $extr =~ s/-?\d+\^(.*)/$1/g;
        $canal = "Local/$extr";
    }

    log_debug( "RESERVE_AGENT_BUTTON server $server, canal $canal, queue $queue", 16 );

    foreach my $vvalor ( @{ $agents_on_queue{"$server^$queue"} } ) {
        if ( $vvalor eq "$server^$canal" ) {
            log_debug( "RESERVE_AGENT_BUTTON no thanks, we already have it", 32 );

            # We already have it here, return
            return;
        }
    }

    foreach my $vvalor ( @{ $agents_on_queue{"$server^$queue"} } ) {
        if ( $vvalor =~ /^!/ ) {

            # If it starts with ! it is available
            log_debug( "RESERVE_AGENT_BUTTON yes please, we have a previous reservation", 32 );
            push @temparray, "$server^$canal";
            $done = 1;
            last;
        }
        else {
            push @temparray, $vvalor;
        }
    }
    if ( $done == 0 ) {

        # if there is no empty slot, insert at the end
        push @temparray, "$server^$canal";
        log_debug( "RESERVE_AGENT_BUTTON yes, but you will have to wait at the end of the line", 32 );
    }
    @temparray = unique(@temparray);
    @{ $agents_on_queue{"$server^$queue"} } = @temparray;
    print_agentonqueue("en reserve");
}

sub find_uniqueid {

    # returns the uniqueid of a given channel
    my $canal = shift;
    $canal =~ s/\s//g;
    my $server = shift;
    my $uniqid = "";
    my $match  = 0;

    if ( keys(%datos) ) {
        for ( keys %datos ) {
            $match = 0;
            while ( my ( $key, $val ) = each( %{ $datos{$_} } ) ) {
                if ( $key eq "Channel" && $val =~ m/\Q$canal\E/ ) {
                    $match++;
                }
                if ( $key eq "Server" && ( $val eq $server || $server eq "-1" ) ) {
                    $match++;
                }
            }
            if ( $match > 1 ) {
                $uniqid = $_;
                last;
            }
        }
    }

    return $uniqid;
}

sub log_debug {
    my $texto   = shift;
    my $nivel   = shift;
    my $verbose = "0";

    if ( !defined($nivel) ) { $nivel = 1; }

    if ( !defined($texto) ) { return }

    if ( $debuglevel & $nivel ) {
        $texto =~ s/\0//g;
        if ( $texto !~ m/^\d+\.\d+\.\d+\.\d+/ ) {
            $verbose = $texto;
            $verbose =~ s/^\*\* ([^\s]*).*/$1/g;
        }
        else {
            my $parte = $texto;
            $parte =~ s/(\d+\.\d+\.\d+\.\d+)\s+(.*)/$1/g;
            $verbose = $parte;
        }
        if ( $debuglevel == -1 ) {

            # Debug log Cache
            $debuglevel_cache .= "$texto\n";
            $cont_debug_cache++;
            if ( $cont_debug_cache > 1000 ) {
                $debuglevel_cache = "";
                $debuglevel       = 0;
            }
        }
        else {
            if ( $debuglevel_cache ne "" ) {
                print $debuglevel_cache. "\n";
                $debuglevel_cache = "";
            }
            if ( $verbose ne $global_verbose ) {
                print "\n";
            }
            $global_verbose = $verbose;
            print "$tab$texto\n";
        }
    }
}

sub alarma_al_minuto {
    my $nro_servidor = 0;
    my $heading      = "** ALARM ";
    manager_connection();

    # %cache_hit = ();   # Clears button cache
    foreach (@p) {
        if ( defined($_) && $_ ne "" ) {
            log_debug( "$heading Enviando status a " . $ip_addy{$_}, 16 ) if DEBUG;
            my ($ip) = split( /\|/, $manager_socket{$_} );

            if ( $ip eq $ip_addy{$_} ) {
                my $contador = 0;
                foreach my $valor (@manager_host) {
                    if ( $valor eq $ip ) {
                        $nro_servidor = $contador;
                    }
                    $contador++;
                }
            }

            my $comando = "Action: Command\r\n";
            $comando .= "Command: sip show peers\r\n\r\n";
            send_command_to_manager( $comando, $_ );

            $comando = "Action: Command\r\n";
            $comando .= "ActionID: iaxpeers\r\n";
            $comando .= "Command: iax2 show peers\r\n\r\n";
            send_command_to_manager( $comando, $_ );

            $comando = "Action: Command\r\n";
            $comando .= "ActionID: sccppeers\r\n";
            $comando .= "Command: sccp show lines\r\n\r\n";
            send_command_to_manager( $comando, $_ );

            if ( $poll_voicemail == 1 ) {

                # Send commands to check the mailbox status for each mailbox defined
                while ( my ( $key, $val ) = each(%mailbox) ) {
                    my ($servidormbox) = split( /\^/, $key );
                    if ( "$servidormbox" eq "$nro_servidor" ) {
                        log_debug( "$heading mailbox $ip_addy{$_} $key $val", 32 ) if DEBUG;
                        send_command_to_manager( "Action: MailboxStatus\r\nMailbox: $val\r\n\r\n", $_ );
                    }
                }
            }
        }
    }
    alarm($poll_interval);
}

sub send_policy_to_flash {
    my $socket = shift;
    if ( !defined($socket) ) {
        log_debug( "POLICY socket $socket not open!!!", 64 ) if DEBUG;
    }
    my $policy =
"<?xml version=\"1.0\"?>\r\n<!DOCTYPE cross-domain-policy SYSTEM \"http://www.macromedia.com/xml/dtds/cross-domain-policy.dtd\">\r\n<cross-domain-policy>\r\n<allow-access-from domain=\"*\" to-ports=\"$listen_port\" />\r\n</cross-domain-policy>\r\n\0";
    actual_syswrite( $socket, $policy, "isclient", "policy enviado\n" );
}

sub send_status_to_flash {
    my $socket       = shift;
    my $status       = shift;
    my $nocrypt      = shift;
    my $encriptado   = $status;
    my $but_no       = 0;
    my $heading      = "** SEND_STATUS_TO_FLASH ";
    my $contexto     = "";
    my $cmd          = "";
    my $cmd_crypt    = "";
    my $data         = "";
    my $data_crypt   = "";
    my $noencriptado = "";

    if ( !defined($socket) ) {
        log_debug( "$heading socket $socket not open!!!", 64 ) if DEBUG;
    }

    if ( $encriptado =~ /key\|0/ ) {
        $but_no = '0';
        ( $data, $cmd ) = split( /\|/, $encriptado );
    }
    else {
        $but_no = $status;
        $but_no =~ s/(\d+).*\|.*/$1/g;
        $contexto = $status;
        $contexto =~ s/([^\|]*).*/$1/g;
        $contexto =~ m/.*\@(.*)/;
        $contexto = $1 ? $1 : "";
        ( undef, $cmd, $data ) = split( /\|/, $status );

        if ( $contexto ne "" && $cmd ne "restrict" ) {
            $but_no .= "\@$contexto";
        }

    }

    if ( $flash_contexto{$socket} ne $contexto && $but_no ne "0" ) {

        # If the context does not match, exit without queueing anything
        return;
    }

    if ( !defined( $no_encryption{"$socket"} ) ) {
        $no_encryption{"$socket"} = 0;
    }

    $noencriptado = "<response btn=\"$but_no\" cmd=\"$cmd\" data=\"$data\"/>\0";
    if ( !defined( $keys_socket{$socket} ) || $nocrypt == 1 || $no_encryption{$socket} == 1 ) {
        $encriptado = "<response btn=\"$but_no\" cmd=\"$cmd\" data=\"$data\"/>\0";
        if ( $cmd eq "key" ) {
            $keys_socket{$socket} = $data;
        }
    }
    else {
        $cmd_crypt  = &TEAencrypt( $cmd,  $keys_socket{"$socket"} );
        $data_crypt = &TEAencrypt( $data, $keys_socket{"$socket"} );
        $encriptado = "<response btn=\"$but_no\" cmd=\"$cmd_crypt\" data=\"$data_crypt\">\0";
        if ( $cmd eq "key" ) {
            $keys_socket{$socket} = $data;
        }
    }
    if ( !defined( $ip_addy{$socket} ) ) {
        log_debug( "Skip actual_syswrite to $socket cause it does not exists!", 128 ) if DEBUG;
    }
    else {
        actual_syswrite( $socket, $encriptado, "isclient", $noencriptado );
    }

}

sub manager_login_md5 {
    my $challenge = shift;
    my $handle    = shift;
    my @partes    = split( /\|/, $manager_socket{$handle} );

    my $md5clave = MD5HexDigest( $challenge . $partes[2] );

    $command = "Action: Login\r\n";
    $command .= "Username: $partes[1]\r\n";
    $command .= "AuthType: MD5\r\n";
    $command .= "Key: $md5clave\r\n\r\n";
    send_command_to_manager( $command, $handle, 1 );
}

sub send_command_to_managers {
    my $comando = shift;
    foreach (@p) {
        send_command_to_manager( $comando, $_ );
    }
}

sub send_command_to_manager {
    my $comando            = shift;
    my $socket             = shift;
    my $noneedtoauth       = shift;
    my $astmanproxy_server = shift;
    my @todos_sockets      = ();

    #    if ( !defined($socket) && $astmanproxy_server eq "" ) {
    #        log_debug( "No socket defined nor astmanproxy", 32 ) if DEBUG;
    #        return;
    #    }

    if ( !defined($noneedtoauth) ) {
        $noneedtoauth = 0;
    }

    if ( defined($astmanproxy_server) ) {
        $comando = "Server: $astmanproxy_server\r\n" . $comando;
    }

    if ( !defined($astmanproxy_server) ) {
        $astmanproxy_server = "";
    }

    if ( !defined( $autenticado{$socket} ) && $noneedtoauth == 0 && $astmanproxy_server eq "" ) {
        log_debug( "Cannot send command to " . $ip_addy{$socket} . " (unauthenticated or connection failed)", 1 )
          if DEBUG;
        return;
    }

    if ( $comando eq "" ) {
        return;
    }

    if ( !defined($socket) ) {
        @todos_sockets = @p;
    }
    else {
        push @todos_sockets, $socket;
    }

    foreach (@todos_sockets) {
        my $sockwrite = $_;
        if ( !defined($sockwrite) || $sockwrite eq "" ) { next; }
        my @lineas = split( "\r\n", $comando );
        foreach my $linea (@lineas) {
            push @{ $manager_queue{$sockwrite} }, "$linea\r\n";
        }
        $global_verbose = "separator";
        push @{ $manager_queue{$sockwrite} }, "\r\n";
    }
}

sub construct_cmd {
    my $cola    = shift;
    my $canalid = shift;
    return $cola->{$canalid}{QUEUE} . "="
      . $cola->{$canalid}{POSITION}
      . "|$_[0]|$_[1]|"
      . $cola->{$canalid}{QUEUE} . "-"
      . $cola->{$canalid}{SERVER}
      . "|$canalid";
}

sub recompute_queues_onjoin {
    my ( $hash_temporal, $server, $canalid ) = @_;
    my @return    = ();
    my @corto     = ();
    my @ocupado   = ();
    my $qclidnum  = "";
    my $qclidname = "";

    if ( defined( $hash_temporal->{CallerIDName} ) ) {
        $qclidnum  = $hash_temporal->{CallerID};
        $qclidname = $hash_temporal->{CallerIDName};
    }
    elsif ( defined( $hash_temporal->{CalleridName} ) ) {
        $qclidnum  = $hash_temporal->{Callerid};
        $qclidname = $hash_temporal->{CalleridName};
    }
    else {
        ( $qclidnum, $qclidname ) = split_callerid( $hash_temporal->{CallerID} );
    }
    my $texto_pos = "[$qclidname $qclidnum]";

    my $canal    = "QUEUE/" . $hash_temporal->{Queue};
    my $position = $hash_temporal->{Position};
    $canal =~ tr/a-z/A-Z/;
    my $unico_id = "$canal-$server";

    # Verify if the position is already taken (by means of QUEUE_PRIO)
    my $tengo = 0;
    foreach my $id ( keys %{$cola} ) {
        unless ( $cola->{$id}{QUEUE} ) {
            delete( $cola->{$id} );
            next;
        }

        if ( $canal eq $cola->{$id}{QUEUE} ) {
            if ( $position eq $cola->{$id}{POSITION} ) {
                $tengo = 1;
            }
        }
    }
    if ( $tengo == 1 ) {

        # Queue prio!
        my $time = time();
        foreach my $id ( keys %{$cola} ) {
            unless ( $cola->{$id}{QUEUE} ) {
                delete( $cola->{$id} );
                next;
            }

            if ( $canal eq $cola->{$id}{QUEUE} ) {
                if ( $cola->{$id}{POSITION} >= $position ) {
                    my $diftime = $time - $cola->{$id}{TIME};
                    if ( $queue_hide == 1 ) {
                        push @corto, construct_cmd( $cola, $id, "setalpha", "000" );
                    }
                    push @corto, construct_cmd( $cola, $id, "corto", "" );

                    $cola->{$id}{POSITION}++;
                    my $clidtext = $cola->{$id}{CLIDNAME} . " " . $cola->{$id}{CLID};
                    push @ocupado, construct_cmd( $cola, $id, "settimer", $diftime );
                    push @ocupado, construct_cmd( $cola, $id, "ocupado",  "[$clidtext]" );
                }
            }
        }
        @ocupado = unique(@ocupado);
        @corto   = unique(@corto);
    }

    # Normal, add new call at the end of the queue
    push @return, "$canal=$position|ocupado2|$texto_pos|$unico_id|$canalid";
    push @return, "$canal=$position|setalpha|100|$unico_id|$canalid";

    my $tiempo = time();
    if ( defined( $hash_temporal->{Wait} ) ) {
        $tiempo = $tiempo - $hash_temporal->{Wait};
        push @return, "$canal=$position|settimer|" . $hash_temporal->{Wait} . "\@UP|$unico_id|$canalid";
    }
    $cola->{$canalid}{POSITION} = $position;
    $cola->{$canalid}{QUEUE}    = $canal;
    $cola->{$canalid}{CLID}     = $qclidnum;
    $cola->{$canalid}{CLIDNAME} = $qclidname;
    $cola->{$canalid}{SERVER}   = $server;
    $cola->{$canalid}{TIME}     = $tiempo;
    if (@corto) {
        return ( @corto, @ocupado, @return );
    }
    else {
        return @return;
    }
}

sub recompute_queues_onleave {
    my $canalid = shift;
    my @corto;
    my @ocupado;
    my $maxtime = 0;
    my $save_id;
    my $time = time();

    #print_recomputequeues();

    my $header = "**RECOMP QUEUE";
    log_debug( "$header canalid $canalid", 1 ) if DEBUG;
    my $queue_to_recompute = $cola->{$canalid}{QUEUE};
    my $position_removed   = $cola->{$canalid}{POSITION};

    log_debug( "$header queue_to_recompute $queue_to_recompute", 1 ) if DEBUG;
    log_debug( "$header position removed $position_removed",     1 ) if DEBUG;

    if ( $queue_hide == 1 ) {
        push @corto, construct_cmd( $cola, $canalid, "setalpha", "000" );
    }
    push @corto, construct_cmd( $cola, $canalid, "corto", "" );

    delete $cola->{$canalid};

    foreach my $id ( keys %{$cola} ) {
        unless ( $cola->{$id}{QUEUE} ) {
            delete( $cola->{$id} );
            next;
        }

        if ( $queue_to_recompute eq $cola->{$id}{QUEUE} ) {
            $save_id = $id;
            my $diftime = $time - $cola->{$id}{TIME};
            if ( $diftime > $maxtime ) {
                $maxtime = $diftime;
            }

            if ( $cola->{$id}{POSITION} > $position_removed ) {
                my $clidtext = $cola->{$id}{CLIDNAME} . " " . $cola->{$id}{CLID};
                if ( $queue_hide == 1 ) {
                    push @corto, construct_cmd( $cola, $id, "setalpha", "000" );
                }
                push @corto, construct_cmd( $cola, $id, "corto", "" );
                $cola->{$id}{POSITION}--;
                push @ocupado, construct_cmd( $cola, $id, "settimer", $diftime );
                push @ocupado, construct_cmd( $cola, $id, "ocupado",  "[$clidtext]" );

                #push @ocupado, $cola->{$id}{QUEUE} . "|ocupado|[$clidtext]|" . $cola->{$id}{QUEUE} . "-" . $cola->{$id}{SERVER} . "|$id";
                push @ocupado, $cola->{$id}{QUEUE} . "|settimer|0\@STOP|" . $cola->{$id}{QUEUE} . "-" . $cola->{$id}{SERVER} . "|$id";
            }
        }
    }
    if ( defined($save_id) ) {
        push @ocupado,
          $cola->{$save_id}{QUEUE} . "|settimer|$maxtime\@UP|" . $cola->{$save_id}{QUEUE} . "-" . $cola->{$save_id}{SERVER} . "|$save_id";
    }

    # Remove the item from the hash, recompute_queues is only called
    # from the Leave event

    delete $cola->{$canalid};
    @ocupado = unique(@ocupado);

    if (@corto) {
        return ( @corto, @ocupado );
    }

}

sub split_callerid {
    my $clid         = shift;
    my @return       = ();
    my $calleridname = "";
    my $calleridnum  = "";

    if ( $clid =~ /</ ) {

        #$clid =~ /"?(.*)<(.*)>/;
        $clid =~ /"?'?([^"']*)"?'?\s+?<(.*)>/;
        $calleridname = $1;
        $calleridnum  = $2;
        if ( !defined($calleridname) ) { $calleridname = ""; }
        if ( !defined($calleridnum) )  { $calleridnum  = ""; }
        if ( $calleridname eq $calleridnum ) {
            $calleridname = "";
        }
    }
    else {
        $calleridnum  = $clid;
        $calleridname = "";
    }
    push @return, $calleridnum;
    push @return, $calleridname;

    return @return;
}

sub is_number {
    my $num = shift;
    if ( !defined($num) ) { return 1; }
    if ( $num =~ /[^0-9]/ ) {
        return 0;
    }
    else {
        return 1;
    }
}

sub close_all {
    foreach my $file (@all_flash_files) {
        log_debug( "Removing $file...", 1 ) if DEBUG;
        unlink($file);
    }
    foreach my $hd ( $O->handles ) {
        my $peer_ip = $ip_addy{$hd};
        if ( defined($peer_ip) ) {
            log_debug( "Closing " . $peer_ip, 1 ) if DEBUG;
        }

        $O->remove($hd);
        close($hd);
    }

    log_debug( "Exiting...", 1 ) if DEBUG;
    exit(0);
}

sub encode_base64 {
    my $res = "";
    my $eol = "\n";
    pos( $_[0] ) = 0;
    while ( $_[0] =~ /(.{1,45})/gs ) {
        $res .= substr( pack( "u", $1 ), 1 );
        chop($res);
    }
    $res =~ tr|` -_|AA-Za-z0-9+/|;    # `
    my $padding = ( 3 - length( $_[0] ) % 3 ) % 3;
    $res =~ s/.{$padding}$/"=" x $padding/e if $padding;

    return $res;
}

sub format_clid {

    # Subroutine to format the caller id number
    # The format string is in the form "(xxx) xxx-xxxx"
    # Every x is counted as a digit, any other text is
    # displayed as is. The digits are replaced from right
    # to left. If there are digits left, they are discarded

    my $numero       = shift;
    my $name         = shift;
    my $format       = shift;
    my @chars_number = ();
    my @chars_format = ();
    my @result       = ();
    my $devuelve     = "";

    if ( !defined($name) ) { $name = "" }

    if ( $name eq "<unknown>" ) { $name = ""; }

    if ( !is_number($numero) ) {
        return $numero;
    }

    if ($clid_privacy) {
        return "n/a";
    }

    @chars_number = split( //, $numero );
    @chars_format = reverse split( //, $format );

    my $parate = 0;
    foreach (@chars_format) {
        if (@chars_number) {
            if ( $_ eq "x" or $_ eq "X" ) {
                push( @result, pop @chars_number );
            }
            else {
                push( @result, $_ );
            }
        }
        else {
            if ($parate) { last; }

            if ( $_ eq "x" or $_ eq "X" or $_ ne "(" ) {
                $parate = 1;
                next;
            }
            else {
                push( @result, $_ );
                last;
            }
        }
    }

    $devuelve = join( "", reverse @result );
    $devuelve =~ s/\${CLIDNAME}/$name/gi;
    return $devuelve;
}

sub generate_random_password {
    my $passwordsize = shift;
    my @alphanumeric = ( 'a' .. 'z', 'A' .. 'Z', 0 .. 9 );
    my $randpassword = join '', map $alphanumeric[ rand @alphanumeric ], 0 .. $passwordsize;

    return $randpassword;
}

sub sends_incorrect {
    my $socket = shift;
    my $manda  = "0|incorrect|0";
    my $T      = send_status_to_flash( $socket, $manda, 0 );
}

sub sends_correct {
    my $socket = shift;
    my $manda  = "0|correct|0";
    my $T      = send_status_to_flash( $socket, $manda, 0 );
}

sub sends_version {
    my $socket   = shift;
    my $nocrypt  = 0;
    my $contexto = $flash_contexto{$socket};
    my $boton    = "0";
    if ( $contexto ne "" ) {
        $boton .= "\@$contexto";
    }
    my $version_string = "$boton|version|$FOP_VERSION";
    if ( !$keys_socket{"$socket"} ) {
        $nocrypt = 1;
    }
    send_status_to_flash( $socket, $version_string, $nocrypt );
}

sub sends_key {

    # Generate random key por padding the password
    # and write it to the client
    my $socket  = shift;
    my $keylen  = int( rand(22) );
    my $nocrypt = 0;
    $keylen += 15;
    my $randomkey = generate_random_password($keylen);
    my $mandakey  = "$randomkey|key|0";
    if ( !$keys_socket{$socket} ) {
        $nocrypt = 1;
    }
    if ( !defined( $keys_socket{$socket} ) ) {
        $keys_socket{$socket} = $randomkey;
    }
    send_status_to_flash( $socket, $mandakey, $nocrypt );
}

sub sends_reload {
    my $socket   = shift;
    my $nocrypt  = 0;
    my $contexto = $flash_contexto{$socket};
    my $boton    = "0";
    if ( $contexto ne "" ) {
        $boton .= "\@$contexto";
    }

    if ( !$keys_socket{$socket} ) {
        $nocrypt = 1;
    }
    my $mensaje = "<response btn=\"0\" cmd=\"reload\" data=\"0\"/>\0";
    log_debug( "Sending reload to flash client at " . $ip_addy{$socket}, 1 ) if DEBUG;
    push( @{ $client_queue_nocrypt{$socket} }, $mensaje );
    push( @{ $client_queue{$socket} },         $mensaje );
}

sub unique {
    my %seen;
    my @return = ();
    return grep { !$seen{$_}++ } @_;

    #@return = grep { !$seen{$_}++ } @_;
    #@return = sort ( @return );
    #return @return;
}

sub MD5Digest {
    my $context = &MD5Init();

    # security feature: uncomment and put your own "magic string"
    # note: MD5test.pl will not work with your magic string, of course
    # my $magicString = '!@#$%^';
    # &MD5Update($context, $magicString, length($magicString));

    # this should be done always
    &MD5Update( $context, $_[0], length( $_[0] ) );

    return &MD5Final($context);
}

#
# same as Digest but returns digest in a printable (hex) form
#

sub MD5HexDigest {
    return unpack( "H*", &MD5Digest(@_) );
}

#
# MD5 implementation is below
#

# derived from the RSA Data Security, Inc. MD5 Message-Digest Algorithm

# Original context structure
# typedef struct {
#
#       UINT4 state[4];                                   /* state (ABCD) */
#       UINT4 count[2];        /* number of bits, modulo 2^64 (lsb first) */
#       unsigned char buffer[64];                         /* input buffer */
#
# } MD5_CTX;

# Constants for MD5Transform routine.

sub S11 { 7 }
sub S12 { 12 }
sub S13 { 17 }
sub S14 { 22 }
sub S21 { 5 }
sub S22 { 9 }
sub S23 { 14 }
sub S24 { 20 }
sub S31 { 4 }
sub S32 { 11 }
sub S33 { 16 }
sub S34 { 23 }
sub S41 { 6 }
sub S42 { 10 }
sub S43 { 15 }
sub S44 { 21 }

# F, G, H and I are basic MD5 functions.

sub F { my ( $x, $y, $z ) = @_; ( ( ($x) & ($y) ) | ( ( ~$x ) & ($z) ) ); }
sub G { my ( $x, $y, $z ) = @_; ( ( ($x) & ($z) ) | ( ($y) & ( ~$z ) ) ); }
sub H { my ( $x, $y, $z ) = @_; ( ($x) ^ ($y) ^ ($z) ); }
sub I { my ( $x, $y, $z ) = @_; ( ($y) ^ ( ($x) | ( ~$z ) ) ); }

# ROTATE_LEFT rotates x left n bits.
# Note: "& ~(-1 << $n)" is not in C version
#
sub ROTATE_LEFT {
    my ( $x, $n ) = @_;
    ( $x << $n ) | ( ( $x >> ( 32 - $n ) & ~( -1 << $n ) ) );
}

# FF, GG, HH, and II transformations for rounds 1, 2, 3, and 4.
# Rotation is separate from addition to prevent recomputation.

sub FF {
    my ( $a, $b, $c, $d, $x, $s, $ac ) = @_;

    $a += &F( $b, $c, $d ) + $x + $ac;
    $a = &ROTATE_LEFT( $a, $s );
    $a += $b;

    return $a;
}

sub GG {
    my ( $a, $b, $c, $d, $x, $s, $ac ) = @_;

    $a += &G( $b, $c, $d ) + $x + $ac;
    $a = &ROTATE_LEFT( $a, $s );
    $a += $b;

    return $a;
}

sub HH {
    my ( $a, $b, $c, $d, $x, $s, $ac ) = @_;
    $a += &H( $b, $c, $d ) + $x + $ac;
    $a = &ROTATE_LEFT( $a, $s );
    $a += $b;

    return $a;
}

sub II {
    my ( $a, $b, $c, $d, $x, $s, $ac ) = @_;

    $a += &I( $b, $c, $d ) + $x + $ac;
    $a = &ROTATE_LEFT( $a, $s );
    $a += $b;

    return $a;
}

# MD5 initialization. Begins an MD5 operation, writing a new context.

sub MD5Init {
    my $context = {};

    @{ $context->{count} } = 2;
    $context->{count}[0] = $context->{count}[1] = 0;
    $context->{buffer} = '';

    # Load magic initialization constants.

    @{ $context->{state} } = 4;
    $context->{state}[0] = 0x67452301;
    $context->{state}[1] = 0xefcdab89;
    $context->{state}[2] = 0x98badcfe;
    $context->{state}[3] = 0x10325476;

    return $context;
}

# MD5 block update operation. Continues an MD5 message-digest
# operation, processing another message block, and updating the context.

sub MD5Update {
    my ( $context, $input, $inputLen ) = @_;

    # Compute number of bytes mod 64
    my $index = ( ( $context->{count}[0] >> 3 ) & 0x3F );

    # Update number of bits
    if ( ( $context->{count}[0] += ( $inputLen << 3 ) ) < ( $inputLen << 3 ) ) {
        $context->{count}[1]++;
        $context->{count}[1] += ( $inputLen >> 29 );
    }

    my $partLen = 64 - $index;

    # Transform as many times as possible.

    my $i;
    if ( $inputLen >= $partLen ) {

        substr( $context->{buffer}, $index, $partLen ) = substr( $input, 0, $partLen );

        &MD5Transform( \@{ $context->{state} }, $context->{buffer} );

        for ( $i = $partLen ; $i + 63 < $inputLen ; $i += 64 ) {
            &MD5Transform( $context->{state}, substr( $input, $i ) );
        }

        $index = 0;
    }
    else {
        $i = 0;
    }

    # Buffer remaining input
    substr( $context->{buffer}, $index, $inputLen - $i ) = substr( $input, $i, $inputLen - $i );
}

# MD5 finalization. Ends an MD5 message-digest operation, writing the
# the message digest and zeroizing the context.

sub MD5Final {
    my $context = shift;

    # Save number of bits
    my $bits = &Encode( \@{ $context->{count} }, 8 );

    # Pad out to 56 mod 64.
    my ( $index, $padLen );
    $index  = ( $context->{count}[0] >> 3 ) & 0x3f;
    $padLen = ( $index < 56 ) ? ( 56 - $index ) : ( 120 - $index );

    &MD5Update( $context, $PADDING, $padLen );

    # Append length (before padding)
    MD5Update( $context, $bits, 8 );

    # Store state in digest
    my $digest = &Encode( \@{ $context->{state} }, 16 );

    # &MD5_memset ($context, 0);

    return $digest;
}

# MD5 basic transformation. Transforms state based on block.

sub MD5Transform {
    my ( $state, $block ) = @_;

    my ( $a, $b, $c, $d ) = @{$state};
    my @x = 16;

    &Decode( \@x, $block, 64 );

    # Round 1
    $a = &FF( $a, $b, $c, $d, $x[0],  S11, 0xd76aa478 );    # 1
    $d = &FF( $d, $a, $b, $c, $x[1],  S12, 0xe8c7b756 );    # 2
    $c = &FF( $c, $d, $a, $b, $x[2],  S13, 0x242070db );    # 3
    $b = &FF( $b, $c, $d, $a, $x[3],  S14, 0xc1bdceee );    # 4
    $a = &FF( $a, $b, $c, $d, $x[4],  S11, 0xf57c0faf );    # 5
    $d = &FF( $d, $a, $b, $c, $x[5],  S12, 0x4787c62a );    # 6
    $c = &FF( $c, $d, $a, $b, $x[6],  S13, 0xa8304613 );    # 7
    $b = &FF( $b, $c, $d, $a, $x[7],  S14, 0xfd469501 );    # 8
    $a = &FF( $a, $b, $c, $d, $x[8],  S11, 0x698098d8 );    # 9
    $d = &FF( $d, $a, $b, $c, $x[9],  S12, 0x8b44f7af );    # 10
    $c = &FF( $c, $d, $a, $b, $x[10], S13, 0xffff5bb1 );    # 11
    $b = &FF( $b, $c, $d, $a, $x[11], S14, 0x895cd7be );    # 12
    $a = &FF( $a, $b, $c, $d, $x[12], S11, 0x6b901122 );    # 13
    $d = &FF( $d, $a, $b, $c, $x[13], S12, 0xfd987193 );    # 14
    $c = &FF( $c, $d, $a, $b, $x[14], S13, 0xa679438e );    # 15
    $b = &FF( $b, $c, $d, $a, $x[15], S14, 0x49b40821 );    # 16

    # Round 2
    $a = &GG( $a, $b, $c, $d, $x[1],  S21, 0xf61e2562 );    # 17
    $d = &GG( $d, $a, $b, $c, $x[6],  S22, 0xc040b340 );    # 18
    $c = &GG( $c, $d, $a, $b, $x[11], S23, 0x265e5a51 );    # 19
    $b = &GG( $b, $c, $d, $a, $x[0],  S24, 0xe9b6c7aa );    # 20
    $a = &GG( $a, $b, $c, $d, $x[5],  S21, 0xd62f105d );    # 21
    $d = &GG( $d, $a, $b, $c, $x[10], S22, 0x2441453 );     # 22
    $c = &GG( $c, $d, $a, $b, $x[15], S23, 0xd8a1e681 );    # 23
    $b = &GG( $b, $c, $d, $a, $x[4],  S24, 0xe7d3fbc8 );    # 24
    $a = &GG( $a, $b, $c, $d, $x[9],  S21, 0x21e1cde6 );    # 25
    $d = &GG( $d, $a, $b, $c, $x[14], S22, 0xc33707d6 );    # 26
    $c = &GG( $c, $d, $a, $b, $x[3],  S23, 0xf4d50d87 );    # 27
    $b = &GG( $b, $c, $d, $a, $x[8],  S24, 0x455a14ed );    # 28
    $a = &GG( $a, $b, $c, $d, $x[13], S21, 0xa9e3e905 );    # 29
    $d = &GG( $d, $a, $b, $c, $x[2],  S22, 0xfcefa3f8 );    # 30
    $c = &GG( $c, $d, $a, $b, $x[7],  S23, 0x676f02d9 );    # 31
    $b = &GG( $b, $c, $d, $a, $x[12], S24, 0x8d2a4c8a );    # 32

    # Round 3
    $a = &HH( $a, $b, $c, $d, $x[5],  S31, 0xfffa3942 );    # 33
    $d = &HH( $d, $a, $b, $c, $x[8],  S32, 0x8771f681 );    # 34
    $c = &HH( $c, $d, $a, $b, $x[11], S33, 0x6d9d6122 );    # 35
    $b = &HH( $b, $c, $d, $a, $x[14], S34, 0xfde5380c );    # 36
    $a = &HH( $a, $b, $c, $d, $x[1],  S31, 0xa4beea44 );    # 37
    $d = &HH( $d, $a, $b, $c, $x[4],  S32, 0x4bdecfa9 );    # 38
    $c = &HH( $c, $d, $a, $b, $x[7],  S33, 0xf6bb4b60 );    # 39
    $b = &HH( $b, $c, $d, $a, $x[10], S34, 0xbebfbc70 );    # 40
    $a = &HH( $a, $b, $c, $d, $x[13], S31, 0x289b7ec6 );    # 41
    $d = &HH( $d, $a, $b, $c, $x[0],  S32, 0xeaa127fa );    # 42
    $c = &HH( $c, $d, $a, $b, $x[3],  S33, 0xd4ef3085 );    # 43
    $b = &HH( $b, $c, $d, $a, $x[6],  S34, 0x4881d05 );     # 44
    $a = &HH( $a, $b, $c, $d, $x[9],  S31, 0xd9d4d039 );    # 45
    $d = &HH( $d, $a, $b, $c, $x[12], S32, 0xe6db99e5 );    # 46
    $c = &HH( $c, $d, $a, $b, $x[15], S33, 0x1fa27cf8 );    # 47
    $b = &HH( $b, $c, $d, $a, $x[2],  S34, 0xc4ac5665 );    # 48

    # Round 4
    $a = &II( $a, $b, $c, $d, $x[0],  S41, 0xf4292244 );    # 49
    $d = &II( $d, $a, $b, $c, $x[7],  S42, 0x432aff97 );    # 50
    $c = &II( $c, $d, $a, $b, $x[14], S43, 0xab9423a7 );    # 51
    $b = &II( $b, $c, $d, $a, $x[5],  S44, 0xfc93a039 );    # 52
    $a = &II( $a, $b, $c, $d, $x[12], S41, 0x655b59c3 );    # 53
    $d = &II( $d, $a, $b, $c, $x[3],  S42, 0x8f0ccc92 );    # 54
    $c = &II( $c, $d, $a, $b, $x[10], S43, 0xffeff47d );    # 55
    $b = &II( $b, $c, $d, $a, $x[1],  S44, 0x85845dd1 );    # 56
    $a = &II( $a, $b, $c, $d, $x[8],  S41, 0x6fa87e4f );    # 57
    $d = &II( $d, $a, $b, $c, $x[15], S42, 0xfe2ce6e0 );    # 58
    $c = &II( $c, $d, $a, $b, $x[6],  S43, 0xa3014314 );    # 59
    $b = &II( $b, $c, $d, $a, $x[13], S44, 0x4e0811a1 );    # 60
    $a = &II( $a, $b, $c, $d, $x[4],  S41, 0xf7537e82 );    # 61
    $d = &II( $d, $a, $b, $c, $x[11], S42, 0xbd3af235 );    # 62
    $c = &II( $c, $d, $a, $b, $x[2],  S43, 0x2ad7d2bb );    # 63
    $b = &II( $b, $c, $d, $a, $x[9],  S44, 0xeb86d391 );    # 64

    $state->[0] += $a;
    $state->[1] += $b;
    $state->[2] += $c;
    $state->[3] += $d;

    # Zeroize sensitive information.
    # MD5_memset ((POINTER)x, 0, sizeof (x));
}

# Encodes input (UINT4) into output (unsigned char). Assumes len is
# a multiple of 4.

sub Encode {
    my ( $input, $len ) = @_;

    my $output = '';
    my ( $i, $j );
    for ( $i = 0, $j = 0 ; $j < $len ; $i++, $j += 4 ) {
        substr( $output, $j + 0, 1 ) = chr( $input->[$i] & 0xff );
        substr( $output, $j + 1, 1 ) = chr( ( $input->[$i] >> 8 ) & 0xff );
        substr( $output, $j + 2, 1 ) = chr( ( $input->[$i] >> 16 ) & 0xff );
        substr( $output, $j + 3, 1 ) = chr( ( $input->[$i] >> 24 ) & 0xff );
    }

    return $output;
}

# Decodes input (unsigned char) into output (UINT4). Assumes len is
# a multiple of 4.

sub Decode {
    my ( $output, $input, $len ) = @_;

    my ( $i, $j );

    for ( $i = 0, $j = 0 ; $j < $len ; $i++, $j += 4 ) {
        $output->[$i] =
          ( ord( substr( $input, $j + 0, 1 ) ) ) | ( ord( substr( $input, $j + 1, 1 ) ) << 8 ) |
          ( ord( substr( $input, $j + 2, 1 ) ) << 16 ) | ( ord( substr( $input, $j + 3, 1 ) ) << 24 );
    }
}
#########################################################################
# TEA Encryption algorithm
#
#########################################################################
#        This Perl module is Copyright (c) 2000, Peter J Billam         #
#               c/o P J B Computing, www.pjb.com.au                     #
#########################################################################

sub binary2ascii {
    return &str2ascii( &binary2str(@_) );
}

sub ascii2binary {
    return &str2binary( &ascii2str( $_[$[] ) );
}

sub str2binary {
    my @str      = split //, $_[$[];
    my @intarray = ();
    my $ii       = $[;
    while (1) {
        last unless @str;
        $intarray[$ii] = ( 0xFF & ord shift @str ) << 24;
        last unless @str;
        $intarray[$ii] |= ( 0xFF & ord shift @str ) << 16;
        last unless @str;
        $intarray[$ii] |= ( 0xFF & ord shift @str ) << 8;
        last unless @str;
        $intarray[$ii] |= 0xFF & ord shift @str;
        $ii++;
    }
    return @intarray;
}

sub binary2str {
    my @str = ();
    foreach my $i (@_) {
        push @str, chr( 0xFF & ( $i >> 24 ) ), chr( 0xFF & ( $i >> 16 ) ), chr( 0xFF & ( $i >> 8 ) ), chr( 0xFF & $i );
    }
    return join '', @str;
}

sub ascii2str {
    my $a = $_[$[];    # converts pseudo-base64 to string of bytes
    $a =~ tr#A-Za-z0-9+_##cd;
    my $ia = $[ - 1;
    my $la = length $a;    # BUG not length, final!
    my $ib = $[;
    my @b  = ();
    my $carry;
    while (1) {            # reads 4 ascii chars and produces 3 bytes
        $ia++;
        last if ( $ia >= $la );
        $b[$ib] = $a2b{ substr $a, $ia + $[, 1 } << 2;
        $ia++;
        last if ( $ia >= $la );
        $carry = $a2b{ substr $a, $ia + $[, 1 };
        $b[$ib] |= ( $carry >> 4 );
        $ib++;

        # if low 4 bits of $carry are 0 and its the last char, then break
        $carry = 0xF & $carry;
        last if ( $carry == 0 && $ia == ( $la - 1 ) );
        $b[$ib] = $carry << 4;
        $ia++;
        last if ( $ia >= $la );
        $carry = $a2b{ substr $a, $ia + $[, 1 };
        $b[$ib] |= ( $carry >> 2 );
        $ib++;

        # if low 2 bits of $carry are 0 and its the last char, then break
        $carry = 03 & $carry;
        last if ( $carry == 0 && $ia == ( $la - 1 ) );
        $b[$ib] = $carry << 6;
        $ia++;
        last if ( $ia >= $la );
        $b[$ib] |= $a2b{ substr $a, $ia + $[, 1 };
        $ib++;
    }
    return pack 'c*', @b;
}

sub str2ascii {
    my $b  = $_[$[];      # converts string of bytes to pseudo-base64
    my $ib = $[;
    my $lb = length $b;
    my @s  = ();
    my $b1;
    my $b2;
    my $b3;
    my $carry;

    while (1) {           # reads 3 bytes and produces 4 ascii chars
        if ( $ib >= $lb ) { last; }
        $b1 = ord substr $b, $ib + $[, 1;
        $ib++;
        push @s, $b2a{ $b1 >> 2 };
        $carry = 03 & $b1;
        if ( $ib >= $lb ) { push @s, $b2a{ $carry << 4 }; last; }
        $b2 = ord substr $b, $ib + $[, 1;
        $ib++;
        push @s, $b2a{ ( $b2 >> 4 ) | ( $carry << 4 ) };
        $carry = 0xF & $b2;
        if ( $ib >= $lb ) { push @s, $b2a{ $carry << 2 }; last; }
        $b3 = ord substr $b, $ib + $[, 1;
        $ib++;
        push @s, $b2a{ ( $b3 >> 6 ) | ( $carry << 2 ) }, $b2a{ 077 & $b3 };
        if ( !$ENV{REMOTE_ADDR} && ( ( $ib % 36 ) == 0 ) ) { push @s, "\n"; }
    }
    return join( '', @s );
}

sub asciidigest {    # returns 22-char ascii signature
    return &binary2ascii( &binarydigest( $_[$[] ) );
}

sub binarydigest {
    my $str = $_[$[];    # returns 4 32-bit-int binary signature
                         # warning: mode of use invented by Peter Billam 1998, needs checking !
    return '' unless $str;

    # add 1 char ('0'..'15') at front to specify no of pad chars at end ...
    my $npads = 15 - ( ( length $str ) % 16 );
    $str = chr($npads) . $str;
    if ($npads) { $str .= "\0" x $npads; }
    my @str = &str2binary($str);
    my @key = ( 0x61626364, 0x62636465, 0x63646566, 0x64656667 );

    my ( $cswap, $v0, $v1, $v2, $v3 );
    my $c0 = 0x61626364;
    my $c1 = 0x62636465;    # CBC Initial Value. Retain !
    my $c2 = 0x61626364;
    my $c3 = 0x62636465;    # likewise (abcdbcde).
    while (@str) {

        # shift 2 blocks off front of str ...
        $v0 = shift @str;
        $v1 = shift @str;
        $v2 = shift @str;
        $v3 = shift @str;

        # cipher them XOR'd with previous stage ...
        ( $c0, $c1 ) = &tea_code( $v0 ^ $c0, $v1 ^ $c1, @key );
        ( $c2, $c3 ) = &tea_code( $v2 ^ $c2, $v3 ^ $c3, @key );

        # mix up the two cipher blocks with a 4-byte left rotation ...
        $cswap = $c0;
        $c0    = $c1;
        $c1    = $c2;
        $c2    = $c3;
        $c3    = $cswap;
    }
    return ( $c0, $c1, $c2, $c3 );
}

sub TEAencrypt {
    my ( $str, $key ) = @_;    # encodes with CBC (Cipher Block Chaining)
    use integer;
    return '' unless $str;
    return '' unless $key;
    @key = &binarydigest($key);

    # add 1 char ('0'..'7') at front to specify no of pad chars at end ...
    my $npads = 7 - ( ( length $str ) % 8 );
    $str = chr( $npads | ( 0xF8 & &rand_byte ) ) . $str;
    if ($npads) {
        my $padding = pack 'CCCCCCC', &rand_byte, &rand_byte, &rand_byte, &rand_byte, &rand_byte, &rand_byte, &rand_byte;
        $str = $str . substr( $padding, $[, $npads );
    }
    my @pblocks = &str2binary($str);
    my $v0;
    my $v1;
    my $c0 = 0x61626364;
    my $c1 = 0x62636465;    # CBC Initial Value. Retain !
    my @cblocks;
    while (1) {
        last unless @pblocks;
        $v0 = shift @pblocks;
        $v1 = shift @pblocks;
        ( $c0, $c1 ) = &tea_code( $v0 ^ $c0, $v1 ^ $c1, @key );
        push @cblocks, $c0, $c1;
    }
    my $btmp = &binary2str(@cblocks);
    return &str2ascii( &binary2str(@cblocks) );
}

sub TEAdecrypt {
    my ( $acstr, $key ) = @_;    # decodes with CBC
    use integer;
    return '' unless $acstr;
    return '' unless $key;
    @key = &binarydigest($key);
    my $v0;
    my $v1;
    my $c0;
    my $c1;
    my @pblocks = ();
    my $de0;
    my $de1;
    my $lastc0  = 0x61626364;
    my $lastc1  = 0x62636465;                          # CBC Init Val. Retain!
    my @cblocks = &str2binary( &ascii2str($acstr) );

    while (1) {
        last unless @cblocks;
        $c0 = shift @cblocks;
        $c1 = shift @cblocks;
        ( $de0, $de1 ) = &tea_decode( $c0, $c1, @key );
        $v0 = $lastc0 ^ $de0;
        $v1 = $lastc1 ^ $de1;
        push @pblocks, $v0, $v1;
        $lastc0 = $c0;
        $lastc1 = $c1;
    }
    my $str = &binary2str(@pblocks);

    # remove no of pad chars at end specified by 1 char ('0'..'7') at front
    my $npads = 0x7 & ord $str;
    substr( $str, $[, 1 ) = '';
    if ($npads) { substr( $str, 0 - $npads ) = ''; }
    return $str;
}

sub triple_encrypt {
    my ( $plaintext, $long_key ) = @_;    # not yet ...
}

sub triple_decrypt {
    my ( $cyphertext, $long_key ) = @_;    # not yet ...
}

sub tea_code {
    my ( $v0, $v1, $k0, $k1, $k2, $k3 ) = @_;

    # TEA. 64-bit cleartext block in $v0,$v1. 128-bit key in $k0..$k3.
    # &prn("tea_code: v0=$v0 v1=$v1");
    use integer;
    my $sum = 0;
    my $n   = 32;
    while ( $n-- > 0 ) {
        $sum += 0x9e3779b9;                                                                          # TEA magic number delta
        $v0  += ( ( $v1 << 4 ) + $k0 ) ^ ( $v1 + $sum ) ^ ( ( 0x07FFFFFF & ( $v1 >> 5 ) ) + $k1 );
        $v1  += ( ( $v0 << 4 ) + $k2 ) ^ ( $v0 + $sum ) ^ ( ( 0x07FFFFFF & ( $v0 >> 5 ) ) + $k3 );
    }
    return ( $v0, $v1 );
}

sub tea_decode {
    my ( $v0, $v1, $k0, $k1, $k2, $k3 ) = @_;

    # TEA. 64-bit cyphertext block in $v0,$v1. 128-bit key in $k0..$k3.
    use integer;
    my $sum = 0;
    my $n   = 32;
    $sum = 0x9e3779b9 << 5;    # TEA magic number delta
    while ( $n-- > 0 ) {
        $v1 -= ( ( $v0 << 4 ) + $k2 ) ^ ( $v0 + $sum ) ^ ( ( 0x07FFFFFF & ( $v0 >> 5 ) ) + $k3 );
        $v0 -= ( ( $v1 << 4 ) + $k0 ) ^ ( $v1 + $sum ) ^ ( ( 0x07FFFFFF & ( $v1 >> 5 ) ) + $k1 );
        $sum -= 0x9e3779b9;
    }
    return ( $v0, $v1 );
}

sub rand_byte {
    if ( !$rand_byte_already_called ) {
        srand( time() ^ ( $$ + ( $$ << 15 ) ) );    # could do better, but its only padding
        $rand_byte_already_called = 1;
    }
    int( rand 256 );
}

#
# End TEA

sub print_recomputequeues {

    foreach my $paso1 ( keys %{$cola} ) {
        print "-----------------\n";
        foreach my $it ( keys %{ $cola->{$paso1} } ) {
            print "cola($paso1)($it) = " . $cola->{$paso1}{$it} . "\n";
        }
    }
}

sub print_agentonqueue {
    my $valor = shift;
    if ( keys(%agents_on_queue) ) {
        log_debug( $valor, 1 ) if DEBUG;
        foreach my $valor ( sort ( keys(%agents_on_queue) ) ) {
            foreach my $vvalor ( @{ $agents_on_queue{$valor} } ) {
                log_debug( "agents_on_queue{$valor} = $vvalor", 1 ) if DEBUG;
            }
        }
    }
}

sub print_countqueue {
    my $valor = shift;
    if ( keys(%agents_available_on_queue) ) {
        foreach my $valor ( sort ( keys(%agents_available_on_queue) ) ) {
            foreach my $vvalor ( @{ $agents_available_on_queue{$valor} } ) {
                log_debug( "\t| agents_available_on_queue{$valor} = $vvalor", 32 ) if DEBUG;
            }
        }
    }
}

sub print_datos {
    if ( $debuglevel & 1 ) {
        my $num = shift;

        if ( keys(%datos) ) {
            print "---------------------------------------------------\n";
            print "DATOS $num\n";
            print "---------------------------------------------------\n";
            for ( keys %datos ) {
                print $_. "\n";
                while ( my ( $key, $val ) = each( %{ $datos{$_} } ) ) {
                    if ( defined($val) ) {
                        print "\t$key = $val\n";
                    }
                }
                print "---------------------------------------------------\n";
            }
        }
        else {
            print "NO DATOS TO DISPLAY\n";
        }
    }
}

sub print_cachehit {
    if ( $debuglevel & 1 ) {
        print "---------------------------------------------------\n";
        print "CACHE HIT\n";
        print "---------------------------------------------------\n";
        if ( keys(%cache_hit) ) {
            for ( keys %cache_hit ) {
                print "key $_\n";

                if ( defined( @{ $cache_hit{$_} } ) ) {
                    my @final = ();
                    foreach my $val ( @{ $cache_hit{$_} } ) {
                        print "\tcache_hit($_) = $val\n";
                    }
                }
            }
        }
        else {
            print "NO CACHE HITS TO DISPLAY\n";
        }
        print "---------------------------------------------------\n";
    }
}

sub print_linkbot {
    if ( $debuglevel & 1 ) {
        print "---------------------------------------------------\n";
        print "LINKS BOTONES\n";
        print "---------------------------------------------------\n";
        if ( keys(%linkbot) ) {
            for ( keys %linkbot ) {
                if ( defined( @{ $linkbot{$_} } ) ) {
                    my @final = ();
                    foreach my $val ( @{ $linkbot{$_} } ) {
                        print "\tlinkbot($_) = $val\n";
                    }
                }
            }
        }
        else {
            print "NO DATOS TO DISPLAY\n";
        }
        print "---------------------------------------------------\n";
    }
}

sub print_sesbot {
    my $quien = shift;

    if ( $debuglevel & 1 ) {
        print "---------------------------------------------------\n";
        print "SESIONES BOTONES $quien\n";
        print "---------------------------------------------------\n";
        if ( keys(%sesbot) ) {
            for ( keys %sesbot ) {
                if ( defined( @{ $sesbot{$_} } ) ) {
                    my @final = ();
                    foreach my $val ( @{ $sesbot{$_} } ) {
                        print "\tsesbot($_) = $val\n";
                    }
                }
            }
        }
        else {
            print "NO DATOS TO DISPLAY\n";
        }
        print "---------------------------------------------------\n";

    }
}

sub print_instancias {
    if ( $debuglevel & 1 ) {
        my $num = shift;
        print "---------------------------------------------------\n";
        print "Instancias 2 $num\n";
        print "---------------------------------------------------\n";
        foreach my $caca ( sort ( keys(%instancias) ) ) {
            print $caca. "\n";
            foreach my $pipu ( sort ( keys( %{ $instancias{$caca} } ) ) ) {
                print "\t$pipu = $instancias{$caca}{$pipu}\n";
            }
        }
        print "---------------------------------------------------\n";
    }
}

sub print_botones {
    if ( $debuglevel & 1 ) {
        my $num = shift;
        print "---------------------------------------------------\n";
        print "Botones $num\n";
        print "---------------------------------------------------\n";
        foreach ( sort ( keys(%buttons) ) ) {
            printf( "%-20s %-10s %-10s\n", $_, $buttons{$_}, $button_server{ $buttons{$_} } );
        }
    }
}

sub formatdate {
    my $format = shift;
    @_ = localtime( shift || time );
    my $h = sprintf( "%02d", $_[2] );
    my $i = sprintf( "%02d", $_[1] );
    my $s = sprintf( "%02d", $_[0] );
    my $m = sprintf( "%02d", $_[4] + 1 );
    my $d = sprintf( "%02d", $_[3] );
    my $y = sprintf( "%02d", ( $_[5] + 1900 ) % 100 );
    my $Y = sprintf( "%04d", $_[5] + 1900 );

    $format =~ s/%Y/$Y/g;
    $format =~ s/%y/$y/g;
    $format =~ s/%h/$h/g;
    $format =~ s/%i/$i/g;
    $format =~ s/%s/$s/g;
    $format =~ s/%m/$m/g;
    $format =~ s/%d/$d/g;
    return $format;
}

sub add_queueobject {
    my $server = shift;
    my $queue  = shift;
    my $agent  = shift;

    my @return;

    $queue_object->{$server}{$queue}{$agent}{status} = 1;
    @return = compute_queueobject($server);
    return @return;
}

sub delete_queueobject {
    my $server = shift;
    my $queue  = shift;
    my $agent  = shift;

    my @return;

    delete( $queue_object->{$server}{$queue}{$agent} );
    @return = compute_queueobject($server);
    return @return;
}

sub set_queueobject {
    my $server   = shift;
    my $agent    = shift;
    my $property = shift;
    my $value    = shift;

    my @return;

    foreach my $val ($queue_object) {
        foreach my $iserver ( keys %{$val} ) {
            if ( $server eq $iserver ) {
                foreach my $iqueue ( keys %{ $queue_object->{$iserver} } ) {
                    foreach my $iagent ( keys %{ $queue_object->{$iserver}{$iqueue} } ) {
                        if ( $iagent eq $agent ) {
                            $queue_object->{$iserver}{$iqueue}{$iagent}{$property} = $value;
                        }
                    }
                }
            }
        }
    }
    @return = compute_queueobject($server);
    return @return;
}

sub compute_queueobject {
    my $server = shift;

    my $head = "** compute queueboject: ";
    log_debug( "$head", 16 );

    my $queueagent_counter = {};
    my @return;
    my $canalid;
    my $ready;
    my $busy;
    my $logedof;
    my $paused;

    if ( !defined($server) ) { $server = -1; }
    foreach my $val ($queue_object) {
        foreach my $iserver ( keys %{$val} ) {
            if ( $server eq $iserver || $server == -1 ) {
                foreach my $iqueue ( keys %{ $queue_object->{$iserver} } ) {
                    $queueagent_counter->{$iserver}->{$iqueue}->{ready}   = 0;
                    $queueagent_counter->{$iserver}->{$iqueue}->{logedof} = 0;
                    $queueagent_counter->{$iserver}->{$iqueue}->{busy}    = 0;
                    $queueagent_counter->{$iserver}->{$iqueue}->{paused}  = 0;
                    foreach my $iagent ( keys %{ $queue_object->{$iserver}{$iqueue} } ) {
                        if ( $queue_object->{$iserver}{$iqueue}{$iagent}{paused} > 0 ) {
                            $queueagent_counter->{$iserver}->{$iqueue}->{paused}++;
                        }
                        if ( $queue_object->{$iserver}{$iqueue}{$iagent}{status} == 1 ) {
                            $queueagent_counter->{$iserver}->{$iqueue}->{ready}++;
                        }
                        elsif ( $queue_object->{$iserver}{$iqueue}{$iagent}{status} == 5 ) {
                            $queueagent_counter->{$iserver}->{$iqueue}->{logedof}++;
                        }
                        elsif ($queue_object->{$iserver}{$iqueue}{$iagent}{status} == 2
                            || $queue_object->{$iserver}{$iqueue}{$iagent}{status} == 3
                            || $queue_object->{$iserver}{$iqueue}{$iagent}{status} == 6 )
                        {
                            $queueagent_counter->{$iserver}->{$iqueue}->{busy}++;
                        }
                    }
                    $canalid = "QUEUE/$iqueue-XXXX";
                    $ready   = $queueagent_counter->{$server}->{$iqueue}{ready};
                    $busy    = $queueagent_counter->{$server}->{$iqueue}{busy};
                    $logedof = $queueagent_counter->{$server}->{$iqueue}{logedof};
                    $paused  = $queueagent_counter->{$server}->{$iqueue}{paused};

                    push @return, "QUEUE/$iqueue|agents_ready|$ready|$canalid-$server|$canalid";
                    push @return, "QUEUE/$iqueue|agents_busy|$busy|$canalid-$server|$canalid";
                    push @return, "QUEUE/$iqueue|agents_logedof|$logedof|$canalid-$server|$canalid";
                    push @return, "QUEUE/$iqueue|agents_paused|$paused|$canalid-$server|$canalid";

                }
            }
        }
    }
    return @return;
}

sub print_cola_write {
    my $socket = shift;
    if ( !defined($socket) ) {
        for ( keys %client_queue ) {
            my $contame = 0;
            foreach my $val ( @{ $client_queue{$_} } ) {
                $contame++;
            }
        }
    }
    else {
        my $contame = 0;
        foreach my $val ( @{ $client_queue{$socket} } ) {
            $contame++;
        }
    }
}

sub print_timers {
    if ( $debuglevel & 1 ) {
        if ( keys(%botontimer) ) {
            for my $interno ( keys %botontimer ) {
                print "botontimer $interno = $botontimer{$interno}, type $botontimertype{$interno}\n";
            }
        }
    }
}

sub print_clients {
    if ( $debuglevel & 1 ) {
        my $number_of_flash_clients_connected = @flash_clients;

        if ( $number_of_flash_clients_connected > 0 ) {
            print "\nFlash clients connected: $number_of_flash_clients_connected\n";
            print "---------------------------------------------------\n";

            foreach my $C (@flash_clients) {
                print peerinfo($C) . " $C\n";
            }
            print "---------------------------------------------------\n";
        }
        else {
            print "No flash clients connected\n";
        }
    }
}

sub print_agents {

    if ( $debuglevel & 1 ) {
        if ( keys(%agent_to_channel) ) {
            print "Agent_to_channel: \n";
            foreach my $valor ( sort ( keys(%agent_to_channel) ) ) {
                print "agent_to_channel{$valor} = $agent_to_channel{$valor}\n";
            }
        }
        if ( keys(%reverse_agents) ) {
            print "Reverse Agents: \n";
            foreach my $valor ( sort ( keys(%reverse_agents) ) ) {
                print "reverse_agents{$valor} = $reverse_agents{$valor}\n";
            }
        }
        if ( keys(%channel_to_agent) ) {
            print "Channel to Agent: \n";
            foreach my $valor ( sort ( keys(%channel_to_agent) ) ) {
                print "channel_to_agent{$valor} = $channel_to_agent{$valor}\n";
            }
        }
        if ( keys(%agents_on_queue) ) {
            print "Agents on queue: \n";
            foreach my $valor ( sort ( keys(%agents_on_queue) ) ) {
                print "agents_on_queue{$valor} = $agents_on_queue{$valor}\n";
            }
        }
        if ( keys(%is_agent) ) {
            print "is Agents: \n";
            foreach my $valor ( sort ( keys(%is_agent) ) ) {
                print "is_agent{$valor} = $is_agent{$valor}\n";
            }
        }
        if ( keys(%agents_available_on_queue) ) {
            print "Count on queue: \n";
            foreach my $valor ( sort ( keys(%agents_available_on_queue) ) ) {
                foreach ( @{ $agents_available_on_queue{$valor} } ) {
                    print "agents_available_on_queue{$valor} = $_\n";
                }
            }
        }

    }
}

sub print_status {
    if ( keys(%estadoboton) ) {
        print "---------------------------------------------------\n";
        print "ESTADO BOTONES\n";
        print "---------------------------------------------------\n";
        for ( keys %estadoboton ) {
            my $separador = 0;
            my $nroboton  = $_;
            print "$nroboton\t $estadoboton{$nroboton}\t \n";
        }
        print "---------------------------------------------------\n";
    }
    else {
        print "No estadoboton populated\n";
    }

    if ( keys(%botonled) ) {
        print "----- LEDS --------\n";
        for ( keys %botonled ) {
            print "$_ = $botonled{$_} $botonlabel{$_}\n";
        }
    }
    if ( keys(%botonvoicemail) ) {
        print "----- VOICEMAIL --------\n";
        for ( keys %botonvoicemail ) {
            print "$_ = $botonvoicemail{$_}\n";
        }
    }
}

__END__

=head1 NAME

op_server.pl - Proxy server for the Asterisk Flash Operator Panel

=head1 SYNOPSIS

op_server.pl [options] 

 Options:
   -?, --help
   -p, --pidfile
   -c, --confdir
   -l, --logdir
   -d, --daemon  
   -v, --version
   -X, --debuglevel

=head1 OPTIONS

=over 8

=item B<--help>

Print a brief help message and exits

=item B<--pidfile>

Specify the pid file to use when running in daemon mode. Defaults to /var/run/op_panel.pid

=item B<--confdir>

Specify where to look for the configuration files. If omited, it will look for them in the same directory where op_server.pl resides

=item B<--logdir>

If specified, will write the log files to that directory. If not, it will output to STDOUT and STDERR

=item B<--daemon>

Run the server in daemon mode, detaching itself from the console

=item B<--version>

Display the version and exits

=item B<--debuglevel>

Sets the debug level for the logs. It overrides the value inside op_server.cfg

=back

=head1 DESCRIPTION

B<This program> is a proxy server for the Flash Operator Panel. It reads configuration files and updates the data to display on the panel.

=head1 FILES

=over 8

=item B</etc/op-panel>

The configuration files of the operator panel daemon reside in that directory
(may differ on other distributions). Those include:

=item B<op_server.cfg>

The server's configuration file. See remarks in file for documentation.

=item B<op_buttons.cfg>

Defines the layout of the operator panel, and also which phones to track.

=item B</var/log/op-panel/output.log>

The standard output of the daemon, including debugging prints and dumps.

=item B</var/log/op-panel/error.log>

The standard error of the daemon. Should normally be empty.

=cut  
