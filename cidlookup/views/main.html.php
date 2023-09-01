<!-- right side menu -->
<?php
if(!isset($message)) $message='';
if(!isset($thisItem_description)) $thisItem_description='';
if(!isset($thisItem['opencnam_account_sid'])) $thisItem['opencnam_account_sid']='';
$itemid = $extdisplay;

$rnaventries = array();
$announces   = announcement_list();
foreach($cidsources as $cidsource) {
    if ($cidsource['cidlookup_id'] != 0) {
        $rnaventries[] = array($cidsource['cidlookup_id'],$cidsource['description'],'','');
    }
}

drawListMenu($rnaventries, $type, $display, $extdisplay);

$helptext = __("A Lookup Source let you specify a source for resolving numeric CallerIDs of incoming calls, you can then link an Inbound route to a specific CID source. This way you will have more detailed CDR reports with information taken directly from your CRM. You can also install the phonebook module to have a small number <-> name association. Pay attention, name lookup may slow down your PBX");
$help = '<div class="infohelp">?<span style="display:none;">'.$helptext.'</span></div>';

?>
<!--div class="rnav"><ul>
    <li><a class="<?php echo ($itemid=='' ? 'current':'') ?>" href="config.php?display=cidlookup"><?php echo __("Add CID Lookup Source")?></a></li>
    <?php
    if (isset($cidsources)) {
        foreach ($cidsources as $cidsource) {
            if ($cidsource['cidlookup_id'] != 0) {
                ?>
                <li><a class="<?php echo ($itemid==$cidsource['cidlookup_id'] ? 'current':'')?>" href="config.php?display=cidlookup&amp;itemid=<?php echo urlencode($cidsource['cidlookup_id'])?>"><?php echo $cidsource['description']?></a></li>
                <?php
            }
        }
    }
    ?>
    </ul>
</div-->
<div class='content'>
<div id="cid_message"><?php echo $message?></div>
<div class='is-flex'><h2><?php echo ($itemid ? __("Edit Source") . ": " . $thisItem_description : __("Add Source")); ?></h2><?php echo $help;?></div>

<?php if ($itemid){ ?>
    <?php if($dids_using) {?>
        <small><?php sprintf(__("There are %s DIDs using this source that will no longer have lookups if deleted."),$dids_using)?></small>
    <?php } ?>
<?php } ?>

<form id="mainform" autocomplete="off" name="edit" method="post" onsubmit="return edit_onsubmit(this);">
    <input type="hidden" name="display" value="cidlookup">
    <input type="hidden" name="action" value="<?php echo ($itemid ? 'edit' : 'add') ?>">
    <input type="hidden" name="deptname" value="<?php echo $_SESSION["AMP_user"]->_deptname ?>">
    <?php if ($itemid){ ?>
            <input type="hidden" name="itemid" value="<?php echo $itemid; ?>">
    <?php } ?>
    <table class='table is-narrow is-borderless notfixed'>
        <tr><td colspan="2"><h5><?php echo _dgettext('amp','General Settings');?></h5></td></tr>
        <tr>
            <td>
                <a href="#" class="info"><?php echo __("Source Description")?><span><?php echo __("Enter a description for this source.")?></span></a>
            </td>
            <td>
                <input type="text" class="input w100" id="form_description" name="description" value="<?php echo $thisItem_description; ?>" tabindex="<?php echo ++$tabindex;?>">
            </td>
        </tr>
        <tr>
            <td>
                <a href="#" class="info"><?php echo __("Source type")?>
                    <span><?php echo __("Select the source type, you can choose between:<ul>
                    <li>OpenCNAM: Use OpenCNAM [https://www.opencnam.com/]</li>
                    <li>Internal: use astdb as lookup source, use Issabel address book to populate it</li>
                    <li>ENUM: Use DNS to lookup caller names, it uses ENUM lookup zones as configured in enum.conf</li>
                    <li>HTTP: It executes an HTTP GET passing the caller number as argument to retrieve the correct name</li>
                    <li>MySQL: It queries a MySQL database to retrieve caller name</li>
                    </ul>")?></span>
                </a>
            </td>
            <td>
                <select id="sourcetype" name="sourcetype" tabindex="<?php echo ++$tabindex;?>" class='componentSelect'>
                    <option value="opencnam" <?php echo ($thisItem['sourcetype'] == 'opencnam' ? 'selected' : '')?>><?php echo __("OpenCNAM")?></option>
                    <option value="internal" <?php echo ($thisItem['sourcetype'] == 'internal' ? 'selected' : '')?>><?php echo __("Internal")?></option>
                    <option value="enum" <?php echo ($thisItem['sourcetype'] == 'enum' ? 'selected' : '')?>>ENUM</option>
                    <option value="http" <?php echo ($thisItem['sourcetype'] == 'http' ? 'selected' : '')?>>HTTP</option>
                    <option value="https" <?php echo ($thisItem['sourcetype'] == 'https' ? 'selected' : '')?>>HTTPS</option>
                    <option value="mysql" <?php echo ($thisItem['sourcetype'] == 'mysql' ? 'selected' : '')?>>MySQL</option>
                    <option value="sugarcrm" <?php echo ($thisItem['sourcetype'] == 'sugarcrm' ? 'selected' : '')?>>SugarCRM</option>
                    <option value="superfecta" <?php echo ($thisItem['sourcetype'] == 'superfecta' ? 'superfecta' : '')?>>Superfecta</option>
                </select>
            </td>
        </tr>
        <tr id="cache_results">
            <td>
                <a href="#" class="info"><?php echo __("Cache results")?><span><?php echo __("Decide whether or not cache the results to astDB; it will overwrite present values. It does not affect Internal source behavior")?></span></a>
            </td>
            <td>
                <!--input type="checkbox" name="cache" value="1" <?php echo ($thisItem['cache'] == 1 ? 'checked' : ''); ?> tabindex="<?php echo ++$tabindex;?>"-->
<?php echo ipbx_yesno_checkbox("cache",$thisItem['cache'],false); ?>
            </td>
        </tr>
        <tr id="opencnam" style="display:none;">
            <td colspan="2">
                <div>
                    <h5><?php echo __("OpenCNAM") ?></h5>
                    <p class='notification is-info is-light'><b><?php echo __('NOTE:');?></b> <?php echo __("OpenCNAM's Hobbyist Tier (default) only allows you to do 60 cached CallerID lookups per hour. If you get more than 60 incoming calls per hour, or want real-time CallerID information (more accurate), you should use the Professional Tier.");?></p>
                    <p class='notification is-info is-light'><?php echo __("If you'd like to create an OpenCNAM Professional Tier account, you can do so on their website: <a href='https://www.opencnam.com/register' target='_blank'>https://www.opencnam.com/register</a>");?></p>
                    <table class='table is-borderless is-narrow notfixed'>
                        <tr>
                            <td width="50%">
                                <a href="#" class="info"><?php echo __("Use Professional Tier?")?><span><?php echo __("OpenCNAM's Professional Tier lets you do as many real-time CNAM queries as you want, for a small fee. This is recommended for business users.")?></span></a>
                            </td>
                            <td>
<?php
    $checked = ($thisItem['opencnam_account_sid'] && $thisItem['opencnam_auth_token'])?' checked="checked" ':'';
?>
<div class='field'><input type='checkbox' class='switch' id='opencnam_professional_tier' name='opencnam_professional_tier' value='1' <?php echo $checked;?> tabindex='<?php echo ++$tabindex;?>'/><label style='height:auto; line-height:2em; padding-left:3em;' for='opencnam_professional_tier'>&nbsp;</label></div>

                            </td>
                        </tr>
                        <tr class='opencnam_pro'>
                            <td width="50%">
                                <a href="#" class="info"><?php echo __("Account SID")?><span><?php echo __("Your OpenCNAM Account SID. This can be found on your OpenCNAM dashboard page: https://www.opencnam.com/dashboard")?></span></a>
                            </td>
                            <td>
                                <input class="w100 input" type="text" id="opencnam_account_sid" name="opencnam_account_sid" value="<?php echo (isset($thisItem['opencnam_account_sid']) ? $thisItem['opencnam_account_sid'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
                        </tr>
                        <tr class='opencnam_pro'>
                            <td>
                                <a href="#" class="info"><?php echo __("Auth Token")?><span><?php echo __("Your OpenCNAM Auth Token. This can be found on your OpenCNAM dashboard page: https://www.opencnam.com/dashboard")?></span></a>
                            </td>
                            <td>
                                <input class="w100 input" type="text" id="opencnam_auth_token" name="opencnam_auth_token" value="<?php echo (isset($thisItem['opencnam_auth_token']) ? $thisItem['opencnam_auth_token'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        <tr id="http" style="display: none">
            <td colspan="2">
                <div>
                    <h5>HTTP(S)</h5>
                    <table class='table is-borderless is-narrow notfixed'>
                        <tr>
                            <td width="50%">
                                <a href="#" class="info"><?php echo __("Host")?><span><?php echo __("Host name or IP address")?></span></a>
                            </td>
                            <td>
                                <input class="w100 input" type="text" id="http_host" name="http_host" value="<?php echo (isset($thisItem['http_host']) ? $thisItem['http_host'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a href="#" class="info"><?php echo __("Port")?><span><?php echo __("Port HTTP server is listening at (default 80)")?></span></a>
                            </td>
                            <td>
                                <input class="w100 input" type="text" name="http_port" value="<?php echo (isset($thisItem['http_port']) ? $thisItem['http_port'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a href="#" class="info"><?php echo __("Username")?><span><?php echo __("Username to use in HTTP authentication")?></span></a>
                            </td>
                            <td>
                                <input class="w100 input" type="text" name="http_username" value="<?php echo (isset($thisItem['http_username']) ? $thisItem['http_username'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a href="#" class="info"><?php echo __("Password")?><span><?php echo __("Password to use in HTTP authentication")?></span></a>
                            </td>
                            <td>
                                <input class="w100 input" type="text" name="http_password" value="<?php echo (isset($thisItem['http_password']) ? $thisItem['http_password'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a href="#" class="info"><?php echo __("Path")?><span><?php echo __("Path of the file to GET<br/>e.g.: /cidlookup.php")?></span></a>
                            </td>
                            <td>
                                <input class="w100 input" type="text" name="http_path" value="<?php echo (isset($thisItem['http_path']) ? $thisItem['http_path'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a href="#" class="info"><?php echo __("Query")?><span><?php echo __("Query string, special token '[NUMBER]' will be replaced with caller number<br/>e.g.: number=[NUMBER]&source=crm")?></span></a>
                            </td>
                            <td>
                                <input class="w100 input" type="text" name="http_query" value="<?php echo (isset($thisItem['http_query']) ? $thisItem['http_query'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        <tr id="mysql" style="display: none">
            <td colspan="2">
                <div>
                    <h5><?php echo __("MySQL") ?></h5>
                    <table class='table is-borderless is-narrow notfixed'>
                        <tr>
                            <td width="50%">
                                <a href="#" class="info"><?php echo __("Host")?><span><?php echo __("MySQL Host")?></span></a>
                            </td>
                            <td>
                                <input class="w100 input" type="text" id="mysql_host" name="mysql_host" value="<?php echo (isset($thisItem['mysql_host']) ? $thisItem['mysql_host'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a href="#" class="info"><?php echo __("Database")?><span><?php echo __("Database name")?></span></a>
                            </td>
                            <td>
                                <input class="w100 input" type="text" id="mysql_dbname" name="mysql_dbname" value="<?php echo (isset($thisItem['mysql_dbname']) ? $thisItem['mysql_dbname'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a href="#" class="info"><?php echo __("Query")?><span><?php echo __("Query, special token '[NUMBER]' will be replaced with caller number<br/>e.g.: SELECT name FROM phonebook WHERE number LIKE '%[NUMBER]%'")?></span></a>
                            </td>
                            <td>
                                <input class="w100 input" type="text" id="mysql_query" name="mysql_query" value="<?php echo (isset($thisItem['mysql_query']) ? $thisItem['mysql_query'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a href="#" class="info"><?php echo __("Username")?><span><?php echo __("MySQL Username")?></span></a>
                            </td>
                            <td>
                                <input class="w100 input" type="text" id="mysql_username" name="mysql_username" value="<?php echo (isset($thisItem['mysql_username']) ? $thisItem['mysql_username'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a href="#" class="info"><?php echo __("Password")?><span><?php echo __("MySQL Password")?></span></a>
                            </td>
                            <td>
                                <input class="w100 input" type="text" id="mysql_password" name="mysql_password" value="<?php echo (isset($thisItem['mysql_password']) ? $thisItem['mysql_password'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <a href="#" class="info"><?php echo __("Character Set")?><span><?php echo __("MySQL Character Set. Leave blank for MySQL default latin1")?></span></a>
                            </td>
                            <td>
                                <input class="w100 input" type="text" id="mysql_charset" name="mysql_charset" value="<?php echo (isset($thisItem['mysql_charset']) ? $thisItem['mysql_charset'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        <tr id="sugarcrm" style="display: none">
            <td colspan="2">
                <div>
                    <h5><?php echo __("SugarCRM") ?></h5>
                    <table class='table is-borderless is-narrow notfixed'>
                      <tr>
                          <td colspan="2">
                              <?php echo __("Not yet implemented")?>
                          </td>
                      </tr>
                    </table>
                </div>
            </td>
        </tr>
        <tr id="superfecta" style="display: none">
            <td colspan="2">
                <div>
                <h5><?php echo __("Superfecta") ?></h5>
                    <table class='table is-borderless is-narrow notfixed'>
                      <tr>
                          <td colspan="2">
                              <?php echo __("Not yet implemented")?>
                          </td>
                      </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</form>
<script>
<?php echo js_display_confirmation_toasts(); ?>
</script>
</div> <!-- end div content, be sure to include script tags before -->
<?php echo form_action_bar($extdisplay); ?>
