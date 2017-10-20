#!/bin/bash
#
# /etc/init.d/fop
#
# LSB compliant service control script; see http://www.linuxbase.org/spec/
#
# System startup script for daemon spamd
#
### BEGIN INIT INFO
# Provides: fop
# Required-Start:
# Required-Stop:
# Default-Start:  3 5
# Default-Stop:   0 1 2 6
# Description:    Start Flash Operator Panel
### END INIT INFO
#
# Note on Required-Start: It does specify the init script ordering,
# not real dependencies. Depencies have to be handled by admin
# resp. the configuration tools (s)he uses.

# Source SuSE config (if still necessary, most info has been moved)
test -r /etc/rc.config && . /etc/rc.config

# Check for missing binaries (stale symlinks should not happen)
FOP_BIN=/usr/local/fop/op_server.pl

# Check for existence of needed config file and read it
#
# Later, we may want to make startup behaviour (user ID, firewalling, ...)
# configurable, as there are security implications (read README.spamd).
#FOP_CONFIG=/etc/sysconfig/fop
#test -r $FOP_CONFIG || exit 6
#. $FOP_CONFIG

test -x $FOP_BIN || exit 5

# Shell functions sourced from /etc/rc.status:
#      rc_check         check and set local and overall rc status
#      rc_status        check and set local and overall rc status
#      rc_status -v     ditto but be verbose in local rc status
#      rc_status -v -r  ditto and clear the local rc status
#      rc_failed        set local and overall rc status to failed
#      rc_failed <num>  set local and overall rc status to <num><num>
#      rc_reset         clear local rc status (overall remains)
#      rc_exit          exit appropriate to overall rc status
#      rc_active        checks whether a service is activated by symlinks
. /etc/rc.status


FOP_ARGS="-d -p /var/run/fop.pid"

# First reset status of this service
rc_reset

# Return values acc. to LSB for all commands but status:
# 0 - success
# 1 - generic or unspecified error
# 2 - invalid or excess argument(s)
# 3 - unimplemented feature (e.g. "reload")
# 4 - insufficient privilege
# 5 - program is not installed
# 6 - program is not configured
# 7 - program is not running
#
# Note that starting an already running service, stopping
# or restarting a not-running service as well as the restart
# with force-reload (in case signalling is not supported) are
# considered a success.

case "$1" in
    start)
        echo -n "Starting Flash Operator Panel server "
        ## Start daemon with startproc(8). If this fails
        ## the echo return value is set appropriate.

        # NOTE: startproc returns 0, even if service is
        # already running to match LSB spec.
        startproc -p /var/run/fop.pid $FOP_BIN $FOP_ARGS

        # Remember status and be verbose
        rc_status -v
        ;;
    stop)
        echo -n "Shutting down op_server.pl "
        ## Stop daemon with killproc(8) and if this fails
        ## set echo the echo return value.

        killproc -p /var/run/fop.pid -TERM $FOP_BIN

        # Remember status and be verbose
        rc_status -v
        ;;
    try-restart)
        ## Stop the service and if this succeeds (i.e. the
        ## service was running before), start it again.
        ## Note: try-restart is not (yet) part of LSB (as of 0.7.5)
        $0 status >/dev/null &&  $0 restart

        # Remember status and be quiet
        rc_status
        ;;
    restart)
        ## Stop the service and regardless of whether it was
        ## running or not, start it again.
        $0 stop
        $0 start

        # Remember status and be quiet
        rc_status
        ;;
    force-reload)
        ## Signal the daemon to reload its config. Most daemons
        ## do this on signal 1 (SIGHUP).
        ## If it does not support it, restart.

        echo -n "Reload service op_server.pl "
        ## if it supports it:
        killproc -HUP $FOP_BIN
        touch /var/run/fop.pid
        rc_status -v

        ## Otherwise:
        #$0 stop  &&  $0 start
        #rc_status
        ;;
    reload)
        ## Like force-reload, but if daemon does not support
        ## signalling, do nothing (!)

        # If it supports signalling:
        echo -n "Reload service op_server.pl "
        killproc -HUP $FOP_BIN
        touch /var/run/fop.pid
        rc_status -v

        ## Otherwise if it does not support reload:
        #rc_failed 3
        #rc_status -v
        ;;
    status)
        echo -n "Checking for service fop "
        ## Check status with checkproc(8), if process is running
        ## checkproc will return with exit status 0.

        # Return value is slightly different for the status command:
        # 0 - service running
        # 1 - service dead, but /var/run/  pid  file exists
        # 2 - service dead, but /var/lock/ lock file exists
        # 3 - service not running

        # NOTE: checkproc returns LSB compliant status values.
        checkproc $FOP_BIN
        rc_status -v
        ;;
    *)
        echo "Usage: $0 {start|stop|status|try-restart|restart|reload}"
        exit 1
        ;;
esac
rc_exit

