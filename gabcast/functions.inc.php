<?php
 /* $Id:$ */

function gabcast_configpageinit($dispnum) {
	global $currentcomponent;

	if ( $dispnum == 'users' || $dispnum == 'extensions' ) {
		$currentcomponent->addguifunc('gabcast_configpageload');
	}	
}

function gabcast_configpageload() {
	global $currentcomponent;

	$viewing_itemid = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;
	$action =  isset($_REQUEST['action'])?$_REQUEST['action']:null; 
	if ( $viewing_itemid != '' && $action != 'del') { 
		$list = gabcast_get($viewing_itemid);
		if (is_array($list)) {
			$res = $_SERVER['PHP_SELF']."?display=gabcast&type=tool&ext=$viewing_itemid&action=edit";
			$currentcomponent->addguielem('_top', new gui_link('gabcastlink', _("Edit Gabcast Settings"), $res));
		} else {
			$res = $_SERVER['PHP_SELF']."?display=gabcast&type=tool&ext=$viewing_itemid&action=add";
			$currentcomponent->addguielem('_top', new gui_link('gabcastlink', _("Add Gabcast Settings"), $res));
		}
	}
}

// returns a associative arrays with keys 'destination' and 'description'
function gabcast_destinations() {
	//get the list of meetmes
	$results = gabcast_list();

	// return an associative array with destination and description
	if (isset($results)) {
		foreach($results as $result){
				$extens[] = array('destination' => 'gabcast,'.$result['0'].',1', 'description' => 'gabcast channel '. $result['1'].' <'.$result[0].'>');
		}
	return isset($extens)?$extens:null;
	} else {
		return null;
	}
}

function gabcast_getdest($exten) {
	return array('gabcast,'.$exten.',1');
}

function gabcast_getdestinfo($dest) {
	global $active_modules;

	if (substr(trim($dest),0,8) == 'gabcast,') {
		$exten = explode(',',$dest);
		$exten = $exten[1];
		$thisexten = gabcast_get($exten);
		if (empty($thisexten)) {
			return array();
		} else {
			$type = isset($active_modules['gabcast']['type'])?$active_modules['gabcast']['type']:'tool';
			return array('description' => sprintf(_("Gabcast: %s Ext: %s"),$thisexten[0].' -> channel ',$thisexten[1]),
			             'edit_url' => 'config.php?display=gabcast&action=edit&type='.$type.'&ext='.urlencode($exten),
								  );
		}
	} else {
		return false;
	}
}

function gabcast_get_config($engine) {
        $modulename = 'gabcast';

        // This generates the dialplan
        global $ext;
        switch($engine) {
			case "asterisk":
					if (is_array($featurelist = featurecodes_getModuleFeatures($modulename))) {
							foreach($featurelist as $item) {
									$featurename = $item['featurename'];
									$fname = $modulename.'_'.$featurename;
									if (function_exists($fname)) {
											$fcc = new featurecode($modulename, $featurename);
											$fc = $fcc->getCodeActive();
											unset($fcc);

											if ($fc != '')
													$fname($fc);
									} else {
											$ext->add('from-internal-additional', 'debug', '', new ext_noop($modulename.": No func $fname"));
											var_dump($item);
									}
							}
				   } else {
						$ext->add('from-internal-additional', 'debug', new ext_noop($modulename.": No modules??"));
				   }
				   
				   $context = "gabcast";
				   $ext->add($context, '_X.', '', new ext_dial('IAX2/iax.gabcast.com/422,120'));
				   $ext->add($context, 's', '', new ext_dial('IAX2/iax.gabcast.com/422,120'));
				   
				   $gablist = gabcast_list();
				   if($gablist) {
					   foreach ($gablist as $gab) {
						   $extension = $gab[0];
						   $channbr = $gab[1];
						   $pin = $gab[2];
						   
						   $ext->add($context, $extension, '', new ext_dial('IAX2/iax.gabcast.com/'.$channbr.'*'.$pin.',120'));
					   }
				   }
			break;
        }
}

function gabcast_list() {
        global $db;

        $sql = "SELECT * FROM gabcast ORDER BY channbr";
        $results = $db->getAll($sql);
        if(DB::IsError($results)) {
                $results = null;
        }
        return $results;
}

function gabcast_get($xtn) {
        global $db;

        $sql = "SELECT * FROM gabcast where ext='$xtn'";
        $results = $db->getRow($sql);
        if(DB::IsError($results)) {
                $results = null;
        }
        return $results;
}

function gabcast_add($xtn, $channbr, $pin) {
		// fail if this exten already exists in DB
		if(is_array(gabcast_get($xtn))) {
			echo "<div class=error>An error occured when writing to database</div>";
			return;
		}
        sql("INSERT INTO gabcast (ext, channbr, pin) values ('{$xtn}','{$channbr}','{$pin}')");
}

function gabcast_del($xtn) {
	sql("DELETE FROM gabcast WHERE ext = '{$xtn}'");
}

function gabcast_edit($xtn, $channbr, $pin) {
	gabcast_del($xtn);
	sql("INSERT INTO gabcast (ext, channbr, pin) values ('{$xtn}','{$channbr}','{$pin}')");
}

function gabcast_gabdial($c) {
        global $ext;

        $id = "app-gabcast"; // The context to be included
		$ext->addInclude('from-internal-additional', $id);
		$ext->add($id, $c, '', new ext_macro('user-callerid')); // $cmd,n,Macro(user-callerid)
		$ext->add($id, $c, '', new ext_goto('1','${AMPUSER}','gabcast'));
/*		
        $ext->add($id, $c, '', new ext_macro('user-callerid'));
        $ext->add($id, $c, '', new ext_noop('Checking for ${CALLERID(num)}'));
	$ext->add($id, $c, '', new ext_gotoif('$[ ${DB_EXISTS(GABCAST/${CALLERID(num)} = 1 ]', 'hasgabcast:nogabcast'));
	$ext->add($id, $c, 'hasgabcast', new ext_setvar('DIALSTRING', 'IAX2/iax.gabcast.com/${DB_RESULT}'));
	$ext->add($id, $c, '', new ext_goto('dodial'));
	$ext->add($id, $c, 'nogabcast', new ext_setvar('DIALSTRING', 'IAX2/iax.gabcast.com/gab'));
	$ext->add($id, $c, 'dodial', new ext_dial('${DIALSTRING},120'));
*/
}

?>
