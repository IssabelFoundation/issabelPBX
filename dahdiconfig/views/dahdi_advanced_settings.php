<h2><?php echo _('Advanced Settings')?></h2>
<hr />
<form name="dahdi_advanced_settings" method="post" action="/admin/config.php?type=setup&amp;display=dahdi">
<input type="hidden" name="display" value="dahdi" />
<input type="hidden" name="action" value="edit" />
<div class="setting">
	<label for="module_name"><a href="#" class="info"><?php echo _('Module Name')?>:<span><?php echo _('Specify the kernel module used by the installed analog hardware.')?></span></a></label>
	<input type="text" id="module_name" name="module_name" size="10" value="<?=$dahdi_cards->get_advanced('module_name')?>" />
</div>
<div class="setting">
	<label for="tone_region"><a href="#" class="info"><?php echo _('Tone Region')?>:<span><?php echo _('Please choose your country or your nearest neighboring country for default Tones (Ex: dialtone, busy tone, ring tone etc.)')?></span></a></label>
	<select id="tone_region" name="tone_region">
		<option value="us"><?php echo _('United States/North America')?></option>
		<option value="au"><?php echo _('Australia')?></option>
		<option value="fr"><?php echo _('France')?></option>
		<option value="nl"><?php echo _('Netherlands')?></option>
		<option value="uk"><?php echo _('United Kingdom')?></option>
		<option value="fi"><?php echo _('Finland')?></option>
		<option value="es"><?php echo _('Spain')?></option>
		<option value="jp"><?php echo _('Japan')?></option>
		<option value="no"><?php echo _('Norway')?></option>
		<option value="at"><?php echo _('Austria')?></option>
		<option value="nz"><?php echo _('New Zealand')?></option>
		<option value="it"><?php echo _('Italy')?></option>
		<option value="us-old"><?php echo _('United States Circa 1950 / North America')?></option>
		<option value="gr"><?php echo _('Greece')?></option>
		<option value="tw"><?php echo _('Taiwan')?></option>
		<option value="cl"><?php echo _('Chile')?></option>
		<option value="se"><?php echo _('Sweden')?></option>
		<option value="be"><?php echo _('Belgium')?></option>
		<option value="sg"><?php echo _('Singapore')?></option>
		<option value="il"><?php echo _('Israel')?></option>
		<option value="br"><?php echo _('Brazil')?></option>
		<option value="hu"><?php echo _('Hungary')?></option>
		<option value="lt"><?php echo _('Lithuania')?></option>
		<option value="pl"><?php echo _('Poland')?></option>
		<option value="za"><?php echo _('South Africa')?></option>
		<option value="pt"><?php echo _('Portugal')?></option>
		<option value="ee"><?php echo _('Estonia')?></option>
		<option value="mx"><?php echo _('Mexico')?></option>
		<option value="in"><?php echo _('India')?></option>
		<option value="de"><?php echo _('Germany')?></option>
		<option value="ch"><?php echo _('Switzerland')?></option>
		<option value="dk"><?php echo _('Denmark')?></option>
		<option value="cz"><?php echo _('Czech Republic')?></option>
		<option value="cn"><?php echo _('China')?></option>
		<option value="ar"><?php echo _('Argentina')?></option>
		<option value="my"><?php echo _('Malaysia')?></option>
		<option value="th"><?php echo _('Thailand')?></option>
		<option value="bg"><?php echo _('Bulgaria')?></option>
		<option value="ve"><?php echo _('Venezuela')?></option>
		<option value="ph"><?php echo _('Philippines')?></option>
		<option value="ru"><?php echo _('Russian Federation')?></option>
	</select>
</div>
<div class="setting">
	<label for="opermode_checkbox" class="info"><a href="#" class="info"><?php echo _('Opermode')?>:<span><?php echo _("Specify the On Hook Speed, Ringer Impedance, Ringer Threshold, Current limiting, Tip/Ring voltage adjustment, Minimum Operational Loop current, and AC Impedance selection as predefined for each countries' analog line characteristics. Select the country in which your Asterisk server is operating. FCC is equivalent to United States. TBR21 is equivalent to Austria, Belgium, Denmark, Finland, France, Germany, Greece, Iceland, Ireland, Italy, Luxembourg, Netherlands, Norway, Portugal, Spain, Sweden, Switzerland, and the United Kingdom. If no choice is specified, the default is FCC")?></span></a></label>
	<input type="checkbox" id="opermode_checkbox" name="opermode_checkbox" <?=($dahdi_cards->get_advanced('opermode_checkbox'))?'checked':''?> />
	<select id="opermode" name="opermode">
		<option value="USA"><?php echo _('United States/North America')?></option>
		<option value="ARGENTINA"><?php echo _('Argentina')?></option>
		<option value="AUSTRALIA"><?php echo _('Australia')?></option>
		<option value="AUSTRIA"><?php echo _('Austria')?></option>
		<option value="BAHRAIN"><?php echo _('Bahrain')?></option>
		<option value="BELGIUM"><?php echo _('Belgium')?></option>
		<option value="BRAZIL"><?php echo _('Brazil')?></option>
		<option value="BULGARIA"><?php echo _('Bulgaria')?></option>
		<option value="CANADA"><?php echo _('Canada')?></option>
		<option value="CHILE"><?php echo _('Chile')?></option>
		<option value="CHINA"><?php echo _('China')?></option>
		<option value="COLUMBIA"><?php echo _('Columbia')?></option>
		<option value="CROATIA"><?php echo _('Croatia')?></option>
		<option value="CYRPUS"><?php echo _('Cyrpus')?></option>
		<option value="CZECH"><?php echo _('Czech')?></option>
		<option value="DENMARK"><?php echo _('Denmark')?></option>
		<option value="ECUADOR"><?php echo _('Ecuador')?></option>
		<option value="EGYPT"><?php echo _('Egypt')?></option>
		<option value="ELSALVADOR"><?php echo _('El Salvador')?></option>
		<option value="FCC"><?php echo _('FCC')?></option>
		<option value="FINLAND"><?php echo _('Finland')?></option>
		<option value="FRANCE"><?php echo _('France')?></option>
		<option value="GERMANY"><?php echo _('Germany')?></option>
		<option value="GREECE"><?php echo _('Greece')?></option>
		<option value="GUAM"><?php echo _('Guam')?></option>
		<option value="HONGKONG"><?php echo _('Hongkong')?></option>
		<option value="HUNGARY"><?php echo _('Hungary')?></option>
		<option value="ICELAND"><?php echo _('Iceland')?></option>
		<option value="INDIA"><?php echo _('India')?></option>
		<option value="INDONESIA"><?php echo _('Indonesia')?></option>
		<option value="IRELAND"><?php echo _('Ireland')?></option>
		<option value="ISRAEL"><?php echo _('Israel')?></option>
		<option value="ITALY"><?php echo _('Italy')?></option>
		<option value="JAPAN"><?php echo _('Japan')?></option>
		<option value="JORDAN"><?php echo _('Jordan')?></option>
		<option value="KAZAKHSTAN"><?php echo _('Kazakhstan')?></option>
		<option value="KUWAIT"><?php echo _('Kuwait')?></option>
		<option value="LATVIA"><?php echo _('Latvia')?></option>
		<option value="LEBANON"><?php echo _('Lebanon')?></option>
		<option value="LUXEMBOURG"><?php echo _('Luxembourg')?></option>
		<option value="MACAO"><?php echo _('Macao')?></option>
		<option value="MALAYSIA"><?php echo _('Malaysia')?></option>
		<option value="MALTA"><?php echo _('Malta')?></option>
		<option value="MEXICO"><?php echo _('Mexico')?></option>
		<option value="MOROCCO"><?php echo _('Morocco')?></option>
		<option value="NETHERLANDS"><?php echo _('Netherlands')?></option>
		<option value="NEWZEALAND"><?php echo _('Newzealand')?></option>
		<option value="NIGERIA"><?php echo _('Nigeria')?></option>
		<option value="NORWAY"><?php echo _('Norway')?></option>
		<option value="OMAN"><?php echo _('Oman')?></option>
		<option value="PAKISTAN"><?php echo _('Pakistan')?></option>
		<option value="PERU"><?php echo _('Peru')?></option>
		<option value="PHILIPPINES"><?php echo _('Philippines')?></option>
		<option value="POLAND"><?php echo _('Poland')?></option>
		<option value="PORTUGAL"><?php echo _('Portugal')?></option>
		<option value="ROMANIA"><?php echo _('Romania')?></option>
		<option value="RUSSIA"><?php echo _('Russia')?></option>
		<option value="SAUDIARABIA"><?php echo _('Saudi Arabia')?></option>
		<option value="SINGAPORE"><?php echo _('Singapore')?></option>
		<option value="SLOVAKIA"><?php echo _('Slovakia')?></option>
		<option value="SLOVENIA"><?php echo _('Slovenia')?></option>
		<option value="SOUTHAFRICA"><?php echo _('South Africa')?></option>
		<option value="SOUTHKOREA"><?php echo _('South Korea')?></option>
		<option value="SPAIN"><?php echo _('Spain')?></option>
		<option value="SWEDEN"><?php echo _('Sweden')?></option>
		<option value="SWITZERLAND"><?php echo _('Switzerland')?></option>
		<option value="SYRIA"><?php echo _('Syria')?></option>
		<option value="TAIWAN"><?php echo _('Taiwan')?></option>
		<option value="TBR21"><?php echo _('TBR21')?></option>
		<option value="THAILAND"><?php echo _('Thailand')?></option>
		<option value="UAE"><?php echo _('UAE')?></option>
		<option value="UK"><?php echo _('UK')?></option>
		<option value="YEMEN"><?php echo _('Yemen')?></option>
	</select>
</div>
<div class="setting">
	<label for="alawoverride_checkbox"><a href="#" class="info"><?php _('A-law Override')?>:<span><?php echo _('Specify the audio compression scheme (codec) to be used for analog lines. North American users should choose ulaw. All other countries, unless otherwise known, should be assumed to be alaw. If no choice is specified, the default is ulaw. Confirm the scheme which will be best for operation.')?></span></a></label>
	<input type="checkbox" id="alawoverride_checkbox" name="alawoverride_checkbox" <?=($dahdi_cards->get_advanced('alawoverride_checkbox'))?'checked':''?> />
	<select id="alawoverride" name="alawoverride">
		<option value="0">ulaw</option>
		<option value="1">alaw</option>
	</select>
</div>
<div class="setting">
	<label for="fxs_honor_mode_checkbox"><a href="#" class="info"><?php echo _('FXS Honor Mode')?>:<span><?php echo _('Specify whether to apply the opermode setting to your FXO modules only, or to both FXS and FXO modules. If no choice is specified, the default is apply opermode to fxo modules only.')?></span></a></label>
	<input type="checkbox" id="fxs_honor_mode_checkbox" name="fxs_honor_mode_checkbox" <?=($dahdi_cards->get_advanced('fxs_honor_mode_checkbox'))?'checked':''?> />
	<select id="fxs_honor_mode" name="fxs_honor_mode">
		<option value="0"><?php echo _('Apply Opermode to FXO Modules')?></option>
		<option value="1"><?php echo _('Apply Opermode to FXS and FXO Modules')?></option>
	</select>
</div>
<div class="setting">
	<label for="boostringer_checkbox"><a href="#" class="info"><?php echo _('Boostringer')?>:<span><?php echo _('Specify the voltage used for ringing an analog phone. Normal will set the ring voltage to 40V, and Peak will set the voltage to 89V. If no choice is specified, the default is normal.')?></span></a></label>
	<input type="checkbox" id="boostringer_checkbox" name="boostringer_checkbox" <?=($dahdi_cards->get_advanced('boostringer_checkbox'))?'checked':''?> />
	<select id="boostringer" name="boostringer">
		<option value="0"><?php echo _('Normal')?></option>
		<option value="1"><?php echo _('Peak (89v)')?></option>
	</select>
</div>
<div class="setting">
	<label for="fastringer_checkbox"><a href="#" class="info"><?php echo _('Fastringer')?>:<span><?php echo _('Specify whether to apply Fast Ringer operation. Setting Fast Ringer (25Hz) (commonly used in conjunction with the Low Power option) increases the ringing speed to 25Hz. If no choice is specified, the default is normal.')?></span></a></label>
	<input type="checkbox" id="fastringer_checkbox" name="fastringer_checkbox" <?=($dahdi_cards->get_advanced('fastringer_checkbox'))?'checked':''?> />
	<select id="fastringer" name="fastringer">
		<option value="0"><?php echo _('Normal')?></option>
		<option value="1"><?php echo _('Fast Ringer (25hz)')?></option>
	</select>
</div>
<div class="setting">
	<label for="lowpower_checkbox"><a href="#" class="info"><?php echo _('Lowpower')?>:<span><?php echo _('Specify whether to apply Low Power operation. Setting Fast Ringer to 50V peak in conjunction with the Fast Ringer option increases the peak voltage during Fast Ringer operation to 50V. If no choice is specified, the default is normal.')?></span></a></label>
	<input type="checkbox" id="lowpower_checkbox" name="lowpower_checkbox" <?=($dahdi_cards->get_advanced('lowpower_checkbox'))?'checked':''?> />
	<select id="lowpower" name="lowpower">
		<option value="0"><?php echo _('Normal')?></option>
		<option value="1"><?php echo _('Fast Ringer to 50v Peak')?></option>
	</select>
</div>
<div class="setting">
	<label for="ringdetect_checkbox"><a href="#" class="info"><?php echo _('Ring Detect')?>:<span><?php echo _('Specify whether to apply normal ring detection, or a full wave detection to prevent false ring detection for lines where CallerID is sent before the first ring and proceeded by a polarity reversal (as in the United Kingdom). If you are experiencing trouble with detecting CallerID from analog service providers, or have lines which exhibit a polarity reversal before CallerID is transmitted from the provider, then select Full Wave. If no choice is specified, the default is standard.')?></span></a></label>
	<input type="checkbox" id="ringdetect_checkbox" name="ringdetect_checkbox" <?=($dahdi_cards->get_advanced('ringdetect_checkbox'))?'checked':''?> />
	<select id="ringdetect" name="ringdetect">
		<option value="0"><?php echo _('Standard')?></option>
		<option value="1"><?php echo _('Full Wave')?></option>
	</select>
</div>
<div class="setting">
	<label for="mwi_checkbox"><a href="#" class="info"><?php echo _('MWI Mode')?>:<span><?php echo _('Specify the type of Message Waiting Indicator detection to be done on FXO ports. If no choice is specified, the default is none. The following options are available')?>:
	<ul>
		<li><?php echo _('none - Performs no detection ')?></li>
		<li><?php echo _('FSK - Performs Frequency Shift Key detection')?></li>
		<li><?php echo _('NEON - Performs Neon MWI detection.')?></li>
	</ul>
	</span></a></label>
	<input type="checkbox" id="mwi_checkbox" name="mwi_checkbox" <?=($dahdi_cards->get_advanced('mwi_checkbox'))?'checked':''?> />
	<select id="mwi" name="mwi">
		<option value="none"><?php echo _('None')?></option>
		<option value="fsk">FSK</option>
		<option value="neon">NEON</option>
	</select>
</div>
<div class="setting neon"<?=(($dahdi_cards->get_advanced['mwi'] != 'neon') ? ' style="display:none;"' : "")?>>
	<label for="neon_voltage">Neon MWI Voltage Level')?>: </label>
	<input id="neon_voltage" name="neon_voltage" size="2" value="<?=$dahdi_cards->get_advanced('neon_voltage')?>" /><br />
	<label for="neon_offlimit">Neon MWI Off Limit')?>: </label>
	<input id="neon_offlimit" name="neon_offlimit" size="4" value="<?=$dahdi_cards->get_advanced('neon_offlimit')?>" />
</div>
<div id="vpmsettings"<?=((!$dahdi_cards->has_vpm())? ' style="display:none;"': "")?>>
	<div class="setting">
		<label for="echocan_nlp_type"><a href="#" class="info"><?php echo _('Echo Canc. NLP Type')?>:<span> <?php echo _('This option allows you to specify the type of Non Linear Processor you want applied to the post echo-cancelled audio reflections received from analog connections (VPMADT032 only). There are several options')?>:
		<ul>
			<li><?php echo _('None - This setting disables NLP processing and is not a recommended setting. Under most circumstances, choos- ing None will cause some residual echo.')?></li>
			<li><?php echo _('Mute - This setting causes the NLP to mute inbound audio streams while a user connected to Asterisk is speaking. For users in quiet environments, Mute may be acceptable.')?></li>
			<li><?php echo _('Random Noise - This setting causes the NLP to inject random noise to mask the echo reflection. For users in normal environments, Random Noise may be acceptable.')?></li>
			<li><?php echo _('Hoth Noise - This setting causes the NLP to inject a low-end Gaussian noise with a frequency spectrum similar to voice. For users in normal environments, Hoth Noise may be acceptable.')?></li>
			<li><?php echo _('Suppression NLP - This setting causes the NLP to suppress echo reflections by reducing the amplitude of their volume. Suppression may be used in combination with the Echo cancellation NLP Max Suppression option. For users in loud environments, Suppression NLP may be the best option. This is the default setting for the Echo Cancellation NLP Type option.')?></li>
		</ul>
		</span></a></label>
		<select id="echocan_nlp_type" name="echocan_nlp_type">
			<option value="0"><?php echo _('None')?></option>
			<option value="1"><?php echo _('Mute')?></option>
			<option value="2"><?php echo _('Random Noise')?></option>
			<option value="3"><?php echo _('Hoth Noise')?></option>
			<option value="4"><?php echo _('Suppression NLP (default)')?></option>
		</select>
	</div>
	<div class="setting">
		<label for="echocan_nlp_threshold"><a href="#" class="info"><?php echo _('Echo Cancel NLP Threshold')?>:<span><?php echo _('This option allows you to specify the threshold, in dB difference between the received audio (post echo cancellation) and the transmitted audio, for when the NLP will engage (VPMADT032 only). The default setting is 24 dB.')?></span></a></label>
		<select id="echocan_nlp_threshold" name="echocan_nlp_threshold"></select>
	</div>
	<div class="setting">
		<label for="echocan_nlp_max_supp"><a href="#" class="info"><?php echo _('Echo Cancel NLP Max Suppression')?>:<span><?php echo _('This option, only functional when the Echo Cancellation NLP Type option is set to Suppression NLP, specifies the maximum amount of dB that the NLP should attenuate the residual echo (VPMADT032 only). Lower numbers mean that the NLP will provide less suppression (the residual echo will sound louder). Higher numbers, especially those approaching or equaling the Echo Cancellation NLP Threshold option, will nearly mute the residual echo. The default setting is 24 dB.')?></span></a></label>
		<select id="echocan_nlp_max_supp" name="echocan_nlp_max_supp"></select>
	</div>
</div>
<div class="btn_container">
	<input type="submit" id="advanced_cancel" name="advanced_cancel" value="<?php echo _('Cancel')?>" />
	<input type="submit" id="advanced_submit" name="advanced_submit" value="<?php echo _('Save')?>" />
</div>
</form>

<script>

	for(var i=0; i<=50; i++) {
		$('#echocan_nlp_max_supp').append('<option value="'+i+'">'+i+'</option>');
		$('#echocan_nlp_threshold').append('<option value="'+i+'">'+i+'</option>');
	}

	ChangeSelectByValue('tone_region', '<?=$dahdi_cards->get_advanced('tone_region')?>', true);
	ChangeSelectByValue('opermode', '<?=$dahdi_cards->get_advanced('opermode')?>', true);
	ChangeSelectByValue('alawoverride', '<?=$dahdi_cards->get_advanced('alawoverride')?>', true);
	ChangeSelectByValue('fxs_honor_mode', '<?=$dahdi_cards->get_advanced('fxs_honor_mode')?>', true);
	ChangeSelectByValue('boostringer', '<?=$dahdi_cards->get_advanced('boostringer')?>', true);
	ChangeSelectByValue('fastringer', '<?=$dahdi_cards->get_advanced('fastringer')?>', true);
	ChangeSelectByValue('lowpower', '<?=$dahdi_cards->get_advanced('lowpower')?>', true);
	ChangeSelectByValue('ringdetect', '<?=$dahdi_cards->get_advanced('ringdetect')?>', true);
	ChangeSelectByValue('mwi', '<?=$dahdi_cards->get_advanced('mwi')?>', true);
	ChangeSelectByValue('echocan_nlp_type', '<?=$dahdi_cards->get_advanced('echocan_nlp_type')?>', true);
	ChangeSelectByValue('echocan_nlp_threshold', '<?=$dahdi_cards->get_advanced('echocan_nlp_threshold')?>', true);
	ChangeSelectByValue('echocan_nlp_max_supp', '<?=$dahdi_cards->get_advanced('echocan_nlp_max_supp')?>', true);

	$('#mwi').change(function(evt) {
		if ($('#mwi :selected').val() == 'neon') {
			$('.neon').show();
		} else {
			$('.neon').hide();
		}
	});
</script>
