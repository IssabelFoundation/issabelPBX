<form id="dahdi_editspan_<?php echo $key?>" method="POST" action="config.php?quietmode=1&amp;handler=file&amp;module=dahdiconfig&amp;file=ajax.html.php&amp;type=digital&amp;id=<?php echo $key;?>">
    <input type="hidden" name="editspan_<?php echo $key?>_reserved_ch" value="<?php echo $span['reserved_ch'];?>">
    <input type="hidden" name="editspan_<?php echo $key?>_groupc" value="0">
    <h2><?php echo __('General Settings')?></h2>
    <hr>
    <table width="100%" style="text-align:left;" border="0" cellspacing="0">
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_alarm"><?php echo __('Alarms')?>:</label>
            </td>
            <td>
                <span id="editspan_<?php echo $key?>_alarms" name="editspan_<?php echo $key?>_alarms"><?php echo $span['alarms']?></span>
            </td>
        </tr>
		<?php if(empty($span['type']) || $span['type'] != 'gsm') {?>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_framing">Framing/Coding')?>:</label>
            </td>
            <td>
               	<select id="editspan_<?php echo $key?>_fac" name="editspan_<?php echo $key?>_fac">
            	<?php switch($span['totchans']) {
            	   case 3: ?>
            		<option value="CCS/AMI" <?php echo set_default($span['framing']."/".$span['coding'],'CCS/AMI'); ?>>CCS/AMI</option>
            	<?php 	break;
            	   case 24: ?>
            		<option value="ESF/B8ZS" <?php echo set_default($span['framing']."/".$span['coding'],'ESF/B8ZS'); ?>>ESF/B8ZS</option>
            		<option value="D4/AMI" <?php echo set_default($span['framing']."/".$span['coding'],'D4/AMI'); ?>>D4/AMI</option>
            	<?php 	break;
            	   case 31: ?>
            		<option value="CCS/HDB3" <?php echo set_default($span['framing']."/".$span['coding'], 'CCS/HDB3'); ?>>CCS/HDB3</option>
            		<option value="CCS/HDB3/CRC4" <?php echo set_default($span['framing']."/".$span['coding'],'CCS/HDB3/CRC4'); ?>>CCS/HDB3/CRC4</option>
            	   <?php	break;
            	   default:
            	   	break;
            	} ?>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_channels"><?php echo ('Channels')?>:</label>
            </td>
            <td>
                <span id="editspan_<?php echo $key?>_channels"><?php echo "{$span['definedchans']}/{$span['totchans']}"?> <?php echo !empty($span['spantype']) ? "({$span['spantype']})" : "" ?></span>
            </td>
        </tr>
        <!--<tr id="editspan_<?php echo $key?>_reserved_ch" style="<?php if(isset($span['signalling']) && (($span['signalling'] != 'pri_net') || ($span['signalling'] != 'pri_cpe'))) { ?>display:none;<?php } ?>">
            <td style="width:10px;">
                <label>DChannel:</label>
            </td>
            <td>
                <?php echo $span['reserved_ch'];?>
            </td>
        </tr>
        -->
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_signalling"><?php echo ('Signaling')?>:</label>
            </td>
            <td>
                <select id="editspan_<?php echo $key?>_signalling" name="editspan_<?php echo $key?>_signalling">
                    <option value="--" disabled>--PRI--</option>
					<option value="pri_cpe" <?php echo set_default($span['signalling'],'pri_cpe'); ?>>PRI - CPE</option>
            		<option value="pri_net" <?php echo set_default($span['signalling'],'pri_net'); ?>>PRI - Net</option>
                    <option value="--" disabled>--E &amp; M--</option>
            		<option value="em" <?php echo set_default($span['signalling'],'em'); ?>>E &amp; M</option>
            		<option value="em_w" <?php echo set_default($span['signalling'],'em_w'); ?>>E &amp; M -- Wink</option>
            		<option value="featd" <?php echo set_default($span['signalling'],'featd'); ?>>E &amp; M -- fead(DTMF)</option>
                    <option value="--" disabled>--<?php echo ('ANALOG')?>--</option>
            		<option value="fxo_ks" <?php echo set_default($span['signalling'],'fxo_ks'); ?>>FXOKS</option>
            		<option value="fxo_ls" <?php echo set_default($span['signalling'],'fxo_ls'); ?>>FXOLS</option>
				    <option value="--" disabled>--RHNIO--</option>
					<option value="fxs_ks" <?php echo set_default($span['signalling'],'fxs_ks'); ?>>FXSKS</option>
					<option value="fxs_ls" <?php echo set_default($span['signalling'],'fxs_ls'); ?>>FXSLS</option>
                    <option value="--" disabled>--BRI--</option>
                    <option value="bri_net" <?php echo set_default($span['signalling'],'bri_net'); ?>>BRI PTP - Net</option>
                    <option value="bri_cpe" <?php echo set_default($span['signalling'],'bri_cpe'); ?>>BRI PTP - CPE</option>
                    <option value="bri_net_ptmp" <?php echo set_default($span['signalling'],'bri_net_ptmp'); ?>>BRI PTMP - Net</option>
                    <option value="bri_cpe_ptmp" <?php echo set_default($span['signalling'],'bri_cpe_ptmp'); ?>>BRI PTMP - CPE</option>
            	</select>
            </td>
        </tr>
        <?php $display = (substr($span['signalling'],0,3) == 'bri' || $span['totchans'] != 3 || substr($span['signalling'],0,3) == 'pri') ? 'show' : 'none'; ?>
            <tr id="editspan_<?php echo $key?>_switchtype_tr" style="display:<?php echo $display?>;">
                <td style="width:10px;">
                	<label for="editspan_<?php echo $key?>_switchtype"><?php echo ('Switchtype')?>:</label>
                </td>
                <td>
                 	<select id="editspan_<?php echo $key?>_switchtype" name="editspan_<?php echo $key?>_switchtype">
                		<option value="national" <?php echo set_default($span['switchtype'],'national'); ?>>National ISDN 2 (<?php echo ('default')?>)</option>
                		<option value="dms100" <?php echo set_default($span['switchtype'],'dms100'); ?>>Nortel DMS100</option>
                		<option value="4ess" <?php echo set_default($span['switchtype'],'4ess'); ?>>AT&amp;T 4ESS</option>
                		<option value="5ess" <?php echo set_default($span['switchtype'],'5ess'); ?>>Lucent 5ESS</option>
                		<option value="euroisdn" <?php echo set_default($span['switchtype'],'euroisdn'); ?>>EuroISDN</option>
                		<option value="ni1" <?php echo set_default($span['switchtype'],'ni1'); ?>>Old National ISDN 1</option>
                		<option value="qsig" <?php echo set_default($span['switchtype'],'qsig'); ?>>Q.SIG</option>
                	</select>
                </td>
            </tr>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_syncsrc"><?php echo ('Sync/Clock Source')?>:</label>
            </td>
            <td>
                <select id="editspan_<?php echo $key?>_syncsrc" name="editspan_<?php echo $key?>_syncsrc">
            	<?php for($i=0; $i<=$dahdi_cards->get_span_count($span['location']); $i++): ?>
            		<option value="<?php echo $i?>" <?php echo set_default($span['syncsrc'],$i); ?>><?php echo $i?></option>
            	<?php endfor; ?>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_lbo"><?php echo ('Line Build Out')?>:</label>
            </td>
            <td>
                <select id="editspan_<?php echo $key?>_lbo" name="editspan_<?php echo $key?>_lbo">
            		<option value="0" <?php echo set_default($span['lbo'],0); ?>>0 db (CSU)/0-133 feet (DSX-1)</option>
            		<option value="1" <?php echo set_default($span['lbo'],1); ?>>133-266 feet (DSX-1)</option>
            		<option value="2" <?php echo set_default($span['lbo'],2); ?>>266-399 feet (DSX-1)</option>
            		<option value="3" <?php echo set_default($span['lbo'],3); ?>>399-533 feet (DSX-1)</option>
            		<option value="4" <?php echo set_default($span['lbo'],4); ?>>533-655 feet (DSX-1)</option>
            		<option value="5" <?php echo set_default($span['lbo'],5); ?>>-7.5db (CSU)</option>
            		<option value="6" <?php echo set_default($span['lbo'],6); ?>>-15db (CSU)</option>
            		<option value="7" <?php echo set_default($span['lbo'],7); ?>>-22.5db (CSU)</option>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_pridialplan"><?php echo ('PRI Dialplan')?>:</label>
            </td>
            <td>
              	<select id="editspan_<?php echo $key?>_pridialplan" name="editspan_<?php echo $key?>_pridialplan">
            		<option value="national" <?php echo set_default($span['pridialplan'],'national'); ?>><?php echo ('National')?></option>
            		<option value="dynamic" <?php echo set_default($span['pridialplan'],'dynamic'); ?>><?php echo ('Dynamic')?></option>
            		<option value="unknown" <?php echo set_default($span['pridialplan'],'unknown'); ?>><?php echo ('Unknown')?></option>
            		<option value="local" <?php echo set_default($span['pridialplan'],'local'); ?>><?php echo ('Local')?></option>
            		<option value="private" <?php echo set_default($span['pridialplan'],'private'); ?>><?php echo ('Private')?></option>
            		<option value="international" <?php echo set_default($span['pridialplan'],'international'); ?>><?php echo ('International')?></option>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
            	<label for="editspan_<?php echo $key?>_prilocaldialplan"><?php echo ('PRI Local Dialplan')?>:</label>
            </td>
            <td>
                <select id="editspan_<?php echo $key?>_prilocaldialplan" name="editspan_<?php echo $key?>_prilocaldialplan">
            		<option value="national" <?php echo set_default($span['prilocaldialplan'],'national'); ?>><?php echo ('National')?></option>
            		<option value="dynamic" <?php echo set_default($span['prilocaldialplan'],'dynamic'); ?>><?php echo ('Dynamic')?></option>
            		<option value="unknown" <?php echo set_default($span['prilocaldialplan'],'unknown'); ?>><?php echo ('Unknown')?></option>
            		<option value="local" <?php echo set_default($span['prilocaldialplan'],'local'); ?>><?php echo ('Local')?></option>
            		<option value="private" <?php echo set_default($span['prilocaldialplan'],'private'); ?>><?php echo ('Private')?></option>
            		<option value="international" <?php echo set_default($span['prilocaldialplan'],'international'); ?>><?php echo ('International')?></option>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_priexclusive"><?php echo ('PRI Exclusive')?>:</label>
            </td>
            <td>
                <select id="editspan_<?php echo $key?>_priexclusive" name="editspan_<?php echo $key?>_priexclusive">
                    <option value="" <?php echo set_default($span['priexclusive'],''); ?>></option>
                    <option value="no" <?php echo set_default($span['priexclusive'],'no'); ?>><?php echo ('No')?></option>
                    <option value="yes" <?php echo set_default($span['priexclusive'],'yes'); ?>><?php echo ('Yes')?></option>
                </select>
            </td>
        </tr>
		<?php } else { ?>
	        <tr>
	            <td style="width:10px;">
	                <label for="editspan_<?php echo $key?>_signalling"><?php echo ('Signaling')?>:</label>
	            </td>
	            <td>
	                <select id="editspan_<?php echo $key?>_signalling" name="editspan_<?php echo $key?>_signalling">
	            		<option value="gsm" <?php echo set_default($span['signalling'],'gsm'); ?>>gsm</option>
	            	</select>
	            </td>
	        </tr>
		<?php } ?>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_rxgain"><a href="#" class="info"><?php echo ('Receive Gain')?><span><?php echo ('The values are in db (decibels). A positive number increases the volume level on a channel, and a negative value decreases volume level.')?></span></a></label>
            </td>
            <td>
                <input type="text" name="editspan_<?php echo $key?>_rxgain" id="editspan_<?php echo $key?>_rxgain" value="<?php echo $span['rxgain']; ?>">
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_txgain"><a href="#" class="info"><?php echo ('Transmit Gain')?><span><?php echo ('The values are in db (decibels). A positive number increases the volume level on a channel, and a negative value decreases volume level.')?></span></a></label>
            </td>
            <td>
                <input type="text" name="editspan_<?php echo $key?>_txgain" id="editspan_<?php echo $key?>_txgain" value="<?php echo $span['txgain']; ?>">
            </td>
        </tr>
    </table>
    <br />
    <h2><?php echo ('Group Settings')?> (<a style="cursor:pointer;" onclick="reset_digital_groups(<?php echo $key;?>,<?php echo $span['totchans']-1?>);"><?php echo ('Reset Groups')?></a>)</h2>
    <hr>
    <?php $groups = json_decode($span['additional_groups'],TRUE);
        foreach($groups as $gkey => $data) {
    ?>
    <table width="100%" id="editspan_<?php echo $key?>_group_settings_<?php echo $gkey?>" style="text-align:left;" border="0" cellspacing="0">
        <tr>
            <td style="width:10px;">
                <label><a href="#" class="info"><?php echo ('Group')?>:<span><?php echo ("Group Number, use 's' to skip said group")?></span></a></label>
            </td>
            <td>
        	    <input type="text" id="editspan_<?php echo $key?>_group_<?php echo $gkey?>" name="editspan_<?php echo $key?>_group_<?php echo $gkey?>" size="2" value="<?php echo set_default($data['group']); ?>" />
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label><?php echo ('Context')?>: </label>
            </td>
            <td>
                <input type="text" id="editspan_<?php echo $key?>_context_<?php echo $gkey?>" name="editspan_<?php echo $key?>_context_<?php echo $gkey?>" value="<?php echo set_default($data['context']); ?>" />
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label><?php echo ('Used Channels')?>: </label>
            </td>
            <td>
                <select id="editspan_<?php echo $key?>_definedchans_<?php echo $gkey?>" name="editspan_<?php echo $key?>_definedchans_<?php echo $gkey?>">
            	<?php for($i=1; $i<=($span['totchans']-1); $i++) { ?>
                    <?php $data['usedchans'] = ($data['usedchans'] == $span['totchans']) ? ($data['usedchans'] -1) : $data['usedchans']; ?>
            		<option value="<?php echo $i?>" <?php echo set_default($data['usedchans'],$i); ?>><?php echo $i?></option>
            	<?php } ?>
            	</select>
              <?php echo ('From')?>: <span id="editspan_<?php echo $key?>_from_<?php echo $gkey?>"><?php echo $data['fxx'];?></span>
              <?php echo ('Reserved')?>: <span id="editspan_<?php echo $key?>_reserved_<?php echo $gkey?>"><?php echo $span['reserved_ch']?></span>
            	<input type="hidden" id="editspan_<?php echo $key?>_endchan_<?php echo $gkey?>" name="editspan_<?php echo $key?>_endchan_<?php echo $gkey?>" value="<?php echo $data['endchan']; ?>" />
            	<input type="hidden" id="editspan_<?php echo $key?>_startchan_<?php echo $gkey?>" name="editspan_<?php echo $key?>_startchan_<?php echo $gkey?>" value="<?php echo $data['startchan']; ?>" />
            </td>
        </tr>
    </table>
    <?php } ?>
</form>
