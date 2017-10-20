<form id="form-systemsettings" action="config.php?quietmode=1&amp;handler=file&amp;module=dahdiconfig&amp;file=ajax.html.php&amp;type=systemsettingssubmit">
    <div id="systems">
        <h2><?php echo ('System Settings')?></h2>
        <h3><?php echo ('This edits all settings in system.conf')?></h3>
        <hr />
        <table width="100%" style="text-align:left;">
            <tr>
                <td style="width:10px;">
                    <label for="tone_region"><a href="#" class="info"><?php echo ('Tone Region')?>:<span><?php echo ('Please choose your country or your nearest neighboring country for default Tones (Ex: dialtone, busy tone, ring tone etc.)')?></span></a></label>

                </td>
                <td>
                    <select id="tone_region" name="tone_region">
                		<option value="us" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'us'); ?>><?php echo ('United States/North America')?></option>
                		<option value="au" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'au'); ?>><?php echo ('Australia')?></option>
                		<option value="fr" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'fr'); ?>><?php echo ('France')?></option>
                		<option value="nl" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'nl'); ?>><?php echo ('Netherlands')?></option>
                		<option value="uk" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'uk'); ?>><?php echo ('United Kingdom')?></option>
                		<option value="fi" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'fi'); ?>><?php echo ('Finland')?></option>
                		<option value="es" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'es'); ?>><?php echo ('Spain')?></option>
                		<option value="jp" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'jp'); ?>><?php echo ('Japan')?></option>
                		<option value="no" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'no'); ?>><?php echo ('Norway')?></option>
                		<option value="at" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'at'); ?>><?php echo ('Austria')?></option>
                		<option value="nz" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'nz'); ?>><?php echo ('New Zealand')?></option>
                		<option value="it" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'it'); ?>><?php echo ('Italy')?></option>
                		<option value="us-old" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'us-old'); ?>><?php echo ('United States Circa 1950 / North America')?></option>
                		<option value="gr" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'gr'); ?>><?php echo ('Greece')?></option>
                		<option value="tw" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'tw'); ?>><?php echo ('Taiwan')?></option>
                		<option value="cl" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'cl'); ?>><?php echo ('Chile')?></option>
                		<option value="se" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'se'); ?>><?php echo ('Sweden')?></option>
                		<option value="be" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'be'); ?>><?php echo ('Belgium')?></option>
                		<option value="sg" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'sg'); ?>><?php echo ('Singapore')?></option>
                		<option value="il" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'il'); ?>><?php echo ('Israel')?></option>
                		<option value="br" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'br'); ?>><?php echo ('Brazil')?></option>
                		<option value="hu" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'hu'); ?>><?php echo ('Hungary')?></option>
                		<option value="lt" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'lt'); ?>><?php echo ('Lithuania')?></option>
                		<option value="pl" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'pl'); ?>><?php echo ('Poland')?></option>
                		<option value="za" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'za'); ?>><?php echo ('South Africa')?></option>
                		<option value="pt" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'pt'); ?>><?php echo ('Portugal')?></option>
                		<option value="ee" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ee'); ?>><?php echo ('Estonia')?></option>
                		<option value="mx" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'mx'); ?>><?php echo ('Mexico')?></option>
                		<option value="in" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'in'); ?>><?php echo ('India')?></option>
                		<option value="de" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'de'); ?>><?php echo ('Germany')?></option>
                		<option value="ch" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ch'); ?>><?php echo ('Switzerland')?></option>
                		<option value="dk" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'dk'); ?>><?php echo ('Denmark')?></option>
                		<option value="cz" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'cz'); ?>><?php echo ('Czech Republic')?></option>
                		<option value="cn" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'cn'); ?>><?php echo ('China')?></option>
                		<option value="ar" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ar'); ?>><?php echo ('Argentina')?></option>
                		<option value="my" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'my'); ?>><?php echo ('Malaysia')?></option>
                		<option value="th" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'th'); ?>><?php echo ('Thailand')?></option>
                		<option value="bg" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'bg'); ?>><?php echo ('Bulgaria')?></option>
                		<option value="ve" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ve'); ?>><?php echo ('Venezuela')?></option>
                		<option value="ph" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ph'); ?>><?php echo ('Philippines')?></option>
                		<option value="ru" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ru'); ?>><?php echo ('Russian Federation')?></option>
                	</select>
                </td>
            </tr>
        </table>
        <?php
        $ss = $dahdi_cards->get_all_systemsettings();
        $dh_s_key = '';
        $dh_s_val = '';
        foreach($ss as $key => $value) {
            if(!in_array($key,$dahdi_cards->original_system) && !strpos($key, 'checkbox')) {
                $dh_s_key = $key;
                $dh_s_val = $value;
                unset($ss[$key]);
                break;
            }
        }
        ?>
        <table width="100%" style="text-align:left;">
            <tr id="dh_system_additional_0">
                <td style="width:10px;vertical-align:top;">
                    <label><?php echo ('Other Dahdi System Settings')?>: </label>
                </td>
                <td style="vertical-align:middle;">
                    <a href="#" onclick="dh_system_delete_field(0)"><img height="10px" src="images/trash.png"></a>
                    <input type="hidden" name="dh_system_add[]" value="0" />
                    <input type="hidden" id="dh_system_origsetting_key_0" name="dh_system_origsetting_key_0" value="<?php echo $dh_s_key?>" />
                    <input id="dh_system_setting_key_0" name="dh_system_setting_key_0" value="<?php echo $dh_s_key?>" /> =
                    <input id="dh_system_setting_value_0" name="dh_system_setting_value_0" value="<?php echo $dh_s_val?>" /> <br />
                </td>
            </tr>
            <?php
            $a = 1;
            foreach($ss as $key => $value) {
                if(!in_array($key,$dahdi_cards->original_system)) {
                    ?>
                    <tr id="dh_system_additional_<?php echo $a?>">
                        <td style="width:10px;vertical-align:top;">
                        </td>
                        <td style="vertical-align:middle;">
                            <a href="#" onclick="dh_system_delete_field(<?php echo $a?>)"><img height="10px" src="images/trash.png"></a>
                            <input type="hidden" name="dh_system_add[]" value="<?php echo $a?>" />
                            <input type="hidden" id="dh_system_origsetting_key_<?php echo $a?>"name="dh_system_origsetting_key_<?php echo $a?>" value="<?php echo $key?>" />
                            <input id="dh_system_setting_key_<?php echo $a?>" name="dh_system_setting_key_<?php echo $a?>" value="<?php echo $key?>" /> =
                            <input id="dh_system_setting_value_<?php echo $a?>" name="dh_system_setting_value_<?php echo $a?>" value="<?php echo $value?>" /> <br />
                        </td>
                    </tr>
                    <?php
                    $a++;
                }
            }
            ?>
            <tr id="dh_system_add">
                <td>
                </td>
                <td>
                    <a style="cursor: pointer;" onclick="dh_system_add_field(<?php echo $a?>)"><img src="assets/dahdiconfig/images/add.png"></a>
                </td>
            </tr>
        </table>
    </div>
</form>
