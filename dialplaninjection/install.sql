--
-- Database: `asterisk`
--

-- --------------------------------------------------------

--
-- Table structure for table `dialplaninjection_commands`
--

CREATE TABLE IF NOT EXISTS `dialplaninjection_commands` (
  `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `injectionid` int(11) NOT NULL default '0',
  `command` text NOT NULL,
  `sort` int(11) NOT NULL default '0'
);


-- --------------------------------------------------------

--
-- Table structure for table `dialplaninjection_commands_list`
--

CREATE TABLE IF NOT EXISTS `dialplaninjection_commands_list` (
  `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `description` varchar(100) UNIQUE NOT NULL default '',
  `command` text NOT NULL,
  `info` VARCHAR(255) NULL, 
  `url` VARCHAR(255) NULL
);

--
-- Dumping data for table `dialplaninjection_commands_list`
--


-- --------------------------------------------------------

--
-- Table structure for table `dialplaninjection_dialplaninjections`
--

CREATE TABLE IF NOT EXISTS `dialplaninjection_dialplaninjections` (
  `id` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `description` varchar(100) NOT NULL UNIQUE default '',
  `destination` varchar(250) NOT NULL default '',
  `exten` varchar(15) UNIQUE default NULL
);


--
-- Table structure for table `dialplaninjection_module`
--

CREATE TABLE IF NOT EXISTS `dialplaninjection_module` (
  `id` varchar(50) NOT NULL default '',
  `value` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
);

--
-- Dumping data for table `dialplaninjection_module`
--

INSERT IGNORE INTO `dialplaninjection_module` VALUES ('modulerawname', 'dialplaninjection');
INSERT IGNORE INTO `dialplaninjection_module` VALUES ('moduledisplayname', 'Dialplan Injection');
INSERT IGNORE INTO `dialplaninjection_module` VALUES ('moduleversion', '0.1.1n');





