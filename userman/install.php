<?php
$sqls = array();
$sqls[] = "CREATE TABLE IF NOT EXISTS `issabelpbx_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `default_extension` varchar(45) NOT NULL DEFAULT 'none',
  `fname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `cell` varchar(100) DEFAULT NULL,
  `work` varchar(100) DEFAULT NULL,
  `home` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`)
)";
$sqls[] = "CREATE TABLE IF NOT EXISTS `issabelpbx_users_settings` (
  `uid` int(11) NOT NULL,
  `module` char(65) NOT NULL,
  `key` char(255) NOT NULL,
  `val` longblob NOT NULL,
  `type` char(16) DEFAULT NULL,
  UNIQUE KEY `index4` (`uid`,`module`,`key`),
  KEY `index2` (`uid`,`key`),
  KEY `index6` (`module`,`uid`)
)";
foreach($sqls as $sql) {
	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_issabelpbx($result->getDebugInfo());
	}
}

if (!$db->getAll('SHOW COLUMNS FROM `issabelpbx_users` WHERE FIELD = "default_extension"')) {
	out("Adding default extension column");
    $sql = "ALTER TABLE `issabelpbx_users` ADD COLUMN `default_extension` VARCHAR(45) NOT NULL DEFAULT 'none' AFTER `password`";
    $result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM `issabelpbx_users` WHERE FIELD = "fname"')) {
    out("Adding additional fields");
    $sql = "ALTER TABLE `issabelpbx_users` ADD COLUMN `fname` VARCHAR(100) NULL DEFAULT NULL AFTER `default_extension`, ADD COLUMN `lname` VARCHAR(100) NULL DEFAULT NULL AFTER `fname`, ADD COLUMN `title` VARCHAR(100) NULL DEFAULT NULL AFTER `lname`, ADD COLUMN `department` VARCHAR(100) NULL DEFAULT NULL AFTER `title`, ADD COLUMN `email` VARCHAR(100) NULL DEFAULT NULL AFTER `department`, ADD COLUMN `cell` VARCHAR(100) NULL DEFAULT NULL AFTER `email`, ADD COLUMN `work` VARCHAR(100) NULL DEFAULT NULL AFTER `cell`, ADD COLUMN `home` VARCHAR(100) NULL DEFAULT NULL AFTER `work`";
    $result = $db->query($sql);
}

$issabelpbx_conf =& issabelpbx_conf::create();

$set = array();
$set['value'] = '';
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = 'userman';
$set['category'] = 'User Management Module';
$set['emptyok'] = 1;
$set['name'] = 'Email "From:" Address';
$set['description'] = 'The From: field for emails when using the user management email feature.';
$set['type'] = CONF_TYPE_TEXT;
$issabelpbx_conf->define_conf_setting('AMPUSERMANEMAILFROM',$set,true);
