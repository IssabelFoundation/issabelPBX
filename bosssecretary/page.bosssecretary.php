<?php /* $Id: page.bosssecretary.php   $ */
//Copyright (C) 2008 TI Soluciones (msalazar at solucionesit dot com dot ve) and Ing. David Hrbaty
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.


if (isset($_GET["extensions"], $_GET["ajax"]))
{
	$result = bosssecretary_search($_GET["extensions"]);
	if (isset($result))
	{
		if (!empty($result))
		{
			foreach ($result as $extension)
			{
				echo $extension["extension"] . " extension is " . $extension["type"] . " at '" . $extension["label"] ."' group <br />";
			}

		}
		else
		{
			echo "Not matches";
		}
	}
	else
	{
		echo "Critery invalid!";
	}
	exit(1);
}

	$dispnum = 'bosssecretary'; //used for switch on config.php
	$extensionsCleaned = array();
	$title = _("Boss Secretary");
	$messages	= "";
	$params = array();
	
	
	if (isset($_POST["cleanAdd"]) || (isset($_POST["cleanEdit"])) || isset($_POST["submitAdd"]) || isset($_POST["submitEdit"]))
	{
		if (isset($_POST["submitAdd"])) $_POST["group_number"] = null;
		$extensionsCleaned = bosssecretary_clean_remove_duplicates($_POST["bosses_extensions"], $_POST["secretaries_extensions"], $_POST["group_number"]);
	}

		
	if (isset($_POST["submitAdd"])) 
	{
		$group_number = bosssecretary_get_group_number_free();
		$chiefs = bosssecretary_str_extensions_to_array($_POST["chiefs_extensions"]);
		$errors = bosssecretary_group_add($group_number, $_POST["group_label"], $extensionsCleaned["bosses"],$extensionsCleaned["secretaries"], $chiefs);
		$params["message_title"] = "";
		$params["message_details"] = array();
		if (empty($errors))
		{
			$_GET["bsgroupdisplay"] = "";
			$params["message_title"] = "Group Added";
			$params["message_details"] = array("Group was added successfully");
			needreload();
		}
		else
		{
			//$params["group_number"] = $_POST["group_number"];
			$params["group_label"] = $_POST["group_label"];
			$params["chiefs"] = $_POST["chiefs"];
			$params["bosses"] = $extensionsCleaned["bosses"];
			$params["secretaries"] = $extensionsCleaned["secretaries"];
			$params["message_title"] = "Errors were encountered, details";
			$params["message_details"] = $errors;	
		}
		$content = bosssecretary_get_form_add( $params);
	}
	elseif( isset($_POST["cleanAdd"]))
	{
		$params = $_POST;
		$params["bosses"] = $extensionsCleaned["bosses"];
		$params["secretaries"] = $extensionsCleaned["secretaries"];
		$content = bosssecretary_get_form_add( $params);
	}
	elseif( isset($_POST["cleanEdit"]))
	{
		$params = $_POST;
		$params["bosses"] = $extensionsCleaned["bosses"];
		$params["secretaries"] = $extensionsCleaned["secretaries"];
		$content = bosssecretary_get_form_edit( $params);
	}

	elseif(isset($_POST["submitEdit"]))
	{

		$chiefs = bosssecretary_str_extensions_to_array($_POST["chiefs_extensions"]);
		$errors = bosssecretary_group_edit($_POST["group_number"], $_POST["group_label"], $extensionsCleaned["bosses"],$extensionsCleaned["secretaries"], $chiefs);
		if (empty($errors))
		{
			$params["message_title"] = "Group Edited";
			$params["message_details"] = array("Group " . $_POST["group_number"] . " (" . $_POST["group_label"] . ") was edited successfully");
			needreload();
		}
		else
		{
			$params["message_title"] = "Errors were encountered, details";
			$params["message_details"] = $errors;
		}
		$params["group_number"]	= $_POST["group_number"];
		$params["group_label"] 	= $_POST["group_label"];
		$params["chiefs"] 	= $chiefs;
		$params["bosses"] 	= $extensionsCleaned["bosses"];
		$params["secretaries"] 	= $extensionsCleaned["secretaries"];
		$content = bosssecretary_get_form_edit( $params);
	}
	elseif (isset($_GET["bsgroupdisplay"]))
  	{
		$group =  bosssecretary_extract_group_from_request($_GET["bsgroupdisplay"]);
		if ($group == "add")
		{
			$content = bosssecretary_get_form_add($params);
		}
		else
		{
			$params = bosssecretary_set_params_to_edit(bosssecretary_get_data_of_group($group));
			$content = bosssecretary_get_form_edit($params);
		}
	}
	elseif (isset($_GET["bsgroupdelete"]))
	{
		$group =  bosssecretary_extract_group_from_request($_GET["bsgroupdelete"]);
		if (bosssecretary_group_exists($group))
		{
			if (bosssecretary_group_delete($group))
			{
				$content = "<br /> Group was deleted successfully <br /> <br /> <br /> <h3>Choose a group or add one:</h3> ";
				needreload();
			}
			else
			{
				$content = "<br /> Group was not deleted, please try it again <br /> <br /> <br /><h3>Choose a group or add one:</h3>";
			}
		}
	}	
	else
	{
		$content = "<br /> <br /> <br /> <h3>Choose a group or add one:</h3>";
	}
	

	$groups = bosssecretary_get_groups();
	$linksGroups = bosssecretary_create_nav_groups_links($groups, $dispnum);

	
	bosssecretary_show_nav_users($linksGroups);
	bosssecretary_content($title, $content, $messages);
?>
