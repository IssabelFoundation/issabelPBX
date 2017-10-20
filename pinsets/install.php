<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

global $db;
global $amp_conf;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

$sql[] = "CREATE TABLE IF NOT EXISTS pinsets ( 
	pinsets_id INTEGER NOT NULL PRIMARY KEY $autoincrement, 
	passwords LONGTEXT, 
	description VARCHAR( 50 ) , 
	addtocdr TINYINT( 1 ) , 
	deptname VARCHAR( 50 )
)";

$sql[] = "CREATE TABLE IF NOT EXISTS pinset_usage ( 
	pinsets_id INTEGER NOT NULL,
	dispname VARCHAR( 30 ),
	foreign_id VARCHAR( 30 ),
  PRIMARY KEY (`dispname`, `foreign_id`)
)";

foreach($sql as $q){
	$check = $db->query($q);
	if(DB::IsError($check)) {
    die_issabelpbx("Can not create pinset tables\n".$check->getDebugInfo());
	}
}
outn(_("checking if migration required.."));
$sql = "SELECT `used_by` FROM pinsets WHERE used_by != ''";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(!DB::IsError($check)) {
	outn(_("migrating.."));
  /* We need to now migrate from from the old format of dispname_id where the only supported dispname
     so far has been "routing" and the "id" used was the imperfect nnn-name. As it truns out, it was
     possible to have the same route name perfiously so we will try to detect that. This was really ugly
     so if we can't find stuff we will simply report errors and let the user go back and fix things.
   */
  $sql = "SELECT * FROM pinsets";
  $pinsets = $db->getAll($sql, DB_FETCHMODE_ASSOC);
  if(DB::IsError($result)) { 
    out(_("unknown error fetching table data"));
    out(_("migration aborted"));
  } else {
    /* If there are any rows then lets get our route information. We will force this module to depend on
     * the new core, so we can count on the APIs being available. If there are indentical names, then
     * oh well...
     */
    if (count($pinsets)) {
      $routes = core_routing_list();
      $route_hash = array();
      foreach ($routes as $route) {
        $route_hash[$route['name']] = $route['route_id'];
      }
    }
    $pinset_usage = array();
    foreach ($pinsets as $pinset) {
      $used_by = explode(',',$pinset['used_by']);
      foreach ($used_by as $target) {
        $parts = explode('_',$target,2);
        // there should only be routing but just in case...
        $dispname = $parts[0];
        if ($dispname == 'routing') {
          $lookup = substr($parts[1],4);
          $foreign_id = isset($route_hash[$lookup]) ? $route_hash[$lookup] : false;
        } else {
          $foreign_id = $parts[1];
        }
        if ($foreign_id === false) {
	        out();
          out(_("FAILED migrating route $lookup NOT FOUND"));
	        outn(_("continuing.."));
        } else {
          $pinset_usage[] = array($pinset['pinsets_id'],$dispname,$foreign_id);
        }
      }
    }
    // We new have all the indices, so lets save them
    //
    $compiled = $db->prepare('INSERT INTO `pinset_usage` (`pinsets_id`, `dispname`, `foreign_id`) values (?,?,?)');
    $result = $db->executeMultiple($compiled,$pinset_usage);
    if(DB::IsError($result)) {
      out("FATAL: ".$result->getDebugInfo()."\n".'error inserting into pinsets_uage table');	
    } else {
      out(_("done"));

      outn(_("dropping used_by field.."));
      $sql = "ALTER TABLE `pinsets` DROP `used_by`";
      $result = $db->query($sql);
      if(DB::IsError($result)) { 
        out(_("no used_by field???"));
      } else {
        out(_("ok"));
      }
    }
  }
} else {
	out(_("already done"));
}

?>
