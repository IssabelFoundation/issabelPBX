<?php
/**
 * IssabelPBX messaging and error messaging class
 *
 * short module / framework messaging and error reporting system
 *
 * @author Philippe Lindheimer
 */
class fwmsg {

	// Where errors are stored
	//
	static private $errors = array();
	static private $dest_set = false;

	/**
	 * set_error()
	 * short log an error to the framework for processing
	 *
	 * @access static public
	 * @param string
	 * @param string
	 *
	 * passes in a string and optional element id in the form where the server side
	 * validation error occured.
	 *
	 * TODO: does this need to be changed to be stored in the session? There may be scenarios
	 *       where it needs to be retained across redirects?
	 */
	static public function set_error($msg, $elem_id = false) {
		// TODO: does this also have to be set in the SESSION? (scenarios
		//       like redirect_continue?
		self::$errors[] = array('msg' => $msg, 'elem_id' => $elem_id);
	}

	/**
	 * get_errors()
	 * short return the error array of any errors reported
	 *
	 * @access static public
	 * @return array
	 *
	 * returns the error array of all errors logged, for each msg logged it will have the
	 * element id passed in with it or boolean false if not passed.
	 */
	static public function get_errors() {
		return self::$errors;
	}

	/**
	 * errors()
	 * returns true if there are reported errors
	 *
	 * @access static public
	 * @return boolean
	 *
	 * if the error array is empty it will return false, otherwise true
	 */
	static public function errors() {
		return !empty(self::$errors);
	}

	/**
	 * is_dest_set()
	 * returns true if an explicit set_dest() has been called in this session
	 *
	 * @access static public
	 * @return boolean
	 *
	 * get_dest() stores the destination in the SESSION so it can be retrieved
	 * accross redirects during processing. This indicates whether the current
	 * destination in the session has been just set or not.
	 */
	static public function is_dest_set() {
		return self::$dest_set;
	}

	/**
	 * set_dest()
	 * short set a recently created destination
	 *
	 * @access static public
	 * @param string
	 *
	 * pass in the destination just created from an add action in a module. Used so framework can
	 * passs back the created destination in popOver frames back to the parent window to set the
	 * new selection. Goes through the SESSION since modules usually do redirects()
	 */
	static public function set_dest($dest) {
		$_SESSION['fwmsg']['last_dest'] = $dest;
		self::$dest_set = true;
	}

	/**
	 * get_dest()
	 * short get a recently created destination
	 *
	 * @access static public
	 * @param boolean
	 * @return mixed
	 *
	 * returns the last set destination a module provided or boolean false if no call to set_dest()
	 * has been made to provide a destination. If $unset optional parameter is set to true then
	 * the $_SESSION variable will be cleared.
	 * TODO: this doesnt belong in a messaging class!
	 */
	static public function get_dest($unset=false) {
		$last_dest = isset($_SESSION['fwmsg']['last_dest']) ? $_SESSION['fwmsg']['last_dest'] : false;
		if ($unset && isset($_SESSION['fwmsg']['last_dest'])) {
			unset($_SESSION['fwmsg']['last_dest']);
		}
		return $last_dest;
	}
}
