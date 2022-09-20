<?php

/**
 * short IssabelPBX Label generator
 * long Function  used to generate IssabelPBX 'labels' that can
 * show a help popup when moused over
 *
 * @author Moshe Brevda <mbrevda@gmail.com>
 * @param string $text
 * @param string $help
 * @return string
 * @todo change format to take advantage of html's data attribute. No need for spans!
 *
 * {@source } 
 */
function ipbx_label($text, $help = '') {
	if ($help) {
		$ret = '<a href="#" class="info" tabindex="-1">'
				. $text
				. '<span>'
				. $help
				. '</span></a>';
	} else {
		$ret = $text;
	}
	
	return $ret;
}

/**
 * Text Input Field With Enable/Disable Checkbox
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @param	string
 * @param	string
 * @param	bool
 * @return	string
 */
function ipbx_form_input_check($data = '', $value = '', $extra = '', $label = 'Enable', $disabled_value = 'DEFAULT', $check_enables = true) {
	if (!is_array($data)) {
		$data['name'] = $data['id'] = $data;
	}
	if (!isset($data['id'])) {
		$data['id'] = $data['name'];
	}
	if (!isset($data['value'])) {
		$data['value'] = $value;
	}
	if (!empty($data['disabled'])) {
		$data['value'] = $disabled_value;
    }
    $data['class']='input mr-2';
    $data['style']='width:auto;';

  $cbdata['name'] = $data['name'] . '_cb';
  $cbdata['id'] = $data['id'] . '_cb';
  $cbdata['checked'] = isset($data['disabled']) ? !$data['disabled'] : true;
	$cbdata['data-disabled'] = $disabled_value;
	if ($check_enables) {
  	$cbdata['class'] = "input_checkbox_toggle_false mr-1 ml-1";
	} else {
  	$cbdata['class'] = "input_checkbox_toggle_true mr-1 ml-1";
  	$cbdata['checked'] = ! $cbdata['checked'];
	}
	return form_input($data) . form_checkbox($cbdata) . form_label($label, $cbdata['id']);
}
// ------------------------------------------------------------------------

/*
 * $goto is the current goto destination setting
 * $i is the destination set number (used when drawing multiple destination sets in a single form ie: digital receptionist)
 * ensure that any form that includes this calls the setDestinations() javascript function on submit.
 * ie: if the form name is "edit", and drawselects has been called with $i=2 then use onsubmit="setDestinations(edit,2)"
 * $table specifies if the destinations will be drawn in a new <tr> and <td>
 * 
 */   

function drawselects($goto, $i, $show_custom=false, $table=true, $nodest_msg='', $required = false, $output_array = false, $reset = false) {
    global $tabindex, $active_modules, $drawselect_destinations, $drawselects_module_hash, $fw_popover;
    static $drawselects_id_hash;

    if(isset($tabindex)) {
        // has global tabinex, from module not from core
        $tabindexhtml = ' tabindex="' . ++$tabindex . '" ';
    } else {
        $tabindexhtml = ' tabindex="{tabindex}"';
    }

    if ($reset) {
        unset($drawselect_destinations);
        unset($drawselects_module_hash);
        unset($drawselect_id_hash);
    }
    //php session last_dest
    $fw_popover = isset($fw_popover) ? $fw_popover : FALSE;

    $html=$destmod=$errorclass=$errorstyle='';

    if ($nodest_msg == '') {
        $nodest_msg = '== '.dgettext('amp','choose one').' ==';
    }

    if ($table) {
        $html.='<tr><td colspan=2>';
    }//wrap in table tags if requested

    if(!isset($drawselect_destinations)){ 
        $popover_hash = array();
        $add_a_new = dgettext('amp','Add new %s &#8230;');
        //check for module-specific destination functions
        foreach($active_modules as $rawmod => $module){
            $funct = strtolower($rawmod.'_destinations');
            $popover_hash = array();

            //if the modulename_destinations() function exits, run it and display selections for it
            if (function_exists($funct)) {
                modgettext::push_textdomain($rawmod);
                $destArray = $funct(); //returns an array with 'destination' and 'description', and optionally 'category'
                modgettext::pop_textdomain();
                if(is_Array($destArray)) {
                    foreach($destArray as $dest){
                        $cat=(isset($dest['category'])?$dest['category']:dgettext($rawmod,$module['displayname']));
                        $ds_id = (isset($dest['id']) ? $dest['id'] : $rawmod);
                        $popover_hash[$ds_id] = $cat;
                        $drawselect_destinations[$cat][] = $dest;
                        $drawselects_module_hash[$cat] = $rawmod.'_'.md5($cat);
                        $drawselects_id_hash[$cat] = $ds_id;
                    }
                } 
                if (isset($module['popovers']) && !$fw_popover) {
                    modgettext::push_textdomain($rawmod);
                    $funct = strtolower($rawmod.'_destination_popovers');
                    modgettext::pop_textdomain();
                    if (function_exists($funct)) {
                        $protos = $funct();
                        foreach ($protos as $ds_id => $cat) {
                            $popover_hash[$ds_id] = $cat;
                            $drawselects_module_hash[$cat] = $rawmod;
                            $drawselects_id_hash[$cat] = $ds_id;
                        }
                    } else if (empty($destArray)) {
                        // We have popovers in XML, there were no destinations, and no mod_destination_popovers()
                        // funciton so generate the Add a new selection.
                        //
                        $cat = dgettext($rawmod,$module['displayname']);
                        $drawselects_module_hash[$cat] = $rawmod;
                        $drawselects_id_hash[$cat] = $rawmod;
                        $drawselect_destinations[$cat][99999] = array(
                            "destination" => "popover",
                            "description" => sprintf($add_a_new, $cat)
                        );
                    }
                }
                // if we have a popver_hash either from real values or mod_destination_popovers()
                // then we create the 'Add a new option  
                foreach ($popover_hash as $ds_id => $cat) {
                    if (isset($module['popovers'][$ds_id]) && !$fw_popover) {
                        $drawselect_destinations[$cat][99999] = array(
                            "destination" => "popover",
                            "description" => sprintf($add_a_new, $cat),
                            "category" => $cat
                        ); 
                    } 
                }
            }
        }
        //sort destination alphabetically		

        ksort($drawselect_destinations);
        ksort($drawselects_module_hash);
    }

    $ds_array = $drawselect_destinations;

    //set variables as arrays for the rare (impossible?) case where there are none
    if (!isset($drawselect_destinations)) {
        $drawselect_destinations = array();
    }
    if (!isset($drawselects_module_hash)) {
        $drawselects_module_hash = array();
    }

    //get the destination module name if we have a $goto, add custom if there is an issue
    if($goto){
        foreach($drawselects_module_hash as $mod => $description){
            foreach($drawselect_destinations[$mod] as $destination){
                if($goto==$destination['destination']){
                    $destmod=$mod;
                }
            }
        }
        if($destmod==''){//if we haven't found a match, display error dest
            $destmod='Error';
            $drawselect_destinations['Error'][]=array('destination'=>$goto, 'description'=>'Bad Dest: '.$goto, 'class'=>'drawselect_error');
            $drawselects_module_hash['Error']='error';
            $drawselects_id_hash['Error']='error';
        }
        //Set 'data-last' values for popover return to last saved values
        $data_last_cat = str_replace(' ', '_', $destmod);
        $data_last_dest = $goto;
    } else {
        //Set 'data-last' values for popover return to nothing because this is a new 'route'
        $data_last_cat = '';
        $data_last_dest = '';
    }

    //draw "parent" select box
    $style=' style="'.(($destmod=='Error')?'background-color:red;':'background-color:white;').'"';
    $style='';
    $error_class =($destmod=='Error')?'error':'';
    $html.='<select data-last="'.$data_last_cat.'" name="goto' . $i . '" id="goto' . $i . '" class="destdropdown '.$error_class.'" ' . $style . $tabindexhtml
        . ($required ? ' required ' : '') //html5 validation
        . ' data-id="' . $i . '" '
        . '>';
    $html.='<option value="" class="valid-destination">'.$nodest_msg.'</option>';
    foreach($drawselects_module_hash as $mod => $disc){

        $label_text = $mod;
        $selected=($mod==$destmod)?' SELECTED ':' ';
        $style='';
        //		$style=' style="'.(($mod=='Error')?'background-color:red;':'background-color:white;').'"';
        $style=' class="'.(($mod=='Error')?'invalid-destination':'valid-destination').'"';
        $option_value = $drawselects_module_hash[$mod].$i;
        //$html.='<option value="'.str_replace(' ','_',$mod).'"'.$selected.$style.'>'.$label_text." ($mod, $destmod, $disc)</option>";
        $html.='<option value="'.$option_value.'"'.$selected.$style.'>'.$label_text."</option>";
    }
    $html.='</select> ';

    if(isset($tabindex)) {
        // has global tabinex, from module not from core
        $tabindexhtml = ' tabindex="' . ++$tabindex . '" ';
    } else {
        $tabindexhtml = ' tabindex="{tabindex}"';
    }
    //draw "children" select boxes

    foreach($drawselect_destinations as $cat=>$destination) {
        //        $style=(($cat==$destmod)?'':'display:none;');
        $style='';
        if ($cat == 'Error') {
            $style.=' ' . $errorstyle;
        }//add error style
        $style=' style="'.(($cat=='Error')?'background-color:red;':$style).'"';

        // if $fw_popover is set, then we are in a popover so we don't allow another level
        //
        $rawmod = $drawselects_module_hash[$cat];
        $ds_id = $drawselects_id_hash[$cat];
        $rawmodmatch = preg_match("/_/",$rawmod)?substr($rawmod,0,strpos($rawmod,'_')):$rawmod;

        if (isset($active_modules[$rawmodmatch]['popovers'][$ds_id]) && !$fw_popover) {
            $args = array();
            foreach ($active_modules[$rawmodmatch]['popovers'][$ds_id] as $k => $v) {
                $args[] = $k . '=' . $v;
            }
            $data_url = 'data-url="config.php?' . implode('&', $args) . '" ';
            $data_class = 'data-class="' . $ds_id . '" ';
            $data_mod = 'data-mod="' . $rawmodmatch . '" ';

        } else {
            $data_url = '';
            $data_mod = '';
            if (isset($active_modules[$rawmodmatch]['popovers']) && !$fw_popover) {
                $data_class = 'data-class="' . $ds_id . '" ';
            } else {
                $data_class = '';
            }
        }
        $class_tag = ' class="destdropdown2 ' . $rawmod . ' goto'.$i;
        $class_tag .=(($cat==$destmod)?'':' is-hidden '); // hidden class instead of display none
        $class_tag .= $rawmod == $ds_id ? '"' : ' ' . $ds_id . '"';


        $name_tag = str_replace(' ', '_', $rawmod) . $i;
        $id_tag = $rawmod.$i;
        $html.='<select ' . $data_url . $data_class . $data_mod . 'data-last="'.$data_last_dest.'" name="' . $name_tag 
            . '" id="' . $id_tag . '" ' . $tabindexhtml . $style . $class_tag . ' data-id="' . $i . '" ' . '>';
        foreach ($destination as $key => $dest) {
            $selected=($goto==$dest['destination'])?'SELECTED ':' ';
            $ds_array[$cat][$key]['selected'] = ($goto == $dest['destination']) ? true : false;
            $child_label_text=$dest['description'];
            //			$style=' style="'.(($cat=='Error')?'background-color:red;':'background-color:transparent;').'"';
            $style=' class="'.(($mod=='Error')?'invalid-destination':'valid-destination').'"';
            $html.='<option value="'.$dest['destination'].'" '.$selected.$style.'>'.$child_label_text.'</option>';
        }
        $html.='</select>';
    }
    if (isset($drawselect_destinations['Error'])) {
        unset($drawselect_destinations['Error']);
    }
    if (isset($drawselects_module_hash['Error'])) {
        unset($drawselects_module_hash['Error']);
    }
    if ($table) {
        $html.='</td></tr>';
    }//wrap in table tags if requested
    return $output_array ? $ds_array : $html;
}

// This function will get the MySQL field size of the specified fieldname
// It's useful for finding out the limit of certain fields in MySQL so that
// we can do validation checks on strings to make sure they aren't too long.
// This will help prevent MySQL from needing to do auto chopping on lengthy strings
// which causes problems with multibyte characters getting cut off abruptly.
// The third argument defaultsize is just to futureproof in case someone decides
// to change things in MySQL in the future that would otherwise just pass null back
// and cause a bug.
function module_get_field_size($tablename, $fieldname, $defaultsize) {
    global $db;

    $sql = "SELECT character_maximum_length FROM information_schema.columns WHERE table_name = ? AND column_name = ?";

    $results = $db->getAll($sql, array($tablename, $fieldname));

    if(DB::IsError($results)) {
        $results = null;
    }

    return isset($results)?$results[0][0]:$defaultsize;
}

function js_display_confirmation_toasts() {
    $out='';
    if(!isset($_SERVER['HTTP_X_UP_MODE'])) { // is a full page load, not unpoly fragment
        if(isset($_SESSION['msg'])) {
            if($_SESSION['msg']!='') {
                $type = isset($_SESSION['msgtype'])?htmlspecialchars($_SESSION['msgtype']):'success';
                $msg = json_encode(base64_decode($_SESSION['msg']));
                $out.= "\$( function() {\n";
                $out.= "sweet_toast('$type',".$msg.");\n";
                $out.= "});\n";
            }
        } else {
            $out.='// console.log("no toast");';
        }
    }
    return $out;
}

function form_action_bar($extdisplay, $formname='', $delete_disabled=false, $reset_disabled=false, $delete_text='') {

    global $tabindex;

    $target='';
    if($formname!='') { $target = "data-target=\"$formname\""; }

    if(isset($tabindex)) {
        // has global tabinex, from module not from core
        $tabindexhtml = ' tabindex="' . ++$tabindex . '" ';
    } else {
        $tabindexhtml = ' tabindex="{tabindex}" ';
    }

    $out = "
      <div id='action-bar' class=''>
        <div id='action-buttons'>
          <a id='collapseactionmenuicon' class='action_menu_icon'><i class='fa fa-angle-double-right'></i></a>
          <input name='btnsubmit' ".$tabindexhtml.$target." id='mainformsubmit' type='submit' value='".dgettext('amp','Submit')."' class='button is-rounded is-light is-small is-link'>
          &nbsp;
    ";
    if(!$extdisplay) {
        if(!$reset_disabled) {
            $out .=" <input name='reset' ".$tabindexhtml.$target." type='submit' id='mainformreset' value='".dgettext('amp','Reset')."' class='button is-rounded is-light is-small is-link'> ";
        }
    } else {
        $disabled = ($delete_disabled) ? ' disabled="disabled "':'';
        $extratext = ($delete_text!='')? ' data-text="'.base64_encode($delete_text).'" ' : '';
        $out .= " <input $disabled name='delete' ".$tabindexhtml.$target.$extratext." type='submit' id='mainformdelete' value='".dgettext('amp','Delete')."' class='button is-rounded is-light is-small is-danger'> ";
    }
    $out .= "
        </div>
      </div>
    ";
    return $out;

}

function ipbx_yesno_checkbox($name,$currentvalue,$disabled=false) {
    global $tabindex;
    return ipbx_radio($name,array(array('value'=>1,'text'=>dgettext('amp','Yes')),array('value'=>0,'text'=>dgettext('amp','No'))),($currentvalue ? 1:0),false);
}

function ipbx_radio($name,$valarray, $currentvalue, $disable=false) {
    global $tabindex;

    $output = '<fieldset class="radio">';
    $output .= '<div class="radiotoggle">';

    $count = 0;
    foreach ($valarray as $item) {
        $itemvalue = (isset($item['value']) ? $item['value'] : '');
        $itemtext = (isset($item['text']) ? $item['text'] : '');
        $itemchecked = ((string) $currentvalue == (string) $itemvalue) ? ' checked="checked"' : '';

        $disable_state = $disable ? 'disabled="disabled"':'';
        $output .= "<input type=\"radio\" name=\"$name\" id=\"$name$count\" $disable_state tabindex=\"".++$tabindex."\" value=\"$itemvalue\"$itemchecked/><label for=\"$name$count\">$itemtext</label>\n";
        $count++;
    }
    $output .= '</div>';
    $output .= '</fieldset>';
    return $output;
}

function ipbx_usage_info($text,$tooltip) {
    return "<div class='tag is-success is-light'><a href='#' class='info'>$text:<span>$tooltip</span></a></div>";
}

function ipbx_extension_conflict($conflict_url) {
    $out  = "<div class='notification is-warning column is-three-quarters'><h6>".dgettext("amp","Conflicting Extensions")."</h6>";
    $out .= implode('<br>',$conflict_url);
    $out .= "</div>\n";
    return $out;
}
