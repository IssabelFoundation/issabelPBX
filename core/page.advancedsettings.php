<?php /* $Id */
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }

$getvars = array('action', 'keyword', 'value');
foreach ($getvars as $v){
    $var[$v] = isset($_POST[$v]) ? $_POST[$v] : 0;
}

if($var['action'] === 'setkey') {
    header("Content-type: application/json");
    $keyword = $var['keyword'];
    if ($issabelpbx_conf->conf_setting_exists($keyword)) {
        //special cron manager detections
        if($keyword == 'CRONMAN_UPDATES_CHECK') {
            $cm =& cronmanager::create($db);
            if($var['value']) {
                $cm->enable_updates();
            } else {
                $cm->disable_updates();
            }
        }
        $issabelpbx_conf->set_conf_values(array($keyword => trim($var['value'])),true,$amp_conf['AS_OVERRIDE_READONLY']);
        $status = $issabelpbx_conf->get_last_update_status();
        if ($status[$keyword]['saved']) {
            issabelpbx_log(IPBX_LOG_INFO,sprintf(__("Advanced Settings changed issabelpbx_conf setting: [$keyword] => [%s]"),$var['value']));
            needreload();
        }
        echo json_encode($status[$keyword]);
        exit;
    }
    exit;
}
$amportal_canwrite = $issabelpbx_conf->amportal_canwrite() ? 'true' : 'false';
echo '<script>';
echo 'can_write_amportalconf = ' . $amportal_canwrite . '; ';
echo 'amportalconf_error = "' . __("You must run 'amportal restart' from the Linux command line before you can save setting here.") . '";';
echo 'msgUnsavedChanges = "' . __("You have un-saved changes, press OK to disregard changes and reload page or Cancel to abort.") . '";';
echo 'msgChangesRefresh = "' . __("Your Display settings have been changed, click on 'Refresh Page' to view the affects of your changes once you have saved other outstanding changes that are still un-confirmed.") . '";';
echo '$(function(){';
echo "$('select').chosen({width:'50%', disable_search: true});";

echo '
$("#pagesearch").on("keyup search",function(evt) {
filter=$(this).val().toLowerCase();

if(filter.length > 2) {
var showcat = [];
$(".categorytitle").hide();

$("div.tdsearch").each(function() {

    if($(this).text().toLowerCase().indexOf(filter)>=0) {

        container_tr   = $(this).parent();
        const box = $(this).parent()[0];
        let first;
        let placeholder = box.previousElementSibling;
        while (placeholder) {
          if (placeholder.classList.contains("categorytitle")) {
            first = placeholder;
            break;
          }
          placeholder = placeholder.previousElementSibling;
        }
        showcat.push($(first).attr("id"));
	container_tr.show();
    } else {
        $(this).parent().hide();
    }
});
showcat.forEach(function(item) {
if(typeof(item)!="undefined") {
    $("#"+item).show();
}
});
} else {
$(".categorytitle").show();
$(".trsearch").show();
}

});
';

echo '$("#cancelsearch").on("click",function() { $("#pagesearch").val("");});';

echo '});';
echo '</script>';

echo '<div class="content">';
echo '<div id="main_page">';
echo "<h2>".__("IssabelPBX Advanced Settings")."</h2>";
echo '<div class="notification is-warning"><p>'.__("<b>IMPORTANT:</b> Use extreme caution when making changes!").'</p><p>'.__("Some of these settings can render your system inoperable. You are urged to backup before making any changes. Readonly settings are usually more volatile, they can be changed by changing 'Override Readonly Settings' to true. Once changed you must save the setting by checking the green check box that appears. You can restore the default setting by clicking on the icon to the right of the values if not set at default.").'</p></div>';

$conf                    = $issabelpbx_conf->get_conf_settings();

$display_level            = 10; // TO confusing with multiple levels $conf['AS_DISPLAY_DETAIL_LEVEL']['value'];
$display_hidden            = $conf['AS_DISPLAY_HIDDEN_SETTINGS']['value'];
$display_readonly        = $conf['AS_DISPLAY_READONLY_SETTINGS']['value'];
$display_friendly_name    = $conf['AS_DISPLAY_FRIENDLY_NAME']['value'];

$current_category        = '';
$row                    = 0;



echo '<div class="columns">';

echo '<div  class="column">';
echo '<div class="field mt-3">
  <div class="control has-icons-left">
    <input class="input is-rounded" type="search" id="pagesearch" placeholder="'.__('Search').'">
    <span class="icon is-small is-left">
      <i class="fa fa-search fa-xs"></i>
    </span>
  </div>
</div>';


echo '</div></div>';
$catcont=0;
foreach ($conf as $c){

    if ($c['level'] > $display_level || $c['hidden'] && !$display_hidden || $c['readonly'] && !$display_readonly) {
        continue;
    }
    if ($current_category != $c['category']) {
        $current_category = $c['category'];
        $catcont++;
        $current_category_loc = modgettext::_($current_category, $c['module']);
        echo '<div class="columns categorytitle mt-5" id="cat'.$catcont.'"><div class="column is-12"><h4 class="category">'.__("$current_category_loc").'</h4></div></div>';
        $row++;
    }

    $name_label_raw = $c['name'];
    $description_raw = $c['description'];
    $name_label = modgettext::_($name_label_raw, $c['module']);
    $tt_description = modgettext::_($description_raw, $c['module']);
    if (!$display_friendly_name) {
        $tr_friendly_name = $name_label;
        $name_label = $c['keyword'];
    }

    $row++;
    $dv = $c['type'] == CONF_TYPE_BOOL ? ($c['defaultval'] ? __("True") : __("False")) : $c['defaultval'];
    $default_val = $dv == '' ? __("No Default Provided") : sprintf(__("Default Value: %s"),$dv);
    if ($c['emptyok'] && $c['type'] != CONF_TYPE_BOOL && $c['type'] != CONF_TYPE_SELECT && $c['type'] != CONF_TYPE_FSELECT) {
        $default_val.= ', '.__("field can be left blank");
    }
    if ($c['type'] == CONF_TYPE_INT && $c['options']) {
        $range = explode(',',$c['options']);
        $default_val .= '<br />'.sprintf(__("Acceptable Values: %s - %s"),$range[0],$range[1]);
    }
    if ($display_friendly_name) {
        $default_val .= '<br />'.sprintf(__("Internal Name: %s"),$c['keyword']);
    } else {
        $default_val .= '<br />'.sprintf(__("Friendly Name: %s"),$tr_friendly_name);
    }
    echo '<div class="columns trsearch">';
    echo '<div class="column tdsearch"><a href="javascript:void(null)" class="info">'.$name_label.'<span>'.htmlspecialchars($tt_description).'<br><br>'.$default_val.'</span></a></div>';
    echo '<div class="column">';
    switch ($c['type']) {
        case CONF_TYPE_TEXT:
        case CONF_TYPE_DIR:
        case CONF_TYPE_INT:
            $readonly = !$c['readonly'] || $amp_conf['AS_OVERRIDE_READONLY'] && !$c['hidden'] ? '' : 'readonly="readonly"';
            $extracss = ($c['keyword']=='AMPMGRPASS')?" confidential ":"";
            echo '<input class="valueinput '.$extracss.'" id="'.$c['keyword'].'" type="text" size="60" value="'.htmlspecialchars($amp_conf[$c['keyword']]).'" data-valueinput-orig="'.$amp_conf[$c['keyword']].'" '.$readonly.'/>';
            break;
        case CONF_TYPE_SELECT:
            echo '<select class="valueinput" id="'.$c['keyword'].'" data-valueinput-orig="'.$amp_conf[$c['keyword']].'">';
                $opt = explode(',',$c['options']);
                foreach($opt as $o) {
                    $selected = ($amp_conf[$c['keyword']] == $o) ? ' selected ' : '';
                    echo '<option value="'.$o.'"'.$selected.'>'.$o.'</option>';
                }
            echo '</select>';
            break;
        case CONF_TYPE_FSELECT:
            echo '<select class="valueinput" id="'.$c['keyword'].'" data-valueinput-orig="'.$amp_conf[$c['keyword']].'">';
                $opt = $c['options'];
                foreach($opt as $o => $l) {
                    $selected = ($amp_conf[$c['keyword']] == $o) ? ' selected ' : '';
                    echo '<option value="'.$o.'"'.$selected.'>'.modgettext::_($l, $c['module']).'</option>';
                }
            echo '</select>';
            break;
        case CONF_TYPE_BOOL:
?>
<fieldset class='radio'>
<div class="radiotoggle">
<input type="radio" class="valueinput" data-valueinput-orig="<?php echo $amp_conf[$c['keyword']] ? 1 : 0 ?>" name="<?php echo $c['keyword'] ?>" id="<?php echo $c['keyword'] ?>-true" value=1 <?php echo $amp_conf[$c['keyword']]?"checked=\"checked\"":""?> />
<label for="<?php echo $c['keyword'] ?>-true"><?php echo __("True") ?></label>
<input type="radio" class="valueinput" data-valueinput-orig="<?php echo $amp_conf[$c['keyword']] ? 1 : 0 ?>" name="<?php echo $c['keyword'] ?>" id="<?php echo $c['keyword'] ?>-false" value=0 <?php echo !$amp_conf[$c['keyword']]?"checked=\"checked\"":""?> />
<label for="<?php echo $c['keyword'] ?>-false"><?php echo __("False") ?></label>
</div>
</fieldset>
<?php
            break;
    }
    echo '</div>';
    if(!$c['readonly'] || $amp_conf['AS_OVERRIDE_READONLY'] && !$c['hidden']){
        echo '<div class="column is-1">';
        echo '<span data-tooltip="'.__('Revert to Default').'"><i class="fa fa-rotate-left adv_set_default" data-key="'.$c['keyword'].'" data-default="'.$c['defaultval'].'" data-type="' . (($c['type'] == CONF_TYPE_BOOL) ? 'BOOL' : '') . '" '. (($amp_conf[$c['keyword']] == $c['defaultval']) ? ' style="display:none" ' : '').'></i></span>';
        echo '</div>';

        echo '<div class="column is-1">';
        echo '<span data-tooltip="'.__('Save').'"><i class="fa has-text-success-dark fa-check-square-o save" data-key="'.$c['keyword'].'" title="'.__('Save').'"'.' data-type="'.(($c['type'] == CONF_TYPE_BOOL) ? 'BOOL' : '').'"></i></span>';
        echo '</div>';
    } else {
	    echo '<div class="column is-2"></div>';
    }
    echo '</div>';
}

// Provide enough padding at the bottom (<br />) so that the tooltip from the last setting does not get cut off.
?>
<br /><br /> <br />
<div id='action-bar' class=''>
    <div id='action-buttons'>
      <a id='collapseactionmenuicon' class='action_menu_icon'><i class='fa fa-angle-double-right'></i></a>
<button type="submit" class="button is-link is-light is-small is-rounded" id="page_reload">
<?php echo __("Refresh Page"); ?>
</button>
    </div>
</div>
</div>
</div>
