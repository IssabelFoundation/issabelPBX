<?php
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2006-2014 Schmooze Com Inc.
//
class average_rate_calculator {
	var $_max_age;
	var $_values;
	
	/** Constructor 
	 * @param   array	A reference to an array to use for storage. This will be populated with key/value pairs that store the time/value, respectively.
	 * 			Because it is passed by reference, it can be stored externally in a session or database, allowing persistant use of this object
	 *			across page loads.
	 * @param  int	The maximum age of values to store, in seconds
	 */
	function average_rate_calculator(&$storage_array, $max_age) {
		$this->_max_age = $max_age;
		if (!is_array($storage_array)) {
			$storage_array = array();
		}
		$this->_values =& $storage_array;
	}
	/** Adds a value to the array
	 * @param  float	The value to add
	 * @param  int	The timestamp to use for this value, defaults to now
	 */
	function add($value, $timestamp=null) {
		if (!$timestamp) $timestamp = time();
		$this->_values[$timestamp] = $value;
	}
	/** Calculate the average per second value 
	 * @return  The average value, as a rate per second
	 */
	function average() {
		$this->_clean();
		
		$avgs = array();
		$last_time = false;
		$last_val = false;
		foreach ($this->_values as $time=>$val) {
			if ($last_time) {
				$avgs[] = ($val - $last_val) / ($time - $last_time);
			}
			$last_time = $time;
			$last_val = $val;
		}
		// return the average of all our averages
		if ($count = count($avgs)) {
			return array_sum($avgs) / $count;
		} else {
			return 'unknown';
		}
	}
	/** Clean old values out of the array
	 */
	function _clean() {
		$too_old = time() - $this->_max_age;
		
		foreach (array_keys($this->_values) as $key) {
			if ($key < $too_old) {
				unset($this->_values[$key]);
			}
		}
	}
}

?>
