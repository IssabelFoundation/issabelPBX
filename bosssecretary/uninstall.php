<?php



// shall delete features codes

$fct = new featurecode('bosssecretary', 'bsc_toggle');
$fcu = new featurecode('bosssecretary', 'bsc_on');
$fcl = new featurecode('bosssecretary', 'bsc_off');


$fcl->delete();
$fcu->delete();
$fct->delete();


unset($fcl);
unset($fcu);
unset($fct);

// deleted features codes

// shall delete bosssecretary tree

global $astman;
global $amp_conf;



//add details to astdb
if ($astman) {
        $astman->database_deltree("bosssecretary/group");
} else {
        echo _("Cannot connect to Asterisk Manager with
").$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"];
}


// deleted bosssecretary tree

?>
