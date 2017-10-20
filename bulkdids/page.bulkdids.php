<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//  Copyright 2006 Seth Sargent, Steven Ward
//  Portions Copyright 2009, 2011 Mikael Carlsson, mickecamino@gmail.com
//	Copyright 2013 Schmooze Com Inc.
//
include('bulkdids.inc.php');

set_time_limit(3000);

function bulkdids_fatal($text)  {
	$clean = str_replace("</script>","",str_replace("<script>javascript:alert('","",$text));
	return "\t".$clean."\n";
}

// $change is used as a flag whether or not a reload is needed. If no changes
// are made, no reload will be prompted.
$change = false;
$output = "";

if ($_REQUEST["csv_type"] == "output") {
	bulkdids_exportdids_all();
} elseif ($_REQUEST["csv_type"] == "input") {

 if (!$_SESSION["AMP_user"]->checkSection("did")) {
  $output = "<h3>Access denied due to Administrator restrictions</h3>";
  } else {

    $aFields = array (
      "action" => array(false, -1),
      "DID" => array(false, -1),
      "description" => array(false, -1),
      "destination" => array(false, -1),
      "cidnum" => array(false, -1),
      "pricid" => array(false, -1),
      "alertinfo" => array(false, -1),
      "grppre" => array(false, -1),
      "mohclass" => array(false, -1),
      "ringing" => array(false, -1),
      "delay_answer" => array(false, -1),
      "privacyman" => array(false, -1),
      "pmmaxretries" => array(false, -1),
      "pmminlength" => array(false, -1),
      "cidlookup" => array(false, -1),
      "langcode" => array(false, 1),
      "faxdetect" => array(false, -1),
      "faxdetectiontype" => array(false, -1),
      "faxdetectiontime" => array(false, -1),
      "faxdestination" => array(false, -1));

      $fh = fopen($_FILES["csvFile"]["tmp_name"], "r");
      if ($fh == NULL) {
	      $file_ok = FALSE;
      } else {
	      $file_ok = TRUE;
      }

      $k = 0;
      $i = 0;

      while ($file_ok && (($aInfo = fgetcsv($fh, 2000, ",", "\"")) !== FALSE)) {
              $k++;
	      if (empty($aInfo[0])) {
		      continue;
	      }

	      // If this is the first row then we need to check each field listed (these are the headings)
	      if ($i==0) {
		      for ($j=0; $j<count($aInfo); $j++) {
			      $aKeys = array_keys($aFields);
			      foreach ($aKeys as $sKey) {
				      if ($aInfo[$j] == $sKey) {
					      $aFields[$sKey][0] = true;
					      $aFields[$sKey][1] = $j;
				      }
			      }
		      }
		      $i++;
		      $output .= "<BR><BR>Row $k: Headers parsed. <BR>";
		      continue;
	      }

	      if ($aFields["action"][0]) {
		      $vars["action"] = trim($aInfo[$aFields["action"][1]]);
	      }

	      if ($aFields["DID"][0]) {
		      $vars["extension"]  = trim($aInfo[$aFields["DID"][1]]);
	      }

	      if ($aFields["description"][0]) {
		      $vars["description"] = trim($aInfo[$aFields["description"][1]]);
	      }

	      if ($aFields["destination"][0]) {
		      $vars["destination"] = trim($aInfo[$aFields["destination"][1]]);
	      }

	      if ($aFields["cidnum"][0]) {
		      $vars["cidnum"] = trim($aInfo[$aFields["cidnum"][1]]);
	      }

	      if ($aFields["pricid"][0]) {
		      $vars["pricid"] = trim($aInfo[$aFields["pricid"][1]]);
	      }

	      if ($aFields["alertinfo"][0]) {
		      $vars["alertinfo"] = trim($aInfo[$aFields["alertinfo"][1]]);
	      }

	      if ($aFields["grppre"][0]) {
		      $vars["grppre"] = trim($aInfo[$aFields["grppre"][1]]);
	      }

      	if ($aFields["mohclass"][0] && $aInfo[$aFields["mohclass"][1]]) {
		      $vars["mohclass"] = trim($aInfo[$aFields["mohclass"][1]]);
	      }
	      else  {
		      $vars["mohclass"] = "default";
	      }
	      if ($aFields["ringing"][0]) {
		      $vars["ringing"] = trim($aInfo[$aFields["ringing"][1]]);
	      }

	      if ($aFields["delay_answer"][0]) {
		      $vars["delay_answer"] = trim($aInfo[$aFields["delay_answer"][1]]);
	      }
// If privacyman is enabled then check pmmaxretries and pmminlength
	      if ($aFields["privacyman"][0]) {
		      $vars["privacyman"] = trim($aInfo[$aFields["privacyman"][1]]);

		      if ($aFields["pmmaxretries"][0]) {
		        $vars["pmmaxretries"] = trim($aInfo[$aFields["pmmaxretries"][1]]);
		        if($vars["pmmaxretries"] > "10") $vars["pmmaxretries"] = "10";
	        }

	        if ($aFields["pmminlength"][0]) {
		        $vars["pmminlength"] = trim($aInfo[$aFields["pmminlength"][1]]);
		        if($vars["pmminlength"] > "15") $vars["pmminlength"] = "15";
	        }
	      }

	      if ($aFields["cidlookup"][0]) {
	      	$vars["cidlookup"] = trim($aInfo[$aFields["cidlookup"][1]]);
	      	}

	      if ($aFields["langcode"][0]) {
	      	$vars["langcode"] = trim($aInfo[$aFields["langcode"][1]]);
	      	}

		if ($aFields["faxdetect"][0]) {
      			$vars["faxdetect"] = trim(strtolower($aInfo[$aFields["faxdetect"][1]]));
		}
		if ($aFields["faxdetectiontype"][0]) {
                        $vars["faxdetectiontype"] = trim(strtolower($aInfo[$aFields["faxdetectiontype"][1]]));
                }
		if ($aFields["faxdetectiontime"][0]) {
			$vars["faxdetectiontime"] = trim($aInfo[$aFields["faxdetectiontime"][1]]);
			if ($vars["faxdetectiontime"] < 2) {
				$vars["faxdetectiontime"] = 2;
			} elseif ($vars["faxdetectiontime"] > 10) {
				$vars["faxdetectiontime"] = 10;
			}
		}
		if ($aFields["faxdestination"][0]) {
                        $vars["faxdestination"] = trim($aInfo[$aFields["faxdestination"][1]]);
                }

	      $vars["faxexten"] = "default";
	      $vars["display"]	= "bulkdids";
	      $vars["type"]	= "tool";

	      $_REQUEST = $vars;

		      switch ($vars["action"]) {
		      	case "add":
				ob_start("bulkdids_fatal");
				if(!core_did_add($vars,($vars["destination"]?$vars["destination"]:false)))  {
					$output .= "ERROR: ".$vars["extension"]." ".$vars["description"].". See error above<br>";

				}
				else  {
					$output .= "Row $k: Added: " . $vars["extension"];
					$output .= "<br />";
				}
				// Add Language
				if (isset($vars["langcode"]) && $bulkdids_lang_exists == TRUE) {
					languages_incoming_update($vars["langcode"],$vars["extension"],$vars["cidnum"]);
				}
				// Add CID Lookup Source if it exists, if not, just skip adding it
				if (isset($vars["cidlookup"]) && $bulkdids_cidlookup_exists == TRUE) {
					// Is there a cidlookup defined for the supplied index?
					if (cidlookup_get($vars["cidlookup"])) {
						cidlookup_did_add($vars["cidlookup"],$vars["extension"],$vars["cidnum"]);
					} else {
						$output .= "WARNING: Row $k: " . $vars["extension"] . " CID Lookup NOT added, index ".$vars["cidlookup"]." does NOT exist<BR>";
					}
				}
				//Add inbound fax information
				if (isset($vars["faxdetect"]) && $vars["faxdetect"] == "yes" && $bulkdids_fax_exists == TRUE) {
					fax_save_incoming($vars["cidnum"],$vars["extension"],true,$vars["faxdetectiontype"],$vars["faxdetectiontime"],$vars["faxdestination"],null);
				}
				ob_end_flush();

				// begin status output for this row
				$change = true;
				break;
			case "edit":
				if (core_did_get($vars["extension"],$vars["cidnum"])) {
					core_did_del($vars["extension"],$vars["cidnum"]);
					$error = ob_start("bulkdids_fatal");
					if(!core_did_add($vars,($vars["destination"]?$vars["destination"]:false)))  {
						$output .= "ERROR: ".$vars["extension"]." ".$vars["description"].". See error above<br>";

					}
					else  {
						// Edit Language
						if (isset($vars["langcode"]) && $bulkdids_lang_exists == TRUE) {
							languages_incoming_update($vars["langcode"],$vars["extension"],$vars["cidnum"]);
						}
						// Edit CID Lookup Source if it exists, if not, just skip adding it
						if (isset($vars["cidlookup"]) && $bulkdids_cidlookup_exists == TRUE) {
							if (cidlookup_get($vars["cidlookup"])) {
								cidlookup_did_del($vars["extension"],$vars["cidnum"]);
								cidlookup_did_add($vars["cidlookup"],$vars["extension"],$vars["cidnum"]);
							} else {
								$output .= "WARNING: Row $k: " . $vars["extension"] . " CID Lookup NOT added, index ".$vars["cidlookup"]." does NOT exist<BR>";
							}
						}
						if ($bulkdids_fax_exists == TRUE) {
							fax_delete_incoming($vars["extension"]."/".$vars["cidnum"]);
							if (isset($vars["faxdetect"]) && $vars["faxdetect"] == "yes") {
		                        			fax_save_incoming($vars["cidnum"],$vars["extension"],true,$vars["faxdetectiontype"],$vars["faxdetectiontime"],$vars["faxdestination"],null);
                                			}
						}
						$output .= "Row $k: Edited: " . $vars["extension"] . "<BR>";
					}

					ob_end_flush();
					$change = true;
				}
				break;
			case "del":
				if (core_did_get($vars["extension"],$vars["cidnum"])) {
					core_did_del($vars["extension"],$vars["cidnum"]);
					$change = true;
				}
				// Delete Language
				if (isset($vars["langcode"]) && $bulkdids_lang_exists == TRUE) {
					languages_incoming_delete($vars["extension"],$vars["cidnum"]);
				}
				// Delete CID Lookup Source
				if (isset($vars["cidlookup"]) && $bulkdids_cidlookup_exists == TRUE) {
					cidlookup_did_del($vars["extension"],$vars["cidnum"]);
				}
				if ($bulkdids_fax_exists == TRUE) {
					fax_delete_incoming($vars["extension"]."/".$vars["cidnum"]);
				}
				$output .= "Row $k: Deleted: " . $vars["extension"] . "<BR>";
				break;
			default:
				$output .= "Row $k: Unrecognized action: the only actions recognized are add, edit, del.\n";
				break;
		      }

		      if ($change) {
			  needreload();
		      }
      } // while loop
     }
     print $output;

} else
{
	$table_output = "";
	$table_rows = bulkdids_generate_table_rows();
	if ($table_rows === NULL) {
		$table_output = "Table unavailable";
	} else {
		$table_output .=	"<table cellspacing='0' cellpadding='4' rules='rows'>";
		$table_output .=	"<tr valign='top'>
						<th align='left' valign='top'>#</th>
						<th align='left' valign='top'>Field</th>
						<th align='left' valign='top'>Default</th>
						<th align='left' valign='top'>Allowed</th>
						<th align='left' valign='top'>Field Details</th>
						<th align='left' valign='top'>Description</th>
					</tr>";
		$i = 1;
		foreach ($table_rows as $row) {
			$table_output .= "<tr>";
			$table_output .= "<td valign='top'>" . $i . "</td>";
			$i++;
			foreach ($row as $col) {
				$table_output .= "<td valign='top'>" . $col . "</td>";
			}
			$table_output .= "</tr>";
		}
		$table_output .= "</table>";
	}

?>
<h1>Bulk DIDs</h1>

<h2>Manage DIDs in bulk using CSV files.</h2>

<p>
Start by downloading the
<a href="modules/bulkdids/template.csv">Template CSV file</a>
(right-click > save as) or clicking the Export DIDs button.
</p>
<p>
Modify the CSV file to add, edit, or delete DIDs as desired. Then load
the CSV file. After the CSV file is processed, the action taken for each row
will be displayed.
</p>
<p>

<form action="<?php $_SERVER["PHP_SELF"] ?>" name="uploadcsv" method="post" enctype="multipart/form-data">
<input id="csv_type" name="csv_type" type="hidden" value="none" />
<input type="submit" onclick="document.getElementById('csv_type').value='output';" value="Export DIDs" />
&nbsp;&nbsp;CSV File to Load: <input name="csvFile" type="file" />
<input type="submit" onclick="document.getElementById('csv_type').value='input';"  value="Load File" />
<hr />
</form>
<hr />
<h3>Bulk DIDs CSV File Columns</h3>
<p>
The table below explains each column in the CSV file. You can change the column
order of the CSV file as you like, however, the column names must be preserved.
</p>
<?php
	print $table_output;
}
?>
