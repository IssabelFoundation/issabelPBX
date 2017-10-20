<!-- right side menu -->
<div class="rnav"><ul>
    <li><a id="<?php echo ($itemid=='' ? 'current':'') ?>" href="config.php?display=cidlookup"><?php echo _("Add CID Lookup Source")?></a></li>
    <?php
    if (isset($cidsources)) {
    	foreach ($cidsources as $cidsource) {
    		if ($cidsource['cidlookup_id'] != 0) {
                ?>
                <li><a id="<?php echo ($itemid==$cidsource['cidlookup_id'] ? 'current':'')?>" href="config.php?display=cidlookup&amp;itemid=<?php echo urlencode($cidsource['cidlookup_id'])?>"><?php echo $cidsource['description']?></a></li>
                <?php
			}
    	}
    }
    ?>
    </ul>
</div>
<div id="cid_message"><?php echo $message?></div>
<h2><?php echo ($itemid ? sprintf(_("Source: %s (id %s)"),$thisItem_description,$itemid) : _("Add Source")); ?></h2>
<p style="width: 80%"><?php echo ($itemid ? '' : _("A Lookup Source let you specify a source for resolving numeric CallerIDs of incoming calls, you can then link an Inbound route to a specific CID source. This way you will have more detailed CDR reports with information taken directly from your CRM. You can also install the phonebook module to have a small number <-> name association. Pay attention, name lookup may slow down your PBX")); ?></p>

<?php if ($itemid){ ?>
    <a href='config.php?display=cidlookup&amp;action=delete&amp;itemid=<?php echo $itemid?>'><img src='images/user_delete.png'> <?php echo _("Delete CID Lookup source")?></a>
    <?php if($dids_using) {?>
        <small><?php sprintf(_("There are %s DIDs using this source that will no longer have lookups if deleted."),$dids_using)?></small>
    <?php } ?>
<?php } ?>

<form autocomplete="off" name="edit" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return edit_onsubmit();">
	<input type="hidden" name="display" value="cidlookup">
	<input type="hidden" name="action" value="<?php echo ($itemid ? 'edit' : 'add') ?>">
	<input type="hidden" name="deptname" value="<?php echo $_SESSION["AMP_user"]->_deptname ?>">
    <?php if ($itemid){ ?>
    		<input type="hidden" name="itemid" value="<?php echo $itemid; ?>">
    <?php } ?>
	<table>
	    <tr>
            <td colspan="2">
                <h5><?php echo ($itemid ? _("Edit Source") : _("Add Source")) ?></h5><hr>
            </td>
        </tr>
	    <tr>
		    <td>
                <a href="#" class="info"><?php echo _("Source Description:")?><span><?php echo _("Enter a description for this source.")?></span></a>
            </td>
		    <td>
                <input type="text" id="form_description" name="description" value="<?php echo $thisItem_description; ?>" tabindex="<?php echo ++$tabindex;?>">
            </td>
	    </tr>
	    <tr>
		    <td>
                <a href="#" class="info"><?php echo _("Source type:")?>
                    <span><?php echo _("Select the source type, you can choose between:<ul>
                    <li>OpenCNAM: Use OpenCNAM [https://www.opencnam.com/]</li>
                    <li>Internal: use astdb as lookup source, use phonebook module to populate it</li>
                    <li>ENUM: Use DNS to lookup caller names, it uses ENUM lookup zones as configured in enum.conf</li>
                    <li>HTTP: It executes an HTTP GET passing the caller number as argument to retrieve the correct name</li>
                    <li>MySQL: It queries a MySQL database to retrieve caller name</li>
                    </ul>")?></span>
                </a>
            </td>
    		<td>
    			<select id="sourcetype" name="sourcetype" tabindex="<?php echo ++$tabindex;?>">
    				<option value="opencnam" <?php echo ($thisItem['sourcetype'] == 'opencnam' ? 'selected' : '')?>><?php echo _("OpenCNAM")?></option>
    				<option value="internal" <?php echo ($thisItem['sourcetype'] == 'internal' ? 'selected' : '')?>><?php echo _("Internal")?></option>
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
                <a href="#" class="info"><?php echo _("Cache results:")?><span><?php echo _("Decide whether or not cache the results to astDB; it will overwrite present values. It does not affect Internal source behavior")?></span></a>
            </td>
		    <td>
                <input type="checkbox" name="cache" value="1" <?php echo ($thisItem['cache'] == 1 ? 'checked' : ''); ?> tabindex="<?php echo ++$tabindex;?>">
            </td>
	    </tr>
    	<tr>
    		<td colspan="2">
    			<div id="opencnam" style="display: none">
    				<h5><?php echo _("OpenCNAM") ?></h5><hr>
    				<p style="display:block;max-width:345px;max-height:40px;margin-left:auto;margin-right:auto;margin-bottom:40px;font-style:italic;font-size:12px;"><b>NOTE:</b> OpenCNAM's Hobbyist Tier (default) only allows you to do 60 cached CallerID lookups per hour. If you get more than 60 incoming calls per hour, or want real-time CallerID information (more accurate), you should use the Professional Tier.</p>
    				<p style="display:block;max-width:345px;max-height:40px;margin-left:auto;margin-right:auto;margin-bottom:20px;font-style:italic;font-size:12px;">If you'd like to create an OpenCNAM Professional Tier account, you can do so on their website: <a href="https://www.opencnam.com/register" target="_blank">https://www.opencnam.com/register</a></p>
    				<table cellpadding="2" cellspacing="0" width="100%">
    					<tr>
    						<td width="50%">
                                <a href="#" class="info"><?php echo _("Use Professional Tier?")?><span><?php echo _("OpenCNAM's Professional Tier lets you do as many real-time CNAM queries as you want, for a small fee. This is recommended for business users.")?></span></a>
                            </td>
    						<td>
                                <input type="checkbox" id="opencnam_professional_tier" name="opencnam_professional_tier" value="1" <?php echo ($thisItem['opencnam_account_sid'] && $thisItem['opencnam_auth_token'] ? 'checked' : ''); ?> tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    					<tr class='opencnam_pro'>
    						<td width="50%">
                                <a href="#" class="info"><?php echo _("Account SID:")?><span><?php echo _("Your OpenCNAM Account SID. This can be found on your OpenCNAM dashboard page: https://www.opencnam.com/dashboard")?></span></a>
                            </td>
    						<td>
                                <input type="text" id="opencnam_account_sid" name="opencnam_account_sid" value="<?php echo (isset($thisItem['opencnam_account_sid']) ? $thisItem['opencnam_account_sid'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    					<tr class='opencnam_pro'>
    						<td>
                                <a href="#" class="info"><?php echo _("Auth Token:")?><span><?php echo _("Your OpenCNAM Auth Token. This can be found on your OpenCNAM dashboard page: https://www.opencnam.com/dashboard")?></span></a>
                            </td>
    						<td>
                                <input type="text" id="opencnam_auth_token" name="opencnam_auth_token" value="<?php echo (isset($thisItem['opencnam_auth_token']) ? $thisItem['opencnam_auth_token'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    				</table>
    			</div>
    		</td>
    	</tr>
    	<tr>
    		<td colspan="2">
    			<div id="http" style="display: none">
    				<table cellpadding="2" cellspacing="0" width="100%">
    					<tr>
                            <td colspan="2">
                                <h5>HTTP(S)</h5><hr>
                            </td>
                        </tr>
    					<tr>
    						<td width="50%">
                                <a href="#" class="info"><?php echo _("Host:")?><span><?php echo _("Host name or IP address")?></span></a>
                            </td>
    						<td>
                                <input type="text" id="http_host" name="http_host" value="<?php echo (isset($thisItem['http_host']) ? $thisItem['http_host'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    					<tr>
    						<td>
                                <a href="#" class="info"><?php echo _("Port:")?><span><?php echo _("Port HTTP server is listening at (default 80)")?></span></a>
                            </td>
    						<td>
                                <input type="text" name="http_port" value="<?php echo (isset($thisItem['http_port']) ? $thisItem['http_port'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    					<tr>
    						<td>
                                <a href="#" class="info"><?php echo _("Username:")?><span><?php echo _("Username to use in HTTP authentication")?></span></a>
                            </td>
    						<td>
                                <input type="text" name="http_username" value="<?php echo (isset($thisItem['http_username']) ? $thisItem['http_username'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    					<tr>
    						<td>
                                <a href="#" class="info"><?php echo _("Password:")?><span><?php echo _("Password to use in HTTP authentication")?></span></a>
                            </td>
    						<td>
                                <input type="text" name="http_password" value="<?php echo (isset($thisItem['http_password']) ? $thisItem['http_password'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    					<tr>
    						<td>
                                <a href="#" class="info"><?php echo _("Path:")?><span><?php echo _("Path of the file to GET<br/>e.g.: /cidlookup.php")?></span></a>
                            </td>
    						<td>
                                <input type="text" name="http_path" value="<?php echo (isset($thisItem['http_path']) ? $thisItem['http_path'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    					<tr>
    						<td>
                                <a href="#" class="info"><?php echo _("Query:")?><span><?php echo _("Query string, special token '[NUMBER]' will be replaced with caller number<br/>e.g.: number=[NUMBER]&source=crm")?></span></a>
                            </td>
    						<td>
                                <input type="text" name="http_query" value="<?php echo (isset($thisItem['http_query']) ? $thisItem['http_query'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    				</table>
    			</div>
    		</td>
    	</tr>
    	<tr>
    		<td colspan="2">
    			<div id="mysql" style="display: none">
    				<table cellpadding="2" cellspacing="0" width="100%">
    					<tr>
                            <td colspan="2">
                                <h5><?php echo _("MySQL") ?></h5><hr>
                            </td>
                        </tr>
    					<tr>
    						<td width="50%">
                                <a href="#" class="info"><?php echo _("Host:")?><span><?php echo _("MySQL Host")?></span></a>
                            </td>
    						<td>
                                <input type="text" id="mysql_host" name="mysql_host" value="<?php echo (isset($thisItem['mysql_host']) ? $thisItem['mysql_host'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    					<tr>
    						<td>
                                <a href="#" class="info"><?php echo _("Database:")?><span><?php echo _("Database name")?></span></a>
                            </td>
    						<td>
                                <input type="text" id="mysql_dbname" name="mysql_dbname" value="<?php echo (isset($thisItem['mysql_dbname']) ? $thisItem['mysql_dbname'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    					<tr>
    						<td>
                                <a href="#" class="info"><?php echo _("Query:")?><span><?php echo _("Query, special token '[NUMBER]' will be replaced with caller number<br/>e.g.: SELECT name FROM phonebook WHERE number LIKE '%[NUMBER]%'")?></span></a>
                            </td>
    						<td>
                                <input type="text" id="mysql_query" name="mysql_query" value="<?php echo (isset($thisItem['mysql_query']) ? $thisItem['mysql_query'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    					<tr>
    						<td>
                                <a href="#" class="info"><?php echo _("Username:")?><span><?php echo _("MySQL Username")?></span></a>
                            </td>
    						<td>
                                <input type="text" id="mysql_username" name="mysql_username" value="<?php echo (isset($thisItem['mysql_username']) ? $thisItem['mysql_username'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    					<tr>
    						<td>
                                <a href="#" class="info"><?php echo _("Password:")?><span><?php echo _("MySQL Password")?></span></a>
                            </td>
    						<td>
                                <input type="text" id="mysql_password" name="mysql_password" value="<?php echo (isset($thisItem['mysql_password']) ? $thisItem['mysql_password'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    					<tr>
    						<td>
                                <a href="#" class="info"><?php echo _("Character Set:")?><span><?php echo _("MySQL Character Set. Leave blank for MySQL default latin1")?></span></a>
                            </td>
    						<td>
                                <input type="text" id="mysql_charset" name="mysql_charset" value="<?php echo (isset($thisItem['mysql_charset']) ? $thisItem['mysql_charset'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>">
                            </td>
    					</tr>
    				</table>
    			</div>
    		</td>
    	</tr>
    	<tr>
    		<td colspan="2">
    			<div id="sugarcrm" style="display: none">
    				<table cellpadding="2" cellspacing="0" width="100%">
    				  <tr>
                          <td colspan="2">
                              <h5><?php echo _("SugarCRM") ?></h5><hr>
                          </td>
                      </tr>
    				  <tr>
                          <td colspan="2">
                              <?php echo _("Not yet implemented")?>
                          </td>
                      </tr>
    				</table>
    			</div>
    		</td>
    	</tr>
    	<tr>
    		<td colspan="2">
    			<div id="superfecta" style="display: none">
    				<table cellpadding="2" cellspacing="0" width="100%">
    				  <tr>
                          <td colspan="2">
                              <h5><?php echo _("Superfecta") ?></h5><hr>
                          </td>
                      </tr>
    				  <tr>
                          <td colspan="2">
                              <?php echo _("Not yet implemented")?>
                          </td>
                      </tr>
    				</table>
    			</div>
    		</td>
    	</tr>
    	<tr>
    		<td colspan="2">
                <br>
                <h6><input name="submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>"></h6>
            </td>
    	</tr>
	</table>
</form>
