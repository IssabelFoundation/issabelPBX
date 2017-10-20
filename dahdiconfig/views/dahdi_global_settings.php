<form id="form-globalsettings" action="config.php?quietmode=1&amp;handler=file&amp;module=dahdiconfig&amp;file=ajax.html.php&amp;type=globalsettingssubmit">
<div id="global">
    <h2><?php echo _('Global Settings')?></h2>
    <h3><?php echo _('This edits all settings in chan_dahdi.conf')?></h3>
    <hr />
    <table width="100%" style="text-align:left;">
        <tr>
            <td style="width:10px;">
                <label for="language"><a href="#" class="info"><?php echo _('Select Language')?>:<span><?php echo _('Specify the language') ?></span></a></label>
            </td>
            <td>
                <select id="language" name="language">
            	    <option value="en" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'en'); ?>><?php echo _('English') ?></option>
            		<option value="au" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'au'); ?>><?php echo _('Australian') ?></option>
            		<option value="fr" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'fr'); ?>><?php echo _('French') ?></option>
					<option value="nl" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'nl'); ?>><?php echo _('Dutch') ?></option>
					<option value="de" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'de'); ?>><?php echo _('German') ?></option>
            		<option value="fi" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'fi'); ?>><?php echo _('Finnish') ?></option>
            		<option value="es" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'es'); ?>><?php echo _('Spanish') ?></option>
            		<option value="jp" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'jp'); ?>><?php echo _('Japanese') ?></option>
            		<option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'no'); ?>><?php echo _('Norwegian') ?></option>
            		<option value="it" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'it'); ?>><?php echo _('Italian') ?></option>
            		<option value="gr" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'gr'); ?>><?php echo _('Greek') ?></option>
            		<option value="tw" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'tw'); ?>><?php echo _('Taiwanese') ?></option>
            		<option value="se" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'se'); ?>><?php echo _('Swedish') ?></option>
            		<option value="il" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'il'); ?>><?php echo _('Israeli Hebrew') ?></option>
            		<option value="br" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'br'); ?>><?php echo _('Brazilian') ?></option>
            		<option value="hu" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'hu'); ?>><?php echo _('Hungarian') ?></option>
            		<option value="lt" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'lt'); ?>><?php echo _('Lithuanian') ?></option>
            		<option value="pl" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'pl'); ?>><?php echo _('Polish') ?></option>
            		<option value="za" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'za'); ?>><?php echo _('South African') ?></option>
            		<option value="pt" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'pt'); ?>><?php echo _('Portuguese') ?></option>
            		<option value="ee" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'ee'); ?>><?php echo _('Estonian') ?></option>
            		<option value="in" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'in'); ?>><?php echo _('Hindi') ?></option>
            		<option value="cn" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'cn'); ?>><?php echo _('Chinese') ?></option>
            		<option value="ar" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'ar'); ?>><?php echo _('Argentine Spanish') ?></option>
            		<option value="ru" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'ru'); ?>><?php echo _('Russian') ?></option>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="busydetect"><a href="#" class="info"><?php echo _('Enable Busy Detect')?>:<span><?php echo _('On trunk interfaces (FXS) and E&M interfaces (E&M, Wink, Feature Group D etc) it can be useful to perform busy detection either in an effort to detect hangup or for detecting busies');?>.<br/><?php echo _('This enables listening for the beep-beep busy pattern')?>.</span></a></label>
        	</td>
        	<td>
        	    <select id="busydetect" name="busydetect">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('busydetect'),'yes'); ?>><?php echo _('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('busydetect'),'no'); ?>><?php echo _('No')?></option>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="busycount"><a href="#" class="info"><?php echo _('Busy Detect Count')?>:<span> <?php echo _('If busydetect is enabled, it is also possible to specify how many busy tones to wait for before hanging up.  The default is 3, but it might be safer to set to 6 or even 8.  Mind that the higher the number, the more time that will be needed to hangup a channel, but lowers the probability that you will get random hangups')?>.</span></a></label>
            </td>
            <td>
                <select id="busycount" name="busycount">
            	    <?php for($i=0; $i<=100; $i++) { ?>
            	    <option value="<?php echo $i; ?>" <?php echo set_default($dahdi_cards->get_globalsettings('busycount'),$i); ?>><?php echo $i; ?></option>
            	    <?php } ?>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
            	<label for="usecallerid"><a href="#" class="info"><?php echo _('Use Caller ID')?>:<span><?php echo _('Whether or not to use caller ID')?></span></a></label>
            </td>
            <td>
                <select id="usecallerid" name="usecallerid">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('usecallerid'),'yes'); ?>><?php echo _('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('usecallerid'),'no'); ?>><?php echo _('No')?></option>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="callwaiting"><a href="#" class="info"><?php echo _('Enable Call Waiting')?><span><?php echo _('Whether or not to enable call waiting on internal extensions. With this set to "yes", busy extensions will hear the call-waiting tone, and can use hook-flash to switch between callers. The Dial() app will not return the "BUSY" result for extensions.')?></span></a></label>
            </td>
            <td>
                <select id="callwaiting" name="callwaiting">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('callwaiting'),'yes'); ?>><?php echo _('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('callwaiting'),'no'); ?>><?php echo _('No')?></option>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="usecallingpres"><a href="#" class="info"><?php echo _('Use Caller ID Presentation')?><span><?php echo _('Whether or not to use the caller ID presentation for the outgoing call that the calling switch is sending')?>.</span></a></label>
            </td>
            <td>
                <select id="usecallingpres" name="usecallingpres">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('usecallingpres'),'yes'); ?>><?php echo _('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('usecallingpres'),'no'); ?>><?php echo _('No')?></option>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="threewaycalling"><a href="#" class="info"><?php echo _('Enable Three Way Calling')?><span><?php echo _('Support three-way calling')?></span></a></label>
            </td>
            <td>
                <select id="threewaycalling" name="threewaycalling">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('threewaycalling'),'yes'); ?>><?php echo _('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('threewaycalling'),'no'); ?>><?php echo _('No')?></option>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="transfer"><a href="#" class="info"><?php echo _('Enable Transfer')?><span><strong><?php echo _('For FXS ports (either direct analog or over T1/E1)')?>:</strong><br/>
                    <?php echo _('Support flash-hook call transfer (requires three way calling)')?> <br/>
                    <?php echo _('Also enables call parking (overrides the "canpark" parameter)')?> <br/>
                    <br/>
                    <strong><?php echo _('For digital ports using ISDN PRI protocols')?>:</strong><br/>
                    <?php echo _('Support switch-side transfer (called 2BCT, RLT or other names)')?><br/>
                    <?php echo _('This setting must be enabled on both ports involved, and the "facilityenable" setting must also be enabled to allow sending the transfer to the ISDN switch, since it sent in a FACILITY message')?>.</span></a></label>
            </td>
            <td>
               	<select id="transfer" name="transfer">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('transfer'),'yes'); ?>><?php echo _('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('transfer'),'no'); ?>><?php echo _('No')?></option>
            	</select>
            </td>
        </tr> 
        <tr>
            <td style="width:10px;">
                <label for="cancallforward"><a href="#" class="info"><?php echo _('Enable Call Forwarding')?><span><?php echo _('Support call forward variable')?></span></a></label>
            </td>
            <td>
                <select id="cancallforward" name="cancallforward">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('cancallforward'),'yes'); ?>><?php echo _('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('cancallforward'),'no'); ?>><?php echo _('No')?></option>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="callreturn"><a href="#" class="info"><?php echo _('Enable Call Return')?><span><?php echo _('Whether or not to support Call Return (*69, if your dialplan doesn\'t catch this first)')?></span></a></label>
            </td>
            <td>
               	<select id="callreturn" name="callreturn">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('callreturn'),'yes'); ?>><?php echo _('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('callreturn'),'no'); ?>><?php echo _('No')?></option>
            	</select> 
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="echocancel"><a href="#" class="info"><?php echo _('Enable Echo Canceling')?><span><?php echo _('Enable echo cancellation')?><br/>
                    <?php echo _('Note that if any of your DAHDI cards have hardware echo cancelers, then this setting only turns them on and off. There are no special settings required for hardware echo cancelers; when present and enabled in their kernel modules, they take precedence over the software echo canceler compiled into DAHDI automatically')?>.</span></a></label>
            </td>
            <td>
                <select id="echocancel" name="echocancel">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('echocancel'),'yes'); ?>><?php echo _('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('echocancel'),'no'); ?>><?php echo _('No')?></option>
            	</select>
            </td>  
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="echocancelwhenbridged"><a href="#" class="info"><?php echo _('Enable EC when bridged')?><span><?php echo _('Generally, it is not necessary (and in fact undesirable) to echo cancel when the circuit path is entirely TDM.  You may, however, change this behavior by enabling the echo canceler during pure TDM bridging below')?>.</span></a></label>
            </td>
            <td>
                <select id="echocancelwhenbridged" name="echocancelwhenbridged">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('echocancelwhenbridged'),'yes'); ?>><?php echo _('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('echocancelwhenbridged'),'no'); ?>><?php echo _('No')?></option>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="echotraining"><a href="#" class="info"><?php echo _('Enable Echo Training')?><span><?php echo _('In some cases, the echo canceller doesn\'t train quickly enough and there is echo at the beginning of the call.  Enabling echo training will cause DAHDI to briefly mute the channel, send an impulse, and use the impulse response to pre-train the echo canceller so it can start out with a much closer idea of the actual echo.  Value may be "yes", "no", or a number of milliseconds to delay before training (default = 400)')?><br />
                <br/>
                <strong><?php echo _('WARNING')?>:</strong><?php echo _('In some cases this option can make echo worse!  If you are trying to debug an echo problem, it is worth checking to see if your echo is better with the option set to yes or no.  Use whatever setting gives the best results')?>.<br/>
                <br/>
                <?php echo _('Note that these parameters do not apply to hardware echo cancellers')?>.</span></a></label>
            </td>
            <td>
                <input type="text" name="echotraining" id="echotraining" value="<?php echo $dahdi_cards->get_globalsettings('echotraining'); ?>">
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="immediate"><a href="#" class="info"><?php echo _('Answer Immediately')?><span><?php echo _('Specify whether the channel should be answered immediately or if the simple switch should provide dialtone, read digits, etc')?>.<br/>
                    <?php echo _('Note: If yes the dialplan execution will always start at extension \'s\' priority 1 regardless of the dialed number!')?></span></a></label>
            </td>
            <td>
                <select id="immediate" name="immediate">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('immediate'),'yes'); ?>><?php echo _('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('immediate'),'no'); ?>><?php echo _('No')?></option>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="faxdetect"><a href="#" class="info"><?php echo _('Fax Detection')?><span><?php echo _('For fax detection')?></span></a></label>
            </td>
            <td>
              	<select id="faxdetect" name="faxdetect">
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('faxdetect'),'no'); ?>><?php echo _('No')?></option>
            	    <option value="incoming" <?php echo set_default($dahdi_cards->get_globalsettings('faxdetect'),'incoming'); ?>><?php echo _('Incoming')?></option>
            	    <option value="outgoing" <?php echo set_default($dahdi_cards->get_globalsettings('faxdetect'),'outgoing'); ?>><?php echo _('Outgoing')?></option>
            	    <option value="both" <?php echo set_default($dahdi_cards->get_globalsettings('faxdetect'),'both'); ?>><?php echo _('Both')?></option>
            	</select>
            </td>
        </tr> 
        <tr>
            <td style="width:10px;"> 
                <label for="rxgain"><a href="#" class="info"><?php echo _('Receive Gain')?><span><?php echo _('The values are in db (decibels). A positive number increases the volume level on a channel, and a negative value decreases volume level')?>.</span></a></label>
            </td>
            <td>
                <input type="text" name="rxgain" id="rxgain" value="<?php echo $dahdi_cards->get_globalsettings('rxgain'); ?>">
            </td>
        </tr>
        <tr>
            <td style="width:10px;"> 
                <label for="txgain"><a href="#" class="info"><?php echo _('Transmit Gain')?><span><?php echo _('The values are in db (decibels). A positive number increases the volume level on a channel, and a negative value decreases volume level')?>.</span></a></label>
            </td>
            <td>
                <input type="text" name="txgain" id="txgain" value="<?php echo $dahdi_cards->get_globalsettings('txgain'); ?>">
            </td>
        </tr>
    </table>
    <?php
    $gs = $dahdi_cards->get_all_globalsettings();
    $dh_key = '';
    $dh_val = '';
    foreach($gs as $key => $value) {
        if(!in_array($key,$dahdi_cards->original_global) && !strpos($key, 'checkbox')) {
            $dh_key = $key;
            $dh_val = $value;
            unset($gs[$key]);
            break;
        }
    }
    ?>
    <table width="100%" style="text-align:left;">
        <tr id="dh_global_additional_0">
            <td style="width:10px;vertical-align:top;">
                <label><?php echo _('Other Global Dahdi Settings')?>: </label>
            </td>
            <td style="vertical-align:middle;">
                <a href="#" onclick="dh_global_delete_field(0)"><img height="10px" src="images/trash.png"></a>
                <input type="hidden" name="dh_global_add[]" value="0" />
                <input type="hidden" id="dh_global_origsetting_key_0" name="dh_global_origsetting_key_0" value="<?php echo $dh_key?>" />
                <input id="dh_global_setting_key_0" name="dh_global_setting_key_0" value="<?php echo $dh_key?>" /> =
                <input id="dh_global_setting_value_0" name="dh_global_setting_value_0" value="<?php echo $dh_val?>" /> <br />
            </td>
        </tr>
        <?php
        $a = 1;
        foreach($gs as $key => $value) {
            if(!in_array($key,$dahdi_cards->original_global)) {
                ?>
                <tr id="dh_global_additional_<?php echo $a?>">
                    <td style="width:10px;vertical-align:top;">
                    </td>
                    <td style="vertical-align:middle;">
                        <a href="#" onclick="dh_global_delete_field(<?php echo $a?>)"><img height="10px" src="images/trash.png"></a>
                        <input type="hidden" name="dh_global_add[]" value="<?php echo $a?>" />
                        <input type="hidden" id="dh_global_origsetting_key_<?php echo $a?>"name="dh_global_origsetting_key_<?php echo $a?>" value="<?php echo $key?>" />
                        <input id="dh_global_setting_key_<?php echo $a?>" name="dh_global_setting_key_<?php echo $a?>" value="<?php echo $key?>" /> =
                        <input id="dh_global_setting_value_<?php echo $a?>" name="dh_global_setting_value_<?php echo $a?>" value="<?php echo $value?>" /> <br />
                    </td>
                </tr>
                <?php
                $a++;
            }
        }
        ?>
        <tr id="dh_global_add">
            <td> 
            </td>
            <td>
                <a style="cursor: pointer;" onclick="dh_global_add_field(<?php echo $a?>)"><img src="assets/dahdiconfig/images/add.png"></a>
            </td>
        </tr>
    </table>
</div>
</form>
