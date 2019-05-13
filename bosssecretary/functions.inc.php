<?php


/********************************************************
*														*
* 					API FUNCTIONS						*
*														*
********************************************************/


define("BOSSSECRETARY_PARAM_PREFIX", "bsgroup-");
define("BOSSSECRETARY_LABEL_DEFAULT", "Group ");
define("BOSSSECRETARY_CONTEXT", "ext-bosssecretary");
define("BOSSSECRETARY_MACRO_LOCKED", "macro-bosssecretary-locked");
define("BOSSSECRETARY_MACRO_LOCKED_NAME", "bosssecretary-locked");
define("BOSSSECRETARY_TOGGLE", "app-bosssecretary-toggle");
define("BOSSSECRETARY_ON", "app-bosssecretary-on");
define("BOSSSECRETARY_OFF", "app-bosssecretary-off");
define("BOSSSECRETARY_HINTS", "app-bosssecretary-hints");


function bosssecretary_get_config($engine){
	global $db;
	global $ext;
	global $amp_conf;
	global $astman;

	switch($engine) {
		case "asterisk":




			$fcc_toggle = bosssecretary_get_fcc_toggle();
			$fcc_on  = bosssecretary_get_fcc_on();
			$fcc_off  = bosssecretary_get_fcc_off();
			$groups = bosssecretary_get_all_groups();

			$ctx_app_toggle =   BOSSSECRETARY_TOGGLE;
			$ctx_app_on     =   BOSSSECRETARY_ON;
			$ctx_app_off    =   BOSSSECRETARY_OFF;
			$ctx_app_hints	=	BOSSSECRETARY_HINTS;
			$ctx_bsc		=	BOSSSECRETARY_CONTEXT;

			$ext->addInclude('from-internal-additional', $ctx_bsc);
			$ext->addInclude($ctx_bsc, $ctx_app_toggle);
			$ext->addInclude($ctx_bsc, $ctx_app_on);
			$ext->addInclude($ctx_bsc, $ctx_app_off);
			$ext->addInclude($ctx_bsc, $ctx_app_hints);

			if (!empty($groups))
			{

				$astman->database_deltree("bosssecretary/group");
				$groups = bosssecretary_to_group($groups);



				foreach ($groups as $group)
				{
					$id_group = $group["id_group"];

					foreach ($group["bosses"] as $extension)
					{
						$astman->database_put("bosssecretary","group/$id_group/member/$extension", "boss");
						$astman->database_put("bosssecretary","group/member/$extension", $id_group);
					}
					foreach ($group["secretaries"] as $extension)
					{
						$astman->database_put("bosssecretary","group/$id_group/member/$extension", "secretary");
						$astman->database_put("bosssecretary","group/member/$extension", $id_group);
					}
					foreach ($group["chiefs"] as $extension)
					{
						$astman->database_put("bosssecretary","group/$id_group/member/$extension", "chief");
					}
				}
				// :::: BSC Off [app-bosssecretary-on] ::::

				$ext->add($ctx_app_on, $fcc_on, '', new ext_noop("Bosssecretary on starts..."));
				$ext->add($ctx_app_on, $fcc_on, '', new ext_answer());
				$ext->add($ctx_app_on, $fcc_on, '', new ext_macro ('user-callerid'));
				$ext->add($ctx_app_on, $fcc_on, '', new ext_wait ('2'));
				$ext->add($ctx_app_on, $fcc_on, '', new ext_gotoif('${DB_EXISTS(bosssecretary/group/member/${AMPUSER})}','on','exit'));
				$ext->add($ctx_app_on, $fcc_on, 'on', new ext_setvar('GROUP','${DB(bosssecretary/group/member/${AMPUSER})}'));
				$ext->add($ctx_app_on, $fcc_on, '', new ext_dbdel('bosssecretary/group/${GROUP}/locked'));
				$ext->add($ctx_app_on, $fcc_on, '', new ext_setvar('STATE','NOT_INUSE'));
				$ext->add($ctx_app_on, $fcc_on, '', new ext_gosub('1','sstate',$ctx_app_on));
				$ext->add($ctx_app_on, $fcc_on, '', new ext_playback('activated'));
				$ext->add($ctx_app_on, $fcc_on, '', new ext_hangup());
				$ext->add($ctx_app_on, $fcc_on, 'exit', new ext_noop('${AMPUSER} doesn\\\'t belongs bosssecretary group'));
				$ext->add($ctx_app_on, $fcc_on, '', new ext_hangup());
				$ext->add($ctx_app_on, 'sstate', '', new ext_setvar('DEVICE_STATE(Custom:BSC${GROUP})','${STATE}'));
				$ext->add($ctx_app_on, 'sstate', 'return', new ext_return());


				// :::: BSC Off [app-bosssecretary-off] ::::
				$ext->add($ctx_app_off, $fcc_off, '', new ext_noop("Bosssecretary off starts..."));
				$ext->add($ctx_app_off, $fcc_off, '', new ext_answer());
				$ext->add($ctx_app_off, $fcc_off, '', new ext_macro ('user-callerid'));
				$ext->add($ctx_app_off, $fcc_off, '', new ext_wait ('2'));
				$ext->add($ctx_app_off, $fcc_off, '', new ext_gotoif('${DB_EXISTS(bosssecretary/group/member/${AMPUSER})}','off','exit'));
				$ext->add($ctx_app_off, $fcc_off, 'off', new ext_setvar('GROUP','${DB(bosssecretary/group/member/${AMPUSER})}'));
				$ext->add($ctx_app_off, $fcc_off, "", new ext_setvar('DB(bosssecretary/group/${GROUP}/locked)',"1"));
				$ext->add($ctx_app_off, $fcc_off, '', new ext_setvar('STATE','INUSE'));
				$ext->add($ctx_app_off, $fcc_off, '', new ext_gosub('1','sstate',$ctx_app_off));
				$ext->add($ctx_app_off, $fcc_off, '', new ext_playback('de-activated'));
				$ext->add($ctx_app_off, $fcc_off, '', new ext_hangup());
				$ext->add($ctx_app_off, $fcc_off, 'exit', new ext_noop('${AMPUSER} doesn\\\'t belongs bosssecretary group'));
				$ext->add($ctx_app_off, $fcc_off, '', new ext_hangup());
				$ext->add($ctx_app_off, 'sstate', '', new ext_setvar('DEVICE_STATE(Custom:BSC${GROUP})','${STATE}'));
				$ext->add($ctx_app_off, 'sstate', 'return', new ext_return());

				// :::: BSC Toggle [app-bosssecretary-toggle] ::::
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_noop("Bosssecretary toggle starts..."));
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_answer());
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_macro ('user-callerid'));
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_wait ('2'));
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_gotoif('${DB_EXISTS(bosssecretary/group/member/${AMPUSER})}','check','exit' ));
				$ext->add($ctx_app_toggle, $fcc_toggle, 'check', new ext_setvar('GROUP','${DB(bosssecretary/group/member/${AMPUSER})}'));
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_gotoif('${DB_EXISTS(bosssecretary/group/${GROUP}/locked)}','unlock', 'lock'));
				$ext->add($ctx_app_toggle, $fcc_toggle, "lock", new ext_setvar('DB(bosssecretary/group/${GROUP}/locked)',"1"));
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_setvar('STATE','INUSE'));
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_gosub('1','sstate',$ctx_app_toggle));
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_playback('de-activated'));
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_hangup());
				$ext->add($ctx_app_toggle, $fcc_toggle, 'unlock', new ext_dbdel('bosssecretary/group/${GROUP}/locked'));
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_setvar('STATE','NOT_INUSE'));
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_gosub('1','sstate',$ctx_app_toggle));
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_playback('activated'));
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_hangup());
				$ext->add($ctx_app_toggle, $fcc_toggle, 'exit', new ext_noop('${AMPUSER} doesn\\\'t belongs bosssecretary group'));
				$ext->add($ctx_app_toggle, $fcc_toggle, '', new ext_hangup());
				$ext->add($ctx_app_toggle, 'sstate', '', new ext_setvar('DEVICE_STATE(Custom:BSC${GROUP})','${STATE}'));
				$ext->add($ctx_app_toggle, 'sstate', 'return', new ext_return());

				// :::: BSC Hints [app-bosssecretary-hints] ::::
				foreach ($groups as $group)
				{

					$id_group = $group["id_group"];
					foreach ($group["bosses"] as $extension)
					{
						$hint = "Custom:BSC$id_group";
						$ext_subscribed = $fcc_toggle . $extension;
						$ext->add($ctx_app_hints, $ext_subscribed, '', new ext_goto(1, $fcc_toggle, $ctx_app_toggle));
						$ext->addHint($ctx_app_hints, $ext_subscribed, $hint);

					}
					foreach ($group["secretaries"] as $extension)
					{
						$hint = "Custom:BSC$id_group";
						$ext_subscribed = $fcc_toggle . $extension;
						$ext->add($ctx_app_hints, $ext_subscribed, '', new ext_goto(1, $fcc_toggle, $ctx_app_toggle));
						$ext->addHint($ctx_app_hints, $ext_subscribed, $hint);
					}

				}

				// :::: BSC Context [ext-bosssecretary] ::::
				foreach ($groups as $group)
				{
					$AllExtensions = array_merge($group["bosses"], $group["secretaries"]);
					$id_group = $group["id_group"];

					foreach ($group["bosses"] as $extension)
					{
						$ext->add($ctx_bsc, $extension, '', new ext_noop("Bosssecretary: Checking  lock for $extension extension"));
						$ext->add($ctx_bsc, $extension, '', new ext_macro ('user-callerid'));
						$ext->add($ctx_bsc, $extension, '', new ext_setvar('CALLER','${CALLERID(num)}'));
						$ext->add($ctx_bsc, $extension, '', new ext_gotoif('${DB_EXISTS(bosssecretary/group/'.$id_group.'/member/${CALLER})}','exit_module'));
						$ext->add($ctx_bsc, $extension, '', new ext_gotoif('${DB_EXISTS(bosssecretary/group/'.$id_group.'/locked)}','exit_module','run_module'));
						$ext->add($ctx_bsc, $extension, 'run_module', new ext_noop("Bosssecretary: Executing module"));
						$ext->add($ctx_bsc, $extension, '', new ext_sipaddheader("Alert-Info", "<http://nohost>\;info=alert-group\;x-line-id=0"));
						$extensions = array();
						
						// David
						foreach ($group["secretaries"] as $sip_extension)
						{
							$extensions[] = "$sip_extension";
						}
						//	$extensions[] = "$extension";
						$args = '${RINGTIMER},${DIAL_OPTIONS},' . implode ("-", $extensions);
						$ext->add($ctx_bsc, $extension, '', new ext_macro ("dial", $args));
						$ext->add($ctx_bsc, $extension, 'exit_module', new ext_noop("Bosssecretary: Exit") );
						$ext->add($ctx_bsc, $extension, '', new ext_goto(1, $extension, "ext-local") );
						$extensions = "";
					}
				}
			}

			break;
	}

}


/********************************************************
*														*
* 					DATABASE FUNCTIONS					*
*														*
********************************************************/

function bosssecretary_get_groups()
{
	global $db;
	$sql = "SELECT * from bosssecretary_group";
	$results = $db->getAll($sql);
	if(DB::IsError($results)) {
		$results = null;
	}
	return $results;
}


function bosssecretary_get_fcc_toggle()
{
	$fcc = new featurecode('bosssecretary', 'bsc_toggle');
	$extLock = $fcc->getCodeActive();
	unset($fcc);
	return $extLock;
}

function bosssecretary_get_fcc_on()
{
	$fcc = new featurecode('bosssecretary', 'bsc_on');
	$extLock = $fcc->getCodeActive();
	unset($fcc);
	return $extLock;
}

function bosssecretary_get_fcc_off()
{
	$fcc = new featurecode('bosssecretary', 'bsc_off');
	$extLock = $fcc->getCodeActive();
	unset($fcc);
	return $extLock;
}



function bosssecretary_to_group($groups)
{
	$newGroup = array();
	foreach ($groups as $group)
	{
		if (!isset($newGroup[$group["id_group"]]))
		{
			$newGroup[$group["id_group"]] =  $group;
			$newGroup[$group["id_group"]]["bosses"] = array();
			// isset is so much faster than in_array ;)
			$newGroup[$group["id_group"]]["bosses"][$group["boss_extension"]] = $group["boss_extension"];

			$newGroup[$group["id_group"]]["secretaries"] = array();
			// isset is so much faster than in_array ;)
			$newGroup[$group["id_group"]]["secretaries"][$group["secretary_extension"]] = $group["secretary_extension"];

			if (isset($group["chief_extension"]))
			{
				$newGroup[$group["id_group"]]["chiefs"] = array();
				$newGroup[$group["id_group"]]["chiefs"][$group["chief_extension"]] = $group["chief_extension"];
			}
		}
		else
		{
			// isset is so much faster than in_array ;)
			if (!isset($newGroup[$group["id_group"]]["bosses"][$group["boss_extension"]]))
			{
				$newGroup[$group["id_group"]]["bosses"][$group["boss_extension"]] = $group["boss_extension"];
			}
			// isset is so much faster than in_array ;)
			if (!isset($newGroup[$group["id_group"]]["secretaries"][$group["secretary_extension"]]))
			{
				$newGroup[$group["id_group"]]["secretaries"][$group["secretary_extension"]] = $group["secretary_extension"];
			}
			if (!isset($newGroup[$group["id_group"]]["chiefs"][$group["chief_extension"]]))
			{
				$newGroup[$group["id_group"]]["chiefs"][$group["chief_extension"]] = $group["chief_extension"];
			}
		}
	}
	return $newGroup;
}

function bosssecretary_search($extensions)
{
	global $db;
	$extensions = explode(",", $extensions);
	foreach ($extensions as $extension)
	{
		if (is_numeric($extension))
		{
			$valid[]= trim($extension);
		}

	}
	if (!empty($valid))
	{
		$extensions = implode(",", $valid);
		$sql = "SELECT boss_extension AS extension, 'boss' as `type`, g.* FROM bosssecretary_group as g INNER JOIN bosssecretary_boss as b ON b.id_group = g.id_group  WHERE boss_extension IN ($extensions)
			UNION 
			SELECT secretary_extension AS extension, 'secretary' as `type`, g.* FROM bosssecretary_group as g INNER JOIN bosssecretary_secretary as s ON s.id_group = g.id_group  WHERE secretary_extension IN ($extensions)
			UNION
			SELECT chief_extension AS extension, 'chief' as `type`, g.* FROM bosssecretary_group as g INNER JOIN bosssecretary_chief as c ON c.id_group = g.id_group  WHERE chief_extension IN ($extensions)
";
		//echo $sql . "<br>";

		$results = $db->getAll($sql, array(), DB_FETCHMODE_ASSOC);
		if(DB::IsError($results)) {
			$results = null;
		}
		return $results;
	}
	return null;

}


function bosssecretary_get_all_groups()
{
	global $db;
	$sql = "SELECT
			g.id_group, 
			boss_extension, 
			secretary_extension,
			chief_extension  
		FROM bosssecretary_group AS g  
		INNER JOIN bosssecretary_boss AS b ON g.id_group = b.id_group 
		INNER JOIN bosssecretary_secretary AS s ON g.id_group = s.id_group
		LEFT JOIN bosssecretary_chief AS c ON g.id_group = c.id_group
;";
	$results = $db->getAll($sql, array(), DB_FETCHMODE_ASSOC);
	if(DB::IsError($results)) {
		$results = null;
	}
	return $results;
}

function bosssecretary_array_diff_with_db(array $exts, $group_number)
{
	global $db;


	if (!empty($exts))
	{
		if (bosssecretary_group_exists($group_number))
		{
			$sqlB = "AND id_group <> '" . $db->escapeSimple($group_number) . "'";
			$sqlS = "AND id_group <> '" . $db->escapeSimple($group_number) . "'";
		}
		else
		{
			$sqlB = $sqlS = "";
		}
		$exts = array_unique($exts);
		$strExts = bosssecretary_array_to_mysql_param_in($exts);

		$sql = "SELECT `boss_extension` AS `extension` FROM `bosssecretary_boss` WHERE boss_extension IN ($strExts) $sqlB
				UNION
				SELECT `secretary_extension` AS `extension` FROM `bosssecretary_secretary` WHERE secretary_extension IN ($strExts) $sqlS";

		$results = $db->getAll($sql, array(), DB_FETCHMODE_ASSOC);
		if(DB::IsError($results))
		{
			$results = null;
		}
		else
		{
			foreach ($results as $record)
			{
				if (($key = array_search($record['extension'], $exts)) !== FALSE)
				{
					unset($exts[$key]);
				}
			}
			$strExts = bosssecretary_array_to_mysql_param_in($exts);
			$sql = "SELECT extension FROM `users` WHERE extension IN ($strExts)";
			$results = $db->getAll($sql, array(), DB_FETCHMODE_ASSOC);
			if(DB::IsError($results))
			{
				$results = null;
			}
			else
			{
				$exts = array();
				foreach ($results as $record)
				{
					array_push($exts, current($record));
				}
			}

		}
	}
	return $exts;
}




function bosssecretary_clean_remove_duplicates( $bosses, $secretaries , $group_number = "")
{
	// Extraigo las extensiones de jefes del formulario
	$arr_bosses_extensions = bosssecretary_str_extensions_to_array($bosses);

	// Extraigo las extensiones de secretaria del formulario
	$arr_secretaries_extensions = bosssecretary_str_extensions_to_array($secretaries);

	// Quito de las extensiones de secretarias las extensiones que estan en los jefes
	$arr_secretaries_extensions = bosssecretary_array_diff($arr_secretaries_extensions, $arr_bosses_extensions);


	$extensionsCleaned = array();
	// Ahora quito de las extensiones de secretarias las extensiones que ya son jefes o secretarias segun la BD
	$extensionsCleaned["secretaries"] = bosssecretary_array_diff_with_db($arr_secretaries_extensions, $group_number);

	// Ahora quito de las extensiones de jefes las extensiones que ya son jefes o secretarias segun la BD
	$extensionsCleaned["bosses"] = bosssecretary_array_diff_with_db($arr_bosses_extensions, $group_number);
	return $extensionsCleaned;
}

function bosssecretary_get_group_number_free()
{
	global $db;
	$sql = "SELECT MIN(group_number) AS Bottom FROM bosssecretary_group_numbers_free ORDER BY group_number ASC LIMIT 1";
	$result = $db->getAll($sql, array(), DB_FETCHMODE_ASSOC);
	if(DB::IsError($result)) {
		$result = null;
	}
	if (!isset($result[0]["Bottom"]))
	{
		$sql = "SELECT MAX(id_group) AS Top FROM bosssecretary_group";
		$result = $db->getAll($sql, array(), DB_FETCHMODE_ASSOC);
		if(DB::IsError($result)) {
			$result = null;
		}
		if (!isset($result[0]["Top"]))
		{
			$next = 1;
		}
		else
		{
			$next = $result[0]["Top"] + 1;
		}
	}
	else
	{
		$next = $result[0]["Bottom"];
	}
	return $next;
}


function bosssecretary_set_group_number_free($number)
{
	global $db;
	$sql = "INSERT INTO bosssecretary_group_numbers_free VALUES ('$number')";
	//$result = $db->getAll($sql);
	return sql($sql);
}

function bosssecretary_remove_group_number_free($number)
{
	global $db;
	$sql = "DELETE FROM bosssecretary_group_numbers_free WHERE _rowid NOT IN (SELECT _rowid FROM bosssecretary_group) AND _rowid > (SELECT MAX(_rowid) FROM bosssecretary_group)";
	sql($sql);
	$sql = "DELETE FROM bosssecretary_group_numbers_free WHERE _rowid = '$number'";
	return sql($sql);
}



function bosssecretary_group_add ( $group_number, $group_label,  array $bosses, array $secretaries, $chiefs)
{
	global $db;
	$errors= array();
	if (is_numeric($group_number) or $group_number =="")
	{
		if (!bosssecretary_group_exists($group_number))
		{
			if (empty($bosses))
			{
				array_push($errors, "You must put one boss extension at least");
			}
			if (empty($secretaries))
			{
				array_push($errors, "You must put one secretary extension at least");
			}


			if (empty($errors))
			{
				$sql = "INSERT INTO bosssecretary_group (`id_group`, `label`) VALUES('".$db->escapeSimple($group_number)."', '".$db->escapeSimple($group_label)."')";
				sql($sql);
				//$group_number = mysql_insert_id();
				bosssecretary_remove_group_number_free($group_number);

				foreach ($bosses as $boss)
				{
					$boss = trim($boss);
					if (!empty($boss) and !(bosssecretary_extension_in_bosses_group($boss)))
					{
						$sql = "INSERT INTO bosssecretary_boss VALUES ('$group_number', '$boss')";
						sql($sql);
					}
					else
					{
						array_push($errors, "($boss) Extension exists already in another group like boss");
					}
				}
				foreach ($secretaries as $secretary)
				{
					$secretary = trim($secretary);
					if (!empty($secretary) and !(bosssecretary_extension_in_secretaries_group($secretary)))
					{
						$sql = "INSERT INTO bosssecretary_secretary VALUES ('$group_number', '$secretary')";
						sql($sql);
					}
					else
					{
						array_push($errors, "($secretary) Extension exists already in another group like secretary");
					}

				}
				if (is_array($chiefs))
				{
					foreach ($chiefs as $chief)
					{
						$chief = trim($chief);
						if (!empty($chief))
						{
							$sql = "INSERT INTO bosssecretary_chief VALUES ('$group_number', '$chief')";
							sql($sql);
						}
					}
				}
			}
		}
		else
		{
			array_push($errors, 'Group exists already');
		}
	}
	else
	{
		array_push($errors, 'Group number must be a numeric value');
	}
	return $errors;


}


function bosssecretary_group_edit ( $group_number, $group_label,  array $bosses, array $secretaries, array $chiefs)
{
	global $db;
	$errors= array();
	if (is_numeric($group_number))
	{
		if (bosssecretary_group_exists($group_number) )
		{
			if (empty($bosses))
			{
				array_push($errors, "You must put one boss extension at least");
			}
			if (empty($secretaries))
			{
				array_push($errors, "You must put one secretary extension at least");
			}

			if (empty($errors))
			{
				$sql = "DELETE FROM `bosssecretary_group` WHERE id_group = $group_number";
				sql($sql);

				$sql = "DELETE FROM `bosssecretary_boss` WHERE id_group = $group_number";
				sql($sql);

				$sql = "DELETE FROM `bosssecretary_secretary` WHERE id_group = $group_number";
				sql($sql);

				$sql = "DELETE FROM `bosssecretary_chief` WHERE id_group = $group_number";
				sql($sql);

				$sql = "INSERT INTO bosssecretary_group (`id_group`, `label`) VALUES('".$db->escapeSimple($group_number)."', '".$db->escapeSimple($group_label)."')";
				sql($sql);
				foreach ($bosses as $boss)
				{
					$boss = trim($boss);
					if (!empty($boss))
					{
						$sql = "INSERT INTO bosssecretary_boss VALUES ('$group_number', '$boss')";
						sql($sql);
					}
				}
				foreach ($secretaries as $secretary)
				{
					$secretary = trim($secretary);
					if (!empty($secretary))
					{
						$sql = "INSERT INTO bosssecretary_secretary VALUES ('$group_number', '$secretary')";
						sql($sql);
					}
				}

				foreach ($chiefs as $chief)
				{
					$chief = trim($chief);
					if (!empty($chief))
					{
						$sql = "INSERT INTO bosssecretary_chief VALUES ('$group_number', '$chief')";
						sql($sql);
					}
				}

			}
		}
		else
		{
			array_push($errors, "Group doesn't exists");
		}
	}
	else
	{
		array_push($errors, 'Group number must be a value numeric');
	}
	return $errors;


}

function bosssecretary_group_delete($group_number)
{
	global $db;
	$sql = "DELETE FROM `bosssecretary_group` WHERE id_group = '$group_number'";
	$group = sql($sql);


	$sql = "DELETE FROM `bosssecretary_boss` WHERE id_group = '$group_number'";
	$bosses = sql($sql);

	$sql = "DELETE FROM `bosssecretary_secretary` WHERE id_group = '$group_number'";
	$secretaries = sql($sql);

	$sql = "DELETE FROM `bosssecretary_chief` WHERE id_group = '$group_number'";
	$chiefs = sql($sql);

	bosssecretary_set_group_number_free($group_number);
	return (($group === $bosses) and ($group===$secretaries));
}

function bosssecretary_group_exists( $group)
{
	global $db;

	$sql = "SELECT 'true' from bosssecretary_group WHERE id_group='" . $db->escapeSimple($group). "' LIMIT 1";
	$results = $db->getAll($sql);
	if(DB::IsError($results)) {
		$results = null;
	}
	return count($results) == 1;
}





function bosssecretary_extension_in_bosses_group( $ext)
{
	global $db;
	$sql = "SELECT 'true' from bosssecretary_boss WHERE boss_extension='" . $db->escapeSimple($ext) . "' LIMIT 1";
	$results = $db->getAll($sql);
	if(DB::IsError($results)) {
		$results = null;
	}
	return count($results) == 1;
}


function bosssecretary_extension_in_secretaries_group( $ext)
{
	global $db;
	$sql = "SELECT 'true' from bosssecretary_secretary WHERE secretary_extension='" . $db->escapeSimple($ext) . "' LIMIT 1";
	$results = $db->getAll($sql);
	if(DB::IsError($results)) {
		$results = null;
	}
	return count($results) == 1;
}



function bosssecretary_get_data_of_group($group)
{
	global $db;

	// we use LEFT JOIN because at least we wanna to know info group (id_group and label)!
	$sql = "SELECT
			g.id_group, 
			g.label, 
			b.boss_extension, 
			s.secretary_extension,
			c.chief_extension  
		FROM bosssecretary_group AS g 
		LEFT JOIN bosssecretary_boss AS b ON b.id_group = g.id_group 
		LEFT JOIN bosssecretary_secretary AS s ON s.id_group = g.id_group
		LEFT JOIN bosssecretary_chief AS c ON c.id_group = g.id_group
		WHERE g.id_group='" .  $db->escapeSimple($group) . "'";

	$results = $db->getAll($sql, array(), DB_FETCHMODE_ASSOC);
	if(DB::IsError($results)) {
		$results = null;
	}
	return $results;
}



function bosssecretary_get_extension_data($ext){
	global $db;
	$sql = " SELECT extension, name FROM `users` u WHERE extension = '" .  $db->escapeSimple($ext) . "' LIMIT 1;";
	$results = $db->getAll($sql);

	if(DB::IsError($results)) {
		$results = array();
	}
	else{
		$results = current($results);
	}
	return $results;
}

function bosssecretary_extension_exists($ext){
	global $db;
	$sql = "SELECT 'true' FROM `users` WHERE extension = '" . $db->escapeSimple($ext) . "' LIMIT 1";
	$result = $db->getAll($sql);
	return count($result[0]) === 1;
}


/********************************************************
*														*
* 					UTILS FUNCTIONS						*
*														*
********************************************************/

/*

$haystack = array('a','b','c', 'd');
$needle = array('b','c', 'd','e');

$result = bosssecretary_array_diff($haystack, $needle);

$result is equal to array('a').

Yeah! I know! array_diff is a php function BUT is broken since v4.0.4 and we need it!
http://www.php.net/array_diff

*/


function bosssecretary_array_diff(array $haystack, array $needle)
{
	foreach ($needle as $value)
	{
		if (($key = array_search($value, $haystack)) !== FALSE)
		{
			unset($haystack[$key]);
		}
	}
	return $haystack;
}

/*

$haystack = array('a', 'b', 1, 10);

echo  bosssecretary_array_to_mysql_param_in($haystack);

output is:
'a', 'b', '1', '10'

*/


function bosssecretary_array_to_mysql_param_in(array $params)
{
	global $db;
	$arrParams = array();
	foreach ($params as $value)
	{
		array_push($arrParams, "'" .$db->escapeSimple($value). "'");
	}
	return implode($arrParams, ", ");
}




function bosssecretary_str_extensions_to_array($strExtensions)
{
	$strExtensions = trim($strExtensions);
	$strExtensions = str_replace(" ", "\n", $strExtensions);
	$arrExtensions = explode("\n", $strExtensions);
	foreach ($arrExtensions as $key => &$ext)
	{
		$ext = str_replace('\n','', $ext);
		$ext = trim($ext);
		if (empty($ext) and $ext != '0') // 0 is considered an empty string by some versions of php
		unset($arrExtensions[$key]);
	}
	return $arrExtensions;
}

function bosssecretary_create_nav_groups_links($groups, $dispnum)
{
	$links = array();
	$link["url"] = "config.php?display=$dispnum&bsgroupdisplay=".BOSSSECRETARY_PARAM_PREFIX. "add";
	$link["text"] = "Add Group";
	array_push($links, $link);
	if (!empty($groups))
	{
		foreach ($groups as $group)
		{
			$link["url"] = "config.php?display=$dispnum&bsgroupdisplay=".BOSSSECRETARY_PARAM_PREFIX . $group[0];
			if (trim($group[1]) == "" )
			{
				$group[1] = BOSSSECRETARY_LABEL_DEFAULT . $group[0];
			}
			$link["text"] = $group[0] . " (". $group[1].")";
			array_push($links, $link);
		}
	}
	return $links;
}

function bosssecretary_extract_group_from_request( $param)
{
	return ltrim($param, BOSSSECRETARY_PARAM_PREFIX); // easy, isn't it?
}


function bosssecretary_set_params_to_edit( $records)
{
	$vars = array();
	$first = current($records);
	$vars["group_number"] =  $first["id_group"];
	$vars["group_label"] =  $first["label"];

	if (trim($vars["group_label"]) == "")
	{
		$vars["group_label"] = BOSSSECRETARY_LABEL_DEFAULT . $vars["group_number"];
	}

	$vars["bosses_extensions"] = "";
	$vars["secretaries_extensions"] = "";
	$vars["chiefs_extensions"] = "";
	$s = array();
	$b = array();
	$c = array();
	foreach ($records as $record)
	{

		if (!empty($record["boss_extension"]))
		{
			array_push($b, $record["boss_extension"]);
		}
		if (!empty($record["secretary_extension"]))
		{
			array_push($s, $record["secretary_extension"]);
		}
		if (!empty($record["chief_extension"]))
		{
			array_push($c, $record["chief_extension"]);
		}
	}
	$vars["bosses"] = array_unique($b);
	$vars["secretaries"] = array_unique($s);
	$vars["chiefs"] = array_unique($c);
	return $vars;
}

/********************************************************
*														*
* 					GUI FUNCTIONS						*
*														*
********************************************************/


function bosssecretary_content($title, $content, $messages){
	echo <<<OUTPUT


<div class="content">
	<h2>$title</h2>
<script>
// AJAX to the SubCategory DropDown
function getExtensions(extensions)
{
	
	var url = "config.php?sid=" + Math.random() + "&display=bosssecretary&extensions=" + extensions + "&ajax=true";
	xmlHttp=GetXmlHttpObject(setExtensions);
	xmlHttp.open("GET", url , true);
	xmlHttp.send(null);
	document.getElementById('divExtensions').innerHTML = "Searching";
	return true;
}

function setExtensions()
{
	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
	{
		var datos;
		datos = (xmlHttp.responseText);
		document.getElementById('divExtensions').innerHTML = datos;
	}
}


function GetXmlHttpObject(handler){
	var objXmlHttp=null
	if (navigator.userAgent.indexOf("Opera")>=0){
		alert("This doesn't work in Opera")
		return
	}
	if (navigator.userAgent.indexOf("MSIE")>=0){
		var strName="Msxml2.XMLHTTP"
		if (navigator.appVersion.indexOf("MSIE 5.5")>=0){
			strName="Microsoft.XMLHTTP"
		}
		try{
			objXmlHttp=new ActiveXObject(strName)
			objXmlHttp.onreadystatechange=handler
			return objXmlHttp
		}catch(e){
			alert("Error. Scripting for ActiveX might be disabled")
			return
		}
	}
	if (navigator.userAgent.indexOf("Mozilla")>=0){
		objXmlHttp=new XMLHttpRequest()
		objXmlHttp.onload=handler
		objXmlHttp.onerror=handler
		return objXmlHttp
	}
}

</script>
<form method="post" name=searchbosssecretary action="config.php?display=bosssecretary" onsubmit="getExtensions(document.getElementById('extensions').value); return false;">
<table>
			<tr>
				<td colspan="2"><h5>Buscar grupo</h5> <hr /> </td>
			</tr>			
			<tr>
				<td colspan="2"><label>Extension:</label> <input type="text" id="extensions" name= "extension" value=""/> <input type="button" name="submitSearch" onclick="getExtensions(document.getElementById('extensions').value);" value="Search" /></td>				
			</tr>

			<tr>
				<td colspan="2"><div id="divExtensions"></div></td>
			</tr>
			<tr>
				<td colspan="2"><hr /></td>
			</tr>			
</table>
</form>

	$messages
	$content

</div>

OUTPUT;

}


function bosssecretary_get_form_add( array $params)
{
	$vars["form_title"] = "Add Group";
	$vars["form_url"] = "config.php?display=bosssecretary&bsgroupdisplay=".BOSSSECRETARY_PARAM_PREFIX. "add";
	$vars["bosses_extensions"] 		=	(isset($params["bosses"])) ? implode($params["bosses"], "\n") : '';
	$vars["secretaries_extensions"]	=	(isset($params["secretaries"])) ? implode($params["secretaries"], "\n") : '';
	$vars["chiefs_extensions"]	=	(isset($params["chiefs"])) ? implode($params["chiefs"], "\n") : '';
	$vars["group_label"] = (isset($params["group_label"])) ? $params["group_label"] : '';
	$vars["delete_button"] = "";
	$vars["action"] = "Add";
	$vars["message_details"] = $params["message_details"];
	$vars["message_title"] = $params["message_title"];
	return bosssecretary_get_form($vars);
}


function bosssecretary_get_form_edit( array $params)
{
	$vars["form_title"] = "Edit Group";
	$vars["form_url"] = "config.php?display=bosssecretary&bsgroupdisplay=".BOSSSECRETARY_PARAM_PREFIX. $params["group_number"];
	$vars["bosses_extensions"] = (isset($params["bosses"])) ? implode($params["bosses"], "\n") : '';
	$vars["secretaries_extensions"] = (isset($params["secretaries"])) ? implode($params["secretaries"], "\n") : '';
	$vars["chiefs_extensions"]	=	(isset($params["chiefs"])) ? implode($params["chiefs"], "\n") : '';
	$vars["group_number"] = $params["group_number"];
	$vars["group_label"] = $params["group_label"];
	$vars["delete_button"] = bosssecretary_get_delete_button();
	$vars["action"] = "Edit";
	$vars["message_details"] = $params["message_details"];
	$vars["message_title"] = $params["message_title"];
	$vars["delete_question"] = "Do you really to want delete " . $vars["group_number"] . " (" .$vars["group_label"] . ") group?";
	$vars["delete_url"] = "config.php?display=bosssecretary&bsgroupdelete=".BOSSSECRETARY_PARAM_PREFIX. $params["group_number"];
	return bosssecretary_get_form($vars);
}


function bosssecretary_get_form ( array $vars)
{
	$sForm = file_get_contents(dirname(__FILE__). "/form_template.tpl");


	$vars["messages"] = "";
	if (!empty($vars["message_details"]))
	{
		$vars["messages"] = "<h5>".$vars["message_title"] . "</h5>";
		$vars["messages"] .= "<ul>";
		foreach ($vars["message_details"] as $details)
		{
			$vars["messages"] .= "<li>$details</li>";
		}
		$vars["messages"] .= "</ul>";
		unset($vars["message_details"]);
		unset($vars["message_title"]);
	}


	foreach ($vars as $var => $value)
	{
		$sForm = str_replace("{".$var. "}", $value, $sForm);
	}
	return $sForm;
}




function bosssecretary_get_delete_button()
{
	$sForm = file_get_contents(dirname(__FILE__). "/delete_button.tpl");
	return str_replace("{delete_button_label}", "Delete Group", $sForm);
}



function bosssecretary_show_nav_users($links){
	echo <<<OUTPUT

<div class="rnav">
	<ul>

OUTPUT;
	foreach ($links as $link){
		$url  = $link['url'];
		$text = $link['text'];

		echo <<<OUTPUT
		<li><a href="{$url}">{$text}</a></li>
	
OUTPUT;
}
echo <<<OUTPUT
	</ul>
</div>

OUTPUT;
}

?>
