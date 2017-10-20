<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

//for translation only
if (false) {
  _("Pickup ParkedCall Any");
  _("Park Prefix");
  _("Pickup ParkedCall Prefix");
}

$fcc = new featurecode('parking', 'parkedcall');
$fcc->setDescription('Pickup ParkedCall Prefix');
$fcc->setDefault('*85');
$fcc->setProvideDest();
$fcc->update();
unset($fcc);

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

$sql['parkplus'] = "
	CREATE TABLE IF NOT EXISTS parkplus (
		id INTEGER NOT NULL $autoincrement,
		defaultlot VARCHAR(10) NOT NULL DEFAULT 'no',
		type VARCHAR(10) NOT NULL DEFAULT 'public',
		name VARCHAR(40) NOT NULL DEFAULT '',
		parkext VARCHAR(40) NOT NULL DEFAULT '',
		parkpos VARCHAR(40) NOT NULL DEFAULT '',
		numslots INTEGER NOT NULL DEFAULT 4,
		parkingtime INTEGER NOT NULL DEFAULT 45,
		parkedmusicclass VARCHAR(100) DEFAULT 'default',
		generatefc VARCHAR(10) NOT NULL DEFAULT 'yes',
		generatehints VARCHAR(10) NOT NULL DEFAULT 'yes',
		findslot VARCHAR(10) NOT NULL DEFAULT 'first',
		parkedplay VARCHAR(10) NOT NULL DEFAULT 'both',
		parkedcalltransfers VARCHAR(10) NOT NULL DEFAULT 'caller',
		parkedcallreparking VARCHAR(10) NOT NULL DEFAULT 'caller',
		alertinfo VARCHAR(254) NOT NULL DEFAULT '',
		cidpp VARCHAR(100) NOT NULL DEFAULT '',
		autocidpp VARCHAR(10) NOT NULL DEFAULT 'none',
		announcement_id INT DEFAULT NULL,
		comebacktoorigin VARCHAR(10) NOT NULL DEFAULT 'yes',
		dest VARCHAR(100) NOT NULL DEFAULT 'app-blackhole,hangup,1',
		PRIMARY KEY (id)
	)";

foreach ($sql as $t => $s) {
	out(sprintf(_("creating table %s if needed"), $t));
	$result = $db->query($s);
	if(DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo());
	}
}

$sql = "SELECT * FROM parkplus WHERE defaultlot = 'yes'";
$default_lot = sql($sql,"getAll");

// There should never be more than a single default lot so just blow them
// all away if we or esomeone did something dumb.
if (count($default_lot) > 1) {
	out(_("ERROR: too many default lots detected, deleting and reinitializing"));
	$sql = "DELETE FROM parkplus WHERE defaultlot = 'yes'";
	sql($sql);
	//We deleted all default lots to we need to trick the system into reinstalling the default lot!
	$default_lot = 0;
}

//If we have a default parking lot and we are within the new verion then we should check to make sure destination isn't blank
if(count($default_lot) == 1 && empty($default_lot[0]['dest'])) {
	$sql = "UPDATE parkplus SET dest = 'app-blackhole,hangup,1' WHERE defaultlot = 'yes'";
	sql($sql);
}

//Add default parking lot or try to migrate the old one.
if (count($default_lot) == 0) {

	outn(_("Initializing default parkinglot.."));
	$sql = "INSERT INTO parkplus (id, defaultlot, name, parkext, parkpos, numslots) VALUES (1, 'yes', 'Default Lot', '70', '71', 8)";
	sql($sql);
	out(_("done"));

	$sql = "SELECT keyword,data FROM parkinglot WHERE id = '1'";
	$results = $db->getAssoc($sql);
	if (!DB::IsError($results)) {

		out(_("migrating old parkinglot data"));

		$var['name'] = "Default Lot";
		$var['type'] = 'public';
		$var['parkext'] = '';
		$var['parkpos'] = '';
		$var['numslots'] = 4;
		$var['parkingtime'] = 45;
		$var['parkedmusicclass'] = 'default';
		$var['generatehints'] = 'yes';
		$var['generatefc'] = 'yes';
		$var['findslot'] = 'first';
		$var['parkedplay'] = 'both';
		$var['parkedcalltransfers'] = 'caller';
		$var['parkedcallreparking'] = 'caller';
		$var['alertinfo'] = '';
		$var['cidpp'] = '';
		$var['autocidpp'] = 'none';
		$var['announcement_id'] = null;
		$var['comebacktoorigin'] = 'yes';
		$var['dest'] = '';

		foreach ($results as $set => $val) {
			switch($set) {
			case 'numslots':
			case 'parkingtime':
			case 'parkedplay':
			case 'parkedcalltransfers':
			case 'parkedcallreparking':
			case 'parkedmusicclass':
			case 'findslot':
				$var[$set] = $val;
				break;
			case 'parkext':
				$var[$set] = $val;
				$var['parkpos'] = $val + 1;
				break;
			case 'parkalertinfo':
				$var['alertinfo'] = $val;
				break;
			case 'parkcid':
				$var['cidpp'] = $val;
				break;
			case 'parkingannmsg_id':
				$var['announcement_id'] = $val;
				break;
			case 'goto':
				$var['dest'] = $val;
				break;
			case 'parkinghints':
				$var['generatehints'] = $val;
				break;
			case 'parking_dest':
				$var['comebacktoorigin'] = ($val == 'device' ? 'yes' : 'no');
				break;
			default:
				/* parkedcallhangup
					 parkedcallrecording
					 adsipark
					 parkingcontext
				 */
				out(sprintf(_("%s no longer supported"),$set));
				break;
			}
		}

		foreach ($var as $key => $value) {
			$sql = "UPDATE parkplus SET `$key` = " . q($value) . " WHERE defaultlot = 'yes'";
			sql($sql);
		}

		out(_("migrated ... dropping old table parkinglot"));
		sql('DROP TABLE IF EXISTS parkinglot');
		unset($var);
		unset($results);
	}
}
