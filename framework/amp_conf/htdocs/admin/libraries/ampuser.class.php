<?php

class ampuser {
	var $username;
	var $_password;
	var $_extension_high;
	var $_extension_low;
	var $_deptname;
	var $_sections;
	
	function ampuser($username) {
		$this->username = $username;
		if ($user = $this->getAmpUser($username)) {
			$this->_password = $user["password_sha1"];
			$this->_extension_high = $user["extension_high"];
			$this->_extension_low = $user["extension_low"];
			$this->_deptname = $user["deptname"];
			$this->_sections = $user["sections"];
		} else {
			// user doesn't exist
			$this->_password = false;
			$this->_extension_high = "";
			$this->_extension_low = "";
			$this->_deptname = "";
			$this->_sections = array();
		}
	}
	
	/** Give this user full admin access
	*/
	function setAdmin() {
		$this->_extension_high = "";
		$this->_extension_low = "";
		$this->_deptname = "";
		$this->_sections = array("*");
	}
	
	function checkPassword($password) {
		// strict checking so false will never match
		return ($this->_password === $password);
	}
	
	function checkSection($section) {
		// if they have * then it means all sections
		return in_array("*", $this->_sections) || in_array($section, $this->_sections);
	}
	
	function getAmpUser($username) {
		global $db;

		$sql = "SELECT username, password_sha1, extension_low, extension_high, deptname, sections FROM ampusers WHERE username = '".$db->escapeSimple($username)."'";
		$results = $db->getAll($sql);
		if($db->IsError($results)) {
		   die_issabelpbx($sql."<br>\n".$results->getMessage());
		}

		if (count($results) > 0) {
			$user = array();
			$user["username"] = $results[0][0];
			$user["password_sha1"] = $results[0][1];
			$user["extension_low"] = $results[0][2];
			$user["extension_high"] = $results[0][3];
			$user["deptname"] = $results[0][4];
			$user["sections"] = explode(";",$results[0][5]);
			return $user;
		} else {
			return false;
		}
	}
}

?>
