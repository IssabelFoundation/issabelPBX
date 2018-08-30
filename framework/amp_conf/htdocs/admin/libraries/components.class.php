<?php
// This is a global to be used by gettabindex()
//
$_guielement_tabindex = 1;
$_guielement_formfields = 0;

class component {
	var $_compname; // Component name (e.g. users, devices, etc.)
	var $_type; // Type name of this component ( e.g. setup, tool etc.)
	
	var $_guielems_top; // Array of guielements
	var $_guielems_middle; // Array of guielements
	var $_guielems_bottom; // Array of guielements
	
	var $_jsfuncs; // Array of JavaScript functions
	var $_guifuncs; // Array of gui functions
	var $_processfuncs; // Array of process functions

	var $_sorted_guielems;
	var $_sorted_jsfuncs;
	var $_sorted_guifuncs;
	var $_sorted_processfuncs;
	
	var $_lists; // Array of lists
	
	var $_opts; //array of configurable options

	function component($compname, $type = 'setup') {
		global $display;
		$this->_compname = $compname;
		$this->_type = $type;
		
		$this->_sorted_guielems = true;
		$this->_sorted_jsfuncs = true;
		$this->_sorted_guifuncs = true;
		$this->_sorted_processfuncs = true;
		
		//set section to hidden if requested by user
		$user_hidden = isset($_COOKIE['guielToggle']) ? json_decode($_COOKIE['guielToggle']) : array();
		foreach($user_hidden as $k => $v) {
			list($page, $section) = explode('#', $k);
			if ($page == $display) {
				$this->_opts[$section]['guielToggle'] = $v ? true :false;
			}
		}
	}
	
	/*
	 * Toggle open state
	 * true = open, false = closed
	 * wont over write a users settings
	 */
	function sectionToggle($section, $state = false) {
		$section = preg_replace('/[^A-Za-z]/', '' ,$section);
		if (!isset($this->_opts[$section]['guielToggle'])) {
			$this->_opts[$section]['guielToggle'] = $state;
		}
	}
	
	function addguielem($section, $guielem, $sortorder = 5, $placement = null) {
		// Note that placement is only used in 'middle', eg, a named module
		if ( $sortorder < 0 || $sortorder > 9 ) {
			trigger_error('$sortorder must be between 0 and 9 in component->addguielem()');
			return;
		}

		switch ($section) {
			case '_top':
				$this->_guielems_top[$sortorder][] = $guielem;
				break;
			case '_bottom':
				$this->_guielems_bottom[$sortorder][] = $guielem;
				break;
			default:
				$this->_guielems_middle[$section][$sortorder][] = $guielem;
				if (!isset($this->_guielems_middle[$section]['placement'])) {
					if ($placement === null) {
						$this->_guielems_middle[$section]['placement'] = $sortorder;
					} else {
						$this->_guielems_middle[$section]['placement'] = $placement;
					}
				}
				break;
		}
		
		$this->_sorted_guielems = false;
	}

	function delguielem($section, $elemname) {
		switch ($section) {
			case '_top':
					foreach ($this->_guielems_top as $index1 => $elements) {
						foreach ($elements as $index2 => $element) {
							if ($element->_elemname == $elemname) {
								unset($this->_guielems_top[$index1][$index2]);
								return true;
							}
						}
					}
				break;
			case '_bottom':
					foreach ($this->_guielems_bottom as $index1 => $elements) {
						foreach ($elements as $index2 => $element) {
							if ($element->_elemname == $elemname) {
								unset($this->_guielems_bottom[$index1][$index2]);
								return true;
							}
						}
					}
				break;
			default:
					if (isset($this->_guielems_middle[$section])) {
						foreach ($this->_guielems_middle[$section] as $index1 => $elements) {
							foreach ($elements as $index2 => $element) {
								if ($element->_elemname == $elemname) {
									unset($this->_guielems_bottom[$index1][$index2]);
									return true;
								}
							}
						}
					}
			}
		return false;
	}

	function addjsfunc($function, $jstext, $sortorder = 5) {
		if ( $sortorder < 0 || $sortorder > 9 ) {
			trigger_error('$sortorder must be between 0 and 9 in component->addjsfunc()');
			return;
		}
		
		$this->_jsfuncs[$function][$sortorder][] = $jstext;
		
		$this->_sorted_jsfuncs = false;
	}

	function addguifunc($function, $sortorder = 5) {
        if ( $sortorder < 0 || $sortorder > 9 ) {
                trigger_error('$sortorder must be between 0 and 9 in component->addguifunc()');
                return;
        }
		if ( !function_exists($function) ) {
			trigger_error("$function does not exist");
			return;
		}

		$this->_guifuncs[$sortorder][] = $function;

		$this->_sorted_guifuncs = false;
	}

	function addprocessfunc($function, $sortorder = 5) {
        if ( $sortorder < 0 || $sortorder > 9 ) {
                trigger_error('$sortorder must be between 0 and 9 in component->addprocessfunc()');
                return;
        }
		if ( !function_exists($function) ) {
			trigger_error("$function does not exist");
			return;
		}

		$this->_processfuncs[$sortorder][] = $function;

		$this->_sorted_processfuncs = false;
	}

	function addoptlist($listname, $sort = true) {
		if ( (isset($listname) ? $listname : '') == '') {
			trigger_error('missing $listname in component->addoptlist()');
			return;
		} elseif (isset($this->_lists[$listname]) && is_array($this->_lists[$listname]) ) {
			trigger_error("list $listname already exists");
		}
		
		// does this list need sorting ?
		$this->_lists[$listname]['sort'] = $sort;
		// nothing really, but an array will be here after addlistitem
		$this->_lists[$listname]['array'] = array();
	}
	
	function setoptlistopts($listname, $opt, $val) {
		$this->_lists[$listname][$opt] = $val;
	}
	
	function addoptlistitem($listname, $value, $text, $uselang = true) {
		// must add the list before using it
		if ( !isset($this->_lists[$listname]) ) {
			$this->addoptlist($listname);
		}

		// add the item
		$this->_lists[$listname]['array'][] = array('text' => $text, 'value' => $value);
	}
	
	function getoptlist($listname) {
		if ( isset($this->_lists[$listname]['array']) ) {
			// sort the array by text
			if ( $this->_lists[$listname]['sort'] ) {
				asort($this->_lists[$listname]['array']);
			}

			// and return it!
			return $this->_lists[$listname]['array'];
		} else {
			trigger_error("'$listname' does not exist in component->getoptlist()");
			return null;
		}
	}
	
	function addgeneralarray($arrayname) {
		if ( (isset($arrayname) ? $arrayname : '') == '') {
			trigger_error('missing $arrayname in component->addarray()');
			return;
		} elseif ( isset($this->_lists[$arrayname]) && is_array($this->_lists[$arrayname]) ) {
			trigger_error("array $arrayname already exists");
		}
		
		// nothing really, but an array will be here after addlistitem
		$this->_lists[$arrayname] = array();
	}
	
	function addgeneralarrayitem($arrayname, $arraykey, $item) {
		if ( !isset($this->_lists[$arrayname]) ) {
			$this->addgeneralarray($arrayname);
		}
		
		$this->_lists[$arrayname][$arraykey] = $item;
	}
	
	function getgeneralarray($arrayname) {
		if ( isset($this->_lists[$arrayname]) ) {
			return $this->_lists[$arrayname];
		} else {
			trigger_error("'$arrayname' does not exist in component->getgeneralarray()");
			return null;
		}
	}
	
	function getgeneralarrayitem($arrayname, $arraykey) {
		if ( isset($this->_lists[$arrayname][$arraykey]) ) {
			return $this->_lists[$arrayname][$arraykey];
		} else {
			trigger_error("'$arraykey' does not exist in array '$arrayname'");
			return null;
		}
	}
	
	function sortguielems() {
		// sort top gui elements
		if ( is_array($this->_guielems_top) )
			ksort($this->_guielems_top);
		
		// sort middle gui elements
		if ( is_array($this->_guielems_middle) ) {
			foreach ( array_keys($this->_guielems_middle) as $section ) {
				ksort($this->_guielems_middle[$section]);
			}
			ksort($this->_guielems_middle);
		}
				
		// sort bottom gui elements
		if ( is_array($this->_guielems_top) )
			ksort($this->_guielems_top);
		
		
		$this->_sorted_guielems = true;
	}
	
	function sortjsfuncts() {
		// sort js funcs
		if ( is_array($this->_jsfuncs) ) {
			foreach ( array_keys($this->_jsfuncs) as $function ) {
				ksort($this->_jsfuncs[$function]);
			}
			ksort($this->_jsfuncs);
		}
		
		$this->_sorted_jsfuncs = true;	
	}

	function sortguifuncs() {
		// sort process functions
		if ( is_array($this->_guifuncs) ) {
			ksort($this->_guifuncs);
		}

		$this->_sorted_guifuncs = true;
	}

	function sortprocessfuncs() {
		// sort process functions
		if ( is_array($this->_processfuncs) ) {
			ksort($this->_processfuncs);
		}

		$this->_sorted_processfuncs = true;
	}
	
	function generateconfigpage() {

		$htmlout = '';
		$formname = "frm_$this->_compname";
		$hasoutput = false;
		
		if ( !$this->_sorted_guielems ) 
			$this->sortguielems();


		// Start of form
		
		$form_action = isset($this->_opts['form_action']) ? $this->_opts['form_action'] : $_SERVER['PHP_SELF'];
		$htmlout .= "<form class=\"popover-form\" name=\"$formname\" action=\"".$form_action."\" method=\"post\" onsubmit=\"return ".$formname."_onsubmit();\">\n";
		$htmlout .= "<input type=\"hidden\" name=\"display\" value=\"$this->_compname\" />\n";
		$htmlout .= "<input type=\"hidden\" name=\"type\" value=\"$this->_type\" />\n\n";
		
		// Start of table
		$htmlout .= "<table><!-- start of table $formname -->\n";
		
		// Gui Elements / JavaScript validation
		// Top
		if ( is_array($this->_guielems_top) ) {
			$hasoutput = true;
			foreach ( array_keys($this->_guielems_top) as $sortorder ) {
				foreach ( array_keys($this->_guielems_top[$sortorder]) as $idx ) {
					$elem = $this->_guielems_top[$sortorder][$idx];
					$htmlout .= $elem->generatehtml();
					$this->addjsfunc('onsubmit()', $elem->generatevalidation());
				}
			}
		}

		// Middle
		if ( is_array($this->_guielems_middle) ) {
			$hasoutput = true;
			for ($placement = 0; $placement < 10; $placement++) {
				foreach ( array_keys($this->_guielems_middle) as $section ) {
					if ($this->_guielems_middle[$section]['placement'] !== $placement)
						continue;
					// Header for $section				
					$htmlout .= "\t<tr class=\"guielToggle\" data-toggle_class=\"".preg_replace('/[^A-Za-z]/', '' ,$section)."\">\n";
					$htmlout .= "\t\t<td colspan=\"2\">";
					if ($section) {
						$mysec = preg_replace('/[^A-Za-z]/', '' ,$section);
						$state = isset($this->_opts[$mysec]['guielToggle']) && $this->_opts[$mysec]['guielToggle'] == false
						 		? '+' : '-  ';
						$htmlout .= "<h5>" 
								. "<span class=\"guielToggleBut\">$state</span>" 
								. _($section) 
								. "</h5><hr>";
					} else {
						$htmlout .= '<hr>';
					}

					$htmlout .= "</td>\n";
					$htmlout .= "\t</tr>\n";
					
					// Elements
					foreach ( array_keys($this->_guielems_middle[$section]) as $sortorder ) {
						if ($sortorder == 'placement') 
							continue;
						foreach ( array_keys($this->_guielems_middle[$section][$sortorder]) as $idx ) {
							$elem = $this->_guielems_middle[$section][$sortorder][$idx];
							$htmlout .= $elem->generatehtml(preg_replace('/[^A-Za-z]/', '' ,$section));
							$this->addjsfunc('onsubmit()', $elem->generatevalidation());
						}
					}
				}
			}
			// Spacer before bottom
			if ( is_array($this->_guielems_bottom) ) {
				$htmlout .= "\t<tr>\n";
				$htmlout .= "\t\t<td colspan=\"2\">";
				$htmlout .= "</td>\n";
				$htmlout .= "\t</tr>\n";
			}
		}

		// Bottom
		if ( is_array($this->_guielems_bottom) ) {
			$hasoutput = true;
			foreach ( array_keys($this->_guielems_bottom) as $sortorder ) {
				foreach ( array_keys($this->_guielems_bottom[$sortorder]) as $idx ) {
					$elem = $this->_guielems_bottom[$sortorder][$idx];
					$htmlout .= $elem->generatehtml();
					$this->addjsfunc('onsubmit()', $elem->generatevalidation());
				}
			}
		}
		
		$tabindex = guielement::gettabindex();
		// End of table

		// Don't put a submit button if there were not form fields generated
		//
		if (guielement::getformfields()) {
			$htmlout .= "\t<tr>\n";
			$htmlout .= "\t\t<td colspan=\"2\">";
			$htmlout .= "<h6>";
			$htmlout .= "<input name=\"Submit\" type=\"submit\" tabindex=\"$tabindex\" value=\""._("Submit")."\">";
			$htmlout .= "</h6>";
			$htmlout .= "</td>\n";
			$htmlout .= "\t</tr>\n";
		}
		$htmlout .= "</table><!-- end of table $formname -->\n\n";

		if ( !$this->_sorted_jsfuncs )
			$this->sortjsfuncts();

		// Javascript
		$htmlout .= "<script type=\"text/javascript\">\n<!--\n";
		$htmlout .= "var theForm = document.$formname;\n\n";
		
		// TODO:	* Create standard JS to go thru each text box looking for first one not hidden and set focus
		if ( is_array($this->_jsfuncs) ) {
			foreach ( array_keys($this->_jsfuncs) as $function ) {
				// Functions
				$htmlout .= "function ".$formname."_$function {\n";
				foreach ( array_keys($this->_jsfuncs[$function]) as $sortorder ) {
					foreach ( array_keys($this->_jsfuncs[$function][$sortorder]) as $idx ) {
						$func = $this->_jsfuncs[$function][$sortorder][$idx];
						$htmlout .= ( isset($func) ) ? "$func" : '';
					}
				}
				if ( $function == 'onsubmit()' )
					$htmlout .= "\treturn true;\n";
				$htmlout .= "}\n";
			}
		}
		$htmlout .= "//-->\n</script>";
		
		// End of form
		$htmlout .= "\n</form>\n\n";
		
		if ( $hasoutput ) {
			return $htmlout;
		} else {
			return '';
		}
	}

	function processconfigpage() {
		if ( !$this->_sorted_processfuncs )
			$this->sortprocessfuncs();

		if ( is_array($this->_processfuncs) ) {
			foreach ( array_keys($this->_processfuncs) as $sortorder ) {
				foreach ( $this->_processfuncs[$sortorder] as $func ) {
					$func($this->_compname);
				}
			}
		}
	}
	
	function buildconfigpage() {
		if ( !$this->_sorted_guifuncs ) {
			$this->sortguifuncs();
		}

		if ( is_array($this->_guifuncs) ) {
			foreach ( array_keys($this->_guifuncs) as $sortorder ) {
				foreach ( $this->_guifuncs[$sortorder] as $func ) {
					$modparts = explode("_",$func,2);
					$thismod = $modparts[0];

					modgettext::push_textdomain($thismod);
					$func($this->_compname);
					modgettext::pop_textdomain();
				}
			}
		}
	}

	function isequal($compname, $type) {
		return $this->_compname == $compname && $this->_type == $type;
	}
}

class guielement {
	var $_elemname;
	var $_html;
	var $_javascript;
	var $_opts;
	
	function guielement($elemname, $html = '', $javascript = '') {
		global $CC;
		// name that will be the id tag
		$this->_elemname = $elemname;

		// normally the $html will be the actual page output, obviously here in the base class it's meaningless
		// this does mean, of course, this constructor MUST be called before any child class constructor code
		// otherwise $html will be blanked out
		$this->_html = $html;
		$this->_javascript = $javascript;
		

		$this->_opts = & $CC->_opts;
	}
	
	function generatehtml() {
		return $this->_html;
	}
	
	function generatevalidation() {
		return $this->_javascript;
	}
	function gettabindex() {
		global $_guielement_tabindex;
		return $_guielement_tabindex;
	}
	function settabindex($new_tab) {
		global $_guielement_tabindex;
		$_guielement_tabindex = $new_tab;
	}
	function incrementfields() {
		global $_guielement_formfields;
		$_guielement_formfields++;
	}
	function getformfields() {
		global $_guielement_formfields;
		return $_guielement_formfields;
	}
}

// Hidden field
// Odd ball this one as neither guiinput or guitext !
/**
 * 
 * @param $table bool if this element is in a table or not, Default is true.
*/
class gui_hidden extends guielement {
	function gui_hidden($elemname, $currentvalue = '', $table=true) {
		// call parent class contructor
		guielement::guielement($elemname, '', '');
		
		$this->_html = "<input type=\"hidden\" name=\"$this->_elemname\" id=\"$this->_elemname\" value=\"" . htmlentities($currentvalue) . "\">";
		
		// make it a new row
		if($table) {
			$this->_html = "\t<tr>\n\t\t<td>" . $this->_html . "</td>\n\t</tr>\n";
		}
	}
}

/*
************************************************************
** guiinput is the base class of all form fields          **
************************************************************
*/

class guiinput extends guielement {
	var $currentvalue;
	var $prompttext;
	var $helptext;
	var $jsvalidation;
	var $failvalidationmsg;
	var $canbeempty;
	
	var $html_input;
	
	function guiinput($elemname, $currentvalue = '', $prompttext = '', $helptext = '', $jsvalidation = '', $failvalidationmsg = '', $canbeempty = true, $jsvalidationtest='') {

		// call parent class contructor
		guielement::guielement($elemname, '', '');
		
		// current valid of the field
		$this->currentvalue = $currentvalue;
		// this will appear on the left column
		$this->prompttext = $prompttext;
		// tooltip over prompttext (optional)
		$this->helptext = $helptext;
		// JavaScript validation field on the element
		$this->jsvalidation = $jsvalidation;
		// JavaScript validation test
		$this->jsvalidationtest = $jsvalidationtest;
		// Msg to use if above validation fails (forced to use gettext language stuff)
		$this->failvalidationmsg = $failvalidationmsg;
		// Can this field be empty ?
		$this->canbeempty = $canbeempty;
		
		// this will be the html that makes up the input element
		$this->html_input = '';
		guielement::incrementfields();
	}
	
	function generatevalidation() {
		$output = '';
		
		if ($this->jsvalidation != '' ) {
			if(!$this->jsvalidationtest){
				$thefld = "theForm." . $this->_elemname;
				$thefldvalue = $thefld . ".value";
			}else{
				$thefld="theForm." . $this->_elemname;
				$thefldvalue =$this->jsvalidationtest;
			}
		
			if ($this->canbeempty) {
				$output .= "\tdefaultEmptyOK = true;\n";
			} else {
				$output .= "\tdefaultEmptyOK = false;\n";
			}

			$output .= "\tif (" . str_replace("()", "(" . $thefldvalue . ")", $this->jsvalidation) . ") \n";
			$output .= "\t\treturn warnInvalid(" . $thefld . ", \"" . $this->failvalidationmsg . "\");\n";
			$output .= "\n";
		}
		
		return $output;
	}
	
	function generatehtml($section = '') {
		// this effectivly creates the template using the prompttext and html_input
		// we would expect the $html_input to be set by the child class
		
		$output = '';
		
		// start new row
		if ($section) {
			$mysec = preg_replace('/[^A-Za-z]/', '' ,$section);
			$output .= '<tr class="' . $section . '" '
					. ((isset($this->_opts[$mysec]['guielToggle']) && $this->_opts[$mysec]['guielToggle'] == false) 
						? ' style="display:none" ' 
						: '')
					.' >' . "\n";
		} else {
			$output .= "\t<tr>\n";
		}
		

		// prompt in first column
		$output .= "\t\t<td>";
		if ($this->helptext != '') {
			$output .= ipbx_label($this->prompttext,$this->helptext);
		} else {
			$output .= $this->prompttext;
		}
		$output .= "</td>\n";
		
		// actual input in second row
		$output .= "\t\t<td>";
		$output .= $this->html_input;
		$output .= "</td>\n";
		
		// end this row
		$output .= "\t</tr>\n";
		
		return $output;
	}
}

// Textbox
class gui_textbox extends guiinput {
	function gui_textbox($elemname, $currentvalue = '', $prompttext = '', $helptext = '', $jsvalidation = '', $failvalidationmsg = '', $canbeempty = true, $maxchars = 0, $disable=false) {
		// call parent class contructor
		parent::__construct($elemname, $currentvalue, $prompttext, $helptext, $jsvalidation, $failvalidationmsg, $canbeempty);
		
		$maxlength = ($maxchars > 0) ? " maxlength=\"$maxchars\"" : '';
		$tabindex = guielement::gettabindex();
		$disable_state = $disable ? 'disabled="true"':'';
		$this->html_input = "<input type=\"text\" name=\"$this->_elemname\" id=\"$this->_elemname\" size=\"35\" $disable_state $maxlength tabindex=\"$tabindex\" value=\"" . htmlspecialchars($this->currentvalue) . "\">";
	}
}

// Textbox with Enable/Disable Check after
class gui_textbox_check extends gui_textbox {
	function __construct($elemname, $currentvalue = '', $prompttext = '', $helptext = '', $jsvalidation = '', $failvalidationmsg = '', $canbeempty = true, $maxchars = 0, $disable=false, $label='Enable', $disabled_value='DEFAULT', $check_enables='true') {
		// call parent class contructor
		if ($disable) {
			$currentvalue = $disabled_value;
		}
		parent::__construct($elemname, $currentvalue, $prompttext, $helptext, $jsvalidation, $failvalidationmsg, $canbeempty, $maxchars, $disable);

		$cb_state = $disable && $check_enables || !$disable && !$check_enables ? '':' CHECKED';

		$OnClickClass = "class=\"input_checkbox_toggle_" . ($check_enables ? 'false':'true') . "\"";

		$cbid = $this->_elemname . '_cb';
		$this->html_input .= "<input type=\"checkbox\" name=\"$cbid\" id=\"$cbid\" data-disabled=\"$disabled_value\" {$OnClickClass}{$cb_state}> <label for=\"$cbid\">$label</label>\n";

	}
}

// Password
class gui_password extends guiinput {
	function gui_password($elemname, $currentvalue = '', $prompttext = '', $helptext = '', $jsvalidation = '', $failvalidationmsg = '', $canbeempty = true, $maxchars = 0, $disable=false) {
		// call parent class contructor
		$parent_class = get_parent_class($this);
		parent::$parent_class($elemname, $currentvalue, $prompttext, $helptext, $jsvalidation, $failvalidationmsg, $canbeempty);
		
		$maxlength = ($maxchars > 0) ? " maxlength=\"$maxchars\"" : '';
		$tabindex = guielement::gettabindex();
		$disable_state = $disable ? 'disabled="true"':'';
		$this->html_input = "<input type=\"password\" name=\"$this->_elemname\" id=\"$this->_elemname\" $disable_state $maxlength tabindex=\"$tabindex\" value=\"" . htmlentities($this->currentvalue) . "\">";
	}
}

// Select box
class gui_selectbox extends guiinput {
	function gui_selectbox($elemname, $valarray, $currentvalue = '', $prompttext = '', $helptext = '', $canbeempty = true, $onchange = '', $disable=false) {
		if (!is_array($valarray)) {
			trigger_error('$valarray must be a valid array in gui_selectbox');
			return;
		}
		
		// currently no validation fucntions availble for select boxes
		// using the normal $canbeempty to flag if a blank option is provided
		$parent_class = get_parent_class($this);
		parent::$parent_class($elemname, $currentvalue, $prompttext, $helptext);

		$this->html_input = $this->buildselectbox($valarray, $currentvalue, $canbeempty, $onchange, $disable);
	}
	
	// Build select box
	function buildselectbox($valarray, $currentvalue, $canbeempty, $onchange, $disable) {
		$output = '';
		$onchange = ($onchange != '') ? " onchange=\"$onchange\"" : '';
		
		$tabindex = guielement::gettabindex();
		$disable_state = $disable ? 'disabled="true"':'';
		$output .= "\n\t\t\t<select name=\"$this->_elemname\" id=\"$this->_elemname\" tabindex=\"$tabindex\" $disable_state $onchange >\n";
		// include blank option if required
		if ($canbeempty)
			$output .= "<option value=\"\">&nbsp;</option>";			

		// build the options
		foreach ($valarray as $item) {
			$itemvalue = (isset($item['value']) ? $item['value'] : '');
			$itemtext = (isset($item['text']) ? $item['text'] : '');
			$itemselected = ((string) $currentvalue == (string) $itemvalue) ? ' selected' : '';
			
			$output .= "\t\t\t\t<option value=\"$itemvalue\"$itemselected>$itemtext</option>\n";
		}
		$output .= "\t\t\t</select>\n\t\t";
		
		return $output;
	}
}

class gui_checkbox extends guiinput {
	function gui_checkbox($elemname, $checked=false, $prompttext='', $helptext='', $value='on', $post_text = '', $jsonclick = '', $disable=false) {
		$parent_class = get_parent_class($this);
		parent::$parent_class($elemname, '', $prompttext, $helptext);

		$itemchecked = $checked ? 'checked' : '';
		$disable_state = $disable ? 'disabled="true"' : '';
		$js_onclick_include = ($jsonclick != '') ? 'onclick="' . $jsonclick. '"' : '';
		$tabindex = guielement::gettabindex();

		$this->html_input = "<input type=\"checkbox\" name=\"$this->_elemname\" id=\"$this->_elemname\" $disable_state tabindex=\"$tabindex\" value=\"$value\" $js_onclick_include $itemchecked/>$post_text\n";
	}
}

class gui_radio extends guiinput {
	function gui_radio($elemname, $valarray, $currentvalue = '', $prompttext = '', $helptext = '', $disable=false) {
		if (!is_array($valarray)) {
			trigger_error('$valarray must be a valid array in gui_radio');
			return;
		}

		$parent_class = get_parent_class($this);
		parent::$parent_class($elemname, $currentvalue, $prompttext, $helptext);

		$this->html_input = $this->buildradiobuttons($valarray, $currentvalue, $disable);
	}
	
	function buildradiobuttons($valarray, $currentvalue, $disable=false) {
		$output = '';
		$output .= '<span class="radioset">';
		
		$count = 0;
		foreach ($valarray as $item) {
			$itemvalue = (isset($item['value']) ? $item['value'] : '');
			$itemtext = (isset($item['text']) ? $item['text'] : '');
			$itemchecked = ((string) $currentvalue == (string) $itemvalue) ? ' checked=checked' : '';
			
			$tabindex = guielement::gettabindex();
			$disable_state = $disable ? 'disabled="true"':'';
			$output .= "<input type=\"radio\" name=\"$this->_elemname\" id=\"$this->_elemname$count\" $disable_state tabindex=\"$tabindex\" value=\"$this->_elemname=$itemvalue\"$itemchecked/><label for=\"$this->_elemname$count\">$itemtext</label>\n";
			$count++;
		}
		$output .= '</span>';
		return $output;
	}
}

class gui_drawselects extends guiinput {
	function gui_drawselects($elemname, $index, $dest, $prompttext = '', $helptext = '', $required = false, $failvalidationmsg='', $nodest_msg='') {
		global $currentcomponent;
		$parent_class = get_parent_class($this);
		$jsvalidation = isset($jsvalidation) ? $jsvalidation : '';
		$jsvalidationtest = isset($jsvalidationtest) ? $jsvalidationtest : '';
		parent::$parent_class($elemname, '', $prompttext, $helptext, $jsvalidation, $failvalidationmsg, '', $jsvalidationtest);
		
		$this->html_input=drawselects($dest, $index, false, false, $nodest_msg, $required);

		//attach a value to this element, so that we can find its value
		$currentcomponent->addguielem('', new gui_hidden($elemname,'goto'.$index,false));
	}	
}

class gui_textarea extends guiinput {
	function gui_textarea($elemname, $currentvalue = '', $prompttext = '', $helptext = '', $jsvalidation = '', $failvalidationmsg = '', $canbeempty = true, $maxchars = 0) {
		// call parent class contructor
		$parent_class = get_parent_class($this);
		parent::$parent_class($elemname, $currentvalue, $prompttext, $helptext, $jsvalidation, $failvalidationmsg, $canbeempty);
		
		$maxlength = ($maxchars > 0) ? " maxlength=\"$maxchars\"" : '';
	
		$list = explode("\n",$this->currentvalue);
		$rows = count($list); 
		$rows = (($rows > 20) ? 20 : $rows);

		$this->html_input = "<textarea rows=\"$rows\" cols=\"24\" name=\"$this->_elemname\" id=\"$this->_elemname\"$maxlength>" . htmlentities($this->currentvalue) . "</textarea>";
	}
}

/*
************************************************************
** guitext is the base class of all text fields (e.g. h1) **
************************************************************
*/

class guitext extends guielement {
	var $html_text;

	function guitext($elemname, $html_text = '') {
		// call parent class contructor
		guielement::guielement($elemname, '', '');
		
		$this->html_text = $html_text;
	}
	
	function generatehtml($section = '') {
		// this effectivly creates the template using the html_text
		// we would expect the $html_text to be set by the child class
		
		$output = '';
		
		// start new row
		if ($section) {
			$mysec = preg_replace('/[^A-Za-z]/', '' ,$section);
			$output .= '<tr class="' . $section . '" '
					. ((isset($this->_opts[$mysec]['guielToggle']) && $this->_opts[$mysec]['guielToggle'] == false) 
						? ' style="display:none" ' 
						: '')
					.' >' . "\n";
		} else {
			$output .= "\t<tr>\n";
		}
		

		// actual input in second row
		$output .= "\t\t<td colspan=\"2\">";
		$output .= $this->html_text;
		$output .= "</td>\n";
		
		// end this row
		$output .= "\t</tr>\n";
		
		return $output;
	}	
}

// Label -- just text basically!
class gui_label extends guitext {
	function gui_label($elemname, $text, $uselang = true) {
		// call parent class contructor
		$parent_class = get_parent_class($this);
		parent::$parent_class($elemname, $text);
		
		// nothing really needed here as it's just whatever text was passed
		// but suppose we should do something with the element name
		$this->html_text = "<span id=\"$this->_elemname\">$text</span>";
	}
}

// Main page header
class gui_pageheading extends guitext {
	function gui_pageheading($elemname, $text, $uselang = true) {
		// call parent class contructor
		$parent_class = get_parent_class($this);
		parent::$parent_class($elemname, $text);

		// H2
		$this->html_text = "<h2 id=\"$this->_elemname\">$text</h2>";
	}
}

// Second level / sub header
class gui_subheading extends guitext {
	function gui_subheading($elemname, $text, $uselang = true) {
		// call parent class contructor
		$parent_class = get_parent_class($this);
		parent::$parent_class($elemname, $text);

		// H3
		$this->html_text = "<h3 id=\"$this->_elemname\">$text</h3>";		
	}
}

// URL / Link
class gui_link extends guitext {
	function gui_link($elemname, $text, $url, $uselang = true) {
		
		// call parent class contructor
		$parent_class = get_parent_class($this);
		parent::$parent_class($elemname, $text);

		// A tag
		$this->html_text = "<a href=\"$url\" id=\"$this->_elemname\">$text</a>";
	}
}
class gui_link_label extends guitext {
	function gui_link_label($elemname, $text, $tooltip, $uselang = true) {
		// call parent class contructor
		$parent_class = get_parent_class($this);
		parent::$parent_class($elemname, $text);

		// A tag
		$this->html_text = "<a href=\"#\" class=\"info\" id=\"$this->_elemname\">$text:<span>$tooltip</span></a>";
	}
}

?>
