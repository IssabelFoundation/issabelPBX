<?php

printf( "<h1 class='title'>%s</h1>", __("Welcome to IssabelPBX.") );
	
$notify =& notifications::create($db);
$items = $notify->list_all(true);
if (count($items)) {
	$notify_names = array(
		NOTIFICATION_TYPE_CRITICAL => __('Critical'),
		NOTIFICATION_TYPE_SECURITY => __('Security'),
		NOTIFICATION_TYPE_UPDATE => __('Update'),
		NOTIFICATION_TYPE_ERROR => __('Error'),
		NOTIFICATION_TYPE_WARNING => __('Warning'),
		NOTIFICATION_TYPE_NOTICE => __('Notice'),
	);

	echo "<div class=\"box has-background-warning\">";
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



echo "<div class='box'>";
echo __( "If you're new to IssabelPBX, Welcome. Here are some quick instructions to get you started");

echo "<br><br><div>";
printf( __(
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
echo "</div><br/>\n";

echo "<div>";
printf( __( 
"If you are having any problems there is a community based <a href='%s' target='_new'>IssabelPBX Web Forum</a> where you can post
questions and search for answers for any problems you may be having."),
"http://forums.issabel.org"  );
echo "</div><br/>\n";

print( "<p>" . __("We hope you enjoy using IssabelPBX!") . "</p>\n" );
echo "</div>";
?>
