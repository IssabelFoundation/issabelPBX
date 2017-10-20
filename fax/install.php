<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//for translation only
if (false) {
_("Dial System FAX");
}

global $db;

$sql[]='CREATE TABLE IF NOT EXISTS `fax_details` (
  `key` varchar(50) default NULL,
  `value` varchar(510) default NULL,
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;';


$sql[]='CREATE TABLE IF NOT EXISTS `fax_incoming` (
  `cidnum` varchar(20) default NULL,
  `extension` varchar(50) default NULL,
  `detection` varchar(20) default NULL,
  `detectionwait` varchar(5) default NULL,
  `destination` varchar(50) default NULL,
  `legacy_email` varchar(50) default NULL
)';

$sql[]='CREATE TABLE IF NOT EXISTS `fax_users` (
  `user` varchar(15) default NULL,
  `faxenabled` varchar(10) default NULL,
  `faxemail` varchar(50) default NULL,
  `faxattachformat` varchar(10) default NULL,
  UNIQUE KEY `user` (`user`)
)';


foreach ($sql as $statement){
	$check = $db->query($statement);
	if (DB::IsError($check)){
		die_issabelpbx( "Can not execute $statement : " . $check->getMessage() .  "\n");
	}
}

//check for 2.6-style tables
$sql='describe fax_incoming';
$fields=$db->getAssoc($sql);
if(array_key_exists('faxdestination',$fields)){
	out(_('Migrating fax_incoming table...'));
	$sql='alter table fax_incoming 
				change faxdetection detection varchar(20) default NULL, 
				change faxdetectionwait detectionwait varchar(5) default NULL,
				change faxdestination destination varchar(50) default NULL,
				add legacy_email varchar(50) default NULL,
				drop faxenabled,
				modify extension varchar(50)';
	$q=$db->query($sql);
	if(DB::IsError($q)){
    out(_('WARNING: fax_incoming table may still be using the 2.6 schema!'));
  } else {
    out(_('Successfully migrated fax_incoming table!'));
  }
}
unset($sql);

/* migrate simu_fax from core to fax module, including in miscdests module in case it is being used as a destination.
   this migration is a bit "messy" but assures that any simu_fax settings or destinations being used in the dialplan
   will migrate silently and continue to work.
 */
outn(_("Moving simu_fax feature code from core.."));
$check = $db->query("UPDATE featurecodes set modulename = 'fax' WHERE modulename = 'core' AND featurename = 'simu_fax'");
if (DB::IsError($check)){
  if ($check->getCode() == DB_ERROR_ALREADY_EXISTS) {
    outn(_("duplicate, removing old from core.."));
    $check = $db->query("DELETE FROM featurecodes WHERE modulename = 'core' AND featurename = 'simu_fax'");
    if (DB::IsError($check)){
      out(_("unknown error"));
    } else {
      out(_("removed"));
    }
  } else {
    out(_("unknown error"));
  }
} else {
  out(_("done"));
}
outn(_("Updating simu_fax in miscdest table.."));
$check = $db->query("UPDATE miscdests set destdial = '{fax:simu_fax}' WHERE destdial = '{core:simu_fax}'");
if (DB::IsError($check)){
  out(_("not needed"));
} else {
  out(_("done"));
}
$fcc = new featurecode('fax', 'simu_fax');
$fcc->setDescription('Dial System FAX');
$fcc->setDefault('666');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);

//check to make sure that min/maxrate and ecm are set; if not set them to defaults
$settings=sql('SELECT * FROM fax_details', 'getAssoc', 'DB_FETCHMODE_ASSOC');
foreach($settings as $setting => $value){$set[$setting]=$value['0'];}
if(!is_array($set)){$set=array();}//never return a null value
if(!$set['minrate']){$sql[]='REPLACE INTO fax_details (`key`, `value`) VALUES ("minrate","14400")';}
if(!$set['maxrate']){$sql[]='REPLACE INTO fax_details (`key`, `value`) VALUES ("maxrate","14400")';}
if(!$set['ecm']){$sql[]='REPLACE INTO fax_details (`key`, `value`) VALUES ("ecm","yes")';}
if(!$set['legacy_mode']){$sql[]='REPLACE INTO fax_details (`key`, `value`) VALUES ("legacy_mode","no")';}
if(!$set['force_detection']){$sql[]='REPLACE INTO fax_details (`key`, `value`) VALUES ("force_detection","no")';}
 
if(isset($sql)){
	foreach ($sql as $statement){
		$check = $db->query($statement);
		if (DB::IsError($check)){
			die_issabelpbx( "Can not execute $statement : " . $check->getMessage() .  "\n");
		}
	}
}
/*
incoming columns:

faxexten: disabled
          default (check what global is)
          device_num

determine what default is, if a device then treat as that default device, if system
then treat as it was system here, and if disabled then treat as that.

legacy_email:
  null -> not in legacy mode
  blank or value -> in legacy mode

*/
outn(_("Checking if legacy fax needs migrating.."));
$sql = "SELECT `extension`, `cidnum`, `faxexten`, `faxemail`, `wait`, `answer` FROM `incoming`";
$legacy_settings = $db->getAll($sql, DB_FETCHMODE_ASSOC);
if(!DB::IsError($legacy_settings)) {
	out(_("starting migration"));

  // First step, need to get global settings and if not present use defaults
  //
  $sql = "SELECT variable, value FROM globals WHERE variable IN ('FAX_RX', 'FAX_RX_EMAIL', 'FAX_RX_FROM')";
  $globalvars = $db->getAll($sql, DB_FETCHMODE_ASSOC);

  foreach ($globalvars as $globalvar) {
	  $global[trim($globalvar['variable'])] = $globalvar['value'];	
  }
  $fax_rx =          isset($global['FAX_RX'])       ? $global['FAX_RX'] : 'disabled';
  $fax_rx_email =    isset($global['FAX_RX_EMAIL']) ? $global['FAX_RX_EMAIL'] : '';
  $sender_address  = isset($global['FAX_RX_FROM'])  ? $global['FAX_RX_FROM'] : '';

  // Now some sanity settings, can't email the fax if no email present
  if ($fax_rx_email == '') {
    $fax_rx = 'disabled';
  }

  // TODO Update Module Defaults Here
  // insert_general_values()
  //
  $global_migrate = array();
  $global_migrate[] = array('sender_address',$sender_address);
  $global_migrate[] = array('fax_rx_email',$fax_rx_email);

	outn(_("migrating defaults.."));
	$compiled = $db->prepare("REPLACE INTO `fax_details` (`key`, `value`) VALUES (?,?)");
	$result = $db->executeMultiple($compiled,$global_migrate);
	if(DB::IsError($result)) {
    out(_("failed"));
		die_issabelpbx( "Fatal error during migration: " . $result->getMessage() .  "\n");
	} else {
    out(_("migrated"));
  }

	$detection_type = array(0 => 'dahdi', 1 => 'dahdi', 2 => 'nvfax');
	$non_converts = array();

  if (count($legacy_settings)) {
    foreach($legacy_settings as $row) {
      $legacy_email = null;
      if ($row['faxexten'] == 'default') {
        $row['faxexten'] = $fax_rx;
      } else if ($row['faxexten'] == '') {
        $row['faxexten'] = 'disabled';
			}
			if ($row['wait'] < 2) {
        $detectionwait = '2';
      } elseif ($row['wait'] > 10) {
        $detectionwait = '10';
      } else {
        $detectionwait = $row['wait'];
      }
      $detection = $detection_type[$row['answer']];
      switch ($row['faxexten']) {
        case 'disabled':
          continue; // go back to foreach for now
        break;

        case 'system':
          $legacy_email = $row['faxemail'] ? $row['faxemail'] : $fax_rx_email;

          // Now some sanity, if faxemail is blank then it won't work and we treat as disabled
          //
          if (!$legacy_email) {
            continue;
          }
          $destination = '';
			    $insert_array[] = array($row['extension'], $row['cidnum'], $detection, $detectionwait, $destination, $legacy_email);
        break;

        default:
          if (ctype_digit($row['faxexten'])) {
            $sql = "SELECT `user` FROM `devices` WHERE `id` = '".$row['faxexten']."'";
            $user = $db->getOne($sql); 
            if (ctype_digit($user)) {
              $destination = "from-did-direct,$user,1";
            } else {
							$non_converts[] = array('extension' => $row['extension'], 'cidnum' => $row['cidnum'], 'device' => $row['faxexten'], 'user' => $user);
              continue;
            }
          }
			  $insert_array[] = array($row['extension'], $row['cidnum'], $detection, $detectionwait, $destination, $legacy_email);
        break;
      }
    }

    if (!empty($insert_array)) {
		  $compiled = $db->prepare("INSERT INTO `fax_incoming` (`extension`, `cidnum`, `detection`, `detectionwait`, `destination`, `legacy_email`) VALUES (?,?,?,?,?,?)");
		  $result = $db->executeMultiple($compiled,$insert_array);
    }
		if(!empty($insert_array) && DB::IsError($result)) {
      out("Fatal error migrating to fax module..legacy data retained in incoming and globals tables");
		  die_issabelpbx( "Fatal error during migration: " . $result->getMessage() .  "\n");
		} else {
			$migrate_array = array('faxexten', 'faxemail', 'wait', 'answer');
			foreach ($migrate_array as $field) {
				outn(sprintf(_("Removing field %s from incoming table.."),$field));
				$sql = "ALTER TABLE `incoming` DROP `".$field."`";
				$results = $db->query($sql);
				if (DB::IsError($results)) { 
					out(_("not present"));
				} else {
					out(_("removed"));
				}
			}
			outn(_("Removing old globals.."));
      $sql = "DELETE FROM globals WHERE variable IN ('FAX_RX', 'FAX_RX_EMAIL', 'FAX_RX_FROM')";

			$results = $db->query($sql);
			if (DB::IsError($results)) { 
				out(_("failed"));
			} else {
				out(_("removed"));
			}

	    $failed_faxes = count($non_converts);
      outn(_("Checking for failed migrations.."));
	    if ($failed_faxes) {
        $notifications = notifications::create($db);
		    $extext = _("The following Inbound Routes had FAX processing that failed migration because they were accessing a device with no associated user. They have been disabled and will need to be updated. Click delete icon on the right to remove this notice.")."<br />";
		    foreach ($non_converts as $did) {
          $didval = trim($did['extension']) == '' ? _("blank") : $did['extension'];
          $cidval = trim($did['cidnum']) == '' ? _("blank") : $did['cidnum'];
			    $extext .= "DID: ".$didval." CIDNUM: ".$cidval." PREVIOUS DEVICE: ".$did['device']."<br />";
		    }
		    $notifications->add_error('fax', 'FAXMIGRATE', sprintf(_('%s FAX Migrations Failed'),$failed_faxes), $extext, '', true, true);
        out(sprintf(_('%s FAX Migrations Failed, check notification panel for details'),$failed_faxes));
	    } else {
        out(_("all migrations succeeded successfully"));
      }
		}
  } else {
	  out(_("No Inbound Routes to migrate"));
  }
} else {
	out(_("already done"));
}

//migrate the faxemail field to allow emails longer than 50 characters
$sql = 'describe fax_users';
$fields = $db->getAssoc($sql);
if (array_key_exists('faxemail',$fields) && $fields['faxemail'][0] == 'varchar(50)') {
	out(_('Migrating faxemail field in the fax_users table to allow longer emails...'));
	$sql = 'ALTER TABLE fax_users CHANGE faxemail faxemail text default NULL';
	$q = $db->query($sql);
	if (DB::isError($q)) {
		out(_('WARNING: Failed migration. Email length is limited to 50 characters.'));
	} else {
		out(_('Successfully migrated faxemail field'));
	}
}

//add attachformat field...
if (!array_key_exists('faxattachformat', $fields)){
	out(_('Migrating fax_users table to add faxattachformat...'));
	$sql = 'ALTER TABLE fax_users ADD faxattachformat varchar(10) default NULL';
	$q = $db->query($sql);
	if (DB::IsError($q)) {
		out(_('WARINING: fax_users table may still be using the old schema!'));
	} else {
		out(_('Successfully migrated fax_users table!'));
	}
}

$set['value'] = 'www.issabel.org';
$set['defaultval'] =& $set['value'];
$set['readonly'] = 1;
$set['hidden'] = 1;
$set['module'] = '';
$set['category'] = 'Styling and Logos';
$set['emptyok'] = 0;
$set['name'] = 'tiff2pdf Author';
$set['description'] = "Author to pass to tiff2pdf's -a option";
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf =& issabelpbx_conf::create();
$issabelpbx_conf->define_conf_setting('PDFAUTHOR', $set, true);
?>
