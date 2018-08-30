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
  $cbdata['name'] = $data['name'] . '_cb';
  $cbdata['id'] = $data['id'] . '_cb';
  $cbdata['checked'] = isset($data['disabled']) ? !$data['disabled'] : true;
	$cbdata['data-disabled'] = $disabled_value;
	if ($check_enables) {
  	$cbdata['class'] = "input_checkbox_toggle_false";
	} else {
  	$cbdata['class'] = "input_checkbox_toggle_true";
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

	if ($reset) {
		unset($drawselect_destinations);
		unset($drawselect_module_hash);
		unset($drawselect_id_hash);
	}
	//php session last_dest
	$fw_popover = isset($fw_popover) ? $fw_popover : FALSE;
    
	$html=$destmod=$errorclass=$errorstyle='';
  if ($nodest_msg == '') {
	  $nodest_msg = '== '._('choose one').' ==';
  }

	if ($table) {
		$html.='<tr><td colspan=2>';
	}//wrap in table tags if requested

	if(!isset($drawselect_destinations)){ 
		$popover_hash = array();
		$add_a_new = _('Add new %s &#133');
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
						$cat=(isset($dest['category'])?$dest['category']:$module['displayname']);
						$ds_id = (isset($dest['id']) ? $dest['id'] : $rawmod);
						$popover_hash[$ds_id] = $cat;
						$drawselect_destinations[$cat][] = $dest;
						$drawselects_module_hash[$cat] = $rawmod;
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
						$drawselects_module_hash[$module['displayname']] = $rawmod;
						$drawselects_id_hash[$module['displayname']] = $rawmod;
						$drawselect_destinations[$module['displayname']][99999] = array(
							"destination" => "popover",
							"description" => sprintf($add_a_new, $module['displayname'])
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

	$foundone=false;
	$tabindex_needed=true;
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
	$html.='<select data-last="'.$data_last_cat.'" name="goto' . $i . '" id="goto' . $i . '" class="destdropdown" ' . $style . ' tabindex="' . ++$tabindex . '"'
			. ($required ? ' required ' : '') //html5 validation
			. ' data-id="' . $i . '" '
			. '>';
	$html.='<option value="" style="background-color:white;">'.$nodest_msg.'</option>';
	foreach($drawselects_module_hash as $mod => $disc){

		$label_text = modgettext::_($mod, $drawselects_module_hash[$mod]);

		/* end i18n */
		$selected=($mod==$destmod)?' SELECTED ':' ';
		$style=' style="'.(($mod=='Error')?'background-color:red;':'background-color:white;').'"';
		$html.='<option value="'.str_replace(' ','_',$mod).'"'.$selected.$style.'>'.$label_text.'</option>';
	}
	$html.='</select> ';
	
	//draw "children" select boxes
	$tabindexhtml=' tabindex="'.++$tabindex.'"';//keep out of the foreach so that we don't increment it
	foreach($drawselect_destinations as $cat=>$destination){
		$style=(($cat==$destmod)?'':'display:none;');
		if ($cat == 'Error') {
			$style.=' ' . $errorstyle;
		}//add error style
		$style=' style="'.(($cat=='Error')?'background-color:red;':$style).'"';

		// if $fw_popover is set, then we are in a popover so we don't allow another level
		//
		$rawmod = $drawselects_module_hash[$cat];
		$ds_id = $drawselects_id_hash[$cat];
		if (isset($active_modules[$rawmod]['popovers'][$ds_id]) && !$fw_popover) {
			$args = array();
			foreach ($active_modules[$rawmod]['popovers'][$ds_id] as $k => $v) {
				$args[] = $k . '=' . $v;
			}
			$data_url = 'data-url="config.php?' . implode('&', $args) . '" ';
			$data_class = 'data-class="' . $ds_id . '" ';
			$data_mod = 'data-mod="' . $rawmod . '" ';
		} else {
			$data_url = '';
			$data_mod = '';
			if (isset($active_modules[$rawmod]['popovers']) && !$fw_popover) {
				$data_class = 'data-class="' . $ds_id . '" ';
			} else {
				$data_class = '';
			}
		}
		$class_tag = ' class="destdropdown2 ' . $rawmod;
		$class_tag .= $rawmod == $ds_id ? '"' : ' ' . $ds_id . '"';
		$name_tag = str_replace(' ', '_', $cat) . $i;
		$html.='<select ' . $data_url . $data_class . $data_mod . 'data-last="'.$data_last_dest.'" name="' . $name_tag 
			. '" id="' . $name_tag . '" ' . $tabindexhtml . $style . $class_tag . ' data-id="' . $i . '" ' . '>';
		foreach ($destination as $key => $dest) {
			$selected=($goto==$dest['destination'])?'SELECTED ':' ';
			$ds_array[$cat][$key]['selected'] = ($goto == $dest['destination']) ? true : false;
			$child_label_text=$dest['description'];
			$style=' style="'.(($cat=='Error')?'background-color:red;':'background-color:white;').'"';
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
