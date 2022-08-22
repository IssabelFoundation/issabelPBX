<?php 
//    dynroute - Dynamic Route Module for IssabelPBX 
//    Copyright (C) 2009-2014 John Fawcett john@voipsupport.it
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.

//    This file was originally a derived work of the issabelpbx ivr 
//    and calleridlookup modules in September 2009
//
//    Copyright 2022 Issabel Foundation

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$action     = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:'';
$nbroptions = isset($_REQUEST['nbroptions'])?$_REQUEST['nbroptions']:'2';
$tabindex = 0;

if($extdisplay!='' && $action=='') {
    $action='edit';
}

$rnaventries = array();
$dynroute_results = dynroute_list();
foreach ($dynroute_results as $tresult) {
    $rnaventries[] = array($tresult['dynroute_id'],$tresult['displayname'],'');
}
drawListMenu($rnaventries, $type, $display, $extdisplay);

echo "<div class='content'>\n";

$helptext = _("You use the Dynamic Routing module to route calls based on the info returned from an sql lookup via mysql or odbc, from an agi script, web service or the value of an asterisk variable.")."\n";
$helptext.= _("For configuration instructions see").' <a href="http://www.voipsupport.it/pmwiki/pmwiki.php?n=Freepbx.DynamicRouting" target="_blank">www.voipsupport.it</a>'."\n"; 
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

switch ($action) {
    case "edit":
        dynroute_show_edit($extdisplay, $nbroptions, $_POST, $help);
        break;
    case "delete":
        sql("DELETE from dynroute where dynroute_id='$extdisplay'");
        sql("DELETE FROM dynroute_dests where dynroute_id='$extdisplay'");
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been deleted'));
        $_SESSION['msgtype']='warning';
        redirect_standard();
    case "edited":
        dynroute_do_edit($extdisplay, $_POST);
        needreload();
        $_SESSION['msg']=base64_encode(dgettext('amp','Item has been saved'));
        $_SESSION['msgtype']='success';
        redirect_standard('extdisplay');
        break;
    case "add":
    default:
        // Set the defaults
        $def['timeout']=5;
        dynroute_show_edit('', $nbroptions,  $def, $help);
        break;
}

function dynroute_show_edit($extdisplay, $nbroptions, $post, $help) {
    global $db;
    global $tabindex;

    $empty=array();
    $empty['displayname']='';
    $empty['enable_dtmf_input']='';
    $empty['timeout']='';
    $empty['sourcetype']='';
    $empty['chan_var_name']='';
    $empty['chan_var_name_res']='';
    $empty['announcement_id']='';
    $empty['sourcetype']='';
    $empty['url_query']='';
    $empty['astvar_query']='';
    $empty['agi_query']='';
    $empty['agi_var_name_res']='';
    $empty['odbc_func']='';
    $empty['odbc_query']='';
    $empty['mysql_host']='';
    $empty['mysql_dbname']='';
    $empty['mysql_username']='';
    $empty['mysql_password']='';
    $empty['mysql_query']='';

    $dynroute_details = ($extdisplay!='')?dynroute_get_details($extdisplay):$empty;

    if($extdisplay=='') {
        echo "<div class='is-flex'><h2>"._("Add Dynamic Route")."</h2>$help</div>";
    } else {
        echo "<div class='is-flex'><h2>"._("Edit Dynamic Route").": ".$dynroute_details['displayname']."</h2>$help</div>";
    }

    if ($extdisplay) {
        $usage_list = framework_display_destination_usage(dynroute_getdest($extdisplay));
        if (!empty($usage_list)) {
            echo ipbx_usage_info($usage_list['text'],$usage_list['tooltip']);
        }
    }


?>
    <form id="mainform" name="prompt" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return prompt_onsubmit(this);">
    <input type="hidden" name="action" value="edited" />
    <input type="hidden" name="display" value="dynroute" />
    <input type="hidden" name="id" value="<?php echo $extdisplay ?>" />
    <input name="Submit" type="submit" style="display:none;" value="save" onclick="this.form.submited='save';" />
    <table class='table is-borderless is-narrow'>
        <tr><td colspan="2"><h5><?php  echo dgettext('amp','General Settings') ?></h5></td></tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Name"); ?><span><?php echo _("Enter a name for this dynamic route");?></span></a></td>
            <td><input class="w100 input" type="text" autofocus name="displayname" value="<?php echo $dynroute_details['displayname'] ?>" tabindex="<?php echo ++$tabindex;?>"></td>
        </tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Get DTMF input");?><span><?php echo _("If checked reads in DTMF digis, the value is available in the sql query with special name of [INPUT].");?></span></a></td>
            <td>
                <div class='field'><input type='checkbox' class='switch' id='enable_dtmf_input' name='enable_dtmf_input' value='CHECKED' <?php echo $dynroute_details['enable_dtmf_input'];?> tabindex='<?php echo ++$tabindex;?>'/><label style='height:auto; line-height:2em; padding-left:3em;' for='enable_dtmf_input'>&nbsp;</label></div>
            </td>
        </tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Timeout");?><span><?php echo _("The amount of time (in seconds) to wait for input");?></span></a></td>
            <td><input class="w100 input" type="text" name="timeout" value="<?php echo $dynroute_details['timeout'] ?>" tabindex="<?php echo ++$tabindex;?>"></td>
        </tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Input Variable");?><span><?php echo _("Optional variable name if you want the dmtf input to be available later in the call (e.g. futher dynamic route query or to pass to agi script)");?></span></a></td>
            <td><input class="w100 input" type="text" name="chan_var_name" value="<?php echo $dynroute_details['chan_var_name'] ?>" tabindex="<?php echo ++$tabindex;?>"></td>
        </tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Result Variable");?><span><?php echo _("Optional variable name if you want the query result to be available later in the call (e.g. futher dynamic route query or to pass to agi script)");?></span></a></td>
            <td><input class="w100 input" type="text" name="chan_var_name_res" value="<?php echo $dynroute_details['chan_var_name_res'] ?>" tabindex="<?php echo ++$tabindex;?>"></td>
        </tr>
 
<?php
        $annmsg_id = isset($dynroute_details['announcement_id'])?$dynroute_details['announcement_id']:'';

    if(function_exists('recordings_list')) { //only include if recordings is enabled ?>

        <tr>
            <td>
                <a href="#" class="info"><?php echo _("Announcement")?><span><?php echo _("Message to be played to the caller. To add additional recordings please use the \"System Recordings\" MENU above")?></span></a>
            </td>
            <td>
                <select name="annmsg_id" tabindex="<?php echo ++$tabindex;?>" class="componentSelect">
                <?php
                    $tresults = recordings_list();
                    echo '<option value="">'._("None")."</option>";
                    if (isset($tresults[0])) {
                        foreach ($tresults as $tresult) {
                            echo '<option value="'.$tresult['id'].'"'.($tresult['id'] == $annmsg_id ? ' SELECTED' : '').'>'.$tresult['displayname']."</option>\n";
                        }
                    }
                ?>
                </select>
            </td>
        </tr>

<?php
    } else {
?>
        <tr>
            <td><a href="#" class="info"><?php echo _("Announcement")?><span><?php echo _("Message to be played to the caller.<br><br>You must install and enable the \"Systems Recordings\" Module to edit this option")?></span></a></td>
            <td>
            <?php
                $default = (isset($annmsg_id) ? $annmsg_id : '');
            ?>
                <input type="hidden" name="annmsg_id" value="<?php echo $default; ?>"><?php echo ($default != '' ? $default : 'None'); ?>
            </td>
        </tr>
<?php
    }
?>
        <tr>
            <td><a href="#" class="info"><?php echo _("Source type")?><span><?php echo _("Select the source type, you can choose between:<ul><li>MySQL: It queries a MySQL database to retrieve the routing information</li><li>ODBC: It queries an ODBC data source to retrieve the routing information</li></ul>")?></span></a></td>
            <td>
                <select id="sourcetype" name="sourcetype" onChange="javascript:displaySourceParameters(this, this.selectedIndex)" tabindex="<?php echo ++$tabindex;?>" class="componentSelect">
                    <option value="none" <?php echo ($dynroute_details['sourcetype'] == 'none' ? 'selected' : '')?>>choose...</option>
                    <option value="mysql" <?php echo ($dynroute_details['sourcetype'] == 'mysql' ? 'selected' : '')?>>MySQL</option>
                    <option value="odbc" <?php echo ($dynroute_details['sourcetype'] == 'odbc' ? 'selected' : '')?>>ODBC</option>
                    <option value="url" <?php echo ($dynroute_details['sourcetype'] == 'url' ? 'selected' : '')?>>URL</option>
                    <option value="agi" <?php echo ($dynroute_details['sourcetype'] == 'agi' ? 'selected' : '')?>>AGI</option>
                    <option value="astvar" <?php echo ($dynroute_details['sourcetype'] == 'astvar' ? 'selected' : '')?>>Asterisk variable</option>
                </select>
            </td>
        </tr>

        <tr id="urlrow" <?php if ($dynroute_details['sourcetype'] != 'url') echo 'style="display: none"';?>>
            <td colspan="2">
                <div id="url"> 
                    <table cellpadding="2" cellspacing="0" width="100%">
                        <tr>
                            <td colspan="2"><h5><?php echo _("URL") ?></h5></div></td>
                        </tr>
                        <tr>
                            <td><a href="#" class="info"><?php echo _("URL")?><span><?php echo _("The url that returns the result")?></span></a></td>
                            <td><input type="text" name="url_query" class="input" value="<?php echo (isset($dynroute_details['url_query']) ? htmlentities($dynroute_details['url_query']) : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>

        <tr id="astvarrow" <?php if ($dynroute_details['sourcetype'] != 'astvar') echo 'style="display: none"';?>>
            <td colspan="2">
                <div id="astvar">
                    <table cellpadding="2" cellspacing="0" width="100%">
                        <tr>
                            <td colspan="2"><h5><?php echo _("Asterisk variable") ?></h5></div></td>
                        </tr>
                        <tr>
                            <td><a href="#" class="info"><?php echo _("Variable string")?><span><?php echo _("The string containing one or more asterisk variables")?></span></a></td>
                            <td><input type="text" name="astvar_query" class="input" value="<?php echo (isset($dynroute_details['astvar_query']) ? htmlentities($dynroute_details['astvar_query']) : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>

        <tr id="agirow" <?php if ($dynroute_details['sourcetype'] != 'agi') echo 'style="display: none"';?>>
            <td colspan="2">
                <div id="agi">
                    <table cellpadding="2" cellspacing="0" width="100%">
                        <tr>
                            <td colspan="2"><h5><?php echo _("AGI") ?></h5></div></td>
                        </tr>
                        <tr>
                            <td><a href="#" class="info"><?php echo _("AGI script and parameters")?><span><?php echo _("Name of the AGI script. Optional parameters may be appended using the , as separator")?></span></a></td>
                            <td><input type="text" name="agi_query" class="input" value="<?php echo (isset($dynroute_details['agi_query']) ? htmlentities($dynroute_details['agi_query']) : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                        </tr>
                        <tr>
                            <td><a href="#" class="info"><?php echo _("AGI result variable")?><span><?php echo _("The name of the variable in the script which must be set with the result. See test.agi for an example.")?></span></a></td>
                            <td><input type="text" name="agi_var_name_res" value="<?php echo (isset($dynroute_details['agi_var_name_res']) ? htmlentities($dynroute_details['agi_var_name_res']) : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                       </tr>
                    </table>
                </div>
            </td>
        </tr>

        <tr id="odbcrow" <?php if ($dynroute_details['sourcetype'] != 'odbc') echo 'style="display: none"';?>>
            <td colspan="2">
                <div id="odbc">
                    <table cellpadding="2" cellspacing="0" width="100%">
                        <tr><td colspan="2"><h5><?php echo _("ODBC") ?></h5></div></td></tr>
                        <tr>
                            <td><a href="#" class="info"><?php echo _("Function")?><span><?php echo _("Name of the odbc function (excluding ODBC prefix) in /etc/asterisk/func_odbc.conf")?></span></a></td>
                            <td><input type="text" name="odbc_func" class="input" value="<?php echo (isset($dynroute_details['odbc_func']) ? htmlentities($dynroute_details['odbc_func']) : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                        </tr>
                        <tr>
                            <td><a href="#" class="info"><?php echo _("Query")?><span><?php echo _("The query which gets the result out of the database")?></span></a></td>
                            <td><input type="text" name="odbc_query" class="input" value="<?php echo (isset($dynroute_details['odbc_query']) ? htmlentities($dynroute_details['odbc_query']) : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>

        <tr id="mysqlrow" <?php if ($dynroute_details['sourcetype'] != 'mysql') echo 'style="display: none"';?>>
            <td colspan="2">
                <div id="mysql">
                    <table cellpadding="2" cellspacing="0" width="100%">
                        <tr><td colspan="2"><h5><?php echo _("MySQL") ?></h5></div></td></tr>
                        <tr>
                            <td><a href="#" class="info"><?php echo _("Host");?><span><?php echo _("Hostname or IP address of the server running the MySQL database");?></span></a></td>
                            <td><input class="input" type="text" name="mysql_host" value="<?php echo (isset($dynroute_details['mysql_host']) ? $dynroute_details['mysql_host'] : '') ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                        </tr>
                        <tr>
                            <td><a href="#" class="info"><?php echo _("Database");?><span><?php echo _("The name of the database out of wich the information is being queried");?></span></a></td>
                            <td><input class="input"type="text" name="mysql_dbname" value="<?php echo (isset($dynroute_details['mysql_dbname']) ? $dynroute_details['mysql_dbname'] : '') ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                        </tr>
                        <tr>
                            <td><a href="#" class="info"><?php echo _("Username");?><span><?php echo _("The user/login name for accessing the database");?></span></a></td>
                            <td><input class="input" type="text" name="mysql_username" value="<?php echo (isset($dynroute_details['mysql_username']) ? $dynroute_details['mysql_username'] : '') ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                        </tr>
                        <tr>
                            <td><a href="#" class="info"><?php echo _("Password");?><span><?php echo _("The password wich is needed to access the database");?></span></a></td>
                            <td><input class="input" type="text" name="mysql_password" value="<?php echo (isset($dynroute_details['mysql_password']) ? htmlentities($dynroute_details['mysql_password']) : '') ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                        </tr>
                        <tr>
                            <td><a href="#" class="info"><?php echo _("Query");?><span><?php echo _("The query wich gets the result out of the database");?></span></a></td>
                            <td><input class="input" type="text" name="mysql_query" class="input" value="<?php echo (isset($dynroute_details['mysql_query']) ? htmlentities($dynroute_details['mysql_query']) : '') ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>

    <tr><td colspan="2"><h5><?php  echo dgettext('amp','Optional Destinations') ?></h5></td></tr>

    <tr><td colspan=2>

    <table class='table' id='mydestinations'>
    <thead>
    <tr><th><?php echo _("Match")?></th><th><?php echo _("Destination")?></th><th></th></tr>
    </thead>
    <tbody>
<?php

    // Draw the destinations

    $default_dest_row = dynroute_get_dests($extdisplay,'y');
    if (!empty($default_dest_row)) $default_dest=$default_dest_row[0]['dest']; else $default_dest='';
    $count = 0;
?>
    <tr>
    <td style="text-align:right; width:14em;"><?php echo _("Default destination")?></td>
    <td> 
    <table> <?php echo drawselects($default_dest,$count++); ?> </table></td>
    <td></td>

    </tr>    
<?php
    $dests = dynroute_get_dests($extdisplay,'n');
    if (!empty($dests)) {
        foreach ($dests as $dest) {
            drawdestinations($count++, $dest['selection'], $dest['dest']);
        }
    }
    while ($count < $nbroptions) {
        drawdestinations($count++, null, null);
    }
?>
    
    </tbody>
  </table>
</table>

<?php
    // for cloning purposes, not show
    echo "<div class='is-hidden'><table><tbody id='cloneselect'>";
    echo drawdestinations('NEWCOUNTER',null,null);
    echo "</tbody></table></div>";
?>

    <input type="button" id="destination-add"  class="button is-small is-info is-rounded is-light" value="<?php echo _("Increase Destinations")?>" />
    <script>
    clone_select  = $('#cloneselect').html();
    </script>
<?php

    global $module_hook;
    echo process_tabindex($module_hook->hookHtml,$tabindex);
?>
<script>

$(function() {
    $("#destination-add").on('click',function(){
        addCustomField('','','',$("#last_row"));
    });
    $(document).on('click','.btndelete',function() {
        id_to_delete = $(this).attr('id').substr(3);
        option_to_empty = $('input[name=option'+id_to_delete+']');
        tr_to_delete = option_to_empty.parent().parent();
        option_to_empty.val('');
        tr_to_delete.hide();
    })
});


function addCustomField(key, val) {
    // clone select updating all attributes with counter
    var idx = $('#mydestinations > tbody > tr').length;
    newhtml = clone_select.replace(/NEWCOUNTER/g,''+idx);
    last_element = $('#mydestinations > tbody');
    last_element.append(newhtml);
    bind_dests_double_selects();
    $('.destdropdown:not(".haschosen")').addClass('haschosen').chosen({disable_search: false, inherit_select_classes: true, width: '100%'});
    $('.destdropdown2:not(".haschosen")').addClass('haschosen').chosen({disable_search: false, inherit_select_classes: true, width: '100%'});
}

function prompt_onsubmit(theForm) {
    var msgInvalidOption = "<?php echo _("Invalid default destination"); ?>";

    defaultEmptyOK = true;

    defaultroute = $('#goto0')[0];


    if(theForm.submited=='delete') { 
        // allow delete no matter verification
        return true; 
    }


    if(defaultroute.selectedIndex==0) {
        return warnInvalid($('goto0'), msgInvalidOption);
    } 

    setDestinations(theForm, '_post_dest');

    // go thru the form looking for options
    // where the option isn't blank (as that will be removed) do the validation
    var allelems = theForm.elements;
    if (allelems != null)
    {
        var i, elem;
        for (i = 0; elem = allelems[i]; i++)
        {
            if (elem.type == 'text' && elem.name.indexOf('option') == 0)
            {
                if (elem.value != '') {
                    //if (!isRouteOption(elem.value))
                    //    return warnInvalid(elem, msgInvalidOption);
                    
                    var gotoNum = elem.name.charAt(6);
                    var isok = validateSingleDestination(theForm,gotoNum,true);
                    if (!isok)
                        return false;
                }
             }
          }
    }
    $.LoadingOverlay('show');                              
    return true;
}

function displaySourceParameters(sourcetypeSelect, key) {
    if (sourcetypeSelect.options[key].value == 'none') {
        document.getElementById('urlrow').style.display = 'none';
        document.getElementById('odbcrow').style.display = 'none';
        document.getElementById('agirow').style.display = 'none';
        document.getElementById('astvarrow').style.display = 'none';
        document.getElementById('mysqlrow').style.display = 'none';
    } else if (sourcetypeSelect.options[key].value == 'url') {
        document.getElementById('urlrow').style.display = '';
        document.getElementById('odbcrow').style.display = 'none';
        document.getElementById('agirow').style.display = 'none';
        document.getElementById('astvarrow').style.display = 'none';
        document.getElementById('mysqlrow').style.display = 'none';
    } else if (sourcetypeSelect.options[key].value == 'agi') {
        document.getElementById('urlrow').style.display = 'none';
        document.getElementById('odbcrow').style.display = 'none';
        document.getElementById('agirow').style.display = '';
        document.getElementById('astvarrow').style.display = 'none';
        document.getElementById('mysqlrow').style.display = 'none';
    } else if (sourcetypeSelect.options[key].value == 'astvar') {
        document.getElementById('urlrow').style.display = 'none';
        document.getElementById('odbcrow').style.display = 'none';
        document.getElementById('agirow').style.display = 'none';
        document.getElementById('astvarrow').style.display = '';
        document.getElementById('mysqlrow').style.display = 'none';
    } else if (sourcetypeSelect.options[key].value == 'odbc') {
        document.getElementById('urlrow').style.display = 'none';
        document.getElementById('odbcrow').style.display = '';
        document.getElementById('agirow').style.display = 'none';
        document.getElementById('astvarrow').style.display = 'none';
        document.getElementById('mysqlrow').style.display = 'none';
    } else if (sourcetypeSelect.options[key].value == 'mysql') {
        document.getElementById('urlrow').style.display = 'none';
        document.getElementById('odbcrow').style.display = 'none';
        document.getElementById('agirow').style.display = 'none';
        document.getElementById('astvarrow').style.display = 'none';
        document.getElementById('mysqlrow').style.display = '';
    }
}

<?php echo js_display_confirmation_toasts(); ?>
    </script>
    </form>
    </div>


<?php
echo form_action_bar($extdisplay);
}

function drawdestinations($count, $sel,  $dest) {
    global $tabindex;
    $dotable = true;
    if($count=='NEWCOUNTER') {
        $dotable=true;
    }
?>
    <tr><td>
        <input class="input" type="text" name="option<?php echo $count ?>" value="<?php echo $sel ?>" tabindex="<?php echo ++$tabindex;?>"><br />
    </td>
        <td><table class='destselect'> <?php echo drawselects($dest,$count); ?> </table> </td>
        <td valign='middle'>
<?php
             echo "<button name='del$count' id='del$count' value='Delete' type='button' class='btndelete mt-2 button is-small is-danger' data-tooltip='"._('Delete')."'><span class='icon is-small'><i class='fa fa-trash'></i></span></button>";
?>
    </td>
    </tr>

<?php
} // end drawdestinations function
