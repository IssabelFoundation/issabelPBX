<div class="modal animate__animated animate__fadeIn" id="modprobesettings">
  <div class="modal-background"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title" style="margin-bottom:0;"><?php echo __('Modprobe Settings')?></p>
      <button class="delete" aria-label="close"></button>
    </header>
    <section class="modal-card-body">
        <form id="form-modprobe" action="config.php?quietmode=1&amp;handler=file&amp;module=dahdiconfig&amp;file=ajax.html.php&amp;type=modprobesubmit">
            <div class='columns'>
                <div class='column'>
                    <a href="#" class="info"><?php echo __('Module Name')?><span><?php echo __('Specify the kernel module used by the installed analog hardware')?>.</span></a>
                </div>
                <div class='column'>
                    <select class='componentSelectAutoWidthNoSearch' id="module_name" name="module_name">
            	    <?php foreach($dahdi_cards->get_drivers_list() as $list) { ?>
            	    <option value="<?php echo $list; ?>" <?php echo set_default($dahdi_cards->get_modprobe('module_name'),$list); ?>><?php echo $list; ?></option>
            	    <?php } ?>
            	    </select>
            	</div>
            </div>
            <div class='columns' id="wctc4xxp_settings">
                <div class='column'>
                    <a href="#" class="info"><?php echo __('Mode')?><span><?php echo __('In Any mode it\'ll run 92 channels of either codec.')?><br/><?php echo __('In G.729 mode it\'ll run 120 channels')?>'<br/><?php echo __('In G.723.1 mode it\'ll run 92 channels')?>.</span></a>
                </div>
                <div class='column'>
                  	<input type="checkbox" id="mode_checkbox" name="mode_checkbox" <?php echo ($dahdi_cards->get_modprobe('mode_checkbox'))?'checked':''?> />
                	<select class='componentSelectAutoWidthNoSearch' id="mode" name="mode">
                		<option value="any" <?php echo set_default($dahdi_cards->get_modprobe('mode'),'any'); ?>><?php echo __('Any')?></option>
                		<option value="g723.1" <?php echo set_default($dahdi_cards->get_modprobe('mode'),'g723.1'); ?>>G723.1</option>
                		<option value="g729" <?php echo set_default($dahdi_cards->get_modprobe('mode'),'g729'); ?>>G729</option>
                	</select>
                </div>
            </div>
            <div class='columns' id="normal_mp_settings">
                <div class='column'>
                    <a href="#" class="info"><?php echo __('Opermode')?><span><?php echo __('Specify the On Hook Speed, Ringer Impedance, Ringer Threshold, Current limiting, Tip/Ring voltage adjustment, Minimum Operational Loop current, and AC Impedance selection as predefined for each countries\' analog line characteristics. Select the country in which your Asterisk server is operating. FCC is equivalent to United States. TBR21 is equivalent to Austria, Belgium, Denmark, Finland, France, Germany, Greece, Iceland, Ireland, Italy, Luxembourg, Netherlands, Norway, Portugal, Spain, Sweden, Switzerland, and the United Kingdom. If no choice is specified, the default is FCC')?>.</span></a>
                </div>
                <div class='column'>
                	<input type="checkbox" id="opermode_checkbox" name="opermode_checkbox" <?php echo ($dahdi_cards->get_modprobe('opermode_checkbox'))?'checked':''?> />
                	<select class='componentSelectAutoWidthNoSearch' id="opermode" name="opermode">
                		<option value="USA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'USA'); ?>><?php echo __('United States/North America')?></option>
                		<option value="ARGENTINA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'ARGENTINA'); ?>><?php echo __('Argentina')?></option>
                		<option value="AUSTRALIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'AUSTRALIA'); ?>><?php echo __('Australia')?></option>
                		<option value="AUSTRIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'AUSTRIA'); ?>><?php echo __('Austria')?></option>
                		<option value="BAHRAIN" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'BAHRAIN'); ?>><?php echo __('Bahrain')?></option>
                		<option value="BELGIUM" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'BELGIUM'); ?>><?php echo __('Belgium')?></option>
                		<option value="BRAZIL" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'BRAZIL'); ?>><?php echo __('Brazil')?></option>
                		<option value="BULGARIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'BULGARIA'); ?>><?php echo __('Bulgaria')?></option>
                		<option value="CANADA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'CANADA'); ?>><?php echo __('Canada')?></option>
                		<option value="CHILE" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'CHILE'); ?>><?php echo __('Chile')?></option>
                		<option value="CHINA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'CHINA'); ?>><?php echo __('China')?></option>
                		<option value="COLUMBIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'COLUMBIA'); ?>><?php echo __('Columbia')?></option>
                		<option value="CROATIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'CROATIA'); ?>><?php echo __('Croatia')?></option>
                		<option value="CYRPUS" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'CYRPUS'); ?>><?php echo __('Cyrpus')?></option>
                		<option value="CZECH" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'CZECH'); ?>><?php echo __('Czech')?></option>
                		<option value="DENMARK" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'DENMARK'); ?>><?php echo __('Denmark')?></option>
                		<option value="ECUADOR" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'ECUADOR'); ?>><?php echo __('Ecuador')?></option>
                		<option value="EGYPT" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'EGYPT'); ?>><?php echo __('Egypt')?></option>
                		<option value="ELSALVADOR" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'ELSALVADOR'); ?>><?php echo __('El Salvador')?></option>
                		<option value="FCC" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'FCC'); ?>><?php echo __('fcc')?></option>
                		<option value="FINLAND" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'FINLAND'); ?>><?php echo __('Finland')?></option>
                		<option value="FRANCE" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'FRANCE'); ?>><?php echo __('France')?></option>
                		<option value="GERMANY" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'GERMANY'); ?>><?php echo __('Germany')?></option>
                		<option value="GREECE" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'GREECE'); ?>><?php echo __('Greece')?></option>
                		<option value="GUAM" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'GUAM'); ?>><?php echo __('Guam')?></option>
                		<option value="HONGKONG" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'HONGKONG'); ?>><?php echo __('Hong Kong')?></option>
                		<option value="HUNGARY" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'HUNGARY'); ?>><?php echo __('Hungary')?></option>
                		<option value="ICELAND" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'ICELAND'); ?>><?php echo __('Iceland')?></option>
                		<option value="INDIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'INDIA'); ?>><?php echo __('India')?></option>
                		<option value="INDONESIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'INDONESIA'); ?>><?php echo __('Indonesia')?></option>
                		<option value="IRELAND" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'IRELAND'); ?>><?php echo __('Ireland')?></option>
                		<option value="ISRAEL" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'ISRAEL'); ?>><?php echo __('Israel')?></option>
                		<option value="ITALY" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'ITALY'); ?>><?php echo __('Italy')?></option>
                		<option value="JAPAN" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'JAPAN'); ?>><?php echo __('Japan')?></option>
                		<option value="JORDAN" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'JORDAN'); ?>><?php echo __('Jordan')?></option>
                		<option value="KAZAKHSTAN" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'KAZAKHSTAN'); ?>><?php echo __('Kazakhstan')?></option>
                		<option value="KUWAIT" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'KUWAIT'); ?>><?php echo __('Kuwait')?></option>
                		<option value="LATVIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'LATVIA'); ?>><?php echo __('Latvia')?></option>
                		<option value="LEBANON" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'LEBANON'); ?>><?php echo __('Lebanon')?></option>
                		<option value="LUXEMBOURG" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'LUXEMBOURG'); ?>><?php echo __('Luxembourg')?></option>
                		<option value="MACAO" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'MACACO'); ?>><?php echo __('Macao')?></option>
                		<option value="MALAYSIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'MALAYSIA'); ?>><?php echo __('Malaysia')?></option>
                		<option value="MALTA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'MALTA'); ?>><?php echo __('Malta')?></option>
                		<option value="MEXICO" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'MEXICO'); ?>><?php echo __('Mexico')?></option>
                		<option value="MOROCCO" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'MOROCCO'); ?>><?php echo __('Morocco')?></option>
                		<option value="NETHERLANDS" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'NETHERLANDS'); ?>><?php echo __('Netherlands')?></option>
                		<option value="NEWZEALAND" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'NEWZEALAND'); ?>><?php echo __('Newzealand')?></option>
                		<option value="NIGERIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'NIGERIA'); ?>><?php echo __('Nigeria')?></option>
                		<option value="NORWAY" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'NORWAY'); ?>><?php echo __('Norway')?></option>
                		<option value="OMAN" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'OMAN'); ?>><?php echo __('Oman')?></option>
                		<option value="PAKISTAN" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'PAKISTAN'); ?>><?php echo __('Pakistan')?></option>
                		<option value="PERU" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'PERU'); ?>><?php echo __('Peru')?></option>
                		<option value="PHILIPPINES" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'PHILIPPINES'); ?>><?php echo __('Philippines')?></option>
                		<option value="POLAND" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'POLAND'); ?>><?php echo __('Poland')?></option>
                		<option value="PORTUGAL" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'PORTUGAL'); ?>><?php echo __('Portugal')?></option>
                		<option value="ROMANIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'ROMANIA'); ?>><?php echo __('Romania')?></option>
                		<option value="RUSSIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'RUSSIA'); ?>><?php echo __('Russia')?></option>
                		<option value="SAUDIARABIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'SAUDIARABIA'); ?>><?php echo __('Saudi Arabia')?></option>
                		<option value="SINGAPORE" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'SINGAPORE'); ?>><?php echo __('Singapore')?></option>
                		<option value="SLOVAKIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'SLOVAKIA'); ?>><?php echo __('Slovakia')?></option>
                		<option value="SLOVENIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'SLOVENIA'); ?>><?php echo __('Slovenia')?></option>
                		<option value="SOUTHAFRICA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'SOUTHAFRICA'); ?>><?php echo __('South Africa')?></option>
                		<option value="SOUTHKOREA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'SOUTHKOREA'); ?>><?php echo __('South Korea')?></option>
                		<option value="SPAIN" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'SPAIN'); ?>><?php echo __('Spain')?></option>
                		<option value="SWEDEN" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'SWEDEN'); ?>><?php echo __('Sweden')?></option>
                		<option value="SWITZERLAND" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'SWITZERLAND'); ?>><?php echo __('Switzerland')?></option>
                		<option value="SYRIA" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'SYRIA'); ?>><?php echo __('Syria')?></option>
                		<option value="TAIWAN" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'TAIWAN'); ?>><?php echo __('Taiwan')?></option>
                		<option value="TBR21" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'TBR21'); ?>><?php echo __('TBR21')?></option>
                		<option value="THAILAND" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'THAILAND'); ?>><?php echo __('Thailand')?></option>
                		<option value="UAE" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'UAE'); ?>><?php echo __('UAE')?></option>
                		<option value="UK" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'UK'); ?>><?php echo __('UK')?></option>
                		<option value="YEMEN" <?php echo set_default($dahdi_cards->get_modprobe('opermode'),'YEMEN'); ?>><?php echo __('Yemen')?></option>
                	</select>
                </div>
            </div>
            <div class='columns'>
                <div class='column'>
                    <a href="#" class="info"><?php echo __('A-law Override')?><span><?php echo __('Specify the audio compression scheme (codec) to be used for analog lines. North American users should choose ulaw. All other countries, unless otherwise known, should be assumed to be alaw. If no choice is specified, the default is ulaw. Confirm the scheme which will be best for operation')?>.</span></a>
                </div>
                <div class='column'>
                    <input type="checkbox" id="alawoverride_checkbox" name="alawoverride_checkbox" <?php echo ($dahdi_cards->get_modprobe('alawoverride_checkbox'))?'checked':''?> />
                    <select class='componentSelectAutoWidthNoSearch' id="alawoverride" name="alawoverride">
                		<option value="0" <?php echo set_default($dahdi_cards->get_modprobe('alawoverride'),'0'); ?>><?php echo __('ulaw')?></option>
                		<option value="1" <?php echo set_default($dahdi_cards->get_modprobe('alawoverride'),'1'); ?>><?php echo __('alaw')?></option>
                	</select>
                </div>
            </div>
            <div class='columns'>
                <div class='column'>
                    <a href="#" class="info"><?php echo __('FXS Honor Mode')?><span><?php echo __('Specify whether to apply the opermode setting to your FXO modules only, or to both FXS and FXO modules. If no choice is specified, the default is apply opermode to fxo modules only')?>.</span></a>
                </div>
                <div class='column'>
                    <input type="checkbox" id="fxs_honor_mode_checkbox" name="fxs_honor_mode_checkbox" <?php echo ($dahdi_cards->get_modprobe('fxs_honor_mode_checkbox'))?'checked':''?> />
                	<select class='componentSelectAutoWidthNoSearch' id="fxs_honor_mode" name="fxs_honor_mode">
                		<option value="0" <?php echo set_default($dahdi_cards->get_modprobe('fxs_honor_mode'),'0'); ?>><?php echo __('Apply Opermode to FXO Modules')?></option>
                		<option value="1" <?php echo set_default($dahdi_cards->get_modprobe('fxs_honor_mode'),'1'); ?>><?php echo __('Apply Opermode to FXS and FXO Modules')?></option>
                	</select>
                </div>
            </div>
            <div class='columns'>
                <div class='column'>
                    <a href="#" class="info"><?php echo __('Boostringer')?><span><?php echo __('Specify the voltage used for ringing an analog phone. Normal will set the ring voltage to 40V, and Peak will set the voltage to 89V. If no choice is specified, the default is normal')?>.</span></a>
                </div>
                <div class='column'>
                   	<input type="checkbox" id="boostringer_checkbox" name="boostringer_checkbox" <?php echo ($dahdi_cards->get_modprobe('boostringer_checkbox'))?'checked':''?> />
                	<select class='componentSelectAutoWidthNoSearch' id="boostringer" name="boostringer">
                		<option value="0" <?php echo set_default($dahdi_cards->get_modprobe('boostringer'),'0'); ?>><?php echo __('Normal')?></option>
                		<option value="1" <?php echo set_default($dahdi_cards->get_modprobe('boostringer'),'1'); ?>><?php echo __('Peak (89v)')?></option>
                	</select>
                </div>
            </div>
            <div class='columns'>
                <div class='column'>
                    <a href="#" class="info"><?php echo __('Fastringer:')?><span><?php echo __('Specify whether to apply Fast Ringer operation. Setting Fast Ringer (25Hz) (commonly used in conjunction with the Low Power option) increases the ringing speed to 25Hz. If no choice is specified, the default is normal')?>.</span></a>
                </div>
                <div class='column'>
                    <input type="checkbox" id="fastringer_checkbox" name="fastringer_checkbox" <?php echo ($dahdi_cards->get_modprobe('fastringer_checkbox'))?'checked':''?> />
                	<select class='componentSelectAutoWidthNoSearch' id="fastringer" name="fastringer">
                		<option value="0" <?php echo set_default($dahdi_cards->get_modprobe('fastringer'),'0'); ?>><?php echo __('Normal')?></option>
                		<option value="1" <?php echo set_default($dahdi_cards->get_modprobe('fastringer'),'1'); ?>><?php echo __('Fast Ringer (25hz)')?></option>
                	</select>
                </div>
            </div>
            <div class='columns'>
                <div class='column'>
                    <a href="#" class="info"><?php echo __('Lowpower')?><span><?php echo __('Specify whether to apply Low Power operation. Setting Fast Ringer to 50V peak in conjunction with the Fast Ringer option increases the peak voltage during Fast Ringer operation to 50V. If no choice is specified, the default is normal')?>.</span></a>
                </div>
                <div class='column'>
                    <input type="checkbox" id="lowpower_checkbox" name="lowpower_checkbox" <?php echo ($dahdi_cards->get_modprobe('lowpower_checkbox'))?'checked':''?> />
            	    <select class='componentSelectAutoWidthNoSearch' id="lowpower" name="lowpower">
            		    <option value="0" <?php echo set_default($dahdi_cards->get_modprobe('lowpower'),'0'); ?>><?php echo __('Normal')?></option>
            		    <option value="1" <?php echo set_default($dahdi_cards->get_modprobe('lowpower'),'1'); ?>><?php echo __('Fast Ringer to 50v Peak')?></option>
            	    </select>
            	</div>
            </div>
            <div class='columns' id="tr_ringdetect" style="display:none;">
                <div class='column'>
                    <a href="#" class="info"><?php echo __('Ring Detect')?><span><?php echo __('Specify whether to apply normal ring detection, or a full wave detection to prevent false ring detection for lines where CallerID is sent before the first ring and proceeded by a polarity reversal (as in the United Kingdom). If you are experiencing trouble with detecting CallerID from analog service providers, or have lines which exhibit a polarity reversal before CallerID is transmitted from the provider, then select Full Wave. If no choice is specified, the default is standard')?>.</span></a>
                </div>
                <div class='column'>
                    <input type="checkbox" id="ringdetect_checkbox" name="ringdetect_checkbox" <?php echo ($dahdi_cards->get_modprobe('ringdetect_checkbox'))?'checked':''?> />
                	<select class='componentSelectAutoWidthNoSearch' id="ringdetect" name="ringdetect">
                		<option value="0" <?php echo set_default($dahdi_cards->get_modprobe('ringdetect'),'0'); ?>><?php echo __('Standard')?></option>
                		<option value="1" <?php echo set_default($dahdi_cards->get_modprobe('ringdetect'),'1'); ?>><?php echo __('Full Wave')?></option>
                	</select>
                </div>
            </div>
            <div class='columns'>
                <div class='column'>
                    <a href="#" class="info"><?php echo __('MWI Mode')?><span><?php echo __('Specify the type of Message Waiting Indicator detection to be done on FXO ports. If no choice is specified, the default is none. The following options are available')?>:
                	<ul>
                		<li><?php echo __('none - Performs no detection')?></li>
                		<li><?php echo __('FSK - Performs Frequency Shift Key detection')?></li>
                		<li><?php echo __('NEON - Performs Neon MWI detection')?>.</li>
                	</ul>
                	</span></a>
                </div>
                <div class='column'>
                    <input type="checkbox" id="mwi_checkbox" name="mwi_checkbox" <?php echo ($dahdi_cards->get_modprobe('mwi_checkbox'))?'checked':''?> />
                	<select class='componentSelectAutoWidthNoSearch' id="mwi" name="mwi">
                		<option value="none" <?php echo set_default($dahdi_cards->get_modprobe('mwi'),'none'); ?>><?php echo __('None')?></option>
                		<option value="fsk" <?php echo set_default($dahdi_cards->get_modprobe('mwi'),'fsk'); ?>><?php echo __('FSK')?></option>
                		<option value="neon" <?php echo set_default($dahdi_cards->get_modprobe('mwi'),'neon'); ?>><?php echo __('NEON')?></option>
                	</select>
                </div>
            </div>
            <div class='columns' <?php echo (($dahdi_cards->get_modprobe('mwi') != 'neon') ? ' style="display:none;"' : "")?>>
                <div class='column'>
                </div>
                <div class='column'>
                    <?php echo __('Neon MWI Voltage Level')?>: 
                	<input id="neon_voltage" name="neon_voltage" size="2" value="<?php echo $dahdi_cards->get_modprobe('neon_voltage')?>" /><br />
                	<?php echo __('Neon MWI Off Limit')?>: 
                	<input id="neon_offlimit" name="neon_offlimit" size="4" value="<?php echo $dahdi_cards->get_modprobe('neon_offlimit')?>" />
                </div>
            </div>
            <div class='columns' id="vpmsettings">
                <div class='column'>
                    <a href="#" class="info"><?php echo __('Echo Canc. NLP Type')?><span> <?php echo __('This option allows you to specify the type of Non Linear Processor you want applied to the post echo-cancelled audio reflections received from analog connections (VPMADT032 only). There are several options')?>:
            		<ul>
            			<li><?php echo __('None - This setting disables NLP processing and is not a recommended setting. Under most circumstances, choos- ing None will cause some residual echo')?>.</li>
            			<li><?php echo __('Mute - This setting causes the NLP to mute inbound audio streams while a user connected to Asterisk is speaking. For users in quiet environments, Mute may be acceptable')?>.</li>
            			<li><?php echo __('Random Noise - This setting causes the NLP to inject random noise to mask the echo reflection. For users in normal environments, Random Noise may be acceptable')?>.</li>
            			<li><?php echo __('Hoth Noise - This setting causes the NLP to inject a low-end Gaussian noise with a frequency spectrum similar to voice. For users in normal environments, Hoth Noise may be acceptable')?>.</li>
            			<li><?php echo __('Suppression NLP - This setting causes the NLP to suppress echo reflections by reducing the amplitude of their volume. Suppression may be used in combination with the Echo cancellation NLP Max Suppression option. For users in loud environments, Suppression NLP may be the best option. This is the default setting for the Echo Cancellation NLP Type option')?>.</li>
            		</ul>
            		</span></a>
            	</div>
            	<div class='column'>
            	    <select class='componentSelectAutoWidthNoSearch' id="echocan_nlp_type" name="echocan_nlp_type">
            			<option value="0" <?php echo set_default($dahdi_cards->get_modprobe('echocan_nlp_type'),'0'); ?>><?php echo __('None')?></option>
            			<option value="1" <?php echo set_default($dahdi_cards->get_modprobe('echocan_nlp_type'),'1'); ?>><?php echo __('Mute')?></option>
            			<option value="2" <?php echo set_default($dahdi_cards->get_modprobe('echocan_nlp_type'),'2'); ?>><?php echo __('Random Noise')?></option>
            			<option value="3" <?php echo set_default($dahdi_cards->get_modprobe('echocan_nlp_type'),'3'); ?>><?php echo __('Hoth Noise')?></option>
            			<option value="4" <?php echo set_default($dahdi_cards->get_modprobe('echocan_nlp_type'),'4'); ?>><?php echo __('Suppression NLP (default)')?></option>
            		</select>
            	</div>
            </div>
            <div class='columns'>
                <div class='column'>
                    <a href="#" class="info"><?php echo __('Echo Canc. NLP Threshold')?><span><?php echo __('This option allows you to specify the threshold, in dB difference between the received audio (post echo cancellation) and the transmitted audio, for when the NLP will engage (VPMADT032 only). The default setting is 24 dB')?>.</span></a>
                </div>
                <div class='column'>
                    <select class='componentSelectAutoWidthNoSearch' id="echocan_nlp_threshold" name="echocan_nlp_threshold">
            		    <?php for($i=0; $i<=50; $i++) { ?>
            		    <option value="<?php echo $i; ?>" <?php echo set_default($dahdi_cards->get_modprobe('echocan_nlp_threshold'),$i); ?>><?php echo $i; ?></option>
            		    <?php } ?>
            		</select>
                </div>
            </div>
            <div class='columns'>
                <div class='column'>
                    <a href="#" class="info"><?php echo __('Echo Canc. NLP Max Suppression')?><span><?php __('This option, only functional when the Echo Cancellation NLP Type option is set to Suppression NLP, specifies the maximum amount of dB that the NLP should attenuate the residual echo (VPMADT032 only). Lower numbers mean that the NLP will provide less suppression (the residual echo will sound louder). Higher numbers, especially those approaching or equaling the Echo Cancellation NLP Threshold option, will nearly mute the residual echo. The default setting is 24 dB')?>.</span></a>
                </div>
                <div class='column'>
                    <select class='componentSelectAutoWidthNoSearch' id="echocan_nlp_max_supp" name="echocan_nlp_max_supp">
            		    <?php for($i=0; $i<=50; $i++) { ?>
            		    <option value="<?php echo $i; ?>" <?php echo set_default($dahdi_cards->get_modprobe('echocan_nlp_max_supp'),$i); ?>><?php echo $i; ?></option>
            		    <?php } ?>
            		</select>
                </div>
            </div>
            <div class='columns' id="wct4xxp_wcte12xp_settings">
                <div class='column'>
                    <a href="#" class="info"><?php echo __('Default Line Mode')?><span><?php echo __('The recommended way to set line mode on your Digium 1-, 2-, and 4-port (span) digital telephony cards is to set the jumper(s) on the card for either T1 or E1 mode for each span on the card. With the jumper off, the span is ready for T1 mode; with the jumper on, the span is ready for E1 mode. For more details about the jumpers, see the user manual for the single, dual, or quad span digital cards.<br/>However, sometimes a card will have been installed in a server without first setting the jumper(s) correctly, and it may be inconvenient to remove the card from the server to access its jumper(s).  In this case, the "default_linemode" option can be passed when the card\'s device driver is loaded')?>.</span></a>
                </div>
                <div class='column'>
                  	<input type="checkbox" id="defaultlinemode_checkbox" name="defaultlinemode_checkbox" <?php echo ($dahdi_cards->get_modprobe('defaultlinemode_checkbox'))?'checked':''?> />
                	<select class='componentSelectAutoWidthNoSearch' id="defaultlinemode" name="defaultlinemode">
                		<option value="t1" <?php echo set_default($dahdi_cards->get_modprobe('defaultlinemode'),'t1'); ?>><?php echo __('T1')?></option>
                		<option value="e1" <?php echo set_default($dahdi_cards->get_modprobe('defaultlinemode'),'e1'); ?>><?php echo __('E1')?></option>
                		<option value="auto" <?php echo set_default($dahdi_cards->get_modprobe('defaultlinemode'),'auto'); ?>><?php echo __('Auto')?></option>
                	</select>
                </div>
            </div>
        <?php
        $mp = $dahdi_cards->get_all_modprobe($dahdi_cards->get_modprobe('module_name'));
        $mp_key = '';
        $mp_val = '';
        if(isset($mp['additionals'])) {
            foreach($mp['additionals'] as $key => $value) {
                $mp_key = $key;
                $mp_val = $value;
                unset($mp['additionals'][$key]);
                break;
            }
        }
        ?>
            <div class='columns'>
                <div class='column'>
                    <?php echo __('Other Modprobe Settings')?>: 
                </div>
            </div>
            <div class='neon columns' id="mp_additional_0">
                <div class='column'>
                    <a href="#" onclick="mp_delete_field(0,'<?php echo $dahdi_cards->get_modprobe('module_name')?>')"><button type="button" class="is-danger button is-small"><span class="icon is-small"><i class="fa fa-trash"></i></span></button></a>
                    <input type="hidden" name="mp_setting_add[]" value="0" />
                    <input type="hidden" id="mp_setting_origsetting_key_0" name="mp_setting_origsetting_key_0" value="<?php echo $mp_key?>" />
                    <input id="mp_setting_key_0" type="text" class="valueinput" name="mp_setting_key_0" value="<?php echo $mp_key?>" /> =
                    <input id="mp_setting_value_0" type="text" class="valueinput" name="mp_setting_value_0" value="<?php echo $mp_val?>" /> <br />
                </div>
            </div>
            <?php
            $a = 1;
            if(isset($mp['additionals'])) {
                foreach($mp['additionals'] as $key => $value) {?>
                    <div class="columns mp_js_additionals" id="mp_additional_<?php echo $a?>">
                        <div class='column'>
                            <a href="#" onclick="mp_delete_field(<?php echo $a?>,'<?php echo $dahdi_cards->get_modprobe('module_name')?>')"><button type="button" class="is-danger button is-small"><span class="icon is-small"><i class="fa fa-trash"></i></span></button></a>
                            <input type="hidden" name="mp_setting_add[]" value="<?php echo $a?>" />
                            <input type="hidden" id="mp_setting_origsetting_key_<?php echo $a?>" name="mp_setting_origsetting_key_<?php echo $a?>" value="<?php echo $key?>" />
                            <input id="mp_setting_key_<?php echo $a?>" name="mp_setting_key_<?php echo $a?>" value="<?php echo $key?>" /> =
                            <input id="mp_setting_value_<?php echo $a?>" name="mp_setting_value_<?php echo $a?>" value="<?php echo $value?>" /> <br />
                        </div>
                    </div>
                <?php
                    $a++;
                }
            } ?>
            <div class='columns' id="mp_add">
                <div class='column'>
                </div>
                <div class='column'>
                    <a id="mp_add_button" style="cursor: pointer;" onclick="mp_add_field(<?php echo $a?>,'<?php echo $dahdi_cards->get_modprobe('module_name')?>')" class="button is-small is-rounded"><?php echo __('Add another field');?></a>
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

