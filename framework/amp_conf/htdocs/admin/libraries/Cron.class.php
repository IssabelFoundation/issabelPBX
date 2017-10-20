<?php

// Cron Class. Adds and removes entries to Crontab. 
// 
// If run as root, can manage any user:
//   $cron = new Cron('username');
//
// Otherwise manages current user.
//
// $cron->add("@monthly /bin/true");
// $cron->remove("@monthly /bin/true");
// $cron->add(array("magic" => "@monthly", "command" => "/bin/true"));
// $cron->add(array("hour" => "1", "command" => "/bin/true"));
// $cron->removeAll("/bin/true");

class Cron {

	private $user;

	public function __construct($user = 'asterisk') {

		$this->user = $user;

		// If we're not root, we can only edit our own cron.
		if (posix_geteuid() != 0) {
			$userArray = posix_getpwuid(posix_geteuid());
			if ($userArray['user'] != $user)
				throw new Exception("Trying to edit user $user, when I'm running as ".$userArray['user']);
		}
	}

	// Returns an array of all the lines for the user
	public function getAll() {
		exec('/usr/bin/crontab -u '.$this->user.' -l 2>&1', $output, $ret);
		if (preg_match('/^no crontab for/', $output[0]))
			return array();

		return $output;
	}

	// Returns true or false if the line exactly exists in this users crontab
	public function checkLine($line = null) {
		if ($line == null)
			throw new Exception("Null handed to checkLine");

		$allLines = $this->getAll();
		return in_array($line, $allLines);
	}

	// Add the exact line given to this users crontab
	public function addLine($line) {
		$line = trim($line);
		$backup = $this->getAll();
		$newCrontab = $backup;

		if (!$this->checkLine($line)) {
			$newCrontab[] = $line;
			$this->installCrontab($newCrontab);
			if ($this->checkLine($line))
				return true;
			// It didn't stick. WTF? Put our original one back.
			$this->installCrontab($backup);
			throw new Exception("Cron line added didn't remain in crontab on final check");
		} else {
			// It was already there. 
			return true;
		}
	}

	// Remove the line given (if it exists) from this users crontab.
	// Note: This will only remove the first if there's a duplicate.
	// Returns true if removed, false if not found.
	public function remove($line) {
		$line = trim($line);
		$backup = $this->getAll();
		$newCrontab = $backup;

		$ret = array_search($line, $newCrontab);
		if ($ret !== false) {
			unset($newCrontab[$ret]);
			$this->installCrontab($newCrontab);
			return true;
		} else {
			return false;
		}
	}

	// Add an entry to Cron. Takes either a direct string, or an array of the following options:
	// Either (a string): 
	//   * * * * * /bin/command/to/run
	// or
	//  array (
	//    array("command" => "/bin/command/to/run",  "minute" => "1"), // Runs command at 1 minute past the hour, every hour
	//    array("command" => "/bin/command/to/run", "magic" => "@hourly"), // Runs it hourly
	//    "* * * * * /bin/command/to/run",
	//    array("@monthly /bin/command/to/run"), // Runs it monhtly
	//  )
	//
	//  See the end of 'man 5 crontab' for the extension commands you can use. 
	// 
	// crontab does sanity checking when importing a crontab. If this is throwing an exception
	// about being unable to add an entry,check the error file /tmp/cron.error for reasons.

	public function add() {
		// Takes either an array, or a series of params
		$args = func_get_args();
		if (!isset($args[0]))
			throw new Exception("add takes at least one parameter");

		if (is_array($args[0])) {
			$addArray[] = $args[0];
		} else {
			$addArray[] = array($args[0]);
		}

		foreach ($addArray as $add) {
			if (isset($add[0])) {
				$this->addLine($add[0]);
				continue;
			} else if (is_array($add)) {
				if (!isset($add['command']))
					throw new Exception("No command to execute by cron");

				if (isset($add['magic'])) {
					$newline = $add['magic']." ";
				} else {
					$cronTime = array("minute", "hour", "dom", "month", "dow");
					foreach ($cronTime as $check) {
						if (isset($add[$check])) {
							$cronEntry[$check] = $add[$check];
						} else {
							$cronEntry[$check] = "*";
						}
					}
					$newline = implode(" ", $cronEntry);
				}
				if ($newline == "* * * * *")
					throw new Exception("Can't add * * * * * programatically. Add it as a line. Probably a bug");

				$newline .= " ".$add['command'];
				$this->addLine($newline);
			}
		}
	}

	// Removes all reference of $cmd in cron
	public function removeAll($cmd) {
		$crontab = $this->getAll();
		$changed = false;
		foreach ($crontab as $i => $v) {
			if (preg_match("/^#/", $v))
				continue;
			$cronline = preg_split("/\s/", $v);
			if ($cronline[0][0] == "@") {
				array_shift($cronline);
			} else {
				// Yuck.
				array_shift($cronline);
				array_shift($cronline);
				array_shift($cronline);
				array_shift($cronline);
				array_shift($cronline);
			}
			if (in_array($cmd, $cronline)) {
				unset($crontab[$i]);
				$changed = true;
			}
		}
		if ($changed)
			$this->installCrontab($crontab);
	}
	
	// Actually import the stuff to the crontab
	private function installCrontab($arr) {
		// Run crontab, hand it the array as stdin
		$fds = array( array('pipe', 'r'), array('pipe', 'w'), array('file', '/tmp/cron.error', 'a') );
		$rsc = proc_open('/usr/bin/crontab -u '.$this->user.' -', $fds, $pipes);
		if (!is_resource($rsc))
			throw new Exception("Unable to run crontab");

		fwrite($pipes[0], join("\n", $arr)."\n");
		fclose($pipes[0]);
	}

	// Self tests. Run these to sanity check this class.
	public function tests() {
		$this->add("@monthly /bin/false");
		if (!$this->checkLine("@monthly /bin/false"))
			throw new Exception("1: Line didn't exist when it should");
		$this->remove("@monthly /bin/false");
		if ($this->checkLine("@monthly /bin/false"))
			throw new Exception("2: Line existed when it should");
		$this->add(array("@monthly /bin/false"));
		if (!$this->checkLine("@monthly /bin/false"))
			throw new Exception("3: Line didn't exist when it should");
		$this->add(array("magic" => "@daily", "command" => "/bin/false"));
		if (!$this->checkLine("@monthly /bin/false"))
			throw new Exception("4: Line didn't exist when it should");
		$this->add(array("hour" => "1", "command" => "/bin/false"));
		if (!$this->checkLine("* 1 * * * /bin/false"))
			throw new Exception("5: Line didn't exist when it should");
		$this->removeAll("/bin/false");
		if ($this->checkLine("@monthly /bin/false"))
			throw new Exception("6: Line existed when it shouldn't");
		if ($this->checkLine("* 1 * * * /bin/false"))
			throw new Exception("7: Line didn't exist when it should");
		return true;
	}
}
