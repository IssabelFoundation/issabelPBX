<?php

/* Usage
Grab some XML data, either from a file, URL, etc. however you want. Assume storage in $strYourXML;

$xml = new xml2Array($strYourXML);
xml array is in $xml->data;
	This is basically an array version of the XML data (no attributes), striaght-up. If there are
	multiple items with the same name, they are split into a numeric sub-array, 
	eg, <items><item test="123">foo</item><item>bar</item></items>
	becomes: array('item' => array(0=>array('item'=>'foo'), 1=>array('item'=>'foo'))
attributes are in $xml->attributes;
	These are stored with xpath type paths, as $xml->attributes['/items/item/0']["test"] == "123"
	

Other way (still works, but not as nice):

$objXML = new xml2Array();
$arrOutput = $objXML->parse($strYourXML);
print_r($arrOutput); //print it out, or do whatever!

*/

class xml2Array {
	var $arrOutput = array();
	var $resParser;
	var $strXmlData;
	
	var $attributes;
	var $data;
	
	function xml2Array($strInputXML = false) {
		if (!empty($strInputXML)) {
			$this->data = $this->parseAdvanced($strInputXML);
		}
	}
	
	function parse($strInputXML) {
	
			$this->resParser = xml_parser_create ();
			xml_set_object($this->resParser,$this);
			xml_set_element_handler($this->resParser, "tagOpen", "tagClosed");
			
			xml_set_character_data_handler($this->resParser, "tagData");
		
			$this->strXmlData = xml_parse($this->resParser,$strInputXML );
			if(!$this->strXmlData) {
				die_issabelpbx(sprintf("XML error: %s at line %d",
			xml_error_string(xml_get_error_code($this->resParser)),
			xml_get_current_line_number($this->resParser)));
			}
							
			xml_parser_free($this->resParser);
			
			return $this->arrOutput;
	}
	function tagOpen($parser, $name, $attrs) {
		$tag=array("name"=>$name,"attrs"=>$attrs);
		
		//prevent buffer overflows of PHP_INT_MAX on array keys
		//so reset the array keys
		//http://issues.issabel.org/browse/ISSABELPBX-6411
		$this->arrOutput = array_values($this->arrOutput);
		array_push($this->arrOutput,$tag);
	}
	
	function tagData($parser, $tagData) {
		if(trim($tagData)) {
			if(isset($this->arrOutput[count($this->arrOutput)-1]['tagData'])) {
				$this->arrOutput[count($this->arrOutput)-1]['tagData'] .= "\n".$tagData;
			} 
			else {
				$this->arrOutput[count($this->arrOutput)-1]['tagData'] = $tagData;
			}
		}
	}
	
	function tagClosed($parser, $name) {
		@$this->arrOutput[count($this->arrOutput)-2]['children'][] = isset($this->arrOutput[count($this->arrOutput)-1]) ? $this->arrOutput[count($this->arrOutput)-1] : '';
		array_pop($this->arrOutput);
	}
	
	function recursive_parseLevel($items, &$attrs, $path = "") {
		$array = array();
		foreach (array_keys($items) as $idx) {
			@$items[$idx]['name'] = isset($items[$idx]['name']) ? strtolower($items[$idx]['name']) : '';
			
			$multi = false;
			if (isset($array[ $items[$idx]['name'] ])) {
				// this child is already set, so we're adding multiple items to an array 
				
				if (!is_array($array[ $items[$idx]['name'] ]) || !isset($array[ $items[$idx]['name'] ][0])) {
					// hasn't already been made into a numerically-indexed array, so do that now
					// we're basically moving the current contents of this item into a 1-item array (at the 
					// original location) so that we can add a second item in the code below
					$array[ $items[$idx]['name'] ] = array( $array[ $items[$idx]['name'] ] );

					if (isset($attrs[ $path.'/'.$items[$idx]['name'] ])) {
						// move the attributes to /0
						$attrs[ $path.'/'.$items[$idx]['name'].'/0' ] = $attrs[ $path.'/'.$items[$idx]['name'] ];
						unset($attrs[ $path.'/'.$items[$idx]['name'] ]);
					}
				}
				$multi = true;
			}
			
			if ($multi) {	
				$newitem = &$array[ $items[$idx]['name'] ][];
			} else {
				$newitem = &$array[ $items[$idx]['name'] ];
			}
			
			
			if (isset($items[$idx]['children']) && is_array($items[$idx]['children'])) {
				$newitem = $this->recursive_parseLevel($items[$idx]['children'], $attrs, $path.'/'.$items[$idx]['name']);
			} else if (isset($items[$idx]['tagData'])) {
				$newitem = $items[$idx]['tagData'];
			} else {
				$newitem = false;
			}
			
			if (isset($items[$idx]['attrs']) && is_array($items[$idx]['attrs']) && count($items[$idx]['attrs'])) {
				$attrpath = $path.'/'.$items[$idx]['name'];
				if ($multi) {
					$attrpath .= '/'.(count($array[ $items[$idx]['name'] ])-1);
				}
				foreach ($items[$idx]['attrs'] as $name=>$value) {
					$attrs[ $attrpath ][ strtolower($name) ] = $value;
				}
			}
		}
		return $array;
	}
	
	function parseAdvanced($strInputXML) {
		$array = $this->parse($strInputXML);
		$this->attributes = array();
		return $this->data = $this->recursive_parseLevel($array, $this->attributes);
	}
}

/*
	Return a much more manageable assoc array with module data.
*/
class xml2ModuleArray extends xml2Array {
	function parseModulesXML($strInputXML) {
		$array = $this->parseAdvanced($strInputXML);
		if (isset($array['xml'])) {
			foreach ($array['xml'] as $key=>$module) {
				if ($key == 'module') {
					// copy the structure verbatim
					$modules[ $module['name'] ] = $module;
				}
			}
		}
		
		// if you are confused about what's happening below, uncomment this why we do it
		// echo "<pre>"; print_r($arrOutput); echo "</pre>";
		
		// ignore the regular xml garbage ([0]['children']) & loop through each module
		if(!is_array($arrOutput[0]['children'])) return false;
		foreach($arrOutput[0]['children'] as $module) {
			if(!is_array($module['children'])) return false;
			// loop through each modules's tags
			foreach($module['children'] as $modTags) {
					if(isset($modTags['children']) && is_array($modTags['children'])) {
						$$modTags['name'] = $modTags['children'];
						// loop if there are children (menuitems and requirements)
						foreach($modTags['children'] as $subTag) {
							$subTags[strtolower($subTag['name'])] = $subTag['tagData'];
						}
						$$modTags['name'] = $subTags;
						unset($subTags);
					} else {
						// create a variable for each tag we find
						$$modTags['name'] = $modTags['tagData'];
					}

			}
			// now build our return array
			$arrModules[$RAWNAME]['rawname'] = $RAWNAME;    // This has to be set
			$arrModules[$RAWNAME]['displayName'] = $NAME;    // This has to be set
			$arrModules[$RAWNAME]['version'] = $VERSION;     // This has to be set
			$arrModules[$RAWNAME]['type'] = isset($TYPE)?$TYPE:'setup';
			$arrModules[$RAWNAME]['category'] = isset($CATEGORY)?$CATEGORY:'Unknown';
			$arrModules[$RAWNAME]['info'] = isset($INFO)?$INFO:'http://www.issabel.org/wiki/'.$RAWNAME;
			$arrModules[$RAWNAME]['location'] = isset($LOCATION)?$LOCATION:'local';
			$arrModules[$RAWNAME]['items'] = isset($MENUITEMS)?$MENUITEMS:null;
			$arrModules[$RAWNAME]['requirements'] = isset($REQUIREMENTS)?$REQUIREMENTS:null;
			$arrModules[$RAWNAME]['md5sum'] = isset($MD5SUM)?$MD5SUM:null;
			//print_r($arrModules);
			//unset our variables
			unset($NAME);
			unset($VERSION);
			unset($TYPE);
			unset($CATEGORY);
			unset($AUTHOR);
			unset($EMAIL);
			unset($LOCATION);
			unset($MENUITEMS);
			unset($REQUIREMENTS);
			unset($MD5SUM);
		}
		//echo "<pre>"; print_r($arrModules); echo "</pre>";

		return $arrModules;
	}
}
