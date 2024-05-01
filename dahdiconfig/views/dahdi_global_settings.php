<div class="modal animate__animated animate__fadeIn" id="globalsettings">
  <div class="modal-background"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title" style="margin-bottom:0;"><?php echo __('Global Settings')?></p>
      <button class="delete" aria-label="close"></button>
    </header>
    <section class="modal-card-body">
 


<form id="form-globalsettings" action="config.php?quietmode=1&amp;handler=file&amp;module=dahdiconfig&amp;file=ajax.html.php&amp;type=globalsettingssubmit">
    <div class='columns mb-5'><div class='column'>
        <div class='is-size-7'><?php echo sprintf(__('This edits all settings in %s'),'chan_dahdi.conf')?></div>
    </div></div>

        <div class='columns'>
            <div class='column'>
                <a href="#" class="info"><?php echo __('Select Language')?><span><?php echo __('Specify the language') ?></span></a>

            </div>
            <div class='column'>
                <select class="componentSelect" id="language" name="language">
            	    <option value="en" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'en'); ?>><?php echo __('English') ?></option>
            		<option value="au" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'au'); ?>><?php echo __('Australian') ?></option>
            		<option value="fr" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'fr'); ?>><?php echo __('French') ?></option>
					<option value="nl" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'nl'); ?>><?php echo __('Dutch') ?></option>
					<option value="de" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'de'); ?>><?php echo __('German') ?></option>
            		<option value="fi" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'fi'); ?>><?php echo __('Finnish') ?></option>
            		<option value="es" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'es'); ?>><?php echo __('Spanish') ?></option>
            		<option value="jp" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'jp'); ?>><?php echo __('Japanese') ?></option>
            		<option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'no'); ?>><?php echo __('Norwegian') ?></option>
            		<option value="it" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'it'); ?>><?php echo __('Italian') ?></option>
            		<option value="gr" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'gr'); ?>><?php echo __('Greek') ?></option>
            		<option value="tw" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'tw'); ?>><?php echo __('Taiwanese') ?></option>
            		<option value="se" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'se'); ?>><?php echo __('Swedish') ?></option>
            		<option value="il" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'il'); ?>><?php echo __('Israeli Hebrew') ?></option>
            		<option value="br" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'br'); ?>><?php echo __('Brazilian') ?></option>
            		<option value="hu" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'hu'); ?>><?php echo __('Hungarian') ?></option>
            		<option value="lt" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'lt'); ?>><?php echo __('Lithuanian') ?></option>
            		<option value="pl" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'pl'); ?>><?php echo __('Polish') ?></option>
            		<option value="za" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'za'); ?>><?php echo __('South African') ?></option>
            		<option value="pt" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'pt'); ?>><?php echo __('Portuguese') ?></option>
            		<option value="ee" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'ee'); ?>><?php echo __('Estonian') ?></option>
            		<option value="in" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'in'); ?>><?php echo __('Hindi') ?></option>
            		<option value="cn" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'cn'); ?>><?php echo __('Chinese') ?></option>
            		<option value="ar" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'ar'); ?>><?php echo __('Argentine Spanish') ?></option>
            		<option value="ru" <?php echo set_default($dahdi_cards->get_globalsettings('language'),'ru'); ?>><?php echo __('Russian') ?></option>
            	</select>
            </div>
        </div>
        <div class='columns'>
            <div class='column'>
                <a href="#" class="info"><?php echo __('Enable Busy Detect')?><span><?php echo __('On trunk interfaces (FXS) and E&M interfaces (E&M, Wink, Feature Group D etc) it can be useful to perform busy detection either in an effort to detect hangup or for detecting busies');?>.<br/><?php echo __('This enables listening for the beep-beep busy pattern')?>.</span></a>
        	</div>
        	<div class='column'>
        	    <select class="componentSelect" id="busydetect" name="busydetect">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('busydetect'),'yes'); ?>><?php echo __('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('busydetect'),'no'); ?>><?php echo __('No')?></option>
            	</select>
            </div>
        </div>
        <div class='columns'>
            <div class='column'>
                <a href="#" class="info"><?php echo __('Busy Detect Count')?><span> <?php echo __('If busydetect is enabled, it is also possible to specify how many busy tones to wait for before hanging up.  The default is 3, but it might be safer to set to 6 or even 8.  Mind that the higher the number, the more time that will be needed to hangup a channel, but lowers the probability that you will get random hangups')?>.</span></a>
            </div>
            <div class='column'>
                <select class="componentSelect" id="busycount" name="busycount">
            	    <?php for($i=0; $i<=100; $i++) { ?>
            	    <option value="<?php echo $i; ?>" <?php echo set_default($dahdi_cards->get_globalsettings('busycount'),$i); ?>><?php echo $i; ?></option>
            	    <?php } ?>
            	</select>
            </div>
        </div>
        <div class='columns'>
           <div class='column'>
            	<a href="#" class="info"><?php echo __('Use Caller ID')?><span><?php echo __('Whether or not to use caller ID')?></span></a>
            </div>
            <div class='column'>
                <select class="componentSelect" id="usecallerid" name="usecallerid">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('usecallerid'),'yes'); ?>><?php echo __('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('usecallerid'),'no'); ?>><?php echo __('No')?></option>
            	</select>
            </div>
        </div>
        <div class='columns'>
            <div class='column'>
                <a href="#" class="info"><?php echo __('Enable Call Waiting')?><span><?php echo __('Whether or not to enable call waiting on internal extensions. With this set to "yes", busy extensions will hear the call-waiting tone, and can use hook-flash to switch between callers. The Dial() app will not return the "BUSY" result for extensions.')?></span></a>
            </div>
            <div class='column'>
                <select class="componentSelect" id="callwaiting" name="callwaiting">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('callwaiting'),'yes'); ?>><?php echo __('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('callwaiting'),'no'); ?>><?php echo __('No')?></option>
            	</select>
            </div>
        </div>
        <div class='columns'>
            <div class='column'>
                <a href="#" class="info"><?php echo __('Use Caller ID Presentation')?><span><?php echo __('Whether or not to use the caller ID presentation for the outgoing call that the calling switch is sending')?>.</span></a>
            </div>
            <div class='column'>
                <select class="componentSelect" id="usecallingpres" name="usecallingpres">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('usecallingpres'),'yes'); ?>><?php echo __('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('usecallingpres'),'no'); ?>><?php echo __('No')?></option>
            	</select>
            </div>
        </div>
        <div class='columns'>
            <div class='column'>
                <a href="#" class="info"><?php echo __('Enable Three Way Calling')?><span><?php echo __('Support three-way calling')?></span></a>
            </div>
            <div class='column'>
                <select class="componentSelect" id="threewaycalling" name="threewaycalling">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('threewaycalling'),'yes'); ?>><?php echo __('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('threewaycalling'),'no'); ?>><?php echo __('No')?></option>
            	</select>
            </div>
        </div>
        <div class='columns'>
            <div class='column'>
                <a href="#" class="info"><?php echo __('Enable Transfer')?><span><strong><?php echo __('For FXS ports (either direct analog or over T1/E1)')?></strong><br/>
                    <?php echo __('Support flash-hook call transfer (requires three way calling)')?> <br/>
                    <?php echo __('Also enables call parking (overrides the "canpark" parameter)')?> <br/>
                    <br/>
                    <strong><?php echo __('For digital ports using ISDN PRI protocols')?></strong><br/>
                    <?php echo __('Support switch-side transfer (called 2BCT, RLT or other names)')?><br/>
                    <?php echo __('This setting must be enabled on both ports involved, and the "facilityenable" setting must also be enabled to allow sending the transfer to the ISDN switch, since it sent in a FACILITY message')?>.</span></a>
            </div>
            <div class='column'>
               	<select class="componentSelect" id="transfer" name="transfer">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('transfer'),'yes'); ?>><?php echo __('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('transfer'),'no'); ?>><?php echo __('No')?></option>
            	</select>
            </div>
        </div> 
        <div class='columns'>
            <div class='column'>
                <a href="#" class="info"><?php echo __('Enable Call Forwarding')?><span><?php echo __('Support call forward variable')?></span></a>
            </div>
            <div class='column'>
                <select class="componentSelect" id="cancallforward" name="cancallforward">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('cancallforward'),'yes'); ?>><?php echo __('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('cancallforward'),'no'); ?>><?php echo __('No')?></option>
            	</select>
            </div>
        </div>
        <div class='columns'>
            <div class='column'>
                <a href="#" class="info"><?php echo __('Enable Call Return')?><span><?php echo __('Whether or not to support Call Return (*69, if your dialplan doesn\'t catch this first)')?></span></a>
            </div>
            <div class='column'>
               	<select class="componentSelect" id="callreturn" name="callreturn">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('callreturn'),'yes'); ?>><?php echo __('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('callreturn'),'no'); ?>><?php echo __('No')?></option>
            	</select> 
            </div>
        </div>
        <div class='columns'>
            <div class='column'>
                <a href="#" class="info"><?php echo __('Enable Echo Canceling')?><span><?php echo __('Enable echo cancellation')?><br/>
                    <?php echo __('Note that if any of your DAHDI cards have hardware echo cancelers, then this setting only turns them on and off. There are no special settings required for hardware echo cancelers; when present and enabled in their kernel modules, they take precedence over the software echo canceler compiled into DAHDI automatically')?>.</span></a>
            </div>
            <div class='column'>
                <select class="componentSelect" id="echocancel" name="echocancel">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('echocancel'),'yes'); ?>><?php echo __('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('echocancel'),'no'); ?>><?php echo __('No')?></option>
            	</select>
            </div>  
        </div>
        <div class='columns'>
            <div class='column'>
                <a href="#" class="info"><?php echo __('Enable EC when bridged')?><span><?php echo __('Generally, it is not necessary (and in fact undesirable) to echo cancel when the circuit path is entirely TDM.  You may, however, change this behavior by enabling the echo canceler during pure TDM bridging below')?>.</span></a>
            </div>
            <div class='column'>
                <select class="componentSelect" id="echocancelwhenbridged" name="echocancelwhenbridged">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('echocancelwhenbridged'),'yes'); ?>><?php echo __('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('echocancelwhenbridged'),'no'); ?>><?php echo __('No')?></option>
            	</select>
            </div>
        </div>
        <div class='columns'>
            <div class='column'>
                <a href="#" class="info"><?php echo __('Enable Echo Training')?><span><?php echo __('In some cases, the echo canceller doesn\'t train quickly enough and there is echo at the beginning of the call.  Enabling echo training will cause DAHDI to briefly mute the channel, send an impulse, and use the impulse response to pre-train the echo canceller so it can start out with a much closer idea of the actual echo.  Value may be "yes", "no", or a number of milliseconds to delay before training (default = 400)')?><br />
                <br/>
                <strong><?php echo __('WARNING')?></strong><?php echo __('In some cases this option can make echo worse!  If you are trying to debug an echo problem, it is worth checking to see if your echo is better with the option set to yes or no.  Use whatever setting gives the best results')?>.<br/>
                <br/>
                <?php echo __('Note that these parameters do not apply to hardware echo cancellers')?>.</span></a>
            </div>
            <div class='column'>
                <input type="text" class="input" name="echotraining" id="echotraining" value="<?php echo $dahdi_cards->get_globalsettings('echotraining'); ?>">
            </div>
        </div>
        <div class='columns'>
            <div class='column'>
                <a href="#" class="info"><?php echo __('Answer Immediately')?><span><?php echo __('Specify whether the channel should be answered immediately or if the simple switch should provide dialtone, read digits, etc')?>.<br/>
                    <?php echo __('Note: If yes the dialplan execution will always start at extension \'s\' priority 1 regardless of the dialed number!')?></span></a>
            </div>
            <div class='column'>
                <select class="componentSelect" id="immediate" name="immediate">
            	    <option value="yes" <?php echo set_default($dahdi_cards->get_globalsettings('immediate'),'yes'); ?>><?php echo __('Yes')?></option>
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('immediate'),'no'); ?>><?php echo __('No')?></option>
            	</select>
            </div>
        </div>
        <div class='columns'>
            <div class='column'>
                <a href="#" class="info"><?php echo __('Fax Detection')?><span><?php echo __('For fax detection')?></span></a>
            </div>
            <div class='column'>
              	<select class="componentSelect" id="faxdetect" name="faxdetect">
            	    <option value="no" <?php echo set_default($dahdi_cards->get_globalsettings('faxdetect'),'no'); ?>><?php echo __('No')?></option>
            	    <option value="incoming" <?php echo set_default($dahdi_cards->get_globalsettings('faxdetect'),'incoming'); ?>><?php echo __('Incoming')?></option>
            	    <option value="outgoing" <?php echo set_default($dahdi_cards->get_globalsettings('faxdetect'),'outgoing'); ?>><?php echo __('Outgoing')?></option>
            	    <option value="both" <?php echo set_default($dahdi_cards->get_globalsettings('faxdetect'),'both'); ?>><?php echo __('Both')?></option>
            	</select>
            </div>
        </div> 
        <div class='columns'>
            <div class='column'> 
                <a href="#" class="info"><?php echo __('Receive Gain')?><span><?php echo __('The values are in db (decibels). A positive number increases the volume level on a channel, and a negative value decreases volume level')?>.</span></a>
            </div>
            <div class='column'>
                <input type="text" class="input" name="rxgain" id="rxgain" value="<?php echo $dahdi_cards->get_globalsettings('rxgain'); ?>">
            </div>
        </div>
        <div class='columns'>
            <div class='column'> 
                <a href="#" class="info"><?php echo __('Transmit Gain')?><span><?php echo __('The values are in db (decibels). A positive number increases the volume level on a channel, and a negative value decreases volume level')?>.</span></a>
            </div>
            <div class='column'>
                <input type="text" class="input" name="txgain" id="txgain" value="<?php echo $dahdi_cards->get_globalsettings('txgain'); ?>">
            </div>
        </div>
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
        <div class='columns' id="dh_global_additional_0">
            <div class='column'>
                <?php echo __('Other Global Dahdi Settings')?>
                <br/>
                <a href="#" onclick="dh_global_delete_field(0)"><button type="button" class='is-danger button is-small'><span class='icon is-small'><i class='fa fa-trash'></i></span></button></a>
                <input type="hidden" name="dh_global_add[]" value="0" />
                <input type="hidden" id="dh_global_origsetting_key_0" name="dh_global_origsetting_key_0" value="<?php echo $dh_key?>" />
                <input type="text" class="valueinput" id="dh_global_setting_key_0" name="dh_global_setting_key_0" value="<?php echo $dh_key?>" /> =
                <input type="text" class="valueinput" id="dh_global_setting_value_0" name="dh_global_setting_value_0" value="<?php echo $dh_val?>" /> <br />
            </div>
        </div>
        <?php
        $a = 1;
        foreach($gs as $key => $value) {
            if(!in_array($key,$dahdi_cards->original_global)) {
                ?>
                <div class='columns' id="dh_global_additional_<?php echo $a?>">
                    <div class='column'>
                        <a href="#" onclick="dh_global_delete_field(<?php echo $a?>)"><button type="button" class='is-danger button is-small'><span class='icon is-small'><i class='fa fa-trash'></i></span></button></a>
                        <input type="hidden" name="dh_global_add[]" value="<?php echo $a?>" />
                        <input type="hidden" id="dh_global_origsetting_key_<?php echo $a?>"name="dh_global_origsetting_key_<?php echo $a?>" value="<?php echo $key?>" />
                        <input type="text" class="valueinput" id="dh_global_setting_key_<?php echo $a?>" name="dh_global_setting_key_<?php echo $a?>" value="<?php echo $key?>" /> =
                        <input type="text" class="valueinput" id="dh_global_setting_value_<?php echo $a?>" name="dh_global_setting_value_<?php echo $a?>" value="<?php echo $value?>" /> <br />
                    </div>
                </div>
                <?php
                $a++;
            }
        }
        ?>
        <div class='columns' id="dh_global_add">
            <div class='column'>
                <a style="cursor: pointer;" onclick="dh_global_add_field(<?php echo $a?>)" class='button is-small is-rounded'><?php echo __("Add another field");?></a>
            </div>
        </div>
    </form>
    </section>
    <footer class="modal-card-foot">
      <button data-target="form-globalsettings" class="button is-success formsubmit"><?php echo __('Save')?></button>
      <button class="button"><?php echo __('Cancel')?></button>
    </footer>
  </div>
</div>



