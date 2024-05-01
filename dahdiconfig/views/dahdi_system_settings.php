<div class="modal animate__animated animate__fadeIn" id="syssettings">
  <div class="modal-background"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title" style="margin-bottom:0;"><?php echo __('System Settings')?></p>
      <button class="delete" aria-label="close"></button>
    </header>
    <section class="modal-card-body">
    <form id="form-systemsettings" action="config.php?quietmode=1&amp;handler=file&amp;module=dahdiconfig&amp;file=ajax.html.php&amp;type=systemsettingssubmit">

    <div class='columns mb-5'><div class='column'>
        <div class='is-size-7'><?php echo sprintf(__('This edits all settings in %s'),'system.conf')?></div>
    </div></div>
            <div class='columns'>
                <div class='column'>
                    <a href="#" class="info"><?php echo __('Tone Region')?><span><?php echo __('Please choose your country or your nearest neighboring country for default Tones (Ex: dialtone, busy tone, ring tone etc.)')?></span></a>
                </div>
                <div class='column'>
                    <select id="xxx-tone_region" name="tone_region" class='componentSelect'>
                		<option value="us" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'us'); ?>><?php echo __('United States/North America')?></option>
                		<option value="au" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'au'); ?>><?php echo __('Australia')?></option>
                		<option value="fr" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'fr'); ?>><?php echo __('France')?></option>
                		<option value="nl" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'nl'); ?>><?php echo __('Netherlands')?></option>
                		<option value="uk" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'uk'); ?>><?php echo __('United Kingdom')?></option>
                		<option value="fi" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'fi'); ?>><?php echo __('Finland')?></option>
                		<option value="es" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'es'); ?>><?php echo __('Spain')?></option>
                		<option value="jp" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'jp'); ?>><?php echo __('Japan')?></option>
                		<option value="no" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'no'); ?>><?php echo __('Norway')?></option>
                		<option value="at" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'at'); ?>><?php echo __('Austria')?></option>
                		<option value="nz" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'nz'); ?>><?php echo __('New Zealand')?></option>
                		<option value="it" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'it'); ?>><?php echo __('Italy')?></option>
                		<option value="us-old" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'us-old'); ?>><?php echo __('United States Circa 1950 / North America')?></option>
                		<option value="gr" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'gr'); ?>><?php echo __('Greece')?></option>
                		<option value="tw" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'tw'); ?>><?php echo __('Taiwan')?></option>
                		<option value="cl" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'cl'); ?>><?php echo __('Chile')?></option>
                		<option value="se" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'se'); ?>><?php echo __('Sweden')?></option>
                		<option value="be" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'be'); ?>><?php echo __('Belgium')?></option>
                		<option value="sg" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'sg'); ?>><?php echo __('Singapore')?></option>
                		<option value="il" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'il'); ?>><?php echo __('Israel')?></option>
                		<option value="br" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'br'); ?>><?php echo __('Brazil')?></option>
                		<option value="hu" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'hu'); ?>><?php echo __('Hungary')?></option>
                		<option value="lt" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'lt'); ?>><?php echo __('Lithuania')?></option>
                		<option value="pl" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'pl'); ?>><?php echo __('Poland')?></option>
                		<option value="za" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'za'); ?>><?php echo __('South Africa')?></option>
                		<option value="pt" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'pt'); ?>><?php echo __('Portugal')?></option>
                		<option value="ee" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ee'); ?>><?php echo __('Estonia')?></option>
                		<option value="mx" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'mx'); ?>><?php echo __('Mexico')?></option>
                		<option value="in" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'in'); ?>><?php echo __('India')?></option>
                		<option value="de" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'de'); ?>><?php echo __('Germany')?></option>
                		<option value="ch" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ch'); ?>><?php echo __('Switzerland')?></option>
                		<option value="dk" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'dk'); ?>><?php echo __('Denmark')?></option>
                		<option value="cz" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'cz'); ?>><?php echo __('Czech Republic')?></option>
                		<option value="cn" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'cn'); ?>><?php echo __('China')?></option>
                		<option value="ar" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ar'); ?>><?php echo __('Argentina')?></option>
                		<option value="my" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'my'); ?>><?php echo __('Malaysia')?></option>
                		<option value="th" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'th'); ?>><?php echo __('Thailand')?></option>
                		<option value="bg" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'bg'); ?>><?php echo __('Bulgaria')?></option>
                		<option value="ve" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ve'); ?>><?php echo __('Venezuela')?></option>
                		<option value="ph" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ph'); ?>><?php echo __('Philippines')?></option>
                		<option value="ru" <?php echo set_default($dahdi_cards->get_systemsettings('tone_region'),'ru'); ?>><?php echo __('Russian Federation')?></option>
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
                    <?php echo __('Other Dahdi System Settings')?>
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
                <a style="cursor: pointer;" onclick="dh_system_add_field(<?php echo $a?>)" class='button is-small is-rounded'><?php echo __("Add another field");?></a>
            </div>
        </div>


    </form>
    </section>
    <footer class="modal-card-foot">
      <button data-target="form-systemsettings" class="button is-success formsubmit"><?php echo __('Save')?></button>
      <button class="button"><?php echo __('Cancel')?></button>
    </footer>
  </div>
</div>



