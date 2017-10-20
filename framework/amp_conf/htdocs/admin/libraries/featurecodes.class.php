<?php
class featurecode {
	var $_modulename;	// Module name
	var $_featurename;	// Feature name
	var $_description;	// Description (i.e. what the user will see)
	var $_defaultcode;	// Default code if user doesn't pick one
	var $_customcode;	// Custom code
	var $_enabled;		// Enabled/Disabled (0=disabled; 1=enabled; -1=unknown)
	var $_providedest;		// 1=provide a featurecode destination for this code to modules
	var $_loaded;		// If this feature code was succesfully loaded from the DB
	var $_overridecodes;		// Overide defaults from featurecodes.conf

	// CONSTRUCTOR
	function featurecode($modulename, $featurename) {
		global $amp_conf;

		if ($modulename == '' || $featurename == '')
			die_issabelpbx('feature code class must be called with ModuleName and FeatureName');

		$fd = $amp_conf['ASTETCDIR'].'/issabelpbx_featurecodes.conf';
		$this->_overridecodes = array();
		if (file_exists($fd)) {
			$this->_overridecodes = parse_ini_file($fd,true);
		}

		$this->_modulename = $modulename;
		$this->_featurename = $featurename;
		$this->_enabled = -1;  // -1 means not initialised
		$this->_providedest = 0;  // no destination by default
		$this->_loaded = false;
	}

	// HAS BEEN INIT'D ????
	function isReady() {
		return (!($this->_enabled == -1));
	}
	
	// INIT FUNCTION -- READS FROM DATABASE IF THERE BASICALLY
	// $opt = 0 -- called by user code (i.e. outside this class)
	// $opt = 1 -- called automatically by this class
	// $opt = 2 -- called by user code, run even if called once already
	function init($opt = 0) {
		if ($this->isReady()) {
			if ($opt < 2)
				die_issabelpbx('FeatureCode: init already called!');
		}
			
		$s = "SELECT description, defaultcode, customcode, enabled, providedest ";
		$s .= "FROM featurecodes ";
		$s .= "WHERE modulename = ".sql_formattext($this->_modulename)." AND featurename = ".sql_formattext($this->_featurename)." ";
		
		$res = sql($s, "getRow");
		if (is_array($res)) { // found something, read it
			$this->_description = $res[0];
			if (isset($this->_overridecodes[$this->_modulename][$this->_featurename]) && trim($this->_overridecodes[$this->_modulename][$this->_featurename]) != '') {
				$this->_defaultcode = $this->_overridecodes[$this->_modulename][$this->_featurename];
				if ($this->_defaultcode != $res[1]) {
					$sql = 'UPDATE featurecodes SET defaultcode = '.sql_formattext($this->_defaultcode). 
						'WHERE modulename = '.sql_formattext($this->_modulename). ' AND featurename = '.sql_formattext($this->_featurename);
					sql($sql, 'query');
				}
			} else {
				$this->_defaultcode = $res[1];
			}
			$this->_customcode = $res[2];
			$this->_enabled = $res[3];
			$this->_providedest = $res[4];
			
			$this->_loaded = true;

			return true;
		} else {
			
			return false;
		}
	}
	
	// UPDATE FUNCTION -- WRITES CURRENT STUFF BACK TO DATABASE
	function update() {
		global $amp_conf;
		if ($this->_enabled == -1) {
			// not explicitly set, old default was to enable by default, we will preserve that behaviour
			$this->_enabled = 1;
		}

		if (!$this->isReady())
			die_issabelpbx('FeatureCode: class function init never called...will not update');

		
		if ($this->_loaded) {
			$sql = 'UPDATE featurecodes SET '.
			       'description = '.sql_formattext($this->_description).', '.
			       'defaultcode = '.sql_formattext($this->_defaultcode).', '.
			       'customcode = '.sql_formattext($this->_customcode).', '.
			       'enabled = '.sql_formattext($this->_enabled).', '.
			       'providedest = '.sql_formattext($this->_providedest).' '.
			       'WHERE modulename = '.sql_formattext($this->_modulename).
			       ' AND featurename = '.sql_formattext($this->_featurename);
		} else {
			$sql = 'INSERT INTO featurecodes (modulename, featurename, description, defaultcode, customcode, enabled, providedest) '.
        'VALUES ('.sql_formattext($this->_modulename).', '.sql_formattext($this->_featurename).', '.sql_formattext($this->_description).', '.sql_formattext($this->_defaultcode).', '.sql_formattext($this->_customcode).', '.sql_formattext($this->_enabled).', '.sql_formattext($this->_providedest).') ';
		}

		sql($sql, 'query');
		
		return true;
	}
	
	// SET DESCRIPTION
	function setDescription($description) {
		if (!$this->isReady())
			$this->init(1);

		if ($description == '') {
			unset($this->_description);
		} else {
			$this->_description = $description;
		}
	}
	
	// GET DESCRIPTION
	function getDescription() {
		if (!$this->isReady())
			$this->init(1);
		
		$desc = (isset($this->_description) ? $this->_description : '');
		
		return ($desc != '' ? $desc : $this->_featurename);
	}
	
	// SET DEFAULT CODE
	function setDefault($defaultcode, $defaultenabled = true) {
		if (!$this->isReady())
			$this->init(1);
			
		if (isset($this->_overridecodes[$this->_modulename][$this->_featurename])) {
			$defaultcode = $this->_overridecodes[$this->_modulename][$this->_featurename];
		}

		if (trim($defaultcode) == '') {
			unset($this->_defaultcode);
		} else {
			$this->_defaultcode = $defaultcode;			
		}

		if ($this->_enabled == -1) {
			$this->_enabled = ($defaultenabled) ? 1 : 0;
		}

	}
	
	// GET DEFAULT CODE
	function getDefault() {
		if (!$this->isReady())
			$this->init(1);
		
		$def = (isset($this->_defaultcode) ? $this->_defaultcode : '');
		
		return $def;
	}
	
	// SET CUSTOM CODE
	function setCode($customcode) {
		if (!$this->isReady())
			$this->init(1);

		if ($customcode == '') {
			unset($this->_customcode);
		} else {
			$this->_customcode = $customcode;
		}
	}
	
	// GET FEATURE CODE -- DEFAULT OR CUSTOM IF SET
	//                     RETURN '' IF NOT AVAILABLE
	function getCode() {
		if (!$this->isReady())
			$this->init(1);

		$curcode = (isset($this->_customcode) ? $this->_customcode : '');
		$defcode = (isset($this->_defaultcode) ? $this->_defaultcode : '');
		
		return ($curcode == '' ? $defcode : $curcode);
	}
	
	// GET FEATURE CODE ONLY IF ENABLED
	function getCodeActive() {
		if ($this->isEnabled()) {
			return $this->getCode();
		} else {
			return '';
		}
	}
	
	// SET ENABLED
	function setEnabled($b = true) {
		if (!$this->isReady())
			$this->init(1);

		$this->_enabled = ($b ? 1 : 0);
	}
	
	// GET ENABLED
	function isEnabled() {
		if (!$this->isReady())
			$this->init(1);

		return ($this->_enabled == 1);
	}

	// SET to provide destinatinos
	function setProvideDest($b = true) {
		if (!$this->isReady())
			$this->init(1);

		$this->_providedest = ($b ? 1 : 0);
	}
	
	// GET status if providing providedests
	function isProvideDest() {
		if (!$this->isReady())
			$this->init(1);

		return ($this->_providedest == 1);
	}

	function delete() {
		$s = "DELETE ";
		$s .= "FROM featurecodes ";
		$s .= "WHERE modulename = ".sql_formattext($this->_modulename)." ";
		$s .= "AND featurename = ".sql_formattext($this->_featurename);
		sql($s, 'query');
		
		$this->_enabled = -1; // = not ready
		
		return true;
	}
}

?>