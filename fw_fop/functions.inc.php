<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
// TODO: ugliness!
// This whole file is an "ugly" portr of retrieve_op_conf_from_mysql.php, if it persists
// it would be nice to write properly.
//
// Use hookGet_config to get this to exectue as late as possible for now minimizing any relics
// with changes not yet done.
//
function fw_fop_hookGet_config($engine) {
    $modulename = 'fw_fop';
    
    // This generates the dialplan
    global $ext;  
    global $amp_conf;  
    global $version;
    global $nt;
    switch($engine) {
        case "asterisk":
            $result = fw_fop_retrieve_op_conf_from_mysql();

            // TODO: This used to be done in the POST_RELOAD space after reload was successful and there was likely
            //       a good reason for it as in config changes where made to Asterisk and are not visible thus can line
            //       up with the configuration file. Moving it here may break things but we'll try for now and
            //       see what happens.
            //
            unset($output);
            exec('killall -HUP op_server.pl 2>&1', $output, $exit_val);
            if ($result) {
                if ($exit_val != 0) {
                    $desc = _('Could not reload the FOP operator panel server using killall -HUP op_server.pl. Configuration changes may not be reflected in the panel display. If the FOP module was just installed you may have to retart Asterisk with the "amportal restart" command for the FOP server to run.');
                    $nt->add_error('issabelpbx','reload_fop', _('Could not reload FOP server'), $desc);
                    // send the error output to dbug log if enabled.
                } else {
                    $nt->delete('fw_fop','reload_fop');
                }
            } else {
                $nt->delete('fw_fop','reload_fop'); // clean this up either way
                $desc = _('An error occured trying to configure FOP. There may be a conflict with another module or there may be permission problems. Check the IssabelPBX log for more details.');
                $nt->add_error('fw_fop','configuration', _('FOP Could Not Be Configured'), $desc, "", false, true);
            }
        break;
    }
}


/**
 * function fw_fop_retrieve_op_conf_from_mysql
 * straight port from retrieve_op_conf_from_mysql.php
 *
 * TODO: this should be updated some day so that it is
 * properly written vs. a port of the standalone code
 *
 */
function fw_fop_retrieve_op_conf_from_mysql() {

// Retrieves the sip user/peer entries from the database for the flash operator panel

//################# BEGIN OF CONFIGURATION ####################

global $zapataconfdir;
global $amp_conf;
global $db;
global $chan_dahdi;

// If another module such as isymphony has been loaded and "taken control" then this will fail
// so detect here, log the error and don't bother!
//
$fopwebroot = $amp_conf['AMPWEBROOT'] . '/admin/modules/fw_fop';
if ($amp_conf['FOPWEBROOT'] != $fopwebroot) {
    issabelpbx_log(IPBX_LOG_CRITICAL, _("fw_fop is NOT confgured as your Operator Panel, attempting to disable, FOPWEBROOT: " . $amp_conf['FOPWEBROOT'] . "."));
    out(sprintf(_("fw_fop is NOT confgured as your Operator Panel, FOPWEBROOT: %s."), $amp_conf['FOPWEBROOT']));
    outn(_("disabling fw_fop.."));
    if (is_array($errors = module_disable('fw_fop'))) {
        out(_("The following error(s) occured:"));
        foreach ($errors as $error) {
            out(" - $error");
        }
    } else {
        out(_("disabled"));
    }
    return false;
}

$zapataconfdir = $amp_conf['ASTETCDIR']."/";
$zapataconf = "zapata.conf";
$dahdiconf = "chan_dahdi.conf";
if ($chan_dahdi) {
  $zapataconf = $zapataconfdir . $dahdiconf;
} else {
  $zapataconf = $zapataconfdir . $zapataconf;
}

//###### LAYOUT INFO #########

// NOTE - These values may be overwritten by values in a table in the issabelpbx database named "panel"
// NOTE = Currently only one layout is supported. This layout is applied to all panel contexts (amp users).
// TODO - add support in this code and in "panel" database for a layout for each panel context (amp user)

// structure is - ID, Legend, startpos, stoppos, color1, color2
$rectangle1 = array("trunk","Trunks", 53, 80, "10ff10", "009900");
$rectangle2 = array("extension","Extensions", 1, 40, "1010ff", "99cccc");
$rectangle3 = array("parking","Parking lots", 49, 72, "ffff10", "cc9933");
$rectangle4 = array("conference","Conferences", 45, 68, "006666", "00a010");
$rectangle5 = array("queue","Queues", 41, 64, "ff1010", "a01000");
$rectangles = array($rectangle1,$rectangle2,$rectangle3,$rectangle4,$rectangle5);

$rectmarginx = 1;
$rectmarginy = 1;
$legendoffsetx = 3;
$legendoffsety = 1;

// $layoutbuttonsonly = 0 : allow display of buttons even if no corresponding layout info
// $layoutbuttonsonly = 1 : suppress display of buttons if no corresponding layout info
$layoutbuttonsonly = 1;

//###### BUTTON INFO #########
$buttonsizex = 246; # 1+244+1 from information in op_style.cfg
$buttonsizey = 28; # 1+26+1 from information in op_style.cfg
$numbuttonsx = 4;
$numbuttonsy = 20;
$buttonsoriginx = -1;
$buttonsoriginy = 32;


//###### STYLE INFO #########

// NOTE - These values may be overwritten by the syleinfo function with values generated from the layout info

$extenpos="2-40";
$trunkpos="53-60,72-80";
$parkingpos="50-51,69-71";
$confepos="46-48,65-68";
$queuepos="42-44,61-64";



// Remove or add Zap trunks as needed
// Note: ZAP/* will match any ZAP channel that *is not referenced* in another button (ie: extensions)
$zaplines=array(); # zap channel, description
#array_push($zaplines,array( "Zap/*","PSTN" ));
#array_push($zaplines,array( "Zap/1","Zap 1" ));
#array_push($zaplines,array( "Zap/2","Zap 2" ));
#array_push($zaplines,array( "Zap/3","Zap 3" ));
#array_push($zaplines,array( "Zap/4","Zap 4" ));

if (file_exists($zapataconf)) {
    fw_fop_parse_zapata($zaplines,$zapataconf);
}
// Now no need to parse other files as include declarations are followed
//if (file_exists($zapataautoconf)) {
//    fw_fop_parse_zapata($zaplines,$zapataautoconf);
//}



# Conference Rooms not yet implemented in AMP config
$conferences=array();   #### ext#, description
#array_push($conferences,array( "810","Conf.10" ));
#array_push($conferences,array( "811","Conf.11" ));

# cool hack by Julien BLACHE <jblache@debian.org>
# WARNING: this file will be substituted by the output of this program
$op_conf = $amp_conf["FOPWEBROOT"]."/op_buttons_additional.cfg";
# username to connect to the database
$username = $amp_conf["AMPDBUSER"];
# password to connect to the database
$password = $amp_conf["AMPDBPASS"];
# the name of the box the MySQL database is running on
$hostname = $amp_conf["AMPDBHOST"];
# the name of the database our tables are kept
$database = $amp_conf["AMPDBNAME"];
#sort option: extension or lastname
$sortoption = $amp_conf["FOPSORT"];

################### END OF CONFIGURATION #######################

$warning_banner =
"; do not edit this file, this is an auto-generated file by issabelpbx
; all modifications must be done from the web gui
";

# Get layout-info from a "panel" table in the issabelpbx database
if (fw_fop_table_exists($db,"panel")) {

    $statement = "SELECT id, legend, startpos, stoppos, color1, color2 from panel";
    $results = $db->getAll($statement);
    if(DB::IsError($results)) {
       die($results->getMessage());
    }
    if (count($results) < 1) {
        print "Notice: no panel defined\n";
    }
    $rectangles = $results;
}

// pass layout info to function explicitly rather than via globals
$layoutinfo = array('rectangles'=>$rectangles,'numbuttonsx'=>$numbuttonsx,'numbuttonsy'=>$numbuttonsy);

# Automated generation of style-info from layout-info
$autoextenpos=fw_fop_get_style_info("extension",$layoutinfo);
$autotrunkpos=fw_fop_get_style_info("trunk",$layoutinfo);
$autoparkingpos=fw_fop_get_style_info("parking",$layoutinfo);
$autoconfepos=fw_fop_get_style_info("conference",$layoutinfo);
$autoqueuepos=fw_fop_get_style_info("queue",$layoutinfo);

if ($layoutbuttonsonly == 1) {$extenpos = ""; $trunkpos = ""; $parkingpos = ""; $confepos = ""; $queuepos = "";}

if (isset($autoextenpos)) {$extenpos = $autoextenpos;}
if (isset($autotrunkpos)) {$trunkpos = $autotrunkpos;}
if (isset($autoparkingpos)) {$parkingpos = $autoparkingpos;}
if (isset($autoconfepos)) {$confepos = $autoconfepos;}
if (isset($autoqueuepos)) {$queuepos = $autoqueuepos;}


$fhandle = fopen($op_conf,"w" );
if ($fhandle === false) {
    out(sprintf(_("fw_fop cannot create/overwrite config file: %s"), $op_conf));
    issabelpbx_log(IPBX_LOG_CRITICAL, _("fw_fop failed to create/overwrite config file $op_conf."));
    return false;
}
fwrite($fhandle, $warning_banner);

#First, populate extensions

$extensionlist=array();

if (fw_fop_table_exists($db,"devices")) {
    $statement = "SELECT description,id,dial,tech from devices";
    $results = $db->getAll($statement);
    if(DB::IsError($results)) {
       die($results->getMessage());
    }
    if (count($results) < 1) {
        print "Notice: no Devices defined\n";
    }
    $extensionlist = $results;
}
else { print "Table does not exist: devices\n"; }

# sort the extensions
foreach ($extensionlist as $key=>$extension) {
    $temparray = explode(" ",$extension[0]);
    $lastname[$key] = end($temparray);
    $extnum[$key] = $extension[1];
}

if  (isset($sortoption) && ($sortoption == "lastname")) {
    array_multisort($lastname,$extensionlist);
} else {
    array_multisort($extnum,SORT_STRING,$extensionlist);
}

#Next, populate queues
$queues=array();
if (fw_fop_table_exists($db,"queues_config")) {
    $statement = "SELECT extension,descr from queues_config order by extension";
    $results = $db->getAll($statement);
    if(DB::IsError($results)) {
       die($results->getMessage());
    }
    if (count($results) < 1) {
        print "Notice: no Queues defined\n";
    }
    $queues = $results;
}
else { print "Table does not exist: queues_config\n"; }


## SME server chnges

#Next, populate conferences
$conferences=array();
if(fw_fop_table_exists($db,"meetme")) {
    $statement = "SELECT exten,description FROM meetme ORDER BY exten";
    $results = $db->getAll($statement);
    if(DB::IsError($results)) {
       die($results->getMessage());
    }
    if (count($results) < 1) {
        print "Notice: no Conferences defined\n";
    }
    $conferences = $results;
}
else { print "Table does not exist: meetme\n"; }


#Next, populate parkings
$parkings=array();
if(fw_fop_table_exists($db,"parkinglot")) {
    $statement = "SELECT keyword,data FROM parkinglot";
    $results = $db->getAll($statement);
    if(DB::IsError($results)) {
       die($results->getMessage());
    }
    if (count($results) < 1) {
        print "Notice: no Parking Lots defined\n";
    }
    $parkings = $results;
}
else { print "Table does not exist: parkinglot\n"; }

## End of changes
#Next, populate trunks (sip and iax)
$trunklist=array();
$tables = array("sip","iax");
foreach ($tables as $table) {
    if (fw_fop_table_exists($db,$table)) {
        $statement = "SELECT data,id,'$table' from $table where keyword='account' and flags <> 1 and id LIKE 'tr-%' group by data order by id";
        $results = $db->getAll($statement);
        if(DB::IsError($results)) {
           die($results->getMessage());
        }
        if (count($results) < 1) {
            print "Notice: no $table trunks defined\n";
        }
        $trunklist = array_merge($trunklist,$results);
}
else { print "Table does not exist: $table \n"; }
}

#Determine AMP Users
$ampusers=array();
if (fw_fop_table_exists($db,"ampusers")) {
    $statement = 'SELECT deptname,extension_low,extension_high from ampusers WHERE NOT extension_low = "" AND NOT extension_high = ""';
        $results = $db->getAll($statement);
        if(DB::IsError($results)) {
           die($results->getMessage());
        }
        if (count($results) < 1) {
            print "Notice: no AMP Users defined\n";
        }
        else {
            $ampusers = $results;
        }
}
else { print "Table does not exist: ampusers\n"; }
array_push($ampusers,array("default","0","0")); //add a default panelcontext that can see all extensions

#Write a separate panel context from each AMP User department
foreach ($ampusers as $pcontext) {
    $exten_low = $pcontext[1];
    $exten_high = $pcontext[2];
    $panelcontext = $pcontext[0];
    if ($panelcontext == "") { $panelcontext = $exten_low."to".$exten_high; }

fwrite($fhandle, "\n\n; Panel Context: " . $panelcontext . "\n");

    # WRITE EXTENSIONS

    $btn=0;
    if ($exten_low != 0 && $exten_high != 0) {  #display only allowed range of extensions for panel_contexts
        $extensionrange = array();
        foreach ($extensionlist as $value) {
            if (!is_numeric($value[1])) {array_push($extensionrange,$value);}
            if (($value[1] >= $exten_low) && ($value[1] <= $exten_high))  {array_push($extensionrange,$value);}
        }
    } else {
        $extensionrange = $extensionlist;
    }

    foreach ( $extensionrange as $row ) {
        $description = $row[0];
        $id = $row[1];
        $dial = $row[2];


        # Support for real mailbox settings -
        $tech = $row[3];
        # some sensible defaults for voicemail ext and context
        $vmext = $row[1];
        $vmcontext = "default";
        # the device tech table should also have a dial context - if not assume from-internal
        $context = "from-internal";
        # database table name for iax2 is just iax but sip and zap are ok
        if ($tech == "iax2") {$tech = "iax";}
        # get mailbox setting from relevant tech table and split into ext and content
        if (fw_fop_table_exists($db,$tech)) {
            $statement = "SELECT data from $tech WHERE id = '$id' AND keyword = 'mailbox' ";
            $results = $db->getAll($statement);
            if(DB::IsError($results)) {
               die($results->getMessage());
            }
            if (count($results) < 1) {
                print "Notice: no Mailboxes defined\n";
            }
            else {
            $mailbox = $results[0][0];
            $values = @explode('@', $mailbox,2);
            if (strlen($values[0]) > 0) {$vmext = $values[0];}
            if (strlen($values[1]) > 0) {$vmcontext = $values[1];}
            }
            #while in this table lets get the dial context as well
            $statement = "SELECT data from $tech WHERE id = '$id' AND keyword = 'context' ";
            $results = $db->getAll($statement);
            if(DB::IsError($results)) {
               die($results->getMessage());
            }
            if (count($results) < 1) {
                print "Notice: no Context defined\n";
            }
            else {
            $context = $results[0][0];
            }
        } else { print "Table does not exist: $tech\n"; }
        # - Support for real mailbox settings


        # Support for real VM_PREFIX -
        $vmprefix = "*";
        if (fw_fop_table_exists($db,"globals")) {
            $statement = "SELECT value from globals WHERE variable = 'VM_PREFIX' ";
            $results = $db->getAll($statement);
            if(DB::IsError($results)) {
               die($results->getMessage());
            }
            if (count($results) < 1) {
                print "Notice: no VM Prefix defined\n";
            }
            else {
            $vmprefix = $results[0][0];
            }
        } else { print "Table does not exist: global\n"; }
        # - Support for real VM_PREFIX

        $btn=fw_fop_get_next_btn($extenpos,$btn);
        $icon='4';
        fwrite($fhandle, "\n[$dial]\nPosition=$btn\nLabel=\"$id : $description\"\nExtension=$id\nContext=$context\nIcon=$icon\nVoicemail_Context=$vmcontext\nVoiceMailExt=$vmprefix$vmext@$context\nPanel_Context=$panelcontext\nAstdbkey=$id\n");
    }


    ### NOW WRITE TRUNKS.. WE START WITH ZAP TRUNKS DEFINED ABOVE




    $btn=0;

    foreach ($zaplines as $row) {
        $zapdef=$row[0];
        $zapdesc=$row[1];
        $icon='3';
        # zaplines and trunklist share the trunk positions so need to store previous btn on overflow from zaplines
        $previousbtn = $btn;
        $btn=fw_fop_get_next_btn($trunkpos,$btn);
        if ($btn == 0) {$btn = $previousbtn; break;}
        if ($zapdef == "Zap/*") {
            $numbuttons=$row[2]-1;
            fwrite($fhandle, "\n[$zapdef]\nLabel=\"$zapdesc\"\nExtension=-1\nIcon=$icon\nPanel_Context=$panelcontext\nPosition=".$btn);
            while($numbuttons-->0) {
                $btn=fw_fop_get_next_btn($trunkpos,$btn);
                fwrite($fhandle, ",".$btn);
            }

            fwrite($fhandle, "\n");
        } else {
            fwrite($fhandle, "\n[$zapdef]\nPosition=$btn\nLabel=\"$zapdesc\"\nExtension=-1\nIcon=$icon\nPanel_Context=$panelcontext\n");
        }
    }


    foreach ($trunklist as $row) {
        $account = $row[0];
        $id = $row[1];
        $table = $row[2];
        if ($account == "") {continue;};
        $btn=fw_fop_get_next_btn($trunkpos,$btn);
        if ($btn == 0) {break;}
        if (fw_fop_table_exists($db,$table)) {
        $statement = "SELECT keyword,data from $table where id='$id' and keyword <> 'account' and flags <> 1 order by keyword";
            $results = $db->getAll($statement);
            if(DB::IsError($results)) {
               die($results->getMessage());
            }
            if (count($results) < 1) {
                print "Notice: no Trunks defined\n";
            }
        } else { print "Table does not exist: $table \n"; }

        if ($table == "sip") {$tech="SIP";}
        if ($table == "iax") {$tech="IAX2";}
        #if ($table == "zap") {$tech="ZAP";} #no zap trunks in db

        $callerid = $account;  #default callerid to account

        foreach ($results as $drow) {
            if ( $drow[0] == "callerid" ) {
                $callerid = $drow[1];
                $fields = explode("<",$callerid);
                $callerid=$fields[1] ." ". $fields[0];
                $callerid = str_replace("\t","",$callerid);
                $callerid = str_replace("\"","",$callerid);
                $callerid = str_replace("<","",$callerid);
                $callerid = str_replace(">","",$callerid);
            }
        }
        $icon='3';
        fwrite($fhandle, "\n[$tech/$account]\nPosition=$btn\nLabel=\"$callerid\"\nExtension=-1\nIcon=$icon\nPanel_Context=$panelcontext\n");
    }


    ## SME server changes



        ### Write Parkings lots
    $btn=0;
    $parken="" ;
    $extpark ;
    $parkcontext ;
    $numberlots ;
    $maxparkingslots ;

    foreach ($parkings as $row) {
        if ($row[0] == "parkingenabled") {
            $parken = $row[1] ;
        }
        if ($row[0] == "parkext") {
            $extpark = $row[1] ;
        }
        if ($row[0] == "parkingcontext") {
            $parkcontext = $row[1] ;
        }
        if ($row[0] == "numslots") {
            $numberlots = $row[1] ;
        }
    }
    if ($parken == "s") {
        for ($i = 1 ; $i <= $numberlots ; $i++ ) {
            $btn=fw_fop_get_next_btn($parkingpos,$btn);
            if ($btn == 0) {break;}
            $parknum = $extpark + $i ;
            $icon='1';
            fwrite($fhandle, "\n[PARK$parknum]\nPosition=$btn\nLabel=\"Parked ($parknum)\"\nExtension=$parknum\nContext=$parkcontext\nIcon=$icon\nPanel_Context=$panelcontext\n");
        }
    }

    ## End of chagnes
    ### Write conferences (meetme)

    $btn=0;
    if ($exten_low != 0 && $exten_high != 0) {  #display only allowed range of extensions for panel_contexts
        $confrange = array();
        foreach ($conferences as $value) {
            if (!is_numeric($value)) {array_push($confrange,$value);}
            if (($value >= $exten_low) && ($value <= $exten_high))  {array_push($confrange,$value);}
        }
    } else {
        $confrange = $conferences;
    }
    foreach ($confrange as $row) {
        $btn=fw_fop_get_next_btn($confepos,$btn);
        if ($btn == 0) {break;}
        $confenum=$row[0];
        $confedesc=$row[1];
        $icon='6';
        fwrite($fhandle, "\n[$confenum]\nPosition=$btn\nLabel=\"$confedesc\"\nExtension=$confenum\nContext=from-internal\nIcon=$icon\nPanel_Context=$panelcontext\n");
    }

    ### Write Queues

    $btn=0;
    if ($exten_low != 0 && $exten_high != 0) {  #display only allowed range of extensions for panel_contexts
        $queuerange = array();
        foreach ($queues as $value) {
            if (!is_numeric($value)) {array_push($queuerange,$value);}
            if (($value >= $exten_low) && ($value <= $exten_high))  {array_push($queuerange,$value);}
        }
    } else {
        $queuerange = $queues;
    }
    foreach ($queuerange as $row) {
        $btn=fw_fop_get_next_btn($queuepos,$btn);
        if ($btn == 0) {break;}
        $queuename=$row[0];
        $queuedesc=$row[1];
        $icon='5';
        fwrite($fhandle, "\n[QUEUE/$queuename]\nPosition=$btn\nLabel=\"$queuedesc\"\nExtension=-1\nContext=from-internal\nIcon=$icon\nPanel_Context=$panelcontext\n");
    }

    ### Write rectangles

    foreach ($rectangles as $rect) {
        $comment = $rect[0];
        $color1 = $rect[4];
        $color2 = $rect[5];
        $start = $rect[2];
        $stop = $rect[3];

        $xposition = $buttonsoriginx + $buttonsizex * floor(($start-1)/$numbuttonsy);
        $yposition = $buttonsoriginy + $buttonsizey * (($start-1)%$numbuttonsy);
        $xsize = $buttonsizex * (1 + floor(($stop-1)/$numbuttonsy) - floor(($start-1)/$numbuttonsy));
        $ysize = $buttonsizey * (1 + (($stop-1)%$numbuttonsy) - (($start-1)%$numbuttonsy));

        if (($xsize <= 0) || ($ysize <= 0)) {continue;}

        $xposition += $rectmarginx;
        $yposition += $rectmarginy;
        $xsize -= 2 * $rectmarginx;
        $ysize -= 2 * $rectmarginy;

        fwrite($fhandle, "\n; $comment\n[rectangle]\nx=$xposition\ny=$yposition\nwidth=$xsize\nheight=$ysize\nline_width=0\nline_color=$color1\nfade_color1=$color1\nfade_color2=$color2\nrnd_border=2\nalpha=20\nlayer=bottom\nPanel_Context=$panelcontext\n");
    }

    ### Write legends

    foreach ($rectangles as $legend) {
        $text = $legend[1];
        $start = $legend[2];
        $stop = $legend[3];

        $xposition = $buttonsoriginx + $buttonsizex * floor(($start-1)/$numbuttonsy);
        $yposition = $buttonsoriginy + $buttonsizey * (($start-1)%$numbuttonsy);
        $xsize = $buttonsizex * (1 + floor(($stop-1)/$numbuttonsy) - floor(($start-1)/$numbuttonsy));
        $ysize = $buttonsizey * (1 + (($stop-1)%$numbuttonsy) - (($start-1)%$numbuttonsy));

        if (($xsize <= 0) || ($ysize <= 0)) {continue;}

        $xposition += $legendoffsetx;
        $yposition += $legendoffsety;

        fwrite($fhandle, "\n[LEGEND]\nx=$xposition\ny=$yposition\ntext=$text\nfont_size=18\nfont_family=Arial\nuse_embed_fonts=1\nPanel_Context=$panelcontext\n");
    }

}
return true;

} // function fw_fop_retrieve_op_conf_from_mysql()


function fw_fop_get_next_btn($data,$last) {
    $rangelist=explode(",",$data);
    foreach ($rangelist as $range) {
        $rangeval=explode("-",$range,2);
        if ($last < $rangeval[0]) {return $rangeval[0] ;}
        if (isset($rangeval[1]) && ($last < $rangeval[1])) {return $last+1;}
        #Need to try another range def...
    }
    #If we get here, we ran out of positions :(
    return 0; #?????
}

#this sub checks for the existance of a table
function fw_fop_table_exists($db,$table) {
    $result = mysql_query("SHOW TABLES LIKE '" . $table . "'");
    if(mysql_fetch_row($result) === false) {return(false);}
    return(true);
}

function fw_fop_get_style_info($id,$layoutinfo) {
// do not use globals - instead pass layout info into function explicitly
//    global $rectangles;
//    global $numbuttonsx;
//    global $numbuttonsy;
$rectangles = $layoutinfo['rectangles'];
$numbuttonsx = $layoutinfo['numbuttonsx'];
$numbuttonsy = $layoutinfo['numbuttonsy'];


    foreach ($rectangles as $rect) {
        if ($id == $rect[0]) {

            $start = $rect[2];
            $stop = $rect[3];

            $xposition = floor(($start-1)/$numbuttonsy);
            $yposition = (($start-1)%$numbuttonsy);
            $xsize = 1 + floor(($stop-1)/$numbuttonsy) - floor(($start-1)/$numbuttonsy);
            $ysize = 1 + (($stop-1)%$numbuttonsy) - (($start-1)%$numbuttonsy);

            if (($xsize <= 0) || ($ysize <= 0)) {print "Warning: rectange '$id' has negative area\n"; break;}
            $styleinfo = "";
            if ($ysize > 2) {
                $styleinfo .= ($start + 1) . "-" . ($start + $ysize - 1) . ",";
            }
            elseif ($ysize == 2) {
                $styleinfo .= ($start + 1) . ",";
            }

            for ($i = 1 ; $i < $xsize ; $i++ ) {
                if ($ysize > 1) {
                    $styleinfo .= (($i + $xposition) * $numbuttonsy + $yposition + 1) . "-" . (($i + $xposition) * $numbuttonsy + $yposition + $ysize) . ",";
                }
                else {
                    $styleinfo .= (($i + $xposition) * $numbuttonsy + $yposition + 1) . ",";
                }
            }
            $retval = $styleinfo;
            break;
        }
    }
    return $retval;
}

function fw_fop_parse_zapata(&$zaplines,$conffile) {
    // LETS PARSE zapata.conf
    // Allowed format options
    // %c Zap Channel number
    // %n Line number
    // %N Line number, but restart counter
    // Example:
    // ;AMPLABEL:Channel %c - Button %n

    global $zapataconfdir;
    //global $zaplines; passed by reference instead
    global $ampwildcard;
    global $zaplabel;
    global $istrunk;
    global $lastlabelnum;
  global $chan_dahdi;

    if (!isset($ampwildcard)) {$ampwildcard=0;}
    if (!isset($zaplabel)) {$zaplabel= $chan_dahdi ? "DAHDI %c" : "Zap %c";}
    if (!isset($istrunk)) {$istrunk=0;}
    if (!isset($lastlabelnum)) {$lastlabelnum=0;}

    @$filearray = file($conffile);
  if ($filearray === false) {
    error("Cannot open config file: $conffile\n");
    return $zaplines;
  }
    foreach ($filearray as $line) {
        $line = trim($line);
        if ($line == '') {continue;} //was next in perl version

        $temparray = @explode(";AMPWILDCARDLABEL(",$line,2);
        if (count($temparray) == 2) {
            $temparray = @explode("):",$temparray[1],2);
            if (count($temparray) != 2)  {continue;}
            array_push($zaplines,array("Zap/*",trim($temparray[1]),($temparray[0])));
            $ampwildcard=1;
            continue;
        }

        $temparray = @explode(";AMPLABEL:",$line,2);
        if (count($temparray) == 2) {
            $zaplabel=trim($temparray[1]);
            $ampwildcard=0;
            if (strpos($temparray[1],"%N") === false) {continue;}
            $lastlabelnum=0;
            continue;
        }

        // remove comments
        $line_arr = explode(";",$line,2);
    $line = trim($line_arr[0]);
        if ($line == "") {continue;}

        //normalize whitespace
        $line = str_replace("\t"," ",$line);
        $line = preg_replace("/(\040)+/", " ", $line);

    // check if an include declaration
    $include = explode("#include ",$line,2);

        if (isset($include[1]))  {
            fw_fop_parse_zapata($zaplines,$zapataconfdir.trim($include[1]));
            continue;
        }

        //normalize assignment operator
        $line = str_replace("=>","=",$line);
        $line = str_replace(" ","",$line);

        //echo "Debug: " . $line . "\n";

        // check if trunk or extension
        //note that some versions of php do not support !== but only ===
        if (!(strpos($line,"context=from-pstn") === false)) {
            $istrunk=1;
            continue;
        }
        if (!(strpos($line,"context=from-zaptel") === false)) {
            $istrunk=1;
            continue;
        }
        if (!(strpos($line,"context=from-internal") === false)) {
            $istrunk=0;
            continue;
        }


        $temparray = @explode("channel=",$line,2);
        if ((count($temparray) == 2) and ($ampwildcard == 0))  {
            $temparray = @explode(",",$temparray[1],2);
            foreach ($temparray as $range) {
                list($start,$end) = @explode("-",$range,2);
                $start = intval($start); //crudely maps non-integers to zero
                if (!isset($end)) {$end = $start;}
                for ($i = $start; $i <= $end; $i++) {
                    $lastlabelnum++;
                    $newlabel=$zaplabel;
                    $newlabel=str_replace("%c",$i,$newlabel);
                    $newlabel=str_replace("%n",$lastlabelnum,$newlabel);
                    $newlabel=str_replace("%N",$lastlabelnum,$newlabel);

                    // only add if A) this is a trunk
                    // and B) we have not already defined any zaplines at the top of the file
                    // (I use this to customize it so instead of saying "Zap 1" it will
                    // say something more useful -- like the phone # of the line)

                    if($istrunk == 1) {
                        $inzaplines=false;
                        foreach ($zaplines as $tempvalue) {
                            if($tempvalue[0] == "Zap/$i") {
                                $inzaplines=true;
                                break;
                            }
                        }
                        if (!($inzaplines)) {
                           if ($chan_dahdi) {
                            array_push($zaplines,array( "DAHDI/$i","$newlabel" ));
                           } else {
                            array_push($zaplines,array( "Zap/$i","$newlabel" ));
                           }
                        }
                    } //istrunk
                } //for $i
            } //foreach $range
        } // if "channel="
    } //foreach $line
    return $zaplines;
}
//Finished parsing zapata.conf
?>
