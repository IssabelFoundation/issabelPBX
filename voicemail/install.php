<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2006-2013 Schmooze Com Inc.s
//
//for translation only
if (false) {
_("Voicemail");
_("My Voicemail");
_("Dial Voicemail");
_("Voicemail Admin");
_("Direct Dial Prefix");
}

global $astman;
global $amp_conf;
global $db;

$fcc = new featurecode('voicemail', 'myvoicemail');
$fcc->setDescription('My Voicemail');
$fcc->setDefault('*97');
$fcc->update();
unset($fcc);

$fcc = new featurecode('voicemail', 'dialvoicemail');
$fcc->setDescription('Dial Voicemail');
$fcc->setDefault('*98');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);

$sql = " CREATE TABLE IF NOT EXISTS `voicemail_admin` (
  `variable` varchar(30) NOT NULL DEFAULT '',
  `value` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`variable`)
);";
sql($sql);

$globals_convert['OPERATOR_XTN'] = '';
$globals_convert['VM_OPTS'] = '';
$globals_convert['VM_GAIN'] = '';
$globals_convert['VM_DDTYPE'] = 'u';

$globals_convert['VMX_OPTS_TIMEOUT'] = '';

$globals_convert['VMX_OPTS_LOOP'] = '';
$globals_convert['VMX_OPTS_DOVM'] = '';
$globals_convert['VMX_TIMEOUT'] = '2';
$globals_convert['VMX_REPEAT'] = '1';
$globals_convert['VMX_LOOPS'] = '1';

// Migrate the global settings now being managed here
//
$sql = "SELECT `variable`, `value`";
$sql_where = " FROM globals WHERE `variable` IN ('".implode("','",array_keys($globals_convert))."')";
$sql .= $sql_where;
$globals = $db->getAll($sql,DB_FETCHMODE_ASSOC);
if(DB::IsError($globals)) {
  die_issabelpbx($globals->getMessage());
}
outn(_("Checking for General Setting migrations.."));
if (count($globals)) {
  out(_("preparing"));
  foreach ($globals as $global) {
		unset($globals_convert[$global['variable']]);
		switch ($global['variable']) {
		case 'VMX_OPTS_TIMEOUT':
    	out(sprintf(_("%s no longer used"),$global['variable']));
		break;
		case 'VM_GAIN':
			if (is_numeric($global['value']) && $global['value'] >= 15) {
    		out(sprintf(_("%s changed from %s to max value 15"),$global['variable'], $global['value']));
				$global['value'] = 15;
			} else if (is_numeric($global['value'])) {
				$gain = ceil(round($global['value']) / 3) * 3;
				if ($gain != $global['value']) {
    			out(sprintf(_("%s adjusted from %s to %s"),$global['variable'], $global['value'], $gain));
					$global['value'] = $gain;
				}
			} else if ($global['value'] != '') {
   			out(sprintf(_("%s adjusted from bad value %s to default no gain"), $global['variable'], $global['value']));
				$global['value'] = '';
			}
			// FALL THROUGH TO SAVE
		default:
			$sql = 'INSERT INTO `voicemail_admin` (`variable`, `value`) VALUES ("' . $global['variable'] . '","' . $global['value'] . '")';;
			$result = $db->query($sql);
			if(DB::IsError($result)) {
				out(sprintf(_("ERROR inserting %s into voicemail_admin during migration, it may alreayd exist"), $global['variable']));
			} else {
 				out(sprintf(_("%s migrated"),$global['variable']));
			}
		break;
		}
	}
} else {
  out(_("not needed"));
}

// Now add any defaults not found in the globals table even though that should not happen
// if already there we just ignore
if (isset($globals_convert['VMX_OPTS_TIMEOUT'])) {
	unset($globals_convert['VMX_OPTS_TIMEOUT']);
}
foreach ($global_convert as $key => $value) {
	$sql = 'INSERT INTO `voicemail_admin` (`variable`, `value`) VALUES ("' . $key . '","' . $value . '")';;
	$result = $db->query($sql);
	if(!DB::IsError($result)) {
		out(sprintf(_("%s added"),$key));
	}
}

if (count($globals)) {
	out(_("General Settings migrated"));
	outn(_("Deleting migrated settings.."));
  $sql = "DELETE".$sql_where;
  $globals = $db->query($sql);
  if(DB::IsError($globals)) {
	  out(_("Fatal DB error trying to delete globals, trying to carry on"));
  } else {
	  out(_("done"));
  }
}

// Migrate VM_PREFIX from globals if needed
//
$current_prefix = $default_prefix = '*';
$sql = "SELECT `value` FROM globals WHERE `variable` = 'VM_PREFIX'";
$globals = $db->getAll($sql,DB_FETCHMODE_ASSOC);
if(!DB::IsError($globals)) {
	if (count($globals)) {
		$current_prefix = trim($globals[0]['value']);
		$sql = "DELETE FROM globals WHERE `variable` = 'VM_PREFIX'";
		out(_("migrated VM_PREFIX to feature codes"));
		outn(_("deleting VM_PREFIX from globals.."));
		$res = $db->query($sql);
		if(!DB::IsError($globals)) {
			out(_("done"));
		} else {
			out(_("could not delete"));
		}
	}
}

// Now setup the new feature code, if blank then disable
//
$fcc = new featurecode('voicemail', 'directdialvoicemail');
$fcc->setDescription('Direct Dial Prefix');
$fcc->setDefault($default_prefix);
if ($current_prefix != $default_prefix) {
	if ($current_prefix != '') {
		$fcc->setCode($current_prefix);
	} else {
		$fcc->setEnabled(false);
	}
}
$fcc->update();
unset($fcc);

//1.6.2
$ver = modules_getversion('voicemail');
if ($ver !== null && version_compare($ver,'1.6.2','lt')) { //we have to fix existing users with wrong values for vm ticket #1697
	if ($astman) {
		$sql = "select * from users where voicemail='disabled' or voicemail='';";
		$users = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
		foreach($users as $user) {
			$astman->database_put("AMPUSER",$user['extension']."/voicemail","\"novm\"");
		}
	} else {
		echo _("Cannot connect to Asterisk Manager with ").$amp_conf["AMPMGRUSER"]."/".$amp_conf["AMPMGRPASS"];
		return false;
	}
	sql("update users set voicemail='novm' where voicemail='disabled' or voicemail='';");
}

// vmailadmin module functionality has been fully incporporated into this module
// so if it is installed we remove and delete it from the repository.
//
outn(_("checking if Voicemail Admin (vmailadmin) is installed.."));
$modules = module_getinfo('vmailadmin');
if (!isset($modules['vmailadmin'])) {
  out(_("not installed, ok"));
} else {
  out(_("installed."));
  out(_("Voicemail Admin being removed and merged with Voicemail"));
  outn(_("Attempting to delete.."));
  $result = module_delete('vmailadmin');
  if ($result === true) {
    out(_("ok"));
  } else {
    out($result);
  }
}

$issabelpbx_conf =& issabelpbx_conf::create();

// VM_SHOW_IMAP
//
$set['value'] = false;
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 3;
$set['module'] = 'voicemail';
$set['category'] = 'Voicemail Module';
$set['emptyok'] = 0;
$set['sortorder'] = 100;
$set['name'] = 'Provide IMAP Voicemail Fields';
$set['description'] = 'Installations that have configured Voicemail with IMAP should set this to true so that the IMAP username and password fields are provided in the Voicemail setup screen for extensions. If an extension alread has these fields populated, they will be displayed even if this is set to false.';
$set['type'] = CONF_TYPE_BOOL;
$issabelpbx_conf->define_conf_setting('VM_SHOW_IMAP',$set,true);

// USERESMWIBLF
//
$set['value'] = (file_exists($amp_conf['ASTMODDIR']."/res_mwi_blf.so"));
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 3;
$set['module'] = 'voicemail';
$set['category'] = 'Voicemail Module';
$set['emptyok'] = 0;
$set['sortorder'] = 100;
$set['name'] = 'Create Voicemail Hints';
$set['description'] = 'Setting this flag with generate the required dialplan to integrate with res_mwi_blf which is included with the Official IssabelPBX Distro. It allows users to subscribe to other voicemail box and be notified via BLF of changes.';
$set['type'] = CONF_TYPE_BOOL;
$issabelpbx_conf->define_conf_setting('USERESMWIBLF',$set,true);

/*
   update modules.conf to make sure it preloads res_mwi_blf.so if they have it
   This makes sure that the modules.conf has been updated for older systems
   which assures that mwi blf events are captured when Asterisk first starts
*/
if (file_exists($amp_conf['ASTMODDIR']."/res_mwi_blf.so")) {
    $mod_conf = $amp_conf['ASTETCDIR'].'/modules.conf';
    exec("grep -e '^[[:space:]]*preload[[:space:]]*=.*res_mwi_blf.so' $mod_conf",$output,$ret);
    if ($ret) {
      outn(_("adding preload for res_mwi_blf.so to modules.conf.."));
      exec('sed -i.2.8.0-1.bak "s/\s*preload\s*=>\s*chan_local.so/&\npreload => res_mwi_blf.so ;auto-inserted by IssabelPBX/" '.$mod_conf,$output,$ret);
      exec("grep -e '^[[:space:]]*preload[[:space:]]*=.*res_mwi_blf.so' $mod_conf",$output,$ret);
      if ($ret) {
        out(_("FAILED"));
        out(_("you may need to add the line 'preload => res_mwi_blf.so' to your modules.conf manually"));
      } else {
        out(_("ok"));
      }
    }
    unset($output);
}
