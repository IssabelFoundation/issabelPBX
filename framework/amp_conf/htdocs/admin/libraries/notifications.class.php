<?php
/**
* IssabelPBX Notifications
*
* @package IssabelPBX
*/

define("NOTIFICATION_TYPE_CRITICAL", 100);
define("NOTIFICATION_TYPE_SECURITY", 200);
define("NOTIFICATION_TYPE_UPDATE",  300);
define("NOTIFICATION_TYPE_ERROR",    400);
define("NOTIFICATION_TYPE_WARNING" , 500);
define("NOTIFICATION_TYPE_NOTICE",   600);

/**
* IssabelPBX Notifications Class
*
* @package IssabelPBX
*/
class notifications{

	var $not_loaded = true;
	var $notification_table = array();
	var $_db;
		
	/** 
	* Create the Notification Class statically while checking to make sure the class hasn't already been loaded
	*
	* @param object Database Object
	* @return object Notification object
	*/
	function &create(&$db) {
		static $obj;
		if (!isset($obj)) {
			$obj = new notifications($db);
		}
		return $obj;
	}

	/** 
	* Create the Notification Class
	*
	* @param object Database Object
	*/
	function notifications(&$db) {
		$this->_db =& $db;
	}

    /**
    * Check to see if Notification Already exists
    * 
    * @param string $module Raw name of the module requesting
    * @param string $id ID of the notification
    * @return int Returns the number of notifications per module & id
    */
      function exists($module, $id) {
        $count = sql("SELECT count(*) FROM notifications WHERE `module` = '$module' AND `id` = '$id'", 'getOne');
        return ($count);
      }

    /**
      * Add a Critical Notification Message
      * 
      * @param string $module Raw name of the module requesting
      * @param string $id ID of the notification
      * @param string $display_text The text that will be displayed as the subject/header of the message
      * @param string $extended_text The extended text of the notification when it is expanded
      * @param string $link The link that is set to the notification
      * @param bool $reset Reset notification on module update
      * @param bool $candelete If the notification can be deleted by the user on the notifications display page
      * @return int Returns the number of notifications per module & id
      */
	function add_critical($module, $id, $display_text, $extended_text="", $link="", $reset=true, $candelete=false) {
		$this->_add_type(NOTIFICATION_TYPE_CRITICAL, $module, $id, $display_text, $extended_text, $link, $reset, $candelete);
        $this->_issabelpbx_log(IPBX_LOG_CRITICAL, $module, $id, $display_text, $extended_text);
	}
	/**
      * Add a Security Notification Message
      * 
      * @param string $module Raw name of the module requesting
      * @param string $id ID of the notification
      * @param string $display_text The text that will be displayed as the subject/header of the message
      * @param string $extended_text The extended text of the notification when it is expanded
      * @param string $link The link that is set to the notification
      * @param bool $reset Reset notification on module update
      * @param bool $candelete If the notification can be deleted by the user on the notifications display page
      * @return int Returns the number of notifications per module & id
      */
	function add_security($module, $id, $display_text, $extended_text="", $link="", $reset=true, $candelete=false) {
		$this->_add_type(NOTIFICATION_TYPE_SECURITY, $module, $id, $display_text, $extended_text, $link, $reset, $candelete);
        $this->_issabelpbx_log(IPBX_LOG_SECURITY, $module, $id, $display_text, $extended_text);
	}
	/**
      * Add an Update Notification Message
      * 
      * @param string $module Raw name of the module requesting
      * @param string $id ID of the notification
      * @param string $display_text The text that will be displayed as the subject/header of the message
      * @param string $extended_text The extended text of the notification when it is expanded
      * @param string $link The link that is set to the notification
      * @param bool $reset Reset notification on module update
      * @param bool $candelete If the notification can be deleted by the user on the notifications display page
      * @return int Returns the number of notifications per module & id
      */
	function add_update($module, $id, $display_text, $extended_text="", $link="", $reset=false, $candelete=false) {
		$this->_add_type(NOTIFICATION_TYPE_UPDATE, $module, $id, $display_text, $extended_text, $link, $reset, $candelete);
        $this->_issabelpbx_log(IPBX_LOG_UPDATE, $module, $id, $display_text, $extended_text);
	}
	/**
      * Add an Error Notification Message
      * 
      * @param string $module Raw name of the module requesting
      * @param string $id ID of the notification
      * @param string $display_text The text that will be displayed as the subject/header of the message
      * @param string $extended_text The extended text of the notification when it is expanded
      * @param string $link The link that is set to the notification
      * @param bool $reset Reset notification on module update
      * @param bool $candelete If the notification can be deleted by the user on the notifications display page
      * @return int Returns the number of notifications per module & id
      */
	function add_error($module, $id, $display_text, $extended_text="", $link="", $reset=false, $candelete=false) {
		$this->_add_type(NOTIFICATION_TYPE_ERROR, $module, $id, $display_text, $extended_text, $link, $reset, $candelete);
        $this->_issabelpbx_log(IPBX_LOG_ERROR, $module, $id, $display_text, $extended_text);
	}
	/**
      * Add a Warning Notification Message
      * 
      * @param string $module Raw name of the module requesting
      * @param string $id ID of the notification
      * @param string $display_text The text that will be displayed as the subject/header of the message
      * @param string $extended_text The extended text of the notification when it is expanded
      * @param string $link The link that is set to the notification
      * @param bool $reset Reset notification on module update
      * @param bool $candelete If the notification can be deleted by the user on the notifications display page
      * @return int Returns the number of notifications per module & id
      */
	function add_warning($module, $id, $display_text, $extended_text="", $link="", $reset=false, $candelete=false) {
		$this->_add_type(NOTIFICATION_TYPE_WARNING, $module, $id, $display_text, $extended_text, $link, $reset, $candelete);
        $this->_issabelpbx_log(IPBX_LOG_WARNING, $module, $id, $display_text, $extended_text);
	}
	/**
      * Add a Notice Notification Message
      * 
      * @param string $module Raw name of the module requesting
      * @param string $id ID of the notification
      * @param string $display_text The text that will be displayed as the subject/header of the message
      * @param string $extended_text The extended text of the notification when it is expanded
      * @param string $link The link that is set to the notification
      * @param bool $reset Reset notification on module update
      * @param bool $candelete If the notification can be deleted by the user on the notifications display page
      * @return int Returns the number of notifications per module & id
      */
	function add_notice($module, $id, $display_text, $extended_text="", $link="", $reset=false, $candelete=true) {
		$this->_add_type(NOTIFICATION_TYPE_NOTICE, $module, $id, $display_text, $extended_text, $link, $reset, $candelete);
        $this->_issabelpbx_log(IPBX_LOG_NOTICE, $module, $id, $display_text, $extended_text);
	}

    /**
      * List all Critical Messages
      * 
      * @param bool $show_reset Show resettable messages
      * @return array Returns the list of Messages
      */
	function list_critical($show_reset=false) {
		return $this->_list(NOTIFICATION_TYPE_CRITICAL, $show_reset);
	}
	/**
      * List all Security Messages
      * 
      * @param bool $show_reset Show resettable messages
      * @return array Returns the list of Messages
      */
	function list_security($show_reset=false) {
		return $this->_list(NOTIFICATION_TYPE_SECURITY, $show_reset);
	}
	/**
      * List all Update Messages
      * 
      * @param bool $show_reset Show resettable messages
      * @return array Returns the list of Messages
      */
	function list_update($show_reset=false) {
		return $this->_list(NOTIFICATION_TYPE_UPDATE, $show_reset);
	}
	/**
      * List all Error Messages
      * 
      * @param bool $show_reset Show resettable messages
      * @return array Returns the list of Messages
      */
	function list_error($show_reset=false) {
		return $this->_list(NOTIFICATION_TYPE_ERROR, $show_reset);
	}
	/**
      * List all Warning Messages
      * 
      * @param bool $show_reset Show resettable messages
      * @return array Returns the list of Messages
      */
	function list_warning($show_reset=false) {
		return $this->_list(NOTIFICATION_TYPE_WARNING, $show_reset);
	}
	/**
      * List all Notice Messages
      * 
      * @param bool $show_reset Show resettable messages
      * @return array Returns the list of Messages
      */
	function list_notice($show_reset=false) {
		return $this->_list(NOTIFICATION_TYPE_NOTICE, $show_reset);
	}
	/**
      * List all Messages
      * 
      * @param bool $show_reset Show resettable messages
      * @return array Returns the list of Messages
      */
	function list_all($show_reset=false) {
		return $this->_list("", $show_reset);
	}


    /**
      * Reset the status (hidden/shown) notifications of module & id
      * 
      * @param string $module Raw name of the module requesting
      * @param string $id ID of the notification
      */
	function reset($module, $id) {
		$module        = q($module);
		$id            = q($id);

		$sql = "UPDATE notifications SET reset = 1 WHERE module = $module AND id = $id";
		sql($sql);
	}
    /**
      * Forcefully Delete notifications of all specified level
      * 
      * @param NOTIFICAION LEVEL or blank for ALL levels
      */
	function delete_level($level="") {

		$sql = "DELETE FROM notifications";
		if ($level == '') {
			$level        = q($level);
			$sql .= ' ' . "WHERE level = $level";
		}
		sql($sql);
	}

    /**
      * Forcefully Delete notifications of module & id
      * 
      * @param string $module Raw name of the module requesting
      * @param string $id ID of the notification
      */
	function delete($module, $id) {
		$module        = q($module);
		$id            = q($id);

		$sql = "DELETE FROM notifications WHERE module = $module AND id = $id";
		sql($sql);
	}

    /**
      * Delete notifications of module & id if it is allowed by `candelete`
      * 
      * @param string $module Raw name of the module requesting
      * @param string $id ID of the notification
      */
	function safe_delete($module, $id) {
		$module        = q($module);
		$id            = q($id);

		$sql = "DELETE FROM notifications WHERE module = $module AND id = $id AND candelete = 1";
		sql($sql);
	}

    /**
		 * Ignore all future notifications for this type and delete
		 * if there are currently any
		 * 
		 * @param string $module Raw name of the module requesting
		 * @param string $id ID of the notification
		 */
	function ignore_forever($module, $id) {

		$issabelpbx_conf =& issabelpbx_conf::create();
		$setting = "NOTIFICATION_IGNORE_{$module}_{$id}";

		if (!$issabelpbx_conf->conf_setting_exists($setting)) {
			$set['value'] = true;
			$set['defaultval'] =& $set['value'];
			$set['options'] = '';
			$set['readonly'] = 1;
			$set['hidden'] = 1;
			$set['level'] = 10;
			$set['module'] = '';
			$set['category'] = 'Internal Use';
			$set['emptyok'] = 0;
			$set['name'] = "Ignore Notifications $module-$id";
			$set['description'] = "Always ignore notifications for $module-$id";
			$set['type'] = CONF_TYPE_BOOL;
			$issabelpbx_conf->define_conf_setting($setting,$set,true);
		} else {
			$issabelpbx_conf->set_conf_values(array($setting => true), true, true);
		}
		$this->delete($module, $id);
		return true;
	}

    /**
		 * Start paying attention to this notification type again
		 * 
		 * Undoes the effect of method ignore_forever
		 * 
		 * @param string $module Raw name of the module requesting
		 * @param string $id ID of the notification
		 */
	function undo_ignore_forever($module, $id) {

		$issabelpbx_conf =& issabelpbx_conf::create();
		$setting = "NOTIFICATION_IGNORE_{$module}_{$id}";

		$issabelpbx_conf->remove_conf_setting($setting);
	}


	/* Internal functions
	 */

 	/**
       * Add a Notification Message
       * 
       * @param const $level Notification Level
       * @param string $module Raw name of the module requesting
       * @param string $id ID of the notification
       * @param string $display_text The text that will be displayed as the subject/header of the message
       * @param string $extended_text The extended text of the notification when it is expanded
       * @param string $link The link that is set to the notification
       * @param bool $reset Reset notification on module update
       * @param bool $candelete If the notification can be deleted by the user on the notifications display page
       * @return int Returns the number of notifications per module & id
       * @ignore 
       */
	function _add_type($level, $module, $id, $display_text, $extended_text="", $link="", $reset=false, $candelete=false) {
		global $amp_conf;
		if (!empty($amp_conf["NOTIFICATION_IGNORE_{$module}_{$id}"])) {
			return null;
		}

		if ($this->not_loaded) {
			$this->notification_table = $this->_list("",true);
			$this->not_loaded = false;
		}

		$existing_row = false;
		foreach ($this->notification_table as $row) {
			if ($row['module'] == $module && $row['id'] == $id ) {
				$existing_row = $row;
				break;
			}
		}
		// Found an existing row - check if anything changed or if we are suppose to reset it
		//
		$candelete = $candelete ? 1 : 0;
		if ($existing_row) {

			if (($reset && $existing_row['reset'] == 1) || $existing_row['level'] != $level || $existing_row['display_text'] != $display_text || $existing_row['extended_text'] != $extended_text || $existing_row['link'] != $link || $existing_row['candelete'] == $candelete) {

				// If $reset is set to the special case of PASSIVE then the updates will not change it's value in an update
				//
				$reset_value = ($reset == 'PASSIVE') ? $existing_row['reset'] : 0;

				$module        = q($module);
				$id            = q($id);
				$level         = q($level);
				$display_text  = q($display_text);
				$extended_text = q($extended_text);
				$link          = q($link);
				$now = time();
				$sql = "UPDATE notifications SET
					level = $level,
					display_text = $display_text,
					extended_text = $extended_text,
					link = $link,
					reset = $reset_value,
					candelete = $candelete,
					timestamp = $now
					WHERE module = $module AND id = $id
				";
				sql($sql);

				// TODO: I should really just add this to the internal cache, but really
				//       how often does this get called that if is a big deal.
				$this->not_loaded = true;
			}
		} else {
			// No existing row so insert this new one
			//
			$now           = time();
			$module        = q($module);
			$id            = q($id);
			$level         = q($level);
			$display_text  = q($display_text);
			$extended_text = q($extended_text);
			$link          = q($link);
			$sql = "INSERT INTO notifications 
				(module, id, level, display_text, extended_text, link, reset, candelete, timestamp)
				VALUES 
				($module, $id, $level, $display_text, $extended_text, $link, 0, $candelete, $now)
			";
			sql($sql);

			// TODO: I should really just add this to the internal cache, but really
			//       how often does this get called that if is a big deal.
			$this->not_loaded = true;
		}
	}

	/**
      * List Messages by Level
      * 
      * @param const $level Notification Level to show (can be blank for all)
      * @param bool $show_reset Show resettable messages
      * @return array Returns the list of Messages
      * @ignore
      */
	function _list($level, $show_reset=false) {

		$level = q($level);
		$where = array();

		if (!$show_reset) {
			$where[] = "reset = 0";
		}

		switch ($level) {
			case NOTIFICATION_TYPE_CRITICAL:
			case NOTIFICATION_TYPE_SECURITY:
			case NOTIFICATION_TYPE_UPDATE:
			case NOTIFICATION_TYPE_ERROR:
			case NOTIFICATION_TYPE_WARNING:
			case NOTIFICATION_TYPE_NOTICE:
				$where[] = "level = $level ";
				break;
			default:
		}
		$sql = "SELECT * FROM notifications ";
		if (count($where)) {
			$sql .= " WHERE ".implode(" AND ", $where);
		}
		$sql .= " ORDER BY level, module";

		$list = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
		return $list;
	}

	/**
      * IssabelPBX Logging
      * 
      * @param const $level Notification Level to show (can be blank for all)
      * @param string $module Raw name of the module requesting
      * @param string $id ID of the notification
      * @param string $display_text The text that will be displayed as the subject/header of the message
      * @ignore
      */
  function _issabelpbx_log($level, $module, $id, $display_text, $extended_text=null) {
    global $amp_conf;
    if ($amp_conf['LOG_NOTIFICATIONS']) {
			if ($extended_text) {
				$display_text .= " ($extended_text)";
			}
			issabelpbx_log($level,"[NOTIFICATION]-[$module]-[$id] - $display_text");
		}
	}
	/** 
	* Returns the number of active notifications
	*
	* @return int Number of active Notifications
	*/
	function get_num_active() {
		$sql = "SELECT COUNT(id) FROM notifications WHERE reset = 0";
		return sql($sql,'getOne');
	}
}
