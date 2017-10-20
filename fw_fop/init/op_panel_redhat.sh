#!/bin/bash
#
# chkconfig: 2345 99 15
# description: Flash Operator Panel 
# processname: op_server.pl

# source function library
. /etc/rc.d/init.d/functions

DAEMON=/usr/local/fop/op_server.pl
OPTIONS="-d -c /etc/asterisk/fop"
RETVAL=0

case "$1" in
  start)
	echo -n "Starting Flash Operator Panel: "
	daemon $DAEMON $OPTIONS
	RETVAL=$?
	echo
	[ $RETVAL -eq 0 ] && touch /var/lock/subsys/op_server.pl
	;;
  stop)
	echo -n "Shutting dows Flash Operator Panel: "
	killproc op_server.pl
	RETVAL=$?

	echo
	[ $RETVAL -eq 0 ] && rm -f /var/lock/subsys/op_server.pl
	;;
  restart)
	$0 stop
	$0 start
	RETVAL=$?
	;;
  reload)
	echo -n "Reloading Flash Operator Panel configuration: "
	killproc op_server.pl -HUP
	RETVAL=$?
	echo
	;;
  status)
	status op_server.pl
	RETVAL=$?
	;;
  *)
	echo "Usage: op_panel {start|stop|status|restart|reload}"
	exit 1
esac

exit $RETVAL

