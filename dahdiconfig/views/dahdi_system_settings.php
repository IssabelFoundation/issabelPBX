<div class="modal animate__animated animate__fadeIn" id="syssettings">
  <div class="modal-background"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title" style="margin-bottom:0;"><?php echo _('System Settings')?></p>
      <button class="delete" aria-label="close"></button>
    </header>
    <section class="modal-card-body">
    <form id="form-systemsettings" action="config.php?quietmode=1&amp;handler=file&amp;module=dahdiconfig&amp;file=ajax.html.php&amp;type=systemsettingssubmit">

    <div class='columns mb-5'><div class='column'>
        <div class='is-size-7'><?php echo sprintf(_('This edits all settings in %s'),'system.conf')?></div>
    </div></div>
            <div class='columns'>
                <div class='column'>
                    <a href="#" class="info"><?php echo _('Tone Region')?><span><?php echo _('Please choose your country or your nearest neighboring country for default Tones (Ex: dialtone, busy tone, ring tone etc.)')?></span></a>
                </div>
                <div class='column'>
                    <select id="xxx-tone_region" name="tone_region" class='componentSelect'>
                		<option value="us" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'us'); ?>><?php echo _('United States/North America')?></option>
                		<option value="au" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'au'); ?>><?php echo _('Australia')?></option>
                		<option value="fr" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'fr'); ?>><?php echo _('France')?></option>
                		<option value="nl" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'nl'); ?>><?php echo _('Netherlands')?></option>
                		<option value="uk" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'uk'); ?>><?php echo _('United Kingdom')?></option>
                		<option value="fi" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'fi'); ?>><?php echo _('Finland')?></option>
                		<option value="es" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'es'); ?>><?php echo _('Spain')?></option>
                		<option value="jp" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'jp'); ?>><?php echo _('Japan')?></option>
                		<option value="no" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'no'); ?>><?php echo _('Norway')?></option>
                		<option value="at" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'at'); ?>><?php echo _('Austria')?></option>
                		<option value="nz" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'nz'); ?>><?php echo _('New Zealand')?></option>
                		<option value="it" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'it'); ?>><?php echo _('Italy')?></option>
                		<option value="us-old" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'us-old'); ?>><?php echo _('United States Circa 1950 / North America')?></option>
                		<option value="gr" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'gr'); ?>><?php echo _('Greece')?></option>
                		<option value="tw" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'tw'); ?>><?php echo _('Taiwan')?></option>
                		<option value="cl" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'cl'); ?>><?php echo _('Chile')?></option>
                		<option value="se" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'se'); ?>><?php echo _('Sweden')?></option>
                		<option value="be" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'be'); ?>><?php echo _('Belgium')?></option>
                		<option value="sg" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'sg'); ?>><?php echo _('Singapore')?></option>
                		<option value="il" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'il'); ?>><?php echo _('Israel')?></option>
                		<option value="br" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'br'); ?>><?php echo _('Brazil')?></option>
                		<option value="hu" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'hu'); ?>><?php echo _('Hungary')?></option>
                		<option value="lt" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'lt'); ?>><?php echo _('Lithuania')?></option>
                		<option value="pl" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'pl'); ?>><?php echo _('Poland')?></option>
                		<option value="za" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'za'); ?>><?php echo _('South Africa')?></option>
                		<option value="pt" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'pt'); ?>><?php echo _('Portugal')?></option>
                		<option value="ee" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ee'); ?>><?php echo _('Estonia')?></option>
                		<option value="mx" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'mx'); ?>><?php echo _('Mexico')?></option>
                		<option value="in" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'in'); ?>><?php echo _('India')?></option>
                		<option value="de" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'de'); ?>><?php echo _('Germany')?></option>
                		<option value="ch" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ch'); ?>><?php echo _('Switzerland')?></option>
                		<option value="dk" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'dk'); ?>><?php echo _('Denmark')?></option>
                		<option value="cz" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'cz'); ?>><?php echo _('Czech Republic')?></option>
                		<option value="cn" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'cn'); ?>><?php echo _('China')?></option>
                		<option value="ar" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ar'); ?>><?php echo _('Argentina')?></option>
                		<option value="my" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'my'); ?>><?php echo _('Malaysia')?></option>
                		<option value="th" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'th'); ?>><?php echo _('Thailand')?></option>
                		<option value="bg" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'bg'); ?>><?php echo _('Bulgaria')?></option>
                		<option value="ve" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ve'); ?>><?php echo _('Venezuela')?></option>
                		<option value="ph" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ph'); ?>><?php echo _('Philippines')?></option>
                		<option value="ru" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ru'); ?>><?php echo _('Russian Federation')?></option>
                	</select>
                </div>
            </div>
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
            <div class='columns'>
                <div class='column'>
                    <?php echo _('Other Dahdi System Settings')?>
                </div>
            </div>
            <div class='columns' id="dh_system_additional_0">
                <div class='column'>
                    <a href="#" onclick="dh_system_delete_field(0)"><button type="button" class="is-danger button is-small"><span class="icon is-small"><i class="fa fa-trash"></i></span></button></a>
                    <input type="hidden" name="dh_system_add[]" value="0" />
                    <input type="hidden" id="xxx-dh_system_origsetting_key_0" name="dh_system_origsetting_key_0" value="<?php echo $dh_s_key?>" />
                    <input type="text" class="valueinput" id="xxx-dh_system_setting_key_0" name="dh_system_setting_key_0" value="<?php echo $dh_s_key?>" /> =
                    <input type="text" class="valueinput" id="xxx-dh_system_setting_value_0" name="dh_system_setting_value_0" value="<?php echo $dh_s_val?>" /> <br />
                </div>
            </div>
            <?php
            $a = 1;
            foreach($ss as $key => $value) {
                if(!in_array($key,$dahdi_cards->original_system)) {
                    ?>
                    <div class='columns' id="dh_system_additional_<?php echo $a?>">
                        <div class='column'>
                            <a href="#" onclick="dh_system_delete_field(<?php echo $a?>)"><button type="button" class="is-danger button is-small"><span class="icon is-small"><i class="fa fa-trash"></i></span></button></a>
                            <input type="hidden" name="dh_system_add[]" value="<?php echo $a?>" />
                            <input type="hidden" id="xxx-dh_system_origsetting_key_<?php echo $a?>"name="dh_system_origsetting_key_<?php echo $a?>" value="<?php echo $key?>" />
                            <input type="text" class="valueinput" id="xxx-dh_system_setting_key_<?php echo $a?>" name="dh_system_setting_key_<?php echo $a?>" value="<?php echo $key?>" /> =
                            <input type="text" class="valueinput" id="xxx-dh_system_setting_value_<?php echo $a?>" name="dh_system_setting_value_<?php echo $a?>" value="<?php echo $value?>" /> <br />
                        </div>
                    </div>
                    <?php
                    $a++;
                }
            }
            ?>
            <div class='columns' id="dh_system_add">
            <div class='column'>
                <a style="cursor: pointer;" onclick="dh_system_add_field(<?php echo $a?>)" class='button is-small is-rounded'><?php echo _("Add another field");?></a>
            </div>
        </div>


    </form>
    </section>
    <footer class="modal-card-foot">
      <button data-target="form-systemsettings" class="button is-success formsubmit"><?php echo _('Save')?></button>
      <button class="button"><?php echo _('Cancel')?></button>
    </footer>
  </div>
</div>



