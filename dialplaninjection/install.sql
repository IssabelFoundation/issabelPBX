--
-- Database: `asterisk`
--

-- --------------------------------------------------------

--
-- Table structure for table `dialplaninjection_commands`
--

CREATE TABLE IF NOT EXISTS `dialplaninjection_commands` (
  `id` int(11) NOT NULL auto_increment,
  `injectionid` int(11) NOT NULL default '0',
  `command` text NOT NULL,
  `sort` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;


-- --------------------------------------------------------

--
-- Table structure for table `dialplaninjection_commands_list`
--

CREATE TABLE IF NOT EXISTS `dialplaninjection_commands_list` (
  `id` int(11) NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  `command` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `dialplaninjection_commands_list`
--


-- --------------------------------------------------------

--
-- Table structure for table `dialplaninjection_dialplaninjections`
--

CREATE TABLE IF NOT EXISTS `dialplaninjection_dialplaninjections` (
  `id` int(11) NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  `destination` varchar(250) NOT NULL default '',
  `exten` varchar(15) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `description` (`description`),
  UNIQUE KEY `exten` (`exten`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;


--
-- Table structure for table `dialplaninjection_module`
--

CREATE TABLE IF NOT EXISTS `dialplaninjection_module` (
  `id` varchar(50) NOT NULL default '',
  `value` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `dialplaninjection_module`
--

INSERT IGNORE INTO `dialplaninjection_module` VALUES ('modulerawname', 'dialplaninjection');
INSERT IGNORE INTO `dialplaninjection_module` VALUES ('moduledisplayname', 'Dialplan Injection');
INSERT IGNORE INTO `dialplaninjection_module` VALUES ('moduleversion', '0.1.1n');





