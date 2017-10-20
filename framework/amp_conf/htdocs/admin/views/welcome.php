<?php

printf( "<h2>%s</h2>", dgettext("welcome page", "Welcome to IssabelPBX.") );
	
$notify =& notifications::create($db);
$items = $notify->list_all(true);
if (count($items)) {
	$notify_names = array(
		NOTIFICATION_TYPE_CRITICAL => _('Critical'),
		NOTIFICATION_TYPE_SECURITY => _('Security'),
		NOTIFICATION_TYPE_UPDATE => _('Update'),
		NOTIFICATION_TYPE_ERROR => _('Error'),
		NOTIFICATION_TYPE_WARNING => _('Warning'),
		NOTIFICATION_TYPE_NOTICE => _('Notice'),
	);

	echo "<div class=\"warning\">";
	echo '<h3>Notifications:</h3>';
	echo '<ul>';
	foreach ($items as $item) {
		echo '<li><strong>'.$notify_names[ $item['level'] ].':</strong>&nbsp;'.$item['display_text'];
		if (!empty($item['extended_text'])) {
			if (isset($_GET['item']) && $_GET['item'] == $item['module'].'.'.$item['id']) {
				echo '<p>'.nl2br($item['extended_text']).'</p>';
			} else {
				$dis = isset($_GET['display']) ? addslashes($_GET['display']) : '';
				$link = $_SERVER['PHP_SELF'].'?display='.$dis.'&amp;item='.$item['module'].'.'.$item['id'];
				echo '&nbsp;&nbsp;<a href="'.$link.'"><i>more..</i></a>';
			}
		}
		echo '</td></li>';
	}
	echo '</ul></div>';
}


printf( "<p>%s</p>"  , dgettext("welcome page", "If you're new to IssabelPBX, Welcome. Here are some quick instructions to get you started") );

echo "<p>";
printf( dgettext("welcome page", 
"There are a large number of Plug-in modules available from the Online Repository. This is
available by clicking on the <a href='%s'>Tools menu</a> up the top, then
<a href='%s'>Module Admin</a>, then
<a href='%s'>Check for updates online</a>.
Modules are updated and patched often, so if you are having a problem, it's worth checking there to see if there's
a new version of the module available."), 
	"config.php?type=tool",
	"config.php?display=modules&amp;type=tool",
	"config.php?display=modules&amp;type=tool&amp;extdisplay=online"
);
echo "</p>\n";

echo "<p>";
printf( dgettext( "welcome page",
"If you're having any problems, you can also use the <a href='%s'>Online Support</a> 
module (<b>you need to install this through the <a href='%s'>Module Repository</a> first</b>)
to talk to other users and the developers in real time. Click on <a href='%s'>Start IRC</a>,
when the module is installed, to start a Java IRC client." ),
	"config.php?type=tool&amp;display=irc",
	"config.php?display=modules&amp;type=tool&amp;extdisplay=online",
	"config.php?type=tool&amp;display=irc&amp;action=start"
);
echo "</p>\n";

echo "<p>";
printf( dgettext( "welcome page",
"There is also a community based <a href='%s' target='_new'>IssabelPBX Web Forum</a> where you can post
questions and search for answers for any problems you may be having."),
"http://forums.issabel.org"  );
echo "</p>\n";

print( "<p>" . _("We hope you enjoy using IssabelPBX!") . "</p>\n" );
?>
