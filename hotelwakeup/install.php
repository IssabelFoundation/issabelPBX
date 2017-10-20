<?php
out("Installing Hotel Style Wake Up Calls");
// list of the columns that need to be included in the hotelwakup table.  Add/subract values to this list and trigger a reinstall to alter the table
// this table is used to store module config info
$cols['maxretries'] = "INT NOT NULL";
$cols['waittime'] = "INT NOT NULL";
$cols['retrytime'] = "INT NOT NULL";
$cols['extensionlength'] = "INT NOT NULL";
$cols['cid'] = "VARCHAR(30)";
$cols['cnam'] = "VARCHAR(30)";
$cols['operator_mode'] = "INT NOT NULL";
$cols['operator_extensions'] = "VARCHAR(30)";
//new config table columns
$cols['application'] = "VARCHAR(30)";
$cols['data'] = "VARCHAR(30)";

// list of columns that need to be in the hotelwakeup_calls table.  Add/subract values to this list and trigger a reinstall to alter the table
// this table is used to store scheduled calls info
$sc_cols['time'] = "INT NOT NULL";
$sc_cols['ext'] = "INT NOT NULL";
$sc_cols['maxretries'] = "INT NOT NULL";
$sc_cols['retrytime'] = "INT NOT NULL";
$sc_cols['waittime'] = "INT NOT NULL";
$sc_cols['cid'] = "VARCHAR(30)";
$sc_cols['cnam'] = "VARCHAR(30)";
$sc_cols['application'] = "VARCHAR(30)";
$sc_cols['data'] = "VARCHAR(30)";
$sc_cols['tempdir'] = "VARCHAR(100)";
$sc_cols['outdir'] = "VARCHAR(100)";
$sc_cols['filename'] = "VARCHAR(100)";
$sc_cols['frequency'] = "INT NOT NULL";


// create the hotelwakeup table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS hotelwakeup (";
foreach($cols as $key=>$val)
{
	$sql .= $key.' '.$val.', ';
}
$sql .= "PRIMARY KEY (maxretries))";
$check = $db->query($sql);
if (DB::IsError($check))
{
	die_issabelpbx( "Can not create hotelwakeup table: ".$sql." - ".$check->getMessage() .  "<br>");
}

// create the hotelwakeup_calls table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS hotelwakeup_calls (";
foreach($sc_cols as $key=>$val)
{
	$sql .= $key.' '.$val.', ';
}
$sql .= "PRIMARY KEY (time))";
$check = $db->query($sql);
if (DB::IsError($check))
{
	die_issabelpbx( "Can not create hotelwakeup_calls table: ".$sql." - ".$check->getMessage() .  "<br>");
}

//check status of exist columns in the hotelwakup table and change/drop as required
$curret_cols = array();
$sql = "DESC hotelwakeup";
$res = $db->query($sql);
while($row = $res->fetchRow())
{
	if(array_key_exists($row[0],$cols))
	{
		$curret_cols[] = $row[0];
		//make sure it has the latest definition
		$sql = "ALTER TABLE hotelwakeup MODIFY ".$row[0]." ".$cols[$row[0]];
		$check = $db->query($sql);
		if (DB::IsError($check))
		{
			die_issabelpbx( "Can not update column ".$row[0].": " . $check->getMessage());
		}
	}
	else
	{
		//remove the column
		$sql = "ALTER TABLE hotelwakeup DROP COLUMN ".$row[0];
		$check = $db->query($sql);
		if(DB::IsError($check))
		{
			die_issabelpbx( "Can not remove column ".$row[0].": " . $check->getMessage());
		}
		else
		{
			out('Removed no longer needed column '.$row[0].' from hotelwakup table.');
		}
	}
}
//add missing columns to the hotelwakeup table
foreach($cols as $key=>$val)
{
	if(!in_array($key,$curret_cols))
	{
		$sql = "ALTER TABLE hotelwakeup ADD ".$key." ".$val;
		$check = $db->query($sql);
		if (DB::IsError($check))
		{
			die_issabelpbx( "Can not add column ".$key.": " . $check->getMessage());
		}
		else
		{
			out('Added column '.$key.' to hotelwakeup table');
		}
	}
}

//check status of exist columns in the hotelwakup_calls table and change/drop as required
$sc_curret_cols = array();
$sql = "DESC hotelwakeup_calls";
$res = $db->query($sql);
while($row = $res->fetchRow())
{
	if(array_key_exists($row[0],$sc_cols))
	{
		$sc_curret_cols[] = $row[0];
		//make sure it has the latest definition
		$sql = "ALTER TABLE hotelwakeup_calls MODIFY ".$row[0]." ".$sc_cols[$row[0]];
		$check = $db->query($sql);
		if (DB::IsError($check))
		{
			die_issabelpbx( "Can not update column ".$row[0].": " . $check->getMessage() .  "<br>");
		}
	}
	else
	{
		//remove the column
		$sql = "ALTER TABLE hotelwakeup_calls DROP COLUMN ".$row[0];
		$check = $db->query($sql);
		if(DB::IsError($check))
		{
			die_issabelpbx( "Can not remove column ".$row[0].": " . $check->getMessage() .  "<br>");
		}
		else
		{
			out('Removed no longer needed column '.$row[0].' from hotelwakeup_calls table');
		}
	}
}
//add missing columns to the hotelwakeup_calls table
foreach($sc_cols as $key=>$val)
{
	if(!in_array($key,$sc_curret_cols))
	{
		$sql = "ALTER TABLE hotelwakeup_calls ADD ".$key." ".$val;
		$check = $db->query($sql);
		if (DB::IsError($check))
		{
			die_issabelpbx( "Can not add column ".$key.": " . $check->getMessage() .  "<br>");
		}
		else
		{
			out('Added column '.$key.' to hotelwakeup_calls table');
		}
	}
}

//  Set default values - need mechanism to prevent overwriting existing values 
out("Installing Default Values");
$sql ="INSERT INTO hotelwakeup (maxretries, waittime, retrytime, cnam,             cid,    operator_mode, operator_extensions, extensionlength, application, data) ";
$sql .= "               VALUES ('3',        '60',     '60',      'Wake Up Calls',  '*68',  '1',           '00 , 01',           '4',             'AGI',        'wakeconfirm.php')";

$check = $db->query($sql);

//  Removed the following check because it prevents install if the query above fails to overwrite existing values.
//if (DB::IsError($check)) {
//        die_issabelpbx( "Can not create default values in `hotelwakeup` table: " . $check->getMessage() .  "\n");
//}

// Register FeatureCode - Hotel Wakeup;
$fcc = new featurecode('hotelwakeup', 'hotelwakeup');
$fcc->setDescription('Wake Up Calls');
$fcc->setDefault('*68');
$fcc->update();
unset($fcc);
