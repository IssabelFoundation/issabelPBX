<?php

class modulelist{
	var $_loaded = false;
	var $module_array = array();
	var $_db;

	function &create(&$db) {
		static $obj;
		if (!isset($obj)) {
			$obj = new modulelist($db);
		}
		return $obj;
	}
	function modulelist(&$db) {
		$this->_db =& $db;
		$module_serialized = sql("SELECT `data` FROM `module_xml` WHERE `id` = 'mod_serialized'","getOne");
		if (isset($module_serialized) && $module_serialized) {
			$this->module_array = (unserialize($module_serialized));
			$this->_loaded = true;
		}
	}
	function is_loaded() {
		return $this->_loaded;
	}
	function initialize(&$module_list) {
		$this->module_array = $module_list;
    // strip out extraneous fields (help especially when printing out debugs
    //
    foreach ($this->module_array as $mod_key => $mod) {
      if (isset($mod['changelog'])) {
        unset($this->module_array[$mod_key]['changelog']);
      }
      if (isset($mod['attention'])) {
        unset($this->module_array[$mod_key]['attention']);
      }
      if (!isset($mod['license'])) {
        $this->module_array[$mod_key]['license'] = '';
      }
      if (isset($mod['location'])) {
        unset($this->module_array[$mod_key]['location']);
      }
      if (isset($mod['md5sum'])) {
        unset($this->module_array[$mod_key]['md5sum']);
      }
    }
		$module_serialized = $this->_db->escapeSimple(serialize($this->module_array));
		sql("REPLACE INTO `module_xml` (`id`, `time`, `data`) VALUES ('mod_serialized', '".time()."','".$module_serialized."')");
		$this->_loaded = true;
	}
	function invalidate() {
		unset($this->module_array);
		sql("DELETE FROM `module_xml` WHERE `id` = 'mod_serialized'");
		$this->_loaded = false;
	}
}
