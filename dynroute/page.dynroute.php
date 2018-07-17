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

if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }


$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$id = isset($_REQUEST['id'])?$_REQUEST['id']:'';
$nbroptions = isset($_REQUEST['nbroptions'])?$_REQUEST['nbroptions']:'3';
$tabindex = 0;

switch ($action) {
    case "add":
        $id = dynroute_get_dynroute_id('Unnamed');
        // Set the defaults
        $def['timeout']=5;
        dynroute_sidebar($id);
        dynroute_show_edit($id, $nbroptions,  $def);
        break;
    case "edit":
        dynroute_sidebar($id);
        dynroute_show_edit($id, $nbroptions, $_POST);
        break;
    case "edited":
        if (isset($_REQUEST['delete'])) {
            sql("DELETE from dynroute where dynroute_id='$id'");
            sql("DELETE FROM dynroute_dests where dynroute_id='$id'");
            needreload();
            redirect_standard();
        } else {
            dynroute_do_edit($id, $_POST);
            needreload();
            $_REQUEST['id']=$id;
            if (isset($_REQUEST['increase']) || isset($_REQUEST['decrease'])) {

                if (isset($_REQUEST['increase'])) 
                    $nbroptions++;
                if (isset($_REQUEST['decrease'])) {
                    $nbroptions--;
                }
                if ($nbroptions < 1)
                    $nbroptions = 1;
                $_REQUEST['action']='edit';
                $_REQUEST['nbroptions']=$nbroptions;
                redirect_standard('id','action','nbroptions');
            }
            else  {
                redirect_standard('id');
            }
        }
        break;
    default:
        dynroute_sidebar($id);
?>
<div class="content">
<h2><?php echo _("Dynamic Routing"); ?></h2>
<h3><?php 
echo _("Instructions")."</h3>";
echo _("You use the Dynamic Routing module to route calls based on the info returned from an sql lookup via mysql or odbc, from an agi script, web service or the value of an asterisk variable.")."\n";
echo _("For configuration instructions see").' <a href="http://www.voipsupport.it/pmwiki/pmwiki.php?n=Freepbx.DynamicRouting" target="_blank">www.voipsupport.it</a>'."\n"; ?>
</div>

<?php
}


function dynroute_sidebar($id)  {
?>
        <div class="rnav"><ul>
        <li><a id="<?php echo empty($id)?'current':'nul' ?>" href="config.php?display=dynroute&amp;action=add"><?php echo _("Add Route")?></a></li>
<?php

        $dynroute_results = dynroute_list();
        if (isset($dynroute_results)){
                foreach ($dynroute_results as $tresult) {
                        echo "<li><a id=\"".($id==$tresult['dynroute_id'] ? 'current':'nul')."\" href=\"config.php?display=dynroute";
                        echo "&amp;action=edit&amp;id={$tresult['dynroute_id']}\">{$tresult['displayname']}</a></li>\n";
                }
        }
        echo "</ul></div>\n";
}

function dynroute_show_edit($id, $nbroptions, $post) {
    global $db;
    global $tabindex;

    $dynroute_details = dynroute_get_details($id);
?>
    <div class="content">
    <h2><?php echo _("Dynamic Routing"); ?></h2>
    <h3><?php echo _("Edit Menu")." ".$dynroute_details['displayname']; ?></h3>
<?php 
?>
    <form name="prompt" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return prompt_onsubmit();">
    <input type="hidden" name="action" value="edited" />
    <input type="hidden" name="display" value="dynroute" />
    <input type="hidden" name="id" value="<?php echo $id ?>" />
    <input name="Submit" type="submit" style="display:none;" value="save" onclick="this.form.submited='save';" />
    <input name="delete" type="submit" value="<?php echo _("Delete")." "._("Route")." {$dynroute_details['displayname']}"; ?>" onclick="this.form.submited='delete';" />
    <br/>
<?php
    if ($id) {
        $usage_list = framework_display_destination_usage(dynroute_getdest($id));
        if (!empty($usage_list)) {
        ?>
            <br /><a href="#" class="info"><?php echo $usage_list['text']?>:<span><?php echo $usage_list['tooltip']?></span></a>
        <?php
        }
    }
    ?>
    <table>
        <tr><td colspan=2><hr /></td></tr>
        <tr>
            <td><a href="#" class="info"><?php echo _("Change Name"); ?><span><?php echo _("This changes the short name, visible on the right, of this Route");?></span></a></td>
            <td><input type="text" name="displayname" value="<?php echo $dynroute_details['displayname'] ?>" tabindex="<?php echo ++$tabindex;?>"></td>
        </tr>
                <tr>
                        <td><a href="#" class="info"><?php echo _("Get DTMF input");?><span><?php echo _("If checked reads in DTMF digis, the value is available in the sql query with special name of [INPUT].");?></span></a></td>
                        <td><input type="checkbox" name="enable_dtmf_input" <?php echo $dynroute_details['enable_dtmf_input'] ?> tabindex="<?php echo ++$tabindex;?>"></td>
                </tr>
                <tr>
                        <td><a href="#" class="info"><?php echo _("Timeout");?><span><?php echo _("The amount of time (in seconds) to wait for input");?></span></a></td>
                        <td><input type="text" name="timeout" value="<?php echo $dynroute_details['timeout'] ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                </tr>
                <tr>
                        <td><a href="#" class="info"><?php echo _("Input Variable");?><span><?php echo _("Optional variable name if you want the dmtf input to be available later in the call (e.g. futher dynamic route query or to pass to agi script)");?></span></a></td>
                        <td><input type="text" name="chan_var_name" value="<?php echo $dynroute_details['chan_var_name'] ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                </tr>
                <tr>
                        <td><a href="#" class="info"><?php echo _("Result Variable");?><span><?php echo _("Optional variable name if you want the query result to be available later in the call (e.g. futher dynamic route query or to pass to agi script)");?></span></a></td>
                        <td><input type="text" name="chan_var_name_res" value="<?php echo $dynroute_details['chan_var_name_res'] ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                </tr>
 
<?php
        $annmsg_id = isset($dynroute_details['announcement_id'])?$dynroute_details['announcement_id']:'';

        if(function_exists('recordings_list')) { //only include if recordings is enabled ?>
                <tr>
                        <td><a href="#" class="info"><?php echo _("Announcement")?><span><?php echo _("Message to be played to the caller. To add additional recordings please use the \"System Recordings\" MENU above")?></span></a></td>
                        <td>
                                <select name="annmsg_id" tabindex="<?php echo ++$tabindex;?>">
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
                        <select id="sourcetype" name="sourcetype" onChange="javascript:displaySourceParameters(this, this.selectedIndex)" tabindex="<?php echo ++$tabindex;?>">
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
                                        <tr><td colspan="2"><h5><?php echo _("URL") ?><hr></h5></div></td></tr>
                                        </tr>
                                                <td><a href="#" class="info"><?php echo _("URL")?><span><?php echo _("The url that returns the result")?></span></a></td>
                                                <td><input type="text" name="url_query" size="50" value="<?php echo (isset($dynroute_details['url_query']) ? htmlentities($dynroute_details['url_query']) : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                                        </tr>
                </table>
            </div>
            </td>
        </tr>

        <tr id="astvarrow" <?php if ($dynroute_details['sourcetype'] != 'astvar') echo 'style="display: none"';?>>
                   <td colspan="2">
                       <div id="astvar">
                                <table cellpadding="2" cellspacing="0" width="100%">
                                        <tr><td colspan="2"><h5><?php echo _("Asterisk variable") ?><hr></h5></div></td></tr>
                                        </tr>
                                                <td><a href="#" class="info"><?php echo _("Variable string")?><span><?php echo _("The string containing one or more asterisk variables")?></span></a></td>
                                                <td><input type="text" name="astvar_query" size="50" value="<?php echo (isset($dynroute_details['astvar_query']) ? htmlentities($dynroute_details['astvar_query']) : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                                        </tr>
                </table>
            </div>
            </td>
        </tr>

        <tr id="agirow" <?php if ($dynroute_details['sourcetype'] != 'agi') echo 'style="display: none"';?>>
                   <td colspan="2">
                       <div id="agi">
                                <table cellpadding="2" cellspacing="0" width="100%">
                                        <tr><td colspan="2"><h5><?php echo _("AGI") ?><hr></h5></div></td></tr>
                                        <tr>
                                                <td><a href="#" class="info"><?php echo _("AGI script and parameters")?><span><?php echo _("Name of the AGI script. Optional parameters may be appended using the , as separator")?></span></a></td>
                                                <td><input type="text" name="agi_query" size="50" value="<?php echo (isset($dynroute_details['agi_query']) ? htmlentities($dynroute_details['agi_query']) : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                                        <tr>
                                        </tr>
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
                                        <tr><td colspan="2"><h5><?php echo _("ODBC") ?><hr></h5></div></td></tr>
                                        <tr>
                                                <td><a href="#" class="info"><?php echo _("Function")?><span><?php echo _("Name of the odbc function (excluding ODBC prefix) in /etc/asterisk/func_odbc.conf")?></span></a></td>
                                                <td><input type="text" name="odbc_func" value="<?php echo (isset($dynroute_details['odbc_func']) ? htmlentities($dynroute_details['odbc_func']) : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                                        <tr>
                                        </tr>
                                                <td><a href="#" class="info"><?php echo _("Query")?><span><?php echo _("The query which gets the result out of the database")?></span></a></td>
                                                <td><input type="text" name="odbc_query" size="50" value="<?php echo (isset($dynroute_details['odbc_query']) ? htmlentities($dynroute_details['odbc_query']) : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                                        </tr>
                </table>
            </div>
            </td>
        </tr>



        <tr id="mysqlrow" <?php if ($dynroute_details['sourcetype'] != 'mysql') echo 'style="display: none"';?>>
                   <td colspan="2">
                       <div id="mysql">
                                <table cellpadding="2" cellspacing="0" width="100%">
                                        <tr><td colspan="2"><h5><?php echo _("MySQL") ?><hr></h5></div></td></tr>
                                        <tr>
                        <td><a href="#" class="info"><?php echo _("Host");?><span><?php echo _("Hostname or IP address of the server running the MySQL database");?></span></a></td>
                        <td><input type="text" name="mysql_host" value="<?php echo (isset($dynroute_details['mysql_host']) ? $dynroute_details['mysql_host'] : '') ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                    </tr>
                    <tr>
                        <td><a href="#" class="info"><?php echo _("Database");?><span><?php echo _("The name of the database out of wich the information is being queried");?></span></a></td>
                        <td><input type="text" name="mysql_dbname" value="<?php echo (isset($dynroute_details['mysql_dbname']) ? $dynroute_details['mysql_dbname'] : '') ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                    </tr>
                    <tr>
                        <td><a href="#" class="info"><?php echo _("Username");?><span><?php echo _("The user/login name for accessing the database");?></span></a></td>
                        <td><input type="text" name="mysql_username" value="<?php echo (isset($dynroute_details['mysql_username']) ? $dynroute_details['mysql_username'] : '') ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                    </tr>
                    <tr>
                        <td><a href="#" class="info"><?php echo _("Password");?><span><?php echo _("The password wich is needed to access the database");?></span></a></td>
                        <td><input type="text" name="mysql_password" value="<?php echo (isset($dynroute_details['mysql_password']) ? htmlentities($dynroute_details['mysql_password']) : '') ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                    </tr>
                    <tr>
                        <td><a href="#" class="info"><?php echo _("Query");?><span><?php echo _("The query wich gets the result out of the database");?></span></a></td>
                        <td><input type="text" name="mysql_query" size="50" value="<?php echo (isset($dynroute_details['mysql_query']) ? htmlentities($dynroute_details['mysql_query']) : '') ?>" tabindex="<?php echo ++$tabindex;?>"></td>
                                        </tr>
                </table>
            </div>
            </td>
        </tr>
        <tr><td colspan=2><hr /></td></tr>
        <tr><td colspan=2>

            <input name="increase" type="submit" value="<?php echo _("Increase Destinations")?>">
            &nbsp;
            <input name="Submit" type="submit" value="<?php echo _("Save")?>" tabindex="<?php echo ++$tabindex;?>">
            &nbsp;
            <?php if ($nbroptions > 1) { ?>
            <input name="decrease" type="submit" value="<?php echo _("Decrease Destinations")?>">
            <?php } ?>
        </td>
    </tr>
    <tr><td colspan=2><hr /></td></tr>
    <tr><th><?php echo _("Match")?></th><th><?php echo _("Destination")?></th></tr>
<?php

    // Draw the destinations

    $default_dest_row = dynroute_get_dests($id,'y');
    if (!empty($default_dest_row)) $default_dest=$default_dest_row[0]['dest']; else $default_dest='';
    $count = 0;
?>
    <tr>
    <tr><td colspan=2>&nbsp;</td>
    </tr>    
    <td style="text-align:right;"><?php echo _("Default destination")?></td>
    <td> <table> <?php echo drawselects($default_dest,$count++); ?> </table></td>

    </tr>    
<tr><td colspan=2><hr/></td></tr>
<?php
    $dests = dynroute_get_dests($id,'n');
    if (!empty($dests)) {
        foreach ($dests as $dest) {
            drawdestinations($count++, $dest['selection'], $dest['dest']);
        }
    }
    while ($count < $nbroptions) {
        drawdestinations($count++, null, null);
    }
?>
    
</table>
<?php
    if ($nbroptions < $count) { 
        echo "<input type='hidden' name='nbroptions' value=$count />\n";
    } else {
        echo "<input type='hidden' name='nbroptions' value=$nbroptions />\n";
    } 

    global $module_hook;
    echo $module_hook->hookHtml;
?>
    <input name="increase" type="submit" value="<?php echo _("Increase Destinations")?>">
    &nbsp;
    <input name="Submit" type="submit" value="<?php echo _("Save")?>">
    &nbsp;
    <?php if ($nbroptions > 1) { ?>
    <input name="decrease" type="submit" value="<?php echo _("Decrease Destinations")?>">
    <?php } ?>
    
    <script language="javascript">
    <!--

var theForm = document.prompt;
theForm.displayname.focus();

    function prompt_onsubmit() {
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
                        if (!isRouteOption(elem.value))
                            return warnInvalid(elem, msgInvalidOption);
                        
                        var gotoNum = elem.name.charAt(6);
                        var isok = validateSingleDestination(theForm,gotoNum,true);
                        if (!isok)
                            return false;
                    }
                 }
              }
        }
                                  
        return true;
    }
    $( document ).ready(function() { 
    console.log('listo');
    console.log($('.destdropdown'));
    $('.destdropdown').change(function() { console.log('perro');}); 
    });
    //-->
    </script>
        </form>
        </div>


<?php
}

function drawdestinations($count, $sel,  $dest) { 
    global $tabindex
?>
    <tr> <td style="text-align:left;">

        <input size="10" type="text" name="option<?php echo $count ?>" value="<?php echo $sel ?>" tabindex="<?php echo ++$tabindex;?>"><br />
<?php if (strlen($sel)) {  ?>
        <i style='font-size: x-small'><?php echo _("Leave blank to remove");?></i>
<?php }  ?>
    </td>
        <td> <table> <?php echo drawselects($dest,$count); ?> </table> </td>
    </tr>
    <tr><td colspan=2><hr /></td></tr>
<script language="javascript">
<!--
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
-->
</script>

<?php
}
