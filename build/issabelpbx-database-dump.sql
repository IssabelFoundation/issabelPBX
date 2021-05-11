-- MySQL dump 10.14  Distrib 5.5.56-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: asterisk
-- ------------------------------------------------------
-- Server version	5.5.56-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `asterisk` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `asterisk`;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `variable` varchar(20) NOT NULL DEFAULT '',
  `value` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`variable`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES ('need_reload','true'),('version','2.11.0.49'),('ALLOW_SIP_ANON','no');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ampusers`
--

DROP TABLE IF EXISTS `ampusers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ampusers` (
  `username` varchar(255) NOT NULL,
  `password_sha1` varchar(40) NOT NULL,
  `extension_low` varchar(20) NOT NULL DEFAULT '',
  `extension_high` varchar(20) NOT NULL DEFAULT '',
  `deptname` varchar(20) NOT NULL DEFAULT '',
  `sections` blob NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ampusers`
--

LOCK TABLES `ampusers` WRITE;
/*!40000 ALTER TABLE `ampusers` DISABLE KEYS */;
INSERT INTO `ampusers` VALUES ('admin','d033e22ae348aeb5660fc2140aec35850c4da997','','','','*');
/*!40000 ALTER TABLE `ampusers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcement`
--

DROP TABLE IF EXISTS `announcement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcement` (
  `announcement_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) DEFAULT NULL,
  `recording_id` int(11) DEFAULT NULL,
  `allow_skip` int(11) DEFAULT NULL,
  `post_dest` varchar(255) DEFAULT NULL,
  `return_ivr` tinyint(1) NOT NULL DEFAULT '0',
  `noanswer` tinyint(1) NOT NULL DEFAULT '0',
  `repeat_msg` varchar(2) NOT NULL DEFAULT '',
  `tts_lang` varchar(10) NOT NULL DEFAULT 'en-US',
  `tts_text` text NOT NULL,
  PRIMARY KEY (`announcement_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcement`
--

LOCK TABLES `announcement` WRITE;
/*!40000 ALTER TABLE `announcement` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup`
--

DROP TABLE IF EXISTS `backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `description` varchar(150) DEFAULT NULL,
  `immortal` varchar(25) DEFAULT NULL,
  `data` longtext,
  `email` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup`
--

LOCK TABLES `backup` WRITE;
/*!40000 ALTER TABLE `backup` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_cache`
--

DROP TABLE IF EXISTS `backup_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_cache` (
  `id` varchar(50) NOT NULL,
  `manifest` longtext,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_cache`
--

LOCK TABLES `backup_cache` WRITE;
/*!40000 ALTER TABLE `backup_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_details`
--

DROP TABLE IF EXISTS `backup_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_details` (
  `backup_id` int(11) NOT NULL,
  `key` varchar(50) DEFAULT NULL,
  `index` varchar(25) DEFAULT NULL,
  `value` varchar(250) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_details`
--

LOCK TABLES `backup_details` WRITE;
/*!40000 ALTER TABLE `backup_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_items`
--

DROP TABLE IF EXISTS `backup_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_items` (
  `backup_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `path` text,
  `exclude` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_items`
--

LOCK TABLES `backup_items` WRITE;
/*!40000 ALTER TABLE `backup_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_old`
--

DROP TABLE IF EXISTS `backup_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_old` (
  `name` varchar(50) DEFAULT NULL,
  `voicemail` varchar(50) DEFAULT NULL,
  `recordings` varchar(50) DEFAULT NULL,
  `configurations` varchar(50) DEFAULT NULL,
  `cdr` varchar(55) DEFAULT NULL,
  `fop` varchar(50) DEFAULT NULL,
  `minutes` varchar(50) DEFAULT NULL,
  `hours` varchar(50) DEFAULT NULL,
  `days` varchar(50) DEFAULT NULL,
  `months` varchar(50) DEFAULT NULL,
  `weekdays` varchar(50) DEFAULT NULL,
  `command` varchar(200) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ftpuser` varchar(50) DEFAULT NULL,
  `ftppass` varchar(50) DEFAULT NULL,
  `ftphost` varchar(50) DEFAULT NULL,
  `ftpdir` varchar(150) DEFAULT NULL,
  `sshuser` varchar(50) DEFAULT NULL,
  `sshkey` varchar(150) DEFAULT NULL,
  `sshhost` varchar(50) DEFAULT NULL,
  `sshdir` varchar(150) DEFAULT NULL,
  `emailaddr` varchar(75) DEFAULT NULL,
  `emailmaxsize` varchar(25) DEFAULT NULL,
  `emailmaxtype` varchar(5) DEFAULT NULL,
  `admin` varchar(10) DEFAULT NULL,
  `include` blob,
  `exclude` blob,
  `sudo` varchar(25) DEFAULT NULL,
  `remotesshhost` varchar(50) DEFAULT NULL,
  `remotesshuser` varchar(50) DEFAULT NULL,
  `remotesshkey` varchar(150) DEFAULT NULL,
  `remoterestore` varchar(5) DEFAULT NULL,
  `overwritebackup` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_old`
--

LOCK TABLES `backup_old` WRITE;
/*!40000 ALTER TABLE `backup_old` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_old` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_server_details`
--

DROP TABLE IF EXISTS `backup_server_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_server_details` (
  `server_id` int(11) NOT NULL,
  `key` varchar(50) DEFAULT NULL,
  `value` varchar(250) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_server_details`
--

LOCK TABLES `backup_server_details` WRITE;
/*!40000 ALTER TABLE `backup_server_details` DISABLE KEYS */;
INSERT INTO `backup_server_details` VALUES (1,'path','__ASTVARLIBDIR__/backups'),(2,'path','__ASTSPOOLDIR__/backup'),(3,'host','__AMPDBHOST__'),(3,'port','3306'),(3,'user','__AMPDBUSER__'),(3,'dbname','__AMPDBNAME__'),(3,'password','__AMPDBPASS__'),(4,'host','__CDRDBHOST__'),(4,'port','__CDRDBPORT__'),(4,'user','__CDRDBUSER__'),(4,'dbname','__CDRDBNAME__'),(4,'password','__CDRDBPASS__');
/*!40000 ALTER TABLE `backup_server_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_servers`
--

DROP TABLE IF EXISTS `backup_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `desc` varchar(150) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `readonly` varchar(250) DEFAULT NULL,
  `immortal` varchar(25) DEFAULT NULL,
  `data` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_servers`
--

LOCK TABLES `backup_servers` WRITE;
/*!40000 ALTER TABLE `backup_servers` DISABLE KEYS */;
INSERT INTO `backup_servers` VALUES (1,'Legacy Backup','Location of backups pre 2.10','local','a:1:{i:0;s:1:\"*\";}','true','a:1:{s:10:\"created_by\";s:11:\"install.php\";}'),(2,'Local Storage','Storage location for backups','local','a:1:{i:0;s:1:\"*\";}','true','a:1:{s:10:\"created_by\";s:11:\"install.php\";}'),(3,'Config server','PBX config server, generally a local database server','mysql','a:1:{i:0;s:1:\"*\";}','true','a:1:{s:10:\"created_by\";s:11:\"install.php\";}'),(4,'CDR server','CDR server, generally a local database server','mysql','a:1:{i:0;s:1:\"*\";}','true','a:1:{s:10:\"created_by\";s:11:\"install.php\";}');
/*!40000 ALTER TABLE `backup_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_template_details`
--

DROP TABLE IF EXISTS `backup_template_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_template_details` (
  `template_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `path` text,
  `exclude` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_template_details`
--

LOCK TABLES `backup_template_details` WRITE;
/*!40000 ALTER TABLE `backup_template_details` DISABLE KEYS */;
INSERT INTO `backup_template_details` VALUES (1,'mysql','server-3','a:1:{i:0;s:0:\"\";}'),(1,'astdb','','a:1:{i:0;s:0:\"\";}'),(2,'mysql','server-3','a:1:{i:0;s:0:\"\";}'),(2,'astdb','','a:1:{i:0;s:0:\"\";}'),(2,'mysql','server-4','a:1:{i:0;s:0:\"\";}'),(2,'dir','__ASTETCDIR__','a:1:{i:0;s:0:\"\";}'),(2,'dir','__AMPWEBROOT__','a:1:{i:0;s:0:\"\";}'),(2,'dir','__AMPBIN__','a:2:{i:0;s:20:\"__ASTVARLIBDIR__/moh\";i:1;s:23:\"__ASTVARLIBDIR__/sounds\";}'),(2,'dir','/etc/dahdi','a:1:{i:0;s:0:\"\";}'),(3,'mysql','server-4','a:1:{i:0;s:0:\"\";}'),(4,'dir','__ASTSPOOLDIR__/voicemail','a:1:{i:0;s:0:\"\";}'),(5,'dir','__ASTVARLIBDIR__/moh','a:1:{i:0;s:0:\"\";}'),(5,'dir','__ASTVARLIBDIR__/sounds/custom','a:1:{i:0;s:0:\"\";}'),(6,'mysql','server-3','a:9:{i:0;s:6:\"backup\";i:1;s:12:\"backup_cache\";i:2;s:14:\"backup_details\";i:3;s:12:\"backup_items\";i:4;s:21:\"backup_server_details\";i:5;s:14:\"backup_servers\";i:6;s:23:\"backup_template_details\";i:7;s:16:\"backup_templates\";i:8;s:0:\"\";}');
/*!40000 ALTER TABLE `backup_template_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_templates`
--

DROP TABLE IF EXISTS `backup_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `desc` varchar(150) DEFAULT NULL,
  `immortal` varchar(25) DEFAULT NULL,
  `data` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_templates`
--

LOCK TABLES `backup_templates` WRITE;
/*!40000 ALTER TABLE `backup_templates` DISABLE KEYS */;
INSERT INTO `backup_templates` VALUES (1,'Config Backup','Configurations only','true','a:1:{s:10:\"created_by\";s:11:\"install.php\";}'),(2,'Full Backup','A full backup of core settings and web files, dosen\'t include system sounds or recordings.','true','a:1:{s:10:\"created_by\";s:11:\"install.php\";}'),(3,'CDR\'s','Call Detail Records','true','a:1:{s:10:\"created_by\";s:11:\"install.php\";}'),(4,'Voice Mail','Voice Mail Storage','true','a:1:{s:10:\"created_by\";s:11:\"install.php\";}'),(5,'System Audio','All system audio - including IVR prompts and Music On Hold. DOES NOT BACKUP VOICEMAIL','true','a:1:{s:10:\"created_by\";s:11:\"install.php\";}'),(6,'Exclude Backup Settings','Exclude Backup\'s settings so that they dont get restored, usefull for a remote restore','true','a:1:{s:10:\"created_by\";s:11:\"install.php\";}');
/*!40000 ALTER TABLE `backup_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `callback`
--

DROP TABLE IF EXISTS `callback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `callback` (
  `callback_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) DEFAULT NULL,
  `callbacknum` varchar(100) DEFAULT NULL,
  `destination` varchar(50) DEFAULT NULL,
  `sleep` int(11) DEFAULT NULL,
  `deptname` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`callback_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `callback`
--

LOCK TABLES `callback` WRITE;
/*!40000 ALTER TABLE `callback` DISABLE KEYS */;
/*!40000 ALTER TABLE `callback` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `callrecording`
--

DROP TABLE IF EXISTS `callrecording`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `callrecording` (
  `callrecording_id` int(11) NOT NULL AUTO_INCREMENT,
  `callrecording_mode` varchar(50) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `dest` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`callrecording_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `callrecording`
--

LOCK TABLES `callrecording` WRITE;
/*!40000 ALTER TABLE `callrecording` DISABLE KEYS */;
/*!40000 ALTER TABLE `callrecording` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `callrecording_module`
--

DROP TABLE IF EXISTS `callrecording_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `callrecording_module` (
  `extension` varchar(50) DEFAULT NULL,
  `cidnum` varchar(50) DEFAULT '',
  `callrecording` varchar(10) DEFAULT NULL,
  `display` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `callrecording_module`
--

LOCK TABLES `callrecording_module` WRITE;
/*!40000 ALTER TABLE `callrecording_module` DISABLE KEYS */;
/*!40000 ALTER TABLE `callrecording_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cidlookup`
--

DROP TABLE IF EXISTS `cidlookup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cidlookup` (
  `cidlookup_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  `sourcetype` varchar(100) NOT NULL,
  `cache` tinyint(1) NOT NULL DEFAULT '0',
  `deptname` varchar(30) DEFAULT NULL,
  `http_host` varchar(30) DEFAULT NULL,
  `http_port` varchar(30) DEFAULT NULL,
  `http_username` varchar(30) DEFAULT NULL,
  `http_password` varchar(30) DEFAULT NULL,
  `http_path` varchar(100) DEFAULT NULL,
  `http_query` varchar(100) DEFAULT NULL,
  `mysql_host` varchar(60) DEFAULT NULL,
  `mysql_dbname` varchar(60) DEFAULT NULL,
  `mysql_query` text,
  `mysql_username` varchar(30) DEFAULT NULL,
  `mysql_password` varchar(30) DEFAULT NULL,
  `mysql_charset` varchar(30) DEFAULT NULL,
  `opencnam_account_sid` varchar(34) DEFAULT NULL,
  `opencnam_auth_token` varchar(34) DEFAULT NULL,
  PRIMARY KEY (`cidlookup_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cidlookup`
--

LOCK TABLES `cidlookup` WRITE;
/*!40000 ALTER TABLE `cidlookup` DISABLE KEYS */;
INSERT INTO `cidlookup` VALUES (1,'OpenCNAM','opencnam',0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `cidlookup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cidlookup_incoming`
--

DROP TABLE IF EXISTS `cidlookup_incoming`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cidlookup_incoming` (
  `cidlookup_id` int(11) NOT NULL,
  `extension` varchar(50) DEFAULT NULL,
  `cidnum` varchar(30) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cidlookup_incoming`
--

LOCK TABLES `cidlookup_incoming` WRITE;
/*!40000 ALTER TABLE `cidlookup_incoming` DISABLE KEYS */;
/*!40000 ALTER TABLE `cidlookup_incoming` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cronmanager`
--

DROP TABLE IF EXISTS `cronmanager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cronmanager` (
  `module` varchar(24) NOT NULL DEFAULT '',
  `id` varchar(24) NOT NULL DEFAULT '',
  `time` varchar(5) DEFAULT NULL,
  `freq` int(11) NOT NULL DEFAULT '0',
  `lasttime` int(11) NOT NULL DEFAULT '0',
  `command` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`module`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cronmanager`
--

LOCK TABLES `cronmanager` WRITE;
/*!40000 ALTER TABLE `cronmanager` DISABLE KEYS */;
INSERT INTO `cronmanager` VALUES ('module_admin','UPDATES','21',24,0,'/var/lib/asterisk/bin/module_admin listonline');
/*!40000 ALTER TABLE `cronmanager` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_destinations`
--

DROP TABLE IF EXISTS `custom_destinations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_destinations` (
  `custom_dest` varchar(80) NOT NULL DEFAULT '',
  `description` varchar(40) NOT NULL DEFAULT '',
  `notes` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`custom_dest`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_destinations`
--

LOCK TABLES `custom_destinations` WRITE;
/*!40000 ALTER TABLE `custom_destinations` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_destinations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_extensions`
--

DROP TABLE IF EXISTS `custom_extensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_extensions` (
  `custom_exten` varchar(80) NOT NULL DEFAULT '',
  `description` varchar(40) NOT NULL DEFAULT '',
  `notes` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`custom_exten`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_extensions`
--

LOCK TABLES `custom_extensions` WRITE;
/*!40000 ALTER TABLE `custom_extensions` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_extensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customcontexts_contexts`
--

DROP TABLE IF EXISTS `customcontexts_contexts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customcontexts_contexts` (
  `context` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(100) NOT NULL DEFAULT '',
  `dialrules` varchar(1000) DEFAULT NULL,
  `faildestination` varchar(250) DEFAULT NULL,
  `featurefaildestination` varchar(250) DEFAULT NULL,
  `failpin` varchar(100) DEFAULT NULL,
  `failpincdr` tinyint(1) NOT NULL DEFAULT '0',
  `featurefailpin` varchar(100) DEFAULT NULL,
  `featurefailpincdr` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`context`),
  UNIQUE KEY `description` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customcontexts_contexts`
--

LOCK TABLES `customcontexts_contexts` WRITE;
/*!40000 ALTER TABLE `customcontexts_contexts` DISABLE KEYS */;
/*!40000 ALTER TABLE `customcontexts_contexts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customcontexts_contexts_list`
--

DROP TABLE IF EXISTS `customcontexts_contexts_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customcontexts_contexts_list` (
  `context` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(100) NOT NULL DEFAULT '',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`context`),
  UNIQUE KEY `description` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customcontexts_contexts_list`
--

LOCK TABLES `customcontexts_contexts_list` WRITE;
/*!40000 ALTER TABLE `customcontexts_contexts_list` DISABLE KEYS */;
INSERT INTO `customcontexts_contexts_list` VALUES ('from-internal','Default Internal Context',1),('from-internal-additional','Internal Dialplan',0),('outbound-allroutes','Outbound Routes',0);
/*!40000 ALTER TABLE `customcontexts_contexts_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customcontexts_includes`
--

DROP TABLE IF EXISTS `customcontexts_includes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customcontexts_includes` (
  `context` varchar(100) NOT NULL DEFAULT '',
  `include` varchar(100) NOT NULL DEFAULT '',
  `timegroupid` int(11) DEFAULT NULL,
  `sort` int(11) NOT NULL DEFAULT '0',
  `userules` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`context`,`include`),
  KEY `sort` (`sort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customcontexts_includes`
--

LOCK TABLES `customcontexts_includes` WRITE;
/*!40000 ALTER TABLE `customcontexts_includes` DISABLE KEYS */;
/*!40000 ALTER TABLE `customcontexts_includes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customcontexts_includes_list`
--

DROP TABLE IF EXISTS `customcontexts_includes_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customcontexts_includes_list` (
  `context` varchar(100) NOT NULL DEFAULT '',
  `include` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(100) NOT NULL DEFAULT '',
  `missing` tinyint(1) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`context`,`include`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customcontexts_includes_list`
--

LOCK TABLES `customcontexts_includes_list` WRITE;
/*!40000 ALTER TABLE `customcontexts_includes_list` DISABLE KEYS */;
INSERT INTO `customcontexts_includes_list` VALUES ('from-internal','from-internal-additional','ENTIRE Basic Internal Dialplan',0,0),('from-internal','from-internal-custom','Custom Internal Dialplan',0,0),('from-internal','parkedcalls','Call Parking',0,0),('from-internal-additional','app-fax','app fax',0,9),('from-internal-additional','app-cf-toggle','Call Forward toggle',0,12),('from-internal-additional','app-cf-unavailable-prompt-on','CF Unavailable On',0,23),('from-internal-additional','app-cf-unavailable-on','Call Forward Unavailable On',0,22),('from-internal-additional','app-cf-unavailable-off','Call Forward Unavailable Off',0,21),('from-internal-additional','app-cf-prompting-on','CF prompt On',0,20),('from-internal-additional','app-cf-on','Call Forward On',0,19),('from-internal-additional','app-cf-off-any','CF any Off',0,18),('from-internal-additional','app-cf-off','Call Forward Off',0,17),('from-internal-additional','app-cf-busy-prompting-on','CF Busy prompt On',0,16),('from-internal-additional','app-cf-busy-on','Call Forward Busy On',0,15),('from-internal-additional','app-cf-busy-off-any','CF Busy any Off',0,14),('from-internal-additional','app-cf-busy-off','Call Forward Busy Off',0,13),('from-internal-additional','app-vmmain','Voicemail Main',0,26),('from-internal-additional','app-dialvm','Dial VM',0,25),('from-internal-additional','app-callwaiting-cwon','Call Waiting On',0,31),('from-internal-additional','app-callwaiting-cwoff','Call Waiting Off',0,30),('from-internal-additional','app-dictate-send','app dictate send',0,34),('from-internal-additional','app-dictate-record','app dictate record',0,33),('from-internal-additional','grps','grps',0,5),('from-internal-additional','ext-group','Ring Groups',0,4),('from-internal-additional','vmblast-grp','Voicemail Blast',0,10),('from-internal-additional','fmgrps','fmgrps',0,29),('from-internal-additional','ext-findmefollow','Follow Me',0,28),('from-internal-additional','app-fmf-toggle','Follow Me toggle',0,27),('from-internal-additional','ext-queues','Queues',0,44),('from-internal-additional','app-pbdirectory','app pbdirectory',0,8),('from-internal-additional','app-dnd-toggle','DND Toggle',0,42),('from-internal-additional','app-dnd-on','DND On',0,41),('from-internal-additional','app-dnd-off','DND Off',0,40),('from-internal-additional','app-blacklist','app blacklist',0,11),('from-internal-additional','app-recordings','app recordings',0,32),('from-internal-additional','app-speakingclock','app speakingclock',0,39),('from-internal-additional','app-speakextennum','app speakextennum',0,38),('from-internal-additional','app-echo-test','app echo test',0,37),('from-internal-additional','app-calltrace','app calltrace',0,36),('from-internal-additional','park-hints','park hints',0,3),('outbound-allroutes','outrt-1','9_outside',0,101),('from-internal-additional','ext-meetme','Conferences',0,1),('from-internal-additional','app-speeddial','app speeddial',0,7),('from-internal-additional','app-userlogonoff','app user logonoff',0,45),('from-internal-additional','ext-local-confirm','Extensions Confirm',0,46),('from-internal-additional','findmefollow-ringallv2','findmefollow ringallv2',0,47),('from-internal-additional','app-pickup','app pickup',0,48),('from-internal-additional','app-zapbarge','app zapbarge',0,49),('from-internal-additional','app-chanspy','app chanspy',0,50),('from-internal-additional','ext-test','ext-test',0,51),('from-internal-additional','ext-local','Extensions',0,52),('from-internal-additional','outbound-allroutes','ALL OUTBOUND ROUTES',0,53),('from-internal-additional','ext-injections','ext-injections',0,6),('from-internal-additional','ext-cf-hints','ext-cf-hints',0,24),('from-internal-additional','ext-dnd-hints','ext-dnd-hints',0,43),('from-internal-additional','ext-bosssecretary','ext-bosssecretary',0,35);
/*!40000 ALTER TABLE `customcontexts_includes_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customcontexts_module`
--

DROP TABLE IF EXISTS `customcontexts_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customcontexts_module` (
  `id` varchar(50) NOT NULL DEFAULT '',
  `value` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customcontexts_module`
--

LOCK TABLES `customcontexts_module` WRITE;
/*!40000 ALTER TABLE `customcontexts_module` DISABLE KEYS */;
INSERT INTO `customcontexts_module` VALUES ('displaysortforincludes','1'),('moduledisplayname','Custom Contexts'),('modulerawname','customcontexts'),('moduleversion','0.3.2');
/*!40000 ALTER TABLE `customcontexts_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customerdb`
--

DROP TABLE IF EXISTS `customerdb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customerdb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `addr1` varchar(150) NOT NULL,
  `addr2` varchar(150) DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(5) NOT NULL,
  `zip` varchar(12) DEFAULT NULL,
  `sip` varchar(20) DEFAULT NULL,
  `did` varchar(45) DEFAULT NULL,
  `device` varchar(50) DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `serial` varchar(50) DEFAULT NULL,
  `account` varchar(6) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `username` varchar(25) DEFAULT NULL,
  `password` varchar(25) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customerdb`
--

LOCK TABLES `customerdb` WRITE;
/*!40000 ALTER TABLE `customerdb` DISABLE KEYS */;
/*!40000 ALTER TABLE `customerdb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dahdi`
--

DROP TABLE IF EXISTS `dahdi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dahdi` (
  `id` varchar(20) NOT NULL DEFAULT '-1',
  `keyword` varchar(30) NOT NULL DEFAULT '',
  `data` varchar(255) NOT NULL DEFAULT '',
  `flags` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dahdi`
--

LOCK TABLES `dahdi` WRITE;
/*!40000 ALTER TABLE `dahdi` DISABLE KEYS */;
/*!40000 ALTER TABLE `dahdi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dahdichandids`
--

DROP TABLE IF EXISTS `dahdichandids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dahdichandids` (
  `channel` int(11) NOT NULL DEFAULT '0',
  `description` varchar(40) NOT NULL DEFAULT '',
  `did` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`channel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dahdichandids`
--

LOCK TABLES `dahdichandids` WRITE;
/*!40000 ALTER TABLE `dahdichandids` DISABLE KEYS */;
/*!40000 ALTER TABLE `dahdichandids` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daynight`
--

DROP TABLE IF EXISTS `daynight`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daynight` (
  `ext` varchar(10) NOT NULL DEFAULT '',
  `dmode` varchar(40) NOT NULL DEFAULT '',
  `dest` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ext`,`dmode`,`dest`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daynight`
--

LOCK TABLES `daynight` WRITE;
/*!40000 ALTER TABLE `daynight` DISABLE KEYS */;
/*!40000 ALTER TABLE `daynight` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devices` (
  `id` varchar(20) NOT NULL DEFAULT '',
  `tech` varchar(10) NOT NULL DEFAULT '',
  `dial` varchar(50) NOT NULL DEFAULT '',
  `devicetype` varchar(5) NOT NULL DEFAULT '',
  `user` varchar(50) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `emergency_cid` varchar(100) DEFAULT NULL,
  PRIMARY KEY `id` (`id`),
  KEY `tech` (`tech`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devices`
--

LOCK TABLES `devices` WRITE;
/*!40000 ALTER TABLE `devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dialplaninjection_commands`
--

DROP TABLE IF EXISTS `dialplaninjection_commands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dialplaninjection_commands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `injectionid` int(11) NOT NULL DEFAULT '0',
  `command` text NOT NULL,
  `sort` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dialplaninjection_commands`
--

LOCK TABLES `dialplaninjection_commands` WRITE;
/*!40000 ALTER TABLE `dialplaninjection_commands` DISABLE KEYS */;
/*!40000 ALTER TABLE `dialplaninjection_commands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dialplaninjection_commands_list`
--

DROP TABLE IF EXISTS `dialplaninjection_commands_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dialplaninjection_commands_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT '',
  `command` text NOT NULL,
  `info` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `description` (`description`)
) ENGINE=MyISAM AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dialplaninjection_commands_list`
--

LOCK TABLES `dialplaninjection_commands_list` WRITE;
/*!40000 ALTER TABLE `dialplaninjection_commands_list` DISABLE KEYS */;
INSERT INTO `dialplaninjection_commands_list` VALUES (1,'AbsoluteTimeout','AbsoluteTimeout(seconds)','Set absolute maximum time of call','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+AbsoluteTimeout'),(2,'AddQueueMember','AddQueueMember(queuename[|interface][|penalty])','Dynamically adds queue members','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+AddQueueMember'),(3,'ADSIProg','ADSIProg(script)','Load Asterisk ADSI Scripts into phone','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ADSIProg'),(4,'AgentCallbackLogin','AgentCallbackLogin([AgentNo|][Options|][exten]@context)','Call agent callback login','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+AgentCallbackLogin'),(5,'AgentLogin','AgentLogin(AgentNo[|options])','Call agent login','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+AgentLogin'),(6,'AgentMonitorOutgoing','AgentMonitorOutgoing(options)','Record agent\'s outgoing call','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+AgentMonitorOutgoing'),(7,'AGI','AGI(command|args)','Executes an AGI compliant application','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+AGI'),(8,'AlarmReceiver','AlarmReceiver','Emulate an Ademco Contact ID Alarm Receiver','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+AlarmReceiver'),(9,'ALSAMonitor','ALSAMonitor(password)','Monitor the ALSA console','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ALSAMonitor'),(10,'Answer','Answer([delay])','Answer a channel if ringing','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Answer'),(11,'AppendCDRUserField','AppendCDRUserField(Value)','Append data to CDR User field','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+AppendCDRUserField'),(12,'Authenticate','Authenticate(password[|options]])','Authenticate a user','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Authenticate'),(13,'Background','Background(filename1[&filename2...][|options[|langoverride][|context]])','Play a sound file while executing other commands','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+BackGround'),(14,'BackgroundDetect','BackgroundDetect(filename[|sil[|min|max)','Background a file with talk detect','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+BackGroundDetect'),(15,'Busy','Busy()','Indicate busy condition and wait for hangup','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Busy'),(16,'CALLERID (Set)','Set(CALLERID(all)=John Doe<8883211234>)','Set CallerID.',''),(17,'CALLERID (SetName)','Set(CALLERID(name)=John Doe)','Set CallerID Name.',''),(18,'CALLERID (SetNum)','Set(CALLERID(number)=8883211234)','Set only the Caller ID number (not name).',''),(19,'ChangeMonitor','ChangeMonitor(basename)','Change monitoring filename of a channel','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ChangeMonitor'),(20,'ChanIsAvail','ChanIsAvail (Technology/resource[&Technology2/resource2...][|options])','Check if channel is available','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ChanIsAvail'),(21,'ChanSpy','Chanspy([<scanspec>][|<options>])','Universal channel barge-in','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ChanSpy'),(22,'Congestion','Congestion()','Indicate congestion and wait for hangup','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Congestion'),(23,'ControlPlayback','ControlPlayback(filename,skip,forward,rewind,stop,pause,restart)','Play a sound file with fast forward, rewind and exit controls','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ControlPlayback'),(24,'CURL','CURL(url[|post-data])','Allows for the retrieval of external URLs. Also supports POSTing.',''),(25,'DB (Set)','Set(DB(family/key)=${foo})','Store a value in the database.',''),(26,'DB (Get)','Set(foo=${DB(family/key)})','Retrieve a value from the database.',''),(27,'DBdel','DBdel(family/key)','Delete a key from the database.','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+DBdel'),(28,'DBdeltree','DBdeltree(family/keytree)','Delete a family or keytree from the database.','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+DBdeltree'),(29,'DeadAGI','DeadAGI(command|args)','Executes AGI on a hung-up channel','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+DeadAGI'),(30,'Dial','Dial(type/identifier,timeout,options,URL)','Place a call and connect to the current channel','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Dial'),(31,'Dictate','Dictate([path])','Records and plays back a dictation','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Dictate'),(32,'Directory','Directory(vm-context[|dial-context[|options]])','Provide directory of voicemail extensions','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Directory'),(33,'DISA','DISA(passcode[|context])','DISA (Direct Inward System Access)','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+DISA'),(34,'DUNDILOOKUP','DUNDILOOKUP(number[|context[|options]])','Look up a number with DUNDi','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+DISA'),(35,'EAGI','EAGI(command|args)','Executes an AGI compliant application',''),(36,'Echo','Echo()','Echo audio read back to the user','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Echo'),(37,'EndWhile','EndWhile()','End A While Loop - *1.2beta','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+EndWhile'),(38,'EnumLookup','ENUMLOOKUP(number[,Method-type[,options|record#[,zone-suffix]]])','Lookup number in ENUM','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+EnumLookup'),(39,'ExecIf','ExecIF(<expr>|<app>|<data>)','Conditional exec - *1.2beta','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ExecIf'),(40,'Festival','Festival(text,intkeys)','Say text with the Festival voice synthesizer','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Festival'),(41,'Flash','Flash()','Flashes a Zap Trunk','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Flash'),(42,'Flite','Flite(text,intkeys)','Say text with the Festival Lite voice synthesizer (faster response than Festival)','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Festival'),(43,'ForkCDR','ForkCDR()','Fork The CDR into 2 seperate entities','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ForkCDR'),(44,'Gosub','Gosub([[context|]extension|]priority)','Jump to a subroutine and return (new in v1.2)','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Gosub'),(45,'GosubIf','GosubIf(condition?label1:label2)','Conditional jump to a subroutine and return (new in v1.2)','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+GosubIf'),(46,'Goto','Goto([[context|]extension|]priority)','Goto a particular priority, extension, or context','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Goto'),(47,'GotoIf','GotoIf(condition?label1[:label2])','Conditional goto','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+GotoIf'),(48,'GotoIfTime','GotoIfTime(<time range>|<days of week>|<days of month>|<months>?[[context|]extension|]pri)','Conditional goto on current time','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+GotoIfTime'),(49,'Hangup','Hangup()','Unconditional hangup','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Hangup'),(50,'HasNewVoicemail','HasNewVoicemail(vmbox[@context][:folder][|varname])','Conditionally branches to priority + 101','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+HasNewVoicemail'),(51,'ImportVar','ImportVar(newvar=channelname|variable)','Set variable to value','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ImportVar'),(52,'LookupBlacklist','LookupBlacklist','Look up Caller*ID name/number from blacklist database','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+LookupBlacklist'),(53,'LookupCIDName','LookupCIDName','Look up CallerID Name from local database','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+LookupCIDName'),(54,'Macro','Macro(macroname,arg1,arg2...)','Macro Implementation','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Macro'),(55,'MailboxExists','MailboxExists(mailbox[@context])','Checks if mailbox exists','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+MailboxExists'),(56,'MATH','MATH(<number1><op><number 2>[,<type_of_result>])','Perform (rather simple) calculations.',''),(57,'MeetMe','MeetMe([confno][,[options][,pin]])','Simple MeetMe conference bridge','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+MeetMe'),(58,'MeetMeAdmin','MeetMeAdmin(confno,command[,user])','MeetMe conference Administration','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+MeetMeAdmin'),(59,'MeetMeCount','MeetMeCount(confno[|var])','MeetMe participant count','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+MeetMeCount'),(60,'Milliwatt','Milliwatt()','Generate a Constant 1000Hz tone at 0dbm (mu-law)','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Milliwatt'),(61,'MixMonitor','MixMonitor(<file>.<ext>[|<options>[|<command>]])','Record and mix call legs natively (unlike Monitor) v1.2.x','http://www.voip-info.org/wiki/view/MixMonitor'),(62,'Monitor','Monitor(ext,basename,flags)','Record a telephone conversation to a sound file','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Monitor'),(63,'MP3Player','MP3Player(location)','Play an MP3 sound file or stream','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+MP3Player'),(64,'MusicOnHold','MusicOnHold(class)','Play Music On Hold indefinitely','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+MusicOnHold'),(65,'MYSQL_Connect','MYSQL(Connect connid dhhost dbuser dbpass dbname)','Perform various mySQL database activities','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+MYSQL'),(66,'MYSQL_Query','MYSQL(Query resultid ${connid} query-string)','Perform various mySQL database activities','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+MYSQL'),(67,'MYSQL_Fetch','MYSQL(Fetch fetchid ${resultid} var1 var2 ... varN)','Perform various mySQL database activities','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+MYSQL'),(68,'MYSQL_Clear','MYSQL(Clear ${resultid})','Perform various mySQL database activities','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+MYSQL'),(69,'MYSQL_Disconnect','MYSQL(Disconnect ${connid})','Perform various mySQL database activities','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+MYSQL'),(70,'NoCDR','NoCDR()','Make sure asterisk doesn\'t save CDR for a certain call','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+NoCDR'),(71,'NoOp','NoOp()','No operation. Can print values to console for debugging.','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+NoOp'),(72,'Page','Page(Technology/Resource&Tech2/Res2...[|options])','Page a mobile device (new in Asterisk v1.2)','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Page'),(73,'ParkAndAnnounce','ParkAndAnnounce(announce:template|timeout|dial|return_context)','Park and Announce','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ParkAndAnnounce'),(74,'ParkedCall','ParkedCall(exten)','Answer a parked call','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ParkedCall'),(75,'PauseQueueMemeber','PauseQueueMember(queuename|agent)','Pauses an agent','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+PauseQueueMember'),(76,'Playback','Playback(filename,options...)','Play a sound file','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Playback'),(77,'Playtones','Playtones(tonename)','Play a tone list while executing other commands','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Playtones'),(78,'PrivacyManager','PrivacyManager','Require phone number to be entered, if no CallerID sent',''),(79,'Progress','Progress()','Play early audio to the caller before answering the line','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Progress'),(80,'Queue','Queue(queuename|options|optionalurl|announceoverride|timeout)','Queue a call for a call queue','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Queue'),(81,'Random','Random(probability:label)','Make a random jump in your dial plan','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Random'),(82,'Read','Read(variable[|filename][|maxdigits][|option][|attempts][|timeout])','Read a variable with DTMF','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Read'),(83,'Record','Record(filename.format[|silence][|maxduration][|option])','Record user voice input to a file','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Record'),(84,'RemoveQueueMember','RemoveQueueMember(queuename[|interface])','Dynamically removes queue members','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+RemoveQueueMember'),(85,'ResetCDR','ResetCDR([options])','Reset CDR data','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ResetCDR'),(86,'ResponseTimeout','ResponseTimeout(seconds)','Set maximum timeout awaiting response','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ResponseTimeout'),(87,'RetryDial','RetryDial(announce|sleep|loops|Technology/resource[&Technology2/resource2...[|timeout[|options[|URL]]]])','Place a call, retrying on failure allowing optional exit extension.','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+RetryDial'),(88,'Return','Return()','Return from a Gosub or GosubIf (new in v1.2)','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Return'),(89,'Ringing','Ringing()','Indicate ringing','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Ringing'),(90,'SayAlpha','SayAlpha(string)','Say Alpha','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SayAlpha'),(91,'SayDigits','SayDigits(digits)','Say Digits','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SayDigits'),(92,'SayNumber','SayNumber(number, gender)','Say Number','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SayNumber'),(93,'SayPhonetic','SayPhonetic(text)','Say Phonetic','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SayPhonetic'),(94,'SayUnixTime','SayUnixTime(unixtime,timezone,format)','Say the date and/or time','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SayUnixTime'),(95,'SendDTMF','SendDTMF(digits[|timeout_ms])','Sends arbitrary DTMF digits',''),(96,'SendText','SendText(text)','Send client a text message','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SendText'),(97,'SendURL','SendURL(URL[|option])','Send a client a URL to display',''),(98,'Set','Set(variablename=value[|variable2=value2][|options])','Set channel variable(s) or function value(s)','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Set'),(99,'SetAccount','Set(CDR(accountcode)=account)','Sets account code','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SetAccount'),(100,'SetAMAflags','SetAMAFlags(flags)','Set the channel AMA Flags for billing','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SetAMAflags'),(101,'SetCallerPres','SetCallerPres(presentation)','Channel independent setting of caller presenation','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SetCallerPres'),(102,'SetCDRUserField','Set(CDR(userfield)=Value)','Set CDR User field','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SetCDRUserField'),(103,'SetMusicOnHold','SetMusicOnHold(class)','Set default Music On Hold class','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SetMusicOnHold'),(104,'SIPAddHeader','SIPaddheader()','Add header to outbound SIP invite','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SIPAddHeader'),(105,'SIPdtmfMode','SIPDtmfMode(inband|info|rfc2833)','Change DTMF mode during SIP call','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SIPdtmfmode'),(106,'SoftHangup','SoftHangup(Technology/resource[|a])','Request hangup on another channel','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+SoftHangup'),(107,'StackPop','StackPop()','Remove a return address without returning (new in v1.2)','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+StackPop'),(108,'StopMonitor','StopMonitor','Stop monitoring a channel','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+StopMonitor'),(109,'StopPlaytones','StopPlaytones','Stop playing a tone list','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+StopPlaytones'),(110,'System','System(command)','Execute a system command','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+System'),(111,'Transfer','Transfer([Tech/]dest[|options])','Transfer caller to remote extension','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Transfer'),(112,'TrySystem','TrySystem(command)','Execute a system command with always 0 returned','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+TrySystem'),(113,'TXTCIDName','Set(foo=${TXTCIDNAME(<number>)})','Lookup caller name from TXT record','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+TXTCIDName'),(114,'UnpauseQueueMemeber','UnPauseQueueMember(queuename|agent)','Resumes an agent','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+UnpauseQueueMember'),(115,'UserEvent','UserEvent(eventname[|body])','Send an arbitrary event to the manager interface','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+UserEvent'),(116,'VMAuthenticate','VMAuthenticate([mailbox][@context][|options])','Authenticate a user based on voicemail.conf','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+VMAuthenticate'),(117,'VoiceMail','VoiceMail([flags]boxnumber[@context][&boxnumber2[@context]][&boxnumber3])','Leave a voicemail message','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+VoiceMail'),(118,'VoiceMailMain','VoiceMailMain([[s]mailbox]@context)','Enter voicemail system','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+VoiceMailMain'),(119,'Wait','Wait(seconds)','Waits for some time','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Wait'),(120,'WaitExten','WaitExten(seconds)','Waits for some time','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+WaitExten'),(121,'WaitForRing','WaitForRing(timeout)','Wait for Ring Application','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+WaitForRing'),(122,'WaitMusicOnHold','WaitMusicOnHold(delay)','Wait, playing Music On Hold','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+WaitMusicOnHold'),(123,'While','While(condition)','Start A While Loop - *1.2beta','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+While'),(124,'Zapateller','Zapateller(options)','Block telemarketers with SIT','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+Zapateller'),(125,'ZapBarge','ZapBarge(channel)','Barge in (monitor) Zap channel','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ZapBarge'),(126,'ZapScan','ZapScan','Scan Zap channels to monitor calls','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ZapScan'),(127,'ZapSendKeypadFacility','ZapSendKeypadFacility(digits)','Send digits out of band over a PRI','http://www.voip-info.org/wiki/index.php?page=Asterisk+cmd+ZapSendKeypadFacility');
/*!40000 ALTER TABLE `dialplaninjection_commands_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dialplaninjection_dialplaninjections`
--

DROP TABLE IF EXISTS `dialplaninjection_dialplaninjections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dialplaninjection_dialplaninjections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT '',
  `destination` varchar(250) NOT NULL DEFAULT '',
  `exten` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `description` (`description`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dialplaninjection_dialplaninjections`
--

LOCK TABLES `dialplaninjection_dialplaninjections` WRITE;
/*!40000 ALTER TABLE `dialplaninjection_dialplaninjections` DISABLE KEYS */;
/*!40000 ALTER TABLE `dialplaninjection_dialplaninjections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dialplaninjection_module`
--

DROP TABLE IF EXISTS `dialplaninjection_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dialplaninjection_module` (
  `id` varchar(50) NOT NULL DEFAULT '',
  `value` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dialplaninjection_module`
--

LOCK TABLES `dialplaninjection_module` WRITE;
/*!40000 ALTER TABLE `dialplaninjection_module` DISABLE KEYS */;
INSERT INTO `dialplaninjection_module` VALUES ('modulerawname','dialplaninjection'),('moduledisplayname','Dialplan Injection'),('moduleversion','0.1.1');
/*!40000 ALTER TABLE `dialplaninjection_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disa`
--

DROP TABLE IF EXISTS `disa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `disa` (
  `disa_id` int(11) NOT NULL AUTO_INCREMENT,
  `displayname` varchar(50) DEFAULT NULL,
  `pin` varchar(50) DEFAULT NULL,
  `cid` varchar(50) DEFAULT NULL,
  `context` varchar(50) DEFAULT NULL,
  `digittimeout` int(11) DEFAULT NULL,
  `resptimeout` int(11) DEFAULT NULL,
  `needconf` varchar(10) DEFAULT NULL,
  `hangup` varchar(10) DEFAULT NULL,
  `keepcid` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`disa_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disa`
--

LOCK TABLES `disa` WRITE;
/*!40000 ALTER TABLE `disa` DISABLE KEYS */;
/*!40000 ALTER TABLE `disa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dynamicfeatures`
--

DROP TABLE IF EXISTS `dynamicfeatures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynamicfeatures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `dtmf` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `activate_on` enum('self','peer') COLLATE utf8mb4_unicode_ci DEFAULT 'peer',
  `application` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `arguments` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `moh_class` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dynamicfeatures`
--

LOCK TABLES `dynamicfeatures` WRITE;
/*!40000 ALTER TABLE `dynamicfeatures` DISABLE KEYS */;
/*!40000 ALTER TABLE `dynamicfeatures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dynroute`
--

DROP TABLE IF EXISTS `dynroute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynroute` (
  `dynroute_id` int(11) NOT NULL AUTO_INCREMENT,
  `displayname` varchar(50) NOT NULL,
  `sourcetype` varchar(100) DEFAULT NULL,
  `mysql_host` varchar(60) DEFAULT NULL,
  `mysql_dbname` varchar(60) DEFAULT NULL,
  `mysql_query` text,
  `mysql_username` varchar(30) DEFAULT NULL,
  `mysql_password` varchar(30) DEFAULT NULL,
  `odbc_func` varchar(100) DEFAULT NULL,
  `odbc_query` text,
  `url_query` text,
  `agi_query` text,
  `agi_var_name_res` varchar(255) DEFAULT NULL,
  `astvar_query` text,
  `enable_dtmf_input` varchar(8) DEFAULT NULL,
  `timeout` int(11) DEFAULT NULL,
  `announcement_id` int(11) DEFAULT NULL,
  `chan_var_name` varchar(255) DEFAULT NULL,
  `chan_var_name_res` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`dynroute_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dynroute`
--

LOCK TABLES `dynroute` WRITE;
/*!40000 ALTER TABLE `dynroute` DISABLE KEYS */;
INSERT INTO `dynroute` VALUES (1,'__install_done',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `dynroute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dynroute_dests`
--

DROP TABLE IF EXISTS `dynroute_dests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dynroute_dests` (
  `dynroute_id` int(11) NOT NULL,
  `selection` varchar(255) DEFAULT NULL,
  `default_dest` char(1) DEFAULT 'n',
  `dest` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dynroute_dests`
--

LOCK TABLES `dynroute_dests` WRITE;
/*!40000 ALTER TABLE `dynroute_dests` DISABLE KEYS */;
/*!40000 ALTER TABLE `dynroute_dests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extensions`
--

DROP TABLE IF EXISTS `extensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extensions` (
  `context` varchar(45) NOT NULL DEFAULT 'default',
  `extension` varchar(45) NOT NULL DEFAULT '',
  `priority` varchar(5) NOT NULL DEFAULT '1',
  `application` varchar(45) NOT NULL DEFAULT '',
  `args` varchar(255) DEFAULT NULL,
  `descr` text,
  `flags` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`context`,`extension`,`priority`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extensions`
--

LOCK TABLES `extensions` WRITE;
/*!40000 ALTER TABLE `extensions` DISABLE KEYS */;
INSERT INTO `extensions` VALUES ('outrt-001-9_outside','_9.','1','Macro','dialout-trunk,1,${EXTEN:1}',NULL,0),('outrt-001-9_outside','_9.','2','Macro','outisbusy','No available circuits',0),('outbound-allroutes','include','1','outrt-001-9_outside','','',2);
/*!40000 ALTER TABLE `extensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fax_details`
--

DROP TABLE IF EXISTS `fax_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fax_details` (
  `key` varchar(50) DEFAULT NULL,
  `value` varchar(510) DEFAULT NULL,
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fax_details`
--

LOCK TABLES `fax_details` WRITE;
/*!40000 ALTER TABLE `fax_details` DISABLE KEYS */;
INSERT INTO `fax_details` VALUES ('minrate','14400'),('maxrate','14400'),('ecm','yes'),('legacy_mode','no'),('force_detection','no'),('sender_address','fax@issabel.org'),('fax_rx_email','fax@mydomain.com');
/*!40000 ALTER TABLE `fax_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fax_incoming`
--

DROP TABLE IF EXISTS `fax_incoming`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fax_incoming` (
  `cidnum` varchar(20) DEFAULT NULL,
  `extension` varchar(50) DEFAULT NULL,
  `detection` varchar(20) DEFAULT NULL,
  `detectionwait` varchar(5) DEFAULT NULL,
  `destination` varchar(50) DEFAULT NULL,
  `legacy_email` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fax_incoming`
--

LOCK TABLES `fax_incoming` WRITE;
/*!40000 ALTER TABLE `fax_incoming` DISABLE KEYS */;
/*!40000 ALTER TABLE `fax_incoming` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fax_users`
--

DROP TABLE IF EXISTS `fax_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fax_users` (
  `user` varchar(15) DEFAULT NULL,
  `faxenabled` varchar(10) DEFAULT NULL,
  `faxemail` text,
  `faxattachformat` varchar(10) DEFAULT NULL,
  UNIQUE KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fax_users`
--

LOCK TABLES `fax_users` WRITE;
/*!40000 ALTER TABLE `fax_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `fax_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `featurecodes`
--

DROP TABLE IF EXISTS `featurecodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `featurecodes` (
  `modulename` varchar(50) NOT NULL DEFAULT '',
  `featurename` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(200) NOT NULL DEFAULT '',
  `defaultcode` varchar(20) DEFAULT NULL,
  `customcode` varchar(20) DEFAULT NULL,
  `enabled` tinyint(4) NOT NULL DEFAULT '0',
  `providedest` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`modulename`,`featurename`),
  KEY `enabled` (`enabled`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `featurecodes`
--

LOCK TABLES `featurecodes` WRITE;
/*!40000 ALTER TABLE `featurecodes` DISABLE KEYS */;
INSERT INTO `featurecodes` VALUES ('core','userlogon','User Logon','*11',NULL,1,0),('core','userlogoff','User Logoff','*12',NULL,1,0),('core','zapbarge','ZapBarge','888',NULL,1,1),('core','simu_pstn','Simulate Incoming Call','7777',NULL,1,1),('fax','simu_fax','Dial System FAX','666',NULL,1,1),('core','chanspy','ChanSpy','555',NULL,1,1),('core','pickup','Directed Call Pickup','**',NULL,1,0),('core','pickupexten','Asterisk General Call Pickup','*8',NULL,1,0),('core','blindxfer','In-Call Asterisk Blind Transfer','##',NULL,1,0),('core','atxfer','In-Call Asterisk Attended Transfer','*2',NULL,1,0),('core','automon','In-Call Asterisk Toggle Call Recording','*1',NULL,1,0),('core','disconnect','In-Call Asterisk Disconnect Code','**',NULL,1,0),('queues','que_pause_toggle','Queue Pause Toggle','*46',NULL,1,0),('infoservices','calltrace','Call Trace','*69',NULL,1,0),('infoservices','echotest','Echo Test','*43',NULL,1,1),('infoservices','speakingclock','Speaking Clock','*60',NULL,1,1),('infoservices','speakextennum','Speak Your Exten Number','*65',NULL,1,0),('voicemail','myvoicemail','My Voicemail','*97',NULL,1,0),('voicemail','dialvoicemail','Dial Voicemail','*98',NULL,1,1),('recordings','record_save','Save Recording','*77',NULL,1,0),('recordings','record_check','Check Recording','*99',NULL,1,0),('callforward','cfon','Call Forward All Activate','*72',NULL,1,0),('callforward','cfoff','Call Forward All Deactivate','*73',NULL,1,0),('callforward','cfoff_any','Call Forward All Prompting Deactivate','*74',NULL,1,0),('callforward','cfbon','Call Forward Busy Activate','*90',NULL,1,0),('callforward','cfboff','Call Forward Busy Deactivate','*91',NULL,1,0),('callforward','cfboff_any','Call Forward Busy Prompting Deactivate','*92',NULL,1,0),('callforward','cfuon','Call Forward No Answer/Unavailable Activate','*52',NULL,1,0),('callforward','cfuoff','Call Forward No Answer/Unavailable Deactivate','*53',NULL,1,0),('callwaiting','cwon','Call Waiting - Activate','*70',NULL,1,0),('callwaiting','cwoff','Call Waiting - Deactivate','*71',NULL,1,0),('dictate','dodictate','Perform dictation','*34',NULL,1,0),('dictate','senddictate','Email completed dictation','*35',NULL,1,0),('donotdisturb','dnd_on','DND Activate','*78',NULL,1,0),('donotdisturb','dnd_off','DND Deactivate','*79',NULL,1,0),('donotdisturb','dnd_toggle','DND Toggle','*76',NULL,1,0),('findmefollow','fmf_toggle','Findme Follow Toggle','*21',NULL,1,0),('paging','intercom-prefix','Intercom prefix','*80',NULL,0,0),('paging','intercom-on','User Intercom Allow','*54',NULL,0,0),('paging','intercom-off','User Intercom Disallow','*55',NULL,0,0),('pbdirectory','app-pbdirectory','Phonebook dial-by-name directory','411',NULL,1,1),('blacklist','blacklist_add','Blacklist a number','*30',NULL,1,1),('blacklist','blacklist_remove','Remove a number from the blacklist','*31',NULL,1,1),('blacklist','blacklist_last','Blacklist the last caller','*32',NULL,1,0),('speeddial','callspeeddial','Speeddial prefix','*0',NULL,1,0),('speeddial','setspeeddial','Set user speed dial','*75',NULL,1,0),('queues','que_toggle','Queue Toggle','*45',NULL,1,0),('callforward','cf_toggle','Call Forward Toggle','*740',NULL,1,0),('parking','parkedcall','Pickup ParkedCall Prefix','*85',NULL,1,1),('voicemail','directdialvoicemail','Direct Dial Prefix','*',NULL,1,0),('callforward','cfpon','Call Forward All Prompting Activate','*720',NULL,1,0),('callforward','cfbpon','Call Forward Busy Prompting Activate','*900',NULL,1,0),('callforward','cfupon','Call Forward No Answer/Unavailable Prompting Activate','*520',NULL,1,0),('conferences','conf_status','Conference Status','*87',NULL,1,0),('daynight','toggle-mode-all','All: Call Flow Toggle','*28',NULL,1,0),('queues','que_callers','Queue Callers','*47',NULL,1,0),('timeconditions','toggle-mode-all','All: Time Condition Override','*27',NULL,1,0),('bosssecretary','bsc_toggle','Bosssecretary Toggle','*152',NULL,1,0),('bosssecretary','bsc_on','Bosssecretary On','*153',NULL,1,0),('bosssecretary','bsc_off','Bosssecretary Off','*154',NULL,1,0);
/*!40000 ALTER TABLE `featurecodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `findmefollow`
--

DROP TABLE IF EXISTS `findmefollow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `findmefollow` (
  `grpnum` varchar(20) NOT NULL,
  `strategy` varchar(50) NOT NULL,
  `grptime` smallint(6) NOT NULL,
  `grppre` varchar(100) DEFAULT NULL,
  `grplist` varchar(255) NOT NULL,
  `annmsg_id` int(11) DEFAULT NULL,
  `postdest` varchar(255) DEFAULT NULL,
  `dring` varchar(255) DEFAULT NULL,
  `remotealert_id` int(11) DEFAULT NULL,
  `needsconf` varchar(10) DEFAULT NULL,
  `toolate_id` int(11) DEFAULT NULL,
  `pre_ring` smallint(6) NOT NULL DEFAULT '0',
  `ringing` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`grpnum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `findmefollow`
--

LOCK TABLES `findmefollow` WRITE;
/*!40000 ALTER TABLE `findmefollow` DISABLE KEYS */;
/*!40000 ALTER TABLE `findmefollow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `globals`
--

DROP TABLE IF EXISTS `globals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `globals` (
  `variable` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`variable`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `globals`
--

LOCK TABLES `globals` WRITE;
/*!40000 ALTER TABLE `globals` DISABLE KEYS */;
INSERT INTO `globals` VALUES ('FAX_RX','system'),('FAX_RX_EMAIL','fax@mydomain.com'),('FAX_RX_FROM','fax@issabel.org');
/*!40000 ALTER TABLE `globals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iax`
--

DROP TABLE IF EXISTS `iax`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `iax` (
  `id` varchar(20) NOT NULL DEFAULT '-1',
  `keyword` varchar(30) NOT NULL DEFAULT '',
  `data` varchar(255) NOT NULL,
  `flags` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iax`
--

LOCK TABLES `iax` WRITE;
/*!40000 ALTER TABLE `iax` DISABLE KEYS */;
/*!40000 ALTER TABLE `iax` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `iaxsettings`
--

DROP TABLE IF EXISTS `iaxsettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `iaxsettings` (
  `keyword` varchar(50) NOT NULL DEFAULT '',
  `data` varchar(255) NOT NULL DEFAULT '',
  `seq` tinyint(1) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`keyword`,`seq`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iaxsettings`
--

LOCK TABLES `iaxsettings` WRITE;
/*!40000 ALTER TABLE `iaxsettings` DISABLE KEYS */;
INSERT INTO `iaxsettings` VALUES ('ulaw','1',0,1),('alaw','1',1,1),('slin','',2,1),('g726','',3,1),('gsm','1',4,1),('g729','',5,1),('ilbc','',6,1),('g723','',7,1),('g726aal2','',8,1),('adpcm','',9,1),('lpc10','',10,1),('speex','',11,1),('g722','',12,1);
/*!40000 ALTER TABLE `iaxsettings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incoming`
--

DROP TABLE IF EXISTS `incoming`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incoming` (
  `cidnum` varchar(20) DEFAULT NULL,
  `extension` varchar(50) NOT NULL,
  `destination` varchar(50) DEFAULT NULL,
  `faxexten` varchar(20) DEFAULT NULL,
  `faxemail` varchar(50) DEFAULT NULL,
  `answer` tinyint(1) DEFAULT NULL,
  `wait` int(2) DEFAULT NULL,
  `privacyman` tinyint(1) DEFAULT NULL,
  `alertinfo` varchar(255) DEFAULT NULL,
  `ringing` varchar(20) DEFAULT NULL,
  `mohclass` varchar(80) NOT NULL DEFAULT 'default',
  `description` varchar(80) DEFAULT NULL,
  `grppre` varchar(80) DEFAULT NULL,
  `delay_answer` int(2) DEFAULT NULL,
  `pricid` varchar(20) DEFAULT NULL,
  `pmmaxretries` varchar(2) DEFAULT NULL,
  `pmminlength` varchar(2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incoming`
--

LOCK TABLES `incoming` WRITE;
/*!40000 ALTER TABLE `incoming` DISABLE KEYS */;
/*!40000 ALTER TABLE `incoming` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `indications_zonelist`
--

DROP TABLE IF EXISTS `indications_zonelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `indications_zonelist` (
  `name` varchar(80) NOT NULL,
  `iso` varchar(20) NOT NULL,
  `conf` blob,
  PRIMARY KEY (`iso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indications_zonelist`
--

LOCK TABLES `indications_zonelist` WRITE;
/*!40000 ALTER TABLE `indications_zonelist` DISABLE KEYS */;
INSERT INTO `indications_zonelist` VALUES ('Angola','ao','ringcadence = 1000,5000\nbusy = 425/500,0/500\ncongestion = 500/500,0500\ndial = 425\nringing = 25/1000,0/5000\ncallwaiting = 400/1000,0/5000\n'),('Argentina','ar','ringcadence = 1000,4500\ndial = 425\nbusy = 425/300,0/300\nring = 425/1000,0/4500\ncongestion = 425/200,0/300\ncallwaiting = 425/200,0/9000\ndialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425/330,0/330,425/660,0/660\nrecord = 1400/500,0/14000\ninfo = 425/100,0/100\nstutter = 425/450,0/50\n'),('Australia','au','ringcadence = 400,200,400,2000\ndial = 413+438\nbusy = 425/375,0/375\nring = 413+438/400,0/200,413+438/400,0/2000\ncongestion = 425/375,0/375,420/375,0/375\ncallwaiting = 425/200,0/200,425/200,0/4400\ndialrecall = 413+438\nrecord = !425/1000,!0/15000,425/360,0/15000\ninfo = 425/2500,0/500\nstd = !525/100,!0/100,!525/100,!0/100,!525/100,!0/100,!525/100,!0/100,!525/100\nfacility = 425\nstutter = 413+438/100,0/40\nringmobile = 400+450/400,0/200,400+450/400,0/2000\n'),('Austria','at','ringcadence = 1000,5000\ndial = 420\nbusy = 420/400,0/400\nring = 420/1000,0/5000\ncongestion = 420/200,0/200\ncallwaiting = 420/40,0/1960\ndialrecall = 420\nrecord = 1400/80,0/14920\ninfo = 950/330,1450/330,1850/330,0/1000\nstutter = 380+420\n'),('Belgium','be','ringcadence = 1000,3000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/3000\ncongestion = 425/167,0/167\ncallwaiting = 1400/175,0/175,1400/175,0/3500\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = 900/330,1400/330,1800/330,0/1000\nstutter = 425/1000,0/250\n'),('Brazil','br','ringcadence = 1000,4000\ndial = 425\nbusy = 425/250,0/250\nring = 425/1000,0/4000\ncongestion = 425/250,0/250,425/750,0/250\ncallwaiting = 425/50,0/1000\ndialrecall = 350+440\nrecord = 425/250,0/250\ninfo = 950/330,1400/330,1800/330\nstutter = 350+440\n'),('Bulgaria','bg','ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/250,0/250\ncallwaiting = 425/150,0/150,425/150,0/4000\ndialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\nrecord = 1400/425,0/15000\ninfo = 950/330,1400/330,1800/330,0/1000\nstutter = 425/1500,0/100\n'),('Chile','cl','ringcadence = 1000,3000\ndial = 400\nbusy = 400/500,0/500\nring = 400/1000,0/3000\ncongestion = 400/200,0/200\ncallwaiting = 400/250,0/8750\ndialrecall = !400/100,!0/100,!400/100,!0/100,!400/100,!0/100,400\nrecord = 1400/500,0/15000\ninfo = 950/333,1400/333,1800/333,0/1000\nstutter = !400/100,!0/100,!400/100,!0/100,!400/100,!0/100,!400/100,!0/100,!400/100,!0/100,!400/100,!0/100,400\n'),('China','cn','ringcadence = 1000,4000\ndial = 450\nbusy = 450/350,0/350\nring = 450/1000,0/4000\ncongestion = 450/700,0/700\ncallwaiting = 450/400,0/4000\ndialrecall = 450\nrecord = 950/400,0/10000\ninfo = 450/100,0/100,450/100,0/100,450/100,0/100,450/400,0/400\nstutter = 450+425\n'),('Colombia (Republic of)','co','ringcadance = 1000,4000\ndial = 425\nbusy = 425/250,0/250\nring = 425/1000,0/4500\ncongestion = 425/100,0/250,425/350,0/250,425/650,0/250\ncallwaiting = 400+450/300,0/6000\ndialrecall = 425\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0/1000\n'),('Costa Rica','cr','ringcadence = 1203,4797\ndial = 450\nbusy = 450/330,0/330\nring = 450/1200,0/4900\ncongestion = 450/330,0/330\ncallwaiting = 450/150,0/150,450/150,0/8000\\dialrecall = !450/100,!0/100,!450/100,!0/100,!450/100,!0/100,450\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !450/100,!0/100,!450/100,!0/100,!450/100,!0/100,!450/100,!0/100,!42\n'),('Czech Republic','cz','ringcadence = 1000,4000\ndial = 425/330,0/330,425/660,0/660\nbusy = 425/330,0/330\nring = 425/1000,0/4000\ncongestion = 425/165,0/165\ncallwaiting = 425/330,0/9000\ndialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425/330,0/330,425/660,0/660\nrecord = 1400/500,0/14000\ninfo = 950/330,0/30,1400/330,0/30,1800/330,0/1000\nstutter = 425/450,0/50\n'),('Denmark','dk','ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = !425/200,!0/600,!425/200,!0/3000,!425/200,!0/200,!425/200,0\ndialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\nrecord = 1400/80,0/15000\ninfo = 950/330,1400/330,1800/330,0/1000\nstutter = 425/450,0/50\n'),('Estonia','ee','ringcadence = 1000,4000\ndial = 425\nbusy = 425/300,0/300\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = 950/650,0/325,950/325,0/30,1400/1300,0/2600\ndialrecall = 425/650,0/25\nrecord = 1400/500,0/15000\ninfo = 950/650,0/325,950/325,0/30,1400/1300,0/2600\nstutter = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\n'),('Finland','fi','ringcadence = 1000,4000\ndial = 425\nbusy = 425/300,0/300\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = 425/150,0/150,425/150,0/8000\ndialrecall = 425/650,0/25\nrecord = 1400/500,0/15000\ninfo = 950/650,0/325,950/325,0/30,1400/1300,0/2600\nstutter = 425/650,0/25\n'),('France','fr','ringcadence = 1500,3500\ndial = 440\nbusy = 440/500,0/500\nring = 440/1500,0/3500\ncongestion = 440/250,0/250\ncallwait = 440/300,0/10000\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330\nstutter = !440/100,!0/100,!440/100,!0/100,!440/100,!0/100,!440/100,!0/100,!440/100,!0/100,!440/100,!0/100,440\n'),('Germany','de','ringcadence = 1000,4000\ndial = 425\nbusy = 425/480,0/480\nring = 425/1000,0/4000\ncongestion = 425/240,0/240\ncallwaiting = !425/200,!0/200,!425/200,!0/5000,!425/200,!0/200,!425/200,!0/5000,!425/200,!0/200,!425/200,!0/5000,!425/200,!0/200,!425/200,!0/5000,!425/200,!0/200,!425/200,0\ndialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\nrecord = 1400/80,0/15000\ninfo = 950/330,1400/330,1800/330,0/1000\nstutter = 425+400\n'),('Greece','gr','ringcadence = 1000,4000\ndial = 425/200,0/300,425/700,0/800\nbusy = 425/300,0/300\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = 425/150,0/150,425/150,0/8000\ndialrecall = 425/650,0/25\nrecord = 1400/400,0/15000\ninfo = !950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,0\nstutter = 425/650,0/25\n'),('Hong Kong','hk','ringcadence = 400,200,400,3000\ndial = 350+440\nbusy = 480+620/500,0/500\nring = 440+480/400,0/200,440+480/400,0/3000\ncongestion = 480+620/250,0/250\ncallwaiting = 440/300,0/10000\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\n'),('Hungary','hu','ringcadence = 1250,3750\ndial = 425\nbusy = 425/300,0/300\nring = 425/1250,0/3750\ncongestion = 425/300,0/300\ncallwaiting = 425/40,0/1960\ndialrecall = 425+450\nrecord = 1400/400,0/15000\ninfo = !950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,0\nstutter = 350+375+400\n'),('India','in','ringcadence = 400,200,400,2000\ndial = 400*25\nbusy = 400/750,0/750\nring = 400*25/400,0/200,400*25/400,0/2000\ncongestion = 400/250,0/250\ncallwaiting = 400/200,0/100,400/200,0/7500\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0/1000\nstutter = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\n'),('Iran','ir','ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/240,0/240\ncallwaiting = 425/200,0/200,425/200,0/10000\ndialrecall = 425 record = 1400/80,0/15000\ninfo = 950/330,1400/330,1800/330,0/1000\nstutter = 400+425\n'),('Israel','il','ringcadence = 1000,3000\ndial = 414\nbusy = 414/500,0/500\nring = 414/1000,0/3000\ncongestion = 414/250,0/250\ncallwaiting = 414/100,0/100,414/100,0/100,414/600,0/3000 \ndialrecall = !414/100,!0/100,!414/100,!0/100,!414/100,!0/100,414\nrecord = 1400/500,0/15000\ninfo = 1000/330,1400/330,1800/330,0/1000\nstutter = !414/160,!0/160,!414/160,!0/160,!414/160,!0/160,!414/160,!0/160,!414/160,!0/160,!414/160,!0/160,!414/160,!0/160,!414/160,!0/160,!414/160,!0/160,!414/160,!0/160,414 \n'),('Italy','it','ringcadence = 1000,4000\ndial = 425/200,0/200,425/600,0/1000\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = 425/400,0/100,425/250,0/100,425/150,0/14000\ndialrecall = 470/400,425/400\nrecord = 1400/400,0/15000\ninfo = !950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,0\nstutter = 470/400,425/400\n'),('Japan','jp','ringcadence = 1000,2000\ndial = 400\nbusy = 400/500,0/500\nring = 400+415/1000,0/2000\ncongestion = 400/500,0/500\ncallwaiting = 400+16/500,0/8000\ndialrecall = !400/200,!0/200,!400/200,!0/200,!400/200,!0/200,400\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter =!400/100,!0/100,!400/100,!0/100,!400/100,!0/100,!400/100,!0/100,!400/100,!0/100,!400/100,!0/100,400\n'),('Kenya (Republic of)','ke','ringcadence = 670,3000,1500,5000\nbusy = 425/200,0/600,425/200,0/600\ncongestion = 425/200,0/600\ndial = 425\nringing = 425/670,0/3000,425/1500,0/5000\ninfo = 900/750,1400/750,1800/750,0/1250\ncallwaiting = 425\n'),('Lithuania','lt','ringcadence = 1000,4000\ndial = 425\nbusy = 425/350,0/350\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = 425/150,0/150,425/150,0/4000\ndialrecall = 425/500,0/50\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,0\nstutter = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\n'),('Macao','mo','ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/250,0/250\ncallwaiting = 425/200,0/600\nrecord = 1400/400,0/15000\ninfo = 950/333,1400/333,1800/333,0/1000\nstutter = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\n'),('Malaysia','my','ringcadence = 400,200,400,2000\ndial = 425\nbusy = 425/500,0/500\nring = 425/400,0/200,425/400,0/2000\ncongestion = 425/500,0/500\ncallwaiting = 425/100,0/4000\ndialrecall = 350+440\nrecord = 1400/500,0/60000\ninfo = 950/330,0/15,1400/330,0/15,1800/330,0/1000\nstutter = 450+425\n'),('Mexico','mx','ringcadence = 2000,4000\ndial = 425\nbusy = 425/250,0/250\nring = 425/1000,0/4000\ncongestion = 425/250,0/250\ncallwaiting = 425/200,0/600,425/200,0/10000\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = 950/330,0/30,1400/330,0/30,1800/330,0/1000\nstutter = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\n'),('Netherlands','nl','ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/250,0/250\ncallwaiting = 425/500,0/9500\ndialrecall = 425/500,0/50\nrecord = 1400/500,0/15000\ninfo = 950/330,1400/330,1800/330,0/1000\nstutter = 425/500,0/50\n'),('New Zealand','nz','ringcadence = 400,200,400,2000\ndial = 400\nbusy = 400/250,0/250\nring = 400+450/400,0/200,400+450/400,0/2000\ncongestion = 400/375,0/375\ncallwaiting = !400/200,!0/3000,!400/200,!0/3000,!400/200,!0/3000,!400/200\ndialrecall = !400/100!0/100,!400/100,!0/100,!400/100,!0/100,400\nrecord = 1400/425,0/15000\ninfo = 400/750,0/100,400/750,0/100,400/750,0/100,400/750,0/400\nstutter = !400/100!0/100,!400/100,!0/100,!400/100,!0/100,!400/100!0/100,!400/100,!0/100,!400/100,!0/100,400\n'),('Norway','no','ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = 425/200,0/600,425/200,0/10000\ndialrecall = 470/400,425/400\nrecord = 1400/400,0/15000\ninfo = !950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,0\nstutter = 470/400,425/400\n'),('Pakistan','pk','ringcadence = 400,1000,0,2000\nbusy = 400/500,0/500\nring = 400/1000,0/2000\ncongestion = 400/250,0/250\n'),('Panama','pa','ringcadence = 2000,4000\ndial = 425\nbusy = 425/320,0/320\nring = 425/1200,0/4650\ncongestion = 425/320,0/320\ncallwaiting = 425/180,0/180,425/180\\dialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!42\n'),('Philippines','phl','ringcadence = 1000,4000\ndial = 425\nbusy = 480+620/500,0/500\nring = 425+480/1000,0/4000\ncongestion = 480+620/250,0/250\ncallwaiting = 440/300,0/10000\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\n'),('Poland','pl','ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/500,0/500\ncallwaiting = 425/150,0/150,425/150,0/4000\ndialrecall = 425/500,0/50\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000,!950/330,!1400/330,!1800/330,!0/1000\nstutter = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\n'),('Portugal','pt','ringcadence = 1000,5000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/5000\ncongestion = 425/200,0/200\ncallwaiting = 440/300,0/10000\ndialrecall = 425/1000,0/200\nrecord = 1400/500,0/15000\ninfo = 950/330,1400/330,1800/330,0/1000\nstutter = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\n'),('Russian Federation','ru','ringcadence = 1000,4000\ndial = 425\nbusy = 425/350,0/350\nring = 425/800,0/3200\ncongestion = 425/350,0/350\ncallwaiting = 425/200,0/5000\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\n'),('Singapore','sg','ringcadence = 400,200,400,2000\ndial = 425\nring = 425*24/400,0/200,425*24/400,0/2000 ; modulation should be 100%, not 90%\nbusy = 425/750,0/750\ncongestion = 425/250,0/250\ncallwaiting = 425*24/300,0/200,425*24/300,0/3200\nstutter = !425/200,!0/200,!425/600,!0/200,!425/200,!0/200,!425/600,!0/200,!425/200,!0/200,!425/600,!0/200,!425/200,!0/200,!425/600,!0/200,425\ninfo = 950/330,1400/330,1800/330,0/1000 ; not currently in use acc. to reference\ndialrecall = 425*24/500,0/500,425/500,0/2500 ; unspecified in IDA reference, use repeating Holding Tone A,B\nrecord = 1400/500,0/15000 ; unspecified in IDA reference, use 0.5s tone every 15s\nnutone = 425/2500,0/500\nintrusion = 425/250,0/2000\nwarning = 425/624,0/4376 ; end of period tone, warning\nacceptance = 425/125,0/125\nholdinga = !425*24/500,!0/500 ; followed by holdingb\nholdingb = !425/500,!0/2500\n'),('South Africa','za','ringcadence = 400,200,400,2000\ndial = 400*33\nbusy = 400/500,0/500\nring = 400*33/400,0/200,400*33/400,0/2000\ncongestion = 400/250,0/250\ncallwaiting = 400*33/250,0/250,400*33/250,0/250,400*33/250,0/250,400*33/250,0/250\ndialrecall = 350+440\nrecord = 1400/500,0/10000\ninfo = 950/330,1400/330,1800/330,0/330\nstutter =!400*33/100,!0/100,!400*33/100,!0/100,!400*33/100,!0/100,!400*33/100,!0/100,!400*33/100,!0/100,!400*33/100,!0/100,400*33 \n'),('Spain','es','ringcadence = 1500,3000\ndial = 425\nbusy = 425/200,0/200\nring = 425/1500,0/3000\ncongestion = 425/200,0/200,425/200,0/200,425/200,0/600\ncallwaiting = 425/175,0/175,425/175,0/3500\ndialrecall = !425/200,!0/200,!425/200,!0/200,!425/200,!0/200,425\nrecord = 1400/500,0/15000\ninfo = 950/330,0/1000\ndialout = 500\n'),('Sweden','se','ringcadence = 1000,5000\ndial = 425\nbusy = 425/250,0/250\nring = 425/1000,0/5000\ncongestion = 425/250,0/750\ncallwaiting = 425/200,0/500,425/200,0/9100\ndialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\nrecord = 1400/500,0/15000\ninfo = !950/332,!0/24,!1400/332,!0/24,!1800/332,!0/2024,!950/332,!0/24,!1400/332,!0/24,!1800/332,!0/2024,!950/332,!0/24,!1400/332,!0/24,!1800/332,!0/2024,!950/332,!0/24,!1400/332,!0/24,!1800/332,!0/2024,!950/332,!0/24,!1400/332,!0/24,!1800/332,0\nstutter = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\n'),('Switzerland','ch','ringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/200,0/200\ncallwaiting = 425/200,0/200,425/200,0/4000\ndialrecall = !425/100,!0/100,!425/100,!0/100,!425/100,!0/100,425\nrecord = 1400/80,0/15000\ninfo = 950/330,1400/330,1800/330,0/1000\nstutter = 425+340/1100,0/1100\n'),('Taiwan','tw','ringcadence = 1000,4000\ndial = 350+440\nbusy = 480+620/500,0/500\nring = 440+480/1000,0/2000\ncongestion = 480+620/250,0/250\ncallwaiting = 350+440/250,0/250,350+440/250,0/3250\ndialrecall = 300/1500,0/500\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\n'),('Tanzania (United Republic of)','tz','ringcadence = 1000,4000\nbusy = 425/1000,0/1000\ncongestion = 425/375,0/375\ndial = 425+400\nringing = 425/1000,0/4000\ninfo = 950/375,1400/375,1800/375,0/30,950/375,1400/375,1800/375,0/30,950/375,1400/375,1800/375callwaiting = 425/500,0/200\n'),('Thailand','th','ringcadence = 1000,4000\\dial = 400*50\nbusy = 400/500,0/500\nring = 420/1000,0/5000\ncongestion = 400/300,0/300\ncallwaiting = 1000/400,10000/400,1000/400\ndialrecall = 400*50/400,0/100,400*50/400,0/100\nrecord = 1400/500,0/15000\ninfo = 950/330,1400/330,1800/330\nstutter = !400/200,!0/200,!400/600,!0/200,!400/200,!0/200,!400/600,!0/200,!400/200,!0/200,!400/600,!0/200,!400/200,!0/200,!400/600,!0/200,400\n'),('Turkey','tr','ringcadance = 2000,4000\ndial = 450\nbusy = 450/500,0/500\nring = 450/2000,450/4000\ncongestion = 450/200,0/200,450/200,0/200,450/200,0/200,450/600,0/200\ncallwaiting = 450/200,0/600,450/200,0/8000\ndialrecall = 450/1000,0/250\nrecord = 1400/500,0/15000\ninfo = !950/300,!1400/300,!1800/300,!0/1000,!950/300,!1400/300,!1800/300,!0/1000,!950/300,!1400/300,!1800/300,!0/1000,0\n'),('Uganda (Republic of)','ug','ringcadence = 1000,4000\nbusy = 425/500,0/500\ncongestion = 425/250,0/250\ndial = 425\nringing = 425/1000,0/4000\ncallwaiting = 425/150,0/150,425/150,0/8000\n'),('United Kingdom','uk','ringcadence = 400,200,400,2000\ndial = 350+440\nspecialdial = 350+440/750,440/750\nbusy = 400/375,0/375\ncongestion = 400/400,0/350,400/225,0/525\nspecialcongestion = 400/200,1004/300\nunobtainable = 400\nring = 400+450/400,0/200,400+450/400,0/2000\ncallwaiting = 400/100,0/4000\nspecialcallwaiting = 400/250,0/250,400/250,0/250,400/250,0/5000\ncreditexpired = 400/125,0/125\nconfirm = 1400\nswitching = 400/200,0/400,400/2000,0/400\ninfo = 950/330,0/15,1400/330,0/15,1800/330,0/1000\nrecord = 1400/500,0/60000\nstutter = 350+440/750,440/750\n'),('United States / North America','us','ringcadence = 2000,4000\ndial = 350+440\nbusy = 480+620/500,0/500\nring = 440+480/2000,0/4000\ncongestion = 480+620/250,0/250\ncallwaiting = 440/300,0/10000\ndialrecall = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,!350+440/100,!0/100,350+440\n'),('United States Circa 1950/ North America','us-old','ringcadence = 2000,4000\ndial = 600*120\nbusy = 500*100/500,0/500\nring = 420*40/2000,0/4000\ncongestion = 500*100/250,0/250\ncallwaiting = 440/300,0/10000\ndialrecall = !600*120/100,!0/100,!600*120/100,!0/100,!600*120/100,!0/100,600*120\nrecord = 1400/500,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !600*120/100,!0/100,!600*120/100,!0/100,!600*120/100,!0/100,!600*120/100,!0/100,!600*120/100,!0/100,!600*120/100,!0/100,600*120\n'),('Venezuela / South America','ve','; Tone definition source for ve found on\n; Reference: http://www.itu.int/ITU-T/inr/forms/files/tones-0203.pdf\nringcadence = 1000,4000\ndial = 425\nbusy = 425/500,0/500\nring = 425/1000,0/4000\ncongestion = 425/250,0/250\ncallwaiting = 400+450/300,0/6000\ndialrecall = 425\nrecord =  1400/500,0/15000\ninfo = !950/330,!1440/330,!1800/330,0/1000\n'),('Romania','ro','ringcadence = 1850,4150\ndial = 450\nbusy = 450/167,0/167\nring = 450*25/1850,0/4150\ncongestion = 450/500,0/500\ncallwaiting = 450/150,0/150,450/150,0/8000\ndialrecall = !450/100,!0/100,!450/100,!0/100,!450/100,!0/100,450\nrecord = 1400/400,0/15000\ninfo = !950/330,!1400/330,!1800/330,0\nstutter = !450/100,!0/100,!450/100,!0/100,!450/100,!0/100,!450/100,!0/100,!450/100,!0/100,!450/100,!0/100,450\nfacility = 450\nhowler = 3000\ndialout = 600\nintrusion = 450/150,0/4950\nspecialdial = 450*25/400,0/40\nunobtainable = !450/92,!0/110,!450/92,!0/110,!450/92,!0/110,450/362,0/110\n');
/*!40000 ALTER TABLE `indications_zonelist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventorydb`
--

DROP TABLE IF EXISTS `inventorydb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventorydb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empnum` varchar(10) DEFAULT NULL,
  `empname` varchar(20) NOT NULL,
  `building` varchar(150) DEFAULT NULL,
  `floor` varchar(10) DEFAULT NULL,
  `room` varchar(10) DEFAULT NULL,
  `section` varchar(6) DEFAULT NULL,
  `cubicle` varchar(6) DEFAULT NULL,
  `desk` varchar(6) DEFAULT NULL,
  `exten` varchar(8) DEFAULT NULL,
  `phusername` varchar(10) DEFAULT NULL,
  `phpassword` varchar(10) DEFAULT NULL,
  `mac` varchar(18) DEFAULT NULL,
  `serial` varchar(20) DEFAULT NULL,
  `device` varchar(20) DEFAULT NULL,
  `distdate` varchar(10) DEFAULT NULL,
  `ip` varchar(14) DEFAULT NULL,
  `pbxbox` varchar(20) DEFAULT NULL,
  `extrainfo` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventorydb`
--

LOCK TABLES `inventorydb` WRITE;
/*!40000 ALTER TABLE `inventorydb` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventorydb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `issabelpbx_log`
--

DROP TABLE IF EXISTS `issabelpbx_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `issabelpbx_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `section` varchar(50) DEFAULT NULL,
  `level` enum('error','warning','debug','devel-debug') NOT NULL DEFAULT 'error',
  `status` int(11) NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`,`level`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `issabelpbx_log`
--

LOCK TABLES `issabelpbx_log` WRITE;
/*!40000 ALTER TABLE `issabelpbx_log` DISABLE KEYS */;
INSERT INTO `issabelpbx_log` VALUES (1,'2006-11-06 01:55:36','retrieve_conf','devel-debug',0,'Started retrieve_conf, DB Connection OK'),(2,'2006-11-06 01:55:36','retrieve_conf','devel-debug',0,'Writing extensions_additional.conf');
/*!40000 ALTER TABLE `issabelpbx_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `issabelpbx_settings`
--

DROP TABLE IF EXISTS `issabelpbx_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `issabelpbx_settings` (
  `keyword` varchar(50) NOT NULL DEFAULT '',
  `value` varchar(255) DEFAULT NULL,
  `name` varchar(80) DEFAULT NULL,
  `level` tinyint(1) DEFAULT '0',
  `description` text,
  `type` varchar(25) DEFAULT NULL,
  `options` text,
  `defaultval` varchar(255) DEFAULT NULL,
  `readonly` tinyint(1) DEFAULT '0',
  `hidden` tinyint(1) DEFAULT '0',
  `category` varchar(50) DEFAULT NULL,
  `module` varchar(25) DEFAULT NULL,
  `emptyok` tinyint(1) DEFAULT '1',
  `sortorder` int(11) DEFAULT '0',
  PRIMARY KEY (`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `issabelpbx_settings`
--

LOCK TABLES `issabelpbx_settings` WRITE;
/*!40000 ALTER TABLE `issabelpbx_settings` DISABLE KEYS */;
INSERT INTO `issabelpbx_settings` VALUES ('AGGRESSIVE_DUPLICATE_CHECK','0','Aggresively Check for Duplicate Extensions',0,'When set to true IssabelPBX will update its extension map every page load. This is used to check for duplicate extension numbers in the client side javascript validation. Normally the extension map is only created when Apply Configuration Settings is pressed and retrieve_conf is run.','bool','','0',0,0,'System Setup','',0,-137),('ALWAYS_SHOW_DEVICE_DETAILS','0','Show all Device Setting on Add',0,'When adding a new extension/device, setting this to true will show most available device settings that are displayed when you edit the same extension/device. Otherwise, just a few basic settings are displayed.','bool','','0',0,0,'Device Settings','',0,10),('AMPASTERISKGROUP','asterisk','System Asterisk Group',4,'The user group Asterisk should be running as, used by issabelpbx_engine. Most systems should not change this.','text','','asterisk',1,0,'System Setup','',0,-100),('AMPASTERISKUSER','asterisk','System Asterisk User',4,'The user Asterisk should be running as, used by issabelpbx_engine. Most systems should not change this.','text','','asterisk',1,0,'System Setup','',0,-100),('AMPASTERISKWEBGROUP','asterisk','System Web Group',4,'The user group your httpd should be running as, used by issabelpbx_engine. Most systems should not change this.','text','','asterisk',1,0,'System Setup','',0,-100),('AMPASTERISKWEBUSER','asterisk','System Web User',4,'The user your httpd should be running as, used by issabelpbx_engine. Most systems should not change this.','text','','asterisk',1,0,'System Setup','',0,-100),('AMPBACKUPEMAILFROM','','Email \"From:\" Address',0,'The From: field for emails when using the backup email feature.','text','','',0,0,'Backup Module','backup',1,0),('AMPBADNUMBER','1','Use bad-number Context',2,'Generate the bad-number context which traps any bogus number or feature code and plays a message to the effect. If you use the Early Dial feature on some Grandstream phones, you will want to set this to false.','bool','','1',0,0,'Dialplan and Operational','',0,-100),('AMPBIN','/var/lib/asterisk/bin','IssabelPBX bin Dir',4,'Location of the IssabelPBX command line scripts.','dir','','/var/lib/asterisk/bin',1,0,'Directory Layout','',0,-100),('AMPCGIBIN','/var/www/cgi-bin ','CGI Dir',4,'The path to Apache cgi-bin dir (leave off trailing slash).','dir','','/var/www/cgi-bin ',1,0,'Directory Layout','',0,-100),('AMPDEVGROUP','asterisk','System Device Group',4,'The user group that various device directories should be set to, used by issabelpbx_engine. Examples include /dev/zap, /dev/dahdi, /dev/misdn, /dev/mISDN and /dev/dsp. Most systems should not change this.','text','','asterisk',1,0,'System Setup','',0,-100),('AMPDEVUSER','asterisk','System Device User',4,'The user that various device directories should be set to, used by issabelpbx_engine. Examples include /dev/zap, /dev/dahdi, /dev/misdn, /dev/mISDN and /dev/dsp. Most systems should not change this.','text','','asterisk',1,0,'System Setup','',0,-100),('AMPDISABLELOG','0','Disable IssabelPBX Log',0,'Whether or not to invoke the IssabelPBX log facility.','bool','','0',0,0,'System Setup','',0,-180),('AMPENGINE','asterisk','Telephony Engine',3,'The telephony backend engine being used, asterisk is the only option currently.','select','asterisk','asterisk',1,0,'System Setup','',0,-100),('AMPEXTENSIONS','extensions','User & Devices Mode',0,'Sets the extension behavior in IssabelPBX.  If set to <b>extensions</b>, Devices and Users are administered together as a unified Extension, and appear on a single page. If set to <b>deviceanduser</b>, Devices and Users will be administered separately. Devices (e.g. each individual line on a SIP phone) and Users (e.g. <b>101</b>) will be configured independent of each other, allowing association of one User to many Devices, or allowing Users to login and logout of Devices.','select','extensions,deviceanduser','extensions',0,0,'System Setup','',0,-135),('AMPLOCALBIN','','AMPLOCALBIN Dir for retrieve_conf',2,'If this directory is defined, retrieve_conf will check for a file called <i>retrieve_conf_post_custom</i> and if that file exists, it will be included after other processing thus having full access to the current environment for additional customization.','dir','','',1,0,'Developer and Customization','',1,-100),('AMPMGRPASS','amp111','Asterisk Manager Password',2,'Password for accessing the Asterisk Manager Interface (AMI), this will be automatically updated in manager.conf.','text','','amp111',0,0,'Asterisk Manager','',0,-100),('AMPMGRUSER','admin','Asterisk Manager User',2,'Username for accessing the Asterisk Manager Interface (AMI), this will be automatically updated in manager.conf.','text','','admin',0,0,'Asterisk Manager','',0,-100),('AMPMPG123','1','Convert Music Files to WAV',3,'When set to false, the MP3 files can be loaded and WAV files converted to MP3 in the MoH module. The default behavior of true assumes you have mpg123 loaded as well as sox and will convert MP3 files to WAV. This is highly recommended as MP3 files heavily tax the system and can cause instability on a busy phone system','bool','','1',0,0,'System Setup','music',0,0),('AMPPLAYKEY','','Recordings Crypt Key',3,'Crypt key used by this recordings module when accessing the recording files. Change from the default of \"moufdsuu3nma0\" if desired.','text','','',0,0,'System Setup','recordings',1,0),('AMPSBIN','/usr/sbin','IssabelPBX sbin Dir',4,'Where (root) command line scripts are located.','dir','','/usr/sbin',1,0,'Directory Layout','',0,-100),('AMPSYSLOGLEVEL','FILE','IssabelPBX Log Routing',0,'Determine where to send log information if the log is enabled (\'Disable IssabelPBX Log\' (AMPDISABLELOG) false. There are two places to route the log messages. \'FILE\' will send all log messages to the defined \'IssabelPBX Log File\' (IPBX_LOG_FILE). All the other settings will route the log messages to your System Logging subsystem (syslog) using the specified log level. Syslog can be configured to route different levels to different locations. See \'syslog\' documentation (man syslog) on your system for more details.','select','FILE,LOG_EMERG,LOG_ALERT,LOG_CRIT,LOG_ERR,LOG_WARNING,LOG_NOTICE,LOG_INFO,LOG_DEBUG','FILE',0,0,'System Setup','',0,-190),('AMPVMUMASK','007','Asterisk VMU Mask',4,'Defaults to 077 allowing only the asterisk user to have any permission on VM files. If set to something like 007, it would allow the group to have permissions. This can be used if setting apache to a different user then asterisk, so that the apache user (and thus ARI) can have access to read/write/delete the voicemail files. If changed, some of the voicemail directory structures may have to be manually changed.','text','','007',0,0,'System Setup','',0,-100),('AMPWEBADDRESS','','IssabelPBX Web Address',4,'This is the address of your Web Server. It is mostly obsolete and derived when not supplied and will be phased out, but there are still some areas expecting a variable to be set and if you are using it this will migrate your value.','text','','',0,0,'System Setup','',1,-100),('AMPWEBROOT','/var/www/html','IssabelPBX Web Root Dir',4,'The path to Apache webroot (leave off trailing slash).','dir','','/var/www/html',1,0,'Directory Layout','',0,-100),('AMP_ACCESS_DB_CREDS','0','Allow Login With DB Credentials',0,'When Set to True, admin access to the IssabelPBX GUI will be allowed using the IssabelPBX configured AMPDBUSER and AMPDBPASS credentials. This only applies when Authorization Type is \'database\' mode.','bool','','0',0,0,'System Setup','',0,-126),('ARI_ADMIN_PASSWORD','amp111','User Portal Admin Password',0,'This is the default admin password to allow an administrator to login to ARI bypassing all security. Change this to a secure password. Default = not set','text','','ari_password',0,0,'System Setup','',0,-110),('ARI_ADMIN_USERNAME','admin','User Portal Admin Username',0,'This is the default admin name used to allow an administrator to login to ARI bypassing all security. Change this to whatever you want, do not forget to change the User Portal Admin Password as well. Default = not set','text','','',0,0,'System Setup','',1,-120),('ASTAGIDIR','/var/lib/asterisk/agi-bin','Asterisk AGI Dir',4,'This is the default directory for Asterisks agi files.','dir','','/var/lib/asterisk/agi-bin',1,0,'Directory Layout','',0,-100),('ASTCONFAPP','app_confbridge','Conference Room App',0,'The asterisk application to use for conferencing. If only one is compiled into asterisk, IssabelPBX will auto detect and change this value if set wrong. The app_confbridge application is considered \"experimental\" with known issues and does not work on Asterisk 10 where it was completely rewritten and changed from the version on 1.6 and 1.8.','select','app_meetme,app_confbridge','app_confbridge',0,0,'Dialplan and Operational','',0,-100),('ASTETCDIR','/etc/asterisk','Asterisk etc Dir',4,'This is the default directory for Asterisks configuration files.','dir','','/etc/asterisk',1,0,'Directory Layout','',0,-100),('ASTLOGDIR','/var/log/asterisk','Asterisk Log Dir',4,'This is the default directory for Asterisks log files.','dir','','/var/log/asterisk',1,0,'Directory Layout','',0,-100),('ASTMANAGERHOST','localhost','Asterisk Manager Host',2,'Hostname for the Asterisk Manager','text','','localhost',1,0,'Asterisk Manager','',0,-100),('ASTMANAGERPORT','5038','Asterisk Manager Port',2,'Port for the Asterisk Manager','int','1024,65535','5038',1,0,'Asterisk Manager','',0,-100),('ASTMANAGERPROXYPORT','','Asterisk Manager Proxy Port',2,'Optional port for an Asterisk Manager Proxy','int','1024,65535','',1,0,'Asterisk Manager','',1,-100),('ASTMGRWRITETIMEOUT','5000','Asterisk Manager Write Timeout',2,'Timeout, im ms, for write timeouts for cases where Asterisk disconnects frequently','int','100,100000','5000',1,0,'Asterisk Manager','',1,-100),('ASTMODDIR','/usr/lib/asterisk/modules','Asterisk Modules Dir',4,'This is the default directory for Asterisks modules.','dir','','/usr/lib/asterisk/modules',1,0,'Directory Layout','',0,-100),('ASTRUNDIR','/var/run/asterisk','Asterisk Run Dir',4,'This is the default directory for Asterisks run files.','dir','','/var/run/asterisk',1,0,'Directory Layout','',0,-100),('ASTSPOOLDIR','/var/spool/asterisk','Asterisk Spool Dir',4,'This is the default directory for Asterisks spool directory.','dir','','/var/spool/asterisk',1,0,'Directory Layout','',0,-100),('ASTSTOPPOLLINT','2','Polling Interval for Stopping Asterisk',0,'When Asterisk is stopped or restarted with the \'amportal stop/restart\' commands, it does a graceful stop waiting for active channels to hangup. This sets the polling interval to check if Asterisk is shutdown and update the countdown timer.','select','1,2,3,5,10','2',0,0,'Dialplan and Operational','',0,-100),('ASTSTOPTIMEOUT','120','Waiting Period to Stop Asterisk',0,'When Asterisk is stopped or restarted with the \'amportal stop/restart\' commands, it does a graceful stop waiting for active channels to hangup. This sets the maximum time in seconds to wait prior to force stopping Asterisk','select','0,5,10,30,60,120,300,600,1800,3600,7200,10800','120',0,0,'Dialplan and Operational','',0,-100),('ASTVARLIBDIR','/var/lib/asterisk','Asterisk bin Dir',4,'This is the default directory for Asterisks lib files.','dir','','/var/lib/asterisk',1,0,'Directory Layout','',0,-100),('ASTVERSION','13.22.0','Asterisk Version',10,'Last Asterisk Version detected (or forced)','text','','',1,1,'Internal Use','',1,0),('AST_APP_VQA','','Asterisk Application VQA',10,'Set to the application name if the application is present in this Asterisk install','text','','',1,1,'Internal Use','',1,0),('AST_FUNC_CONNECTEDLINE','CONNECTEDLINE','Asterisk Function CONNECTEDLINE',10,'Set to the function name if the function is present in this Asterisk install','text','','',1,1,'Internal Use','',1,0),('AST_FUNC_DEVICE_STATE','DEVICE_STATE','Asterisk Function DEVICE_STATE',10,'Set to the function name if the function is present in this Asterisk install','text','','',1,1,'Internal Use','',1,0),('AST_FUNC_EXTENSION_STATE','EXTENSION_STATE','Asterisk Function EXTENSION_STATE',10,'Set to the function name if the function is present in this Asterisk install','text','','',1,1,'Internal Use','',1,0),('AST_FUNC_MASTER_CHANNEL','MASTER_CHANNEL','Asterisk Function MASTER_CHANNEL',10,'Set to the function name if the function is present in this Asterisk install','text','','',1,1,'Internal Use','',1,0),('AST_FUNC_PRESENCE_STATE','PRESENCE_STATE','Asterisk Function PRESENCE_STATE',10,'Set to the function name if the function is present in this Asterisk install','text','','',1,1,'Internal Use','',1,0),('AST_FUNC_SHARED','SHARED','Asterisk Function SHARED',10,'Set to the function name if the function is present in this Asterisk install','text','','',1,1,'Internal Use','',1,0),('AS_DISPLAY_FRIENDLY_NAME','1','Display Friendly Name',0,'Normally the friendly names will be displayed on this page and the internal issabelpbx_conf configuration names are shown in the tooltip. If you prefer to view the configuration variables, and the friendly name in the tooltip, set this to false..','bool','','1',0,0,'Advanced Settings Details','',0,0),('AS_DISPLAY_HIDDEN_SETTINGS','0','Display Hidden Settings',0,'This will display settings that are normally hidden by the system. These settings are often internally used settings that are not of interest to most users.','bool','','0',1,1,'Advanced Settings Details','',0,0),('AS_DISPLAY_READONLY_SETTINGS','0','Display Readonly Settings',0,'This will display settings that are readonly. These settings are often internally used settings that are not of interest to most users. Since they are readonly they can only be viewed.','bool','','0',0,0,'Advanced Settings Details','',0,0),('AS_OVERRIDE_READONLY','0','Override Readonly Settings',0,'Setting this to true will allow you to override un-hidden readonly setting to change them. Settings that are readonly may be extremely volatile and have a high chance of breaking your system if you change them. Take extreme caution when electing to make such changes.','bool','','0',0,0,'Advanced Settings Details','',0,0),('AUTHTYPE','database','Authorization Type',3,'Authentication type to use for web admin. If type set to <b>database</b>, the primary AMP admin credentials will be the AMPDBUSER/AMPDBPASS above. When using database you can create users that are restricted to only certain module pages. When set to none, you should make sure you have provided security at the apache level. When set to webserver, IssabelPBX will expect authentication to happen at the apache level, but will take the user credentials and apply any restrictions as if it were in database mode.','select','database,none,webserver','database',1,0,'System Setup','',0,-130),('BADDESTABORT','0','Abort Config Gen on Bad Dest',3,'Setting either of these to true will result in retrieve_conf aborting during a reload if an extension conflict is detected or a destination is detected. It is usually better to allow the reload to go through and then correct the problem but these can be set if a more strict behavior is desired.','bool','','0',0,0,'GUI Behavior','',0,-100),('BLOCK_OUTBOUND_TRUNK_CNAM','0','Block CNAM on External Trunks',0,'Some carriers will reject a call if a CallerID Name (CNAM) is presented. This occurs in several areas when configuring CID on the PBX using the format of \'CNAM\' <CNUM>. To remove the CNAM part of CID on all external trunks, set this value to true. This WILL NOT remove CNAM when a trunk is called from an Intra-Company route. This can be done on each individual trunk in addition to globally if there are trunks where it is desirable to keep CNAM information, though most carriers ignore CNAM.','bool','','0',0,0,'Dialplan and Operational','',0,-100),('BRAND_ALT_JS','','Alternate JS',1,'Alternate JS file, to supplement legacy.script.js','text','','',1,1,'Styling and Logos','',1,360),('BRAND_CSS_ALT_MAINSTYLE','','Primary CSS Stylesheet',1,'Set this to replace the default mainstyle.css style sheet with your own, relative to admin.','text','','',1,0,'Styling and Logos','',1,160),('BRAND_CSS_ALT_POPOVER','','Primary CSS Popover Stylesheet Addtion',1,'Set this to replace the default popover.css style sheet with your own, relative to admin.','text','','',1,0,'Styling and Logos','',1,162),('BRAND_CSS_CUSTOM','','Optional Additional CSS Stylesheet',1,'Optional custom CSS style sheet included after the primary one and any module specific ones are loaded, relative to admin.','text','','',1,0,'Styling and Logos','',1,170),('BRAND_IMAGE_FAVICON','images/favicon.ico','Favicon',1,'Favicon','text','','images/favicon.ico',1,1,'Styling and Logos','',0,40),('BRAND_IMAGE_ISSABELPBX_FOOT','images/issabelpbx_small.png','Image: Footer',1,'Logo in footer.  Path is relative to admin.','text','','images/issabelpbx_small.png',1,0,'Styling and Logos','',1,50),('BRAND_IMAGE_ISSABELPBX_LINK_FOOT','http://www.issabel.org','Link for Footer Logo',1,'link to follow when clicking on logo, defaults to http://www.issabel.org','text','','http://www.issabel.org',1,0,'Styling and Logos','',1,120),('BRAND_IMAGE_ISSABELPBX_LINK_LEFT','http://www.issabel.org','Link for Left Logo',1,'link to follow when clicking on logo, defaults to http://www.issabel.org','text','','http://www.issabel.org',1,0,'Styling and Logos','',1,100),('BRAND_IMAGE_SPONSOR_FOOT','','Image: Footer',1,'Logo in footer.  Path is relative to admin.','text','','',1,0,'Styling and Logos','',1,50),('BRAND_IMAGE_SPONSOR_LINK_FOOT','','Link for Sponsor Footer Logo',1,'link to follow when clicking on sponsor logo','text','','',1,0,'Styling and Logos','',1,120),('BRAND_IMAGE_TANGO_LEFT','images/tango.png','Image: Left Upper',1,'Left upper logo.  Path is relative to admin.','text','','images/tango.png',1,0,'Styling and Logos','',0,40),('BRAND_ISSABELPBX_ALT_FOOT','IssabelPBX&reg;','Alt for Footer Logo',1,'alt attribute to use in place of image and title hover value. Defaults to IssabelPBX','text','','IssabelPBX&reg;',1,0,'Styling and Logos','',1,90),('BRAND_ISSABELPBX_ALT_LEFT','IssabelPBX','Alt for Left Logo',1,'alt attribute to use in place of image and title hover value. Defaults to IssabelPBX','text','','IssabelPBX',1,0,'Styling and Logos','',1,70),('BRAND_SPONSOR_ALT_FOOT','','Alt for Footer Logo',1,'alt attribute to use in place of image and title hover value. Defaults to IssabelPBX','text','','',1,0,'Styling and Logos','',1,90),('BRAND_TITLE','IssabelPBX Administration','Page Title',1,'HTML title of all pages','text','','IssabelPBX Administration',1,1,'Styling and Logos','',0,40),('BROWSER_STATS','1','Browser Stats',0,'Setting this to true will allow the development team to use google analytics to anonymously analyze browser information to help make better development decision.','bool','','1',0,0,'System Setup','',0,-100),('CCBS_AVAILABLE_TIMER_DEFAULT','4800','Max Camp-On Life Busy Default',1,'Asteirsk: ccbs_available_timer. How long a call completion request will remain active, in seconds, before expiring if the phone rang busy when first attempting the call.','select','1200,2400,3600,4800,6000,7200,10800,14400,18000,21600,25200,28800,32400','4800',0,0,'Camp-On Module','campon',0,70),('CCNR_AVAILABLE_TIMER_DEFAULT','7200','Max Camp-On Life No Answer Default',1,'Asteirsk: ccnr_available_timer. How long a call completion request will remain active, in seconds, before expiring if the phone was simply unanswered when first attempting the call.','select','1200,2400,3600,4800,6000,7200,10800,14400,18000,21600,25200,28800,32400','7200',0,0,'Camp-On Module','campon',0,80),('CC_AGENT_ALERT_INFO_DEFAULT','','Default Callback Alert-Info',1,'An optional Alert-Info setting that can be used when initiating a callback. Only valid when \'Caller Policy\' is set to \'generic\' device\'','text','','',0,0,'Camp-On Module','campon',1,120),('CC_AGENT_CID_PREPEND_DEFAULT','','Default Callback CID Prepend',1,'An optional CID Prepend setting that can be used when initiating a callback. Only valid when \'Caller Policy\' is set to a \'generic\' device\'','text','','',0,0,'Camp-On Module','campon',1,130),('CC_AGENT_DIALSTRING_DEFAULT','extension','Default Caller Callback Mode',1,'Affects Asterisk: cc_agent_dialstring. If not set a callback request will be dialed straight to the speciifc device that made the call. If using \'native\' technology support this may be the peferred mode. The \'internal\' (Callback Standard) option will intiate a call back to the caller just as if someone else on the system placed the call, which means the call can pursue Follow-Me. To avoid Follow-Me setting, choose \'extension\' (Callback Extension).','select',',extension,internal','extension',0,0,'Camp-On Module','campon',0,100),('CC_AGENT_POLICY_DEFAULT','generic','Caller Policy Default',1,'Asterisk: cc_agent_policy. Used to enable Camp-On for a user and set the Technology Mode that will be used when engaging the feature. In most cases \'generic\' should be chosen unless you have phones designed to work with channel specific capabilities.','select','never,generic,native','generic',0,0,'Camp-On Module','campon',0,40),('CC_ANNOUNCE_MONITOR_DEFAULT','1','Announce the Callee Extension',1,'When set to true the target extension being called will be announced upone answering the Callback prior to ringing the extension. Setting this to false will go directly to ringing the extension, the CID information will still reflect who is being called back.','bool','','1',0,0,'Camp-On Module','campon',0,140),('CC_BLF_CALLER_BUSY','ONHOLD','BLF Camp-On Busy Caller State',1,'This is the state that will be set for BLF subscriptions once the callee becomes available if the caller is not busy. Restart Asterisk for changes to take effect.','select','NOT_INUSE,INUSE,BUSY,UNAVAILABLE,RINGING,RINGINUSE,ONHOLD','ONHOLD',0,0,'Camp-On Module','campon',0,200),('CC_BLF_OFFERED','NOT_INUSE','BLF Camp-On Available State',1,'This is the state that will be set for BLF subscriptions after attempting a call while it is still possible to Camp-On to the last called number, prior to the offer_timer expiring. Restart Asterisk for changes to take effect.','select','NOT_INUSE,INUSE,BUSY,UNAVAILABLE,RINGING,RINGINUSE,ONHOLD','NOT_INUSE',0,0,'Camp-On Module','campon',0,180),('CC_BLF_PENDING','INUSE','BLF Camp-On Pending State',1,'This is the state that will be set for BLF subscriptions upon a successful Camp-On request, pending a callback when the party becomes available. Restart Asterisk for changes to take effect.','select','NOT_INUSE,INUSE,BUSY,UNAVAILABLE,RINGING,RINGINUSE,ONHOLD','INUSE',0,0,'Camp-On Module','campon',0,190),('CC_BLF_RECALL','RINGING','BLF Camp-On Recalling State',1,'This is the state that will be set for BLF subscriptions once the callee becomes available if the caller is not busy. Restart Asterisk for changes to take effect.','select','NOT_INUSE,INUSE,BUSY,UNAVAILABLE,RINGING,RINGINUSE,ONHOLD','RINGING',0,0,'Camp-On Module','campon',0,210),('CC_FORCE_DEFAULTS','1','Only Use Default Camp-On Settings',1,'You can force all extensions on a system to only used the default Camp-On settings. When in this mode, the individual settings will not be shown on the extension page. If there were unique settings previously configured, the data will be retained but not used unless you switch this back to false. With this set, the Caller Policy (cc_agent_policy) and Callee Policy (cc_monitor_policy) settings will still be configurable for each user so you can still enable/disable Call Camping ability on select extensions.','bool','','1',0,0,'Camp-On Module','campon',0,30),('CC_MAX_AGENTS_DEFAULT','5','Default Max Camped-On Extensions',1,'Asterisk: cc_max_agents. Only valid for when using \'native\' technology support for Caller Policy. This is the number of outstanding Call Completion requests that can be pending to different extensions. With \'generic\' device mode you can only have a single request outstanding and this will be ignored.','select','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20','5',0,0,'Camp-On Module','campon',0,110),('CC_MAX_MONITORS_DEFAULT','5','Default Max Queued Callers',1,'Asterisk: cc_max_monitors. This is the maximum number of callers that are allowed to queue up call completion requests against this extension.','select','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20','5',0,0,'Camp-On Module','campon',0,170),('CC_MAX_REQUESTS_GLOBAL','20','Maximum Active Camp-On Requests',1,'System wide maximum number of outstanding Camp-On requests that can be active. This limit is useful on a system that may have memory constraints since the internal state machine takes up system resources relative to the number of active requests it has to track. Restart Asterisk for changes to take effect.','int','1,1000','20',0,0,'Camp-On Module','campon',0,10),('CC_MONITOR_ALERT_INFO_DEFAULT','','Default Callee Alert-Info',1,'An optional Alert-Info setting that can be used to send to the extension being called back.','text','','',0,0,'Camp-On Module','campon',1,150),('CC_MONITOR_CID_PREPEND_DEFAULT','','Default Callee CID Prepend',1,'An optional CID Prepend setting that can be used to send to the extension being called back.\'','text','','',0,0,'Camp-On Module','campon',1,160),('CC_MONITOR_POLICY_DEFAULT','generic','Callee Policy Default',1,'Asterisk: cc_monitor_policy. Used to control if other phones are allowed to Camp On to an extension. If so, it sets the technology mode used to monitor the availability of the extension. If no specific technology support is available then it should be set to a \'generic\'. In this mode, a callback will be initiated to the extension when it changes from an InUse state to NotInUse. If it was busy when first attempted, this will be when the current call has eneded. If it simply did not answer, then this will be the next time this phone is used to make or answer a call and then hangs up. It is possible to set this to take advantage of \'native\' technology support if available and automatically fallback to \'generic\' whe not by setting it to \'always\'.','select','never,generic,native,always','generic',0,0,'Camp-On Module','campon',0,50),('CC_NON_EXTENSION_POLICY','never','Non Extensions Callee Policy',1,'If this is set to \'generic\' or \'always\' then it will be possible to attempt call completion requests when dialing non-extensions such as ring groups and other possible destinations that could work with call completion. Setting this will bypass any Callee Policies and can result in inconsistent behavior. If set, \'generic\' is the most common, \'always\' will attempt to use technology specific capabilities if the called extension uses a channel that supports that.','select','never,generic,always','never',0,0,'Camp-On Module','campon',0,20),('CC_OFFER_TIMER_DEFAULT','30','Caller Timeout to Request Default',1,'Asterisk: cc_offer_timer. How many seconds after dialing an extenion a user has to make a call completion request.','select','20,30,45,60,120,180,240,300,600','30',0,0,'Camp-On Module','campon',0,60),('CC_RECALL_TIMER_DEFAULT','15','Default Time to Ring Back Caller',1,'Asterisk: cc_recall_timer. How long in seconds to ring back a caller who\'s Caller Policy is set to \'Generic Device\'. This has no affect if set to any other setting.','select','5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60','15',0,0,'Camp-On Module','campon',0,90),('CDRDBHOST','','Remote CDR DB Host',3,'DO NOT set this unless you know what you are doing. Only used if you do not use the default values provided by IssabelPBX.<br>Hostname of db server if not the same as AMPDBHOST.','text','','',1,0,'Remote CDR Database','',1,-100),('CDRDBNAME','','Remote CDR DB Name',3,'DO NOT set this unless you know what you are doing. Only used if you do not use the default values provided by IssabelPBX.<br>Name of database used for cdr records.','text','','',1,0,'Remote CDR Database','',1,-100),('CDRDBPASS','','Remote CDR DB Password',3,'DO NOT set this unless you know what you are doing. Only used if you do not use the default values provided by IssabelPBX.<br>Password for connecting to db if its not the same as AMPDBPASS.','text','','',1,0,'Remote CDR Database','',1,-100),('CDRDBPORT','','Remote CDR DB Port',3,'DO NOT set this unless you know what you are doing. Only used if you do not use the default values provided by IssabelPBX.<br>Port number for db host.','int','1024,65536','',1,0,'Remote CDR Database','',1,-100),('CDRDBTABLENAME','','Remote CDR DB Table',3,'DO NOT set this unless you know what you are doing. Only used if you do not use the default values provided by IssabelPBX. Name of the table in the db where the cdr is stored. cdr is default.','text','','',1,0,'Remote CDR Database','',1,-100),('CDRDBTYPE','','Remote CDR DB Type',3,'DO NOT set this unless you know what you are doing. Only used if you do not use the default values provided by IssabelPBX. Defaults to your configured AMDBENGINE.','select',',mysql,postgres','',1,0,'Remote CDR Database','',1,-100),('CDRDBUSER','','Remote CDR DB User',3,'DO NOT set this unless you know what you are doing. Only used if you do not use the default values provided by IssabelPBX. Username to connect to db with if it is not the same as AMPDBUSER.','text','','',1,0,'Remote CDR Database','',1,-100),('CEL_ENABLED','1','Enable CEL Reporting',3,'Setting this true will enable the CDR module to drill down on CEL data for each CDR. Although the CDR module will assure there is a CEL table available, the reporting functionality in Asterisk and associated ODBC database and CEL configuration must be done outside of IssabelPBX either by the user or at the Distro level.','bool','','0',0,0,'CDR Report Module','cdr',0,10),('CFRINGTIMERDEFAULT','0','Call Forward Ringtimer Default',0,'This is the default time in seconds to try and connect a call that has been call forwarded by the server side CF, CFU and CFB options. (If your phones use client side CF such as SIP redirects, this will not have any affect) If set to the default of 0, it will use the standard ring timer. If set to -1 it will ring the forwarded number with no limit which is consistent with the behavior of some existing PBX systems. If set to any other value, it will ring for that duration before diverting the call to the users voicemail if they have one. This can be overridden for each extension.','select','-1,0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120','0',0,0,'Dialplan and Operational','',0,-100),('CHECKREFERER','1','Check Server Referrer',0,'When set to the default value of true, all requests into IssabelPBX that might possibly add/edit/delete settings will be validated to assure the request is coming from the server. This will protect the system from CSRF (cross site request forgery) attacks. It will have the effect of preventing legitimately entering URLs that could modify settings which can be allowed by changing this field to false.','bool','','1',0,0,'GUI Behavior','',0,-100),('CID_PREPEND_REPLACE','1','Only Use Last CID Prepend',0,'Some modules allow the CNAM to be prepended. If a previous prepend was done, the default behavior is to remove the previous prepend and only use the most recent one. Setting this to false will turn that off allowing all prepends to be \'starcked\' in front of one another.','bool','','1',0,0,'Dialplan and Operational','',0,-100),('CONCURRENCYLIMITDEFAULT','0','Extension Concurrency Limit',0,'Default maximum number of outbound simultaneous calls that an extension can make. This is also very useful as a Security Protection against a system that has been compromised. It will limit the number of simultaneous calls that can be made on the compromised extension. This default is used when an extension is created. A default of 0 means no limit.','select','0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120','0',0,0,'Dialplan and Operational','',0,-100),('CONNECTEDLINE_PRESENCESTATE','1','Display Presence State of Callee',0,'When set to true and when CONNECTEDLINE() capabilities are configured and supported by your handset, the name displayed will include the presence state of the callee.','bool','','1',0,0,'Dialplan and Operational','',0,0),('CRONMAN_UPDATES_CHECK','1','Update Notifications',0,'IssabelPBX allows you to automatically check for updates online. The updates will NOT be automatically installed. It is STRONGYLY advised that you keep this enabled and keep updated of these important notificaions to avoid costly security issues.','bool','','1',1,0,'System Setup','',0,-100),('CUSTOMASERROR','1','Report Unknown Dest as Error',2,'If false, then the Destination Registry will not report unknown destinations as errors. This should be left to the default true and custom destinations should be moved into the new custom apps registry.','bool','','1',0,0,'GUI Behavior','',0,-100),('CWINUSEBUSY','1','Occupied Lines CW Busy',0,'For extensions that have CW enabled, report unanswered CW calls as <b>busy</b> (resulting in busy voicemail greeting). If set to no, unanswered CW calls simply report as <b>no-answer</b>.','bool','','1',0,0,'Dialplan and Operational','',0,-100),('DASHBOARD_INFO_UPDATE_TIME','30','Dashboard Info Update Frequency',0,'Update rate in seconds of the Info section of the System Status panel.','select','15,30,60,120,300,600','30',0,0,'GUI Behavior','dashboard',0,0),('DASHBOARD_STATS_UPDATE_TIME','6','Dashboard Stats Update Frequency',0,'Update rate in seconds of all sections of the System Status panel except the Info box.','select','6,10,20,30,45,60,120,300,600','6',0,0,'GUI Behavior','dashboard',0,0),('DAYNIGHTTCHOOK','0','Hook Time Conditions Module',1,'By default, the Call Flow Control module will not hook Time Conditions allowing one to associate a call flow toggle feauture code with a time condition since time conditions have their own feature code as of version 2.9. If there is already an associaiton configured (on an upgraded system), this will have no affect for the Time Conditions that are effected. Setting this to true reverts the 2.8 and prior behavior by allowing for the use of a call flow toggle to be associated with a time conditon. This can be useful for two scenarios. First, to override a Time Condition without the automatic resetting that occurs with the built in Time Condition overrides. The second use is the ability to associate a single call flow toggle with multiple time conditions thus creating a <b>master switch</b> that can be used to override several possible call flows through different time conditions.','bool','','0',0,0,'Call Flow Control Module','daynight',0,0),('DEFAULT_INTERNAL_AUTO_ANSWER','disabled','Internal Auto Answer Default',0,'Default setting for new extensions. When set to Intercom, calls to new extensions/users from other internal users act as if they were intercom calls meaning they will be auto-answered if the endpoint supports this feature and the system is configured to operate in this mode. All the normal white list and black list settings will be honored if they are set. External calls will still ring as normal, as will certain other circumstances such as blind transfers and when a Follow Me is configured and enabled. If Disabled, the phone rings as a normal phone.','select','disabled,intercom','disabled',0,0,'Dialplan and Operational','',0,-100),('DEVEL','0','Developer Mode',2,'This enables several debug features geared towards developers, including some page load timing information, some debug information in Module Admin, use of original CSS files and other future capabilities will be enabled.','bool','','0',0,0,'Developer and Customization','',0,-100),('DEVELRELOAD','0','Leave Reload Bar Up',2,'Forces the \'Apply Configuration Changes\' reload bar to always be present even when not necessary.','bool','','0',0,0,'Developer and Customization','',0,-100),('DEVICE_ALLOW','','SIP and IAX allow',0,'Default setting for SIP and IAX allow (for codecs). Codecs to allow in addition to those set in general settings unless explicitly \'disallowed\' for the device. Values van be separated with \'&\' e.g. \'ulaw&g729&g729\' where the preference order is preserved. See Asterisk documentation for details.','text','','',0,0,'Device Settings','',1,90),('DEVICE_CALLGROUP','','SIP and DAHDi callgroup',0,'Default setting for SIP, DAHDi (and Zap) callgroup. Callgroup(s) that the device is part of, can be one or more callgroups, e.g. \'1,3-5\' would be in groups 1,3,4,5. See Asterisk documentation for details.','text','','',0,0,'Device Settings','',1,100),('DEVICE_DISALLOW','','SIP and IAX disallow',0,'Default setting for SIP and IAX disallow (for codecs). Codecs to disallow, can help to reset from the general settings by setting a value of \'all\' and then specifically including allowed codecs with the \'allow\' directive. Values van be separated with \'&\' e.g. \'g729&g722\'. See Asterisk documentation for details.','text','','',0,0,'Device Settings','',1,90),('DEVICE_PICKUPGROUP','','SIP and DAHDi pickupgroup',0,'Default setting for SIP, DAHDi (and Zap) pickupgroup. Pickupgroups(s) that the device can pickup calls from, can be one or more groups, e.g. \'1,3-5\' would be in groups 1,3,4,5. Device does not have to be in a group to be able to pickup calls from that group. See Asterisk documentation for details.','text','','',0,0,'Device Settings','',1,110),('DEVICE_QUALIFY','yes','SIP and IAX qualify',0,'Default setting for SIP and IAX qualify. Whether to send periodic OPTIONS messages (for SIP) or otherwise monitor the channel, and at what point to consider the channel unavailable. A value of \'yes\' is equivalent to 2000, time in msec. Can help to keep NAT holes open with SIP but not dependable for remote client firewalls. See Asterisk documentation for details.','text','','yes',0,0,'Device Settings','',0,80),('DEVICE_REMOVE_MAILBOX','0','Remove mailbox Setting when no Voicemail',0,'If set to true, any fixed device associated with a user that has no voicemail configured will have the \"mailbox=\" setting removed in the generated technology configuration file such as sip_additional.conf. This will not affect the value in the GUI.','bool','','0',0,0,'Device Settings','',0,15),('DEVICE_SIP_CANREINVITE','no','SIP canrenivite (directmedia)',0,'Default setting for SIP canreinvite (same as directmedia). See Asterisk documentation for details.','select','no,yes,nonat,update','no',0,0,'Device Settings','',0,20),('DEVICE_SIP_ENCRYPTION','no','SIP encryption',0,'Default setting for SIP encryption. Whether to offer SRTP encrypted media (and only SRTP encrypted media) on outgoing calls to a peer. Calls will fail with HANGUPCAUSE=58 if the peer does not support SRTP. See Asterisk documentation for details.','select','no,yes','no',0,0,'Device Settings','',0,60),('DEVICE_SIP_NAT','no','SIP nat',0,'Default setting for SIP nat. A \'yes\' will attempt to handle nat, also works for local (uses the network ports and address instead of the reported ports), \'no\' follows the protocol, \'never\' tries to block it, no RFC3581, \'route\' ignores the rport information. See Asterisk documentation for details.','select','no,yes,never,route','no',0,0,'Device Settings','',0,50),('DEVICE_SIP_QUALIFYFREQ','60','SIP qualifyfreq',0,'Default setting for SIP qualifyfreq. Only valid for Asterisk 1.6 and above. Frequency that \'qualify\' OPTIONS messages will be sent to the device. Can help to keep NAT holes open but not dependable for remote client firewalls. See Asterisk documentation for details.','int','15,86400','60',0,0,'Device Settings','',0,70),('DEVICE_SIP_SENDRPID','no','SIP sendrpid',0,'Default setting for SIP sendrpid. A value of \'yes\' is equivalent to \'rpid\' and will send the \'Remote-Party-ID\' header. A value of \'pai\' is only valid starting with Asterisk 1.8 and will send the \'P-Asserted-Identity\' header. See Asterisk documentation for details.','select','no,yes,pai','no',0,0,'Device Settings','',0,40),('DEVICE_SIP_TRUSTRPID','yes','SIP trustrpid',0,'Default setting for SIP trustrpid. See Asterisk documentation for details.','select','no,yes','yes',0,0,'Device Settings','',0,30),('DEVICE_STRONG_SECRETS','1','Require Strong Secrets',0,'Requires a strong secret on SIP and IAX devices requiring at least two numeric and non-numeric characters and 6 or more characters. This can be disabled if using devices that can not meet these needs, or you prefer to put other constraints including more rigid constraints that this rule actually considers weak when it may not be.','bool','','1',0,0,'Device Settings','',0,12),('DIAL_OPTIONS','tr','Asterisk Dial Options',0,'Options to be passed to the Asterisk Dial Command when making internal calls or for calls ringing internal phones. The options are documented in Asterisk documentation, a subset of which are described here. The default options T and t allow the calling and called users to transfer a call with ##. The r option allows Asterisk to generate ringing back to the calling phones which is needed by some phones and sometimes needed in complex dialplan features that may otherwise result in silence to the caller.','text','','tr',0,0,'Dialplan and Operational','',1,0),('DIE_ISSABELPBX_VERBOSE','0','Provide Verbose Tracebacks',2,'Provides a very verbose traceback when die_issabelpbx() is called including extensive object details if present in the traceback.','bool','','0',0,0,'Developer and Customization','',0,-100),('DISABLECUSTOMCONTEXTS','0','Disable -custom Context Includes',2,'Normally IssabelPBX auto-generates a custom context that may be usable for adding custom dialplan to modify the normal behavior of IssabelPBX. It takes a good understanding of how Asterisk processes these includes to use this and in many of the cases, there is no useful application. All includes will result in a WARNING in the Asterisk log if there is no context found to include though it results in no errors. If you know that you want the includes, you can set this to true. If you comment it out IssabelPBX will revert to legacy behavior and include the contexts.','bool','','0',0,0,'Dialplan and Operational','',0,-100),('DISABLE_CSS_AUTOGEN','1','Disable Mainstyle CSS Compression',2,'Stops the automatic generation of a stripped CSS file that replaces the primary sheet, usually mainstyle.css.','bool','','0',0,0,'Developer and Customization','',0,-100),('DISPLAY_MONITOR_TRUNK_FAILURES_FIELD','0','Display Monitor Trunk Failures Option',2,'Setting this to true will expose the \"Monitor Trunk Failures\" field on the Trunks page. This field allows for a custom AGI script to be called upon a trunk failure. This is an advanced field requiring a custom script to be properly written and installed. Existing trunk page entries will not be affected if this is set to false but if the settings are changed on those pages the field will go away.','bool','','0',0,0,'Developer and Customization','',0,-100),('DITECH_VQA_INBOUND','7','Ditech VQA Inbound Setting',0,'If Ditech\'s VQA, Voice Quality application is installed, this setting will be used for all inbound calls. For more information \'core show application VQA\' at the Asterisk CLI will show the different settings.','select','0,1,2,3,4,5,6,7','7',0,0,'Dialplan and Operational','',0,-100),('DITECH_VQA_OUTBOUND','7','Ditech VQA Outbound Setting',0,'If Ditech\'s VQA, Voice Quality application is installed, this setting will be used for all outbound calls. For more information \'core show application VQA\' at the Asterisk CLI will show the different settings.','select','0,1,2,3,4,5,6,7','7',0,0,'Dialplan and Operational','',0,-100),('DIVERSIONHEADER','0','Generate Diversion Headers',0,'If this value is set to true, then calls going out your outbound routes that originate from outside your PBX and were subsequently forwarded through a call forward, ring group, follow-me or other means, will have a SIP diversion header added to the call with the original incoming DID assuming there is a DID available. This is useful with some carriers that may require this under certain circumstances.','bool','','0',0,0,'Dialplan and Operational','',0,-100),('DYNAMICHINTS','0','Dynamically Generate Hints',0,'If true, Core will not statically generate hints, but instead make a call to the AMPBIN php script, and generate_hints.php through an Asterisk #exec call. This requires asterisk.conf to be configured with <b>execincludes=yes<b> set in the [options] section.','bool','','0',1,0,'Dialplan and Operational','',0,-100),('ENABLECW','1','CW Enabled by Default',0,'Enable call waiting by default when an extension is created (Default is yes). Set to <b>no</b> to if you do not want phones to be commissioned with call waiting already enabled. The user would then be required to dial the CW feature code (*70 default) to enable their phone. Most installations should leave this alone. It allows multi-line phones to receive multiple calls on their line appearances.','bool','','1',0,0,'Dialplan and Operational','',0,-100),('EXTENSION_LIST_RINGGROUPS','0','Display Extension Ring Group Members',0,'When set to true extensions that belong to one or more Ring Groups will have a Ring Group section and link back to each group they are a member of.','bool','','0',0,0,'Ring Group Module','ringgroups',0,50),('FCBEEPONLY','0','Feature Codes Beep Only',0,'When set to true, a beep is played instead of confirmation message when activating/de-activating: CallForward, CallWaiting, DayNight, DoNotDisturb and FindMeFollow.','bool','','0',0,0,'Dialplan and Operational','',0,-100),('FOLLOWME_AUTO_CREATE','0','Create Follow Me at Extension Creation Time',1,'When creating a new user or extension, setting this to true will automatically create a new Follow Me for that user using the default settings listed below','bool','','0',0,0,'Follow Me Module','findmefollow',0,30),('FOLLOWME_DISABLED','1','Disable Follow Me Upon Creation',1,'This is the default value for the Follow Me \"Disable\" setting. When first creating a Follow Me or if auto-created with a new extension, setting this to true will disable the Follow Me setting which can be changed by the user or admin in multiple locations.','bool','','1',0,0,'Follow Me Module','findmefollow',0,40),('FOLLOWME_PRERING','7','Default Follow Me Initial Ring Time',1,'The default Initial Ring Time for a Follow Me set upon creation and used if auto-created with a new extension.','select','5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60','7',0,0,'Follow Me Module','findmefollow',0,60),('FOLLOWME_RG_STRATEGY','ringallv2-prim','Default Follow Me Ring Strategy',1,'The default Ring Strategy selected for a Follow Me set upon creation and used if auto-created with an extension.','select','ringallv2,ringallv2-prim,ringall,ringall-prim,hunt,hunt-prim,memoryhunt,memoryhunt-prim,firstavailable,firstnotonphone','ringallv2-prim',0,0,'Follow Me Module','findmefollow',0,70),('FOLLOWME_TIME','20','Default Follow Me Ring Time',1,'The default Ring Time for a Follow Me set upon creation and used if auto-created with a new extension.','select','5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120','20',0,0,'Follow Me Module','findmefollow',0,50),('FOPWEBROOT','/var/www/html/admin/modules/fw_fop/','FOP Web Root Dir',4,'Path to the Flash Operator Panel webroot or other modules providing such functionality (leave off trailing slash).','dir','','',1,0,'Flash Operator Panel','',1,-100),('FORCED_ASTVERSION','','Force Asterisk Version',4,'Normally IssabelPBX gets the current Asterisk version directly from Asterisk. This is required to generate proper dialplan for a given version. When using some custom Asterisk builds, the version may not be properly parsed and improper dialplan generated. Setting this to an equivalent Asterisk version will override what is read from Asterisk. This SHOULD be left blank unless you know what you are doing.','text','','',1,0,'System Setup','',1,-100),('FORCE_INTERNAL_AUTO_ANSWER_ALL','0','Force All Internal Auto Answer',0,'Force all extensions to operate in the Internal Auto Answer mode regardless of their individual settings. See \'Internal Auto Answer Default\' for more information.','bool','','0',0,0,'Dialplan and Operational','',0,-100),('FORCE_JS_CSS_IMG_DOWNLOAD','0','Always Download Web Assets',2,'IssabelPBX appends versioning tags on the CSS and javascript files and some of the main logo images. The versioning will help force browsers to load new versions of the files when module versions are upgraded. Setting this value to true will try to force these to be loaded to the browser every page load by appending an additional timestamp in the version information. This is useful during development and debugging where changes are being made to javascript and CSS files.','bool','','0',0,0,'Developer and Customization','',0,-100),('IPBXDBUGDISABLE','1','Disable IssabelPBX dbug Logging',2,'Set to true to stop all dbug() calls from writing to the Debug File (IPBXDBUGFILE)','bool','','1',0,0,'Developer and Customization','',0,-100),('IPBXDBUGFILE','/var/log/asterisk/issabelpbx_dbug','Debug File',2,'Full path and name of IssabelPBX debug file. Used by the dbug() function by developers.','text','','/var/log/asterisk/issabelpbx_dbug',0,0,'Developer and Customization','',0,-100),('IPBX_LOG_FILE','/var/log/asterisk/issabelpbx.log','IssabelPBX Log File',0,'Full path and name of the IssabelPBX Log File used in conjunction with the Syslog Level (AMPSYSLOGLEVEL) being set to FILE, not used otherwise. Initial installs may have some early logging sent to /tmp/issabelpbx_pre_install.log when it is first bootstrapping the installer.','text','','/var/log/asterisk/issabelpbx.log',0,0,'System Setup','',0,-150),('GENERATE_LEGACY_QUEUE_CODES','1','Generate queuenum*/** Login/off Codes',3,'Queue login and out codes were historically queunum* and queunum**. These have been largely replaced by the *45 queue toggle codes. The legacy codes are required to login or out a third party user that is not the extension dialing. These can be removed from the system by setting this to false.','bool','','1',0,0,'Queues Module','queues',0,120),('HTTPBINDADDRESS','0.0.0.0','HTTP Bind Address',2,'Address to bind to. Default is 0.0.0.0','text','','0.0.0.0',0,0,'Asterisk Builtin mini-HTTP server','',0,0),('HTTPBINDPORT','8088','HTTP Bind Port',2,'Port to bind to. Default is 8088','int','10,65536','8088',0,0,'Asterisk Builtin mini-HTTP server','',0,0),('HTTPENABLED','1','Enable the mini-HTTP Server',1,'Whether the Asterisk HTTP interface is enabled or not. This is for Asterisk, it is not directly related for IssabelPBX usage and the value of this setting is irrelevant for accessing core IssabelPBX settings. Default is no.','bool','','0',0,0,'Asterisk Builtin mini-HTTP server','',0,0),('HTTPENABLESTATIC','0','Enable Static Content',2,'Whether Asterisk should serve static content from http-static (HTML pages, CSS, javascript, etc.). Default is no.','bool','','0',0,0,'Asterisk Builtin mini-HTTP server','',0,0),('HTTPPREFIX','','HTTP Prefix',2,'HTTP Prefix allows you to specify a prefix for all requests to the server. For example, if the prefix is set to \"asterisk\" then all requests must begin with /asterisk. If this field is blank it is akin to saying all requests must being with /, essentially no prefix','text','','',0,0,'Asterisk Builtin mini-HTTP server','',1,0),('JQUERYUI_VER','1.8.9','jQuery UI Version',0,'The version of jQuery UI that we wish to use.','text','','1.8.9',1,1,'System Setup','',0,-100),('JQUERY_CSS','assets/css/jquery-ui.css','jQuery UI css',1,'css file for jquery ui','text','','assets/css/jquery-ui.css',1,1,'Styling and Logos','',0,320),('JQUERY_VER','1.7.1','jQuery Version',0,'The version of jQuery that we wish to use.','text','','1.7.1',1,1,'System Setup','',0,-100),('LOG_NOTIFICATIONS','1','Send Dashboard Notifications to Log',0,'When enabled all notification updates to the Dashboard notification panel will also be logged into the specified log file when enabled.','bool','','1',0,0,'System Setup','',0,-160),('LOG_OUT_MESSAGES','1','Log Verbose Messages',0,'IssabelPBX has many verbose and useful messages displayed to users during module installation, system installations, loading configurations and other places. In order to accumulate these messages in the log files as well as the on screen display, set this to true.','bool','','1',0,0,'System Setup','',0,-170),('mainstyle_css_generated','','Compressed Copy of Main CSS',10,'internal use','text','','',1,1,'Internal Use','',1,110),('MAXCALLS','','Dashboard Max Calls Initial Scale',2,'Use this to pre-set the scale for maximum calls on the Dashboard display. If not set, the the scale is dynamically sized based on the active calls on the system.','int','0,3000','',0,0,'GUI Behavior','dashboard',1,0),('MIXMON_DIR','','Override Call Recording Location',9,'Override the default location where asterisk will store call recordings. Be sure to set proper permissions on the directory for the asterisk user.','dir','','',1,0,'Directory Layout','',1,0),('MIXMON_FORMAT','wav','Call Recording Format',0,'Format to save recoreded calls for most call recording unless specified differently in specific applications.','select','wav,WAV,ulaw,ulaw,alaw,sln,gsm,g729','wav',0,0,'System Setup','',0,0),('MIXMON_POST','','Post Call Recording Script',9,'An optional script to be run after the call is hangup. You can include channel and MixMon variables like ${CALLFILENAME}, ${MIXMON_FORMAT} and ${MIXMON_DIR}. To ensure that you variables are properly escaped, use the following notation: ^{MY_VAR}','text','','',1,0,'Developer and Customization','',1,0),('MODULEADMINWGET','0','Use wget For Module Admin',0,'Module Admin normally tries to get its online information through direct file open type calls to URLs that go back to the issabel.org server. If it fails, typically because of content filters in firewalls that do not like the way PHP formats the requests, the code will fall back and try a wget to pull the information. This will often solve the problem. However, in such environment there can be a significant timeout before the failed file open calls to the URLs return and there are often 2-3 of these that occur. Setting this value will force IssabelPBX to avoid the attempt to open the URL and go straight to the wget calls.','bool','','0',0,0,'GUI Behavior','',0,-100),('MODULEADMIN_SKIP_CACHE','0','Disable Module Admin Caching',2,'Module Admin caches a copy of the online XML document that describes what is available on the server. Subsequent online update checks will use the cached information if it is less than 5 minutes old. To bypass the cache and force it to go to the server each time, set this to True. This should normally be false but can be helpful during testing.','bool','','0',1,0,'Developer and Customization','',0,-100),('MODULE_REPO','http://cloud.issabel.org,http://cloud2.issabel.org','Repo Server',10,'repo server','text','','http://cloud.issabel.org,http://cloud2.issabel.org',1,1,'Internal Use','',0,110),('MOHDIR','moh','MoH Subdirectory',4,'This is the subdirectory for the MoH files/directories which is located in ASTVARLIBDIR. Older installation may be using mohmp3 which was the old Asterisk default and should be set to that value if the music files are located there relative to the ASTVARLIBDIR.','select','moh,mohmp3','moh',1,0,'Directory Layout','',0,-100),('NOOPTRACE','0','NoOp Traces in Dialplan',0,'Some modules will generate lots of NoOp() commands proceeded by a [TRACE](trace_level) that can be used during development or while trying to trace call flows. These NoOp() commands serve no other purpose so if you do not want to see excessive NoOp()s in your dialplan you can set this to 0. The higher the number the more detailed level of trace NoOp()s will be generated','select','0,1,2,3,4,5,6,7,8,9,10','0',0,0,'Dialplan and Operational','',0,-100),('NOTICE_BROWSER_STATS','1','Browser Stats Notice',10,'Internal use to track if notice has been given that anonyous browser stats are being collected.','bool','','0',1,1,'Internal Use','',0,110),('OUTBOUND_CID_UPDATE','1','Display CallerID on Calling Phone',0,'When set to true and when CONNECTEDLINE() capabilities are configured and supported by your handset, the CID value being transmitted on this call will be updated on your handset in the CNAM field prepended with CID: so you know what is being presented to the caller if the outbound trunk supports and honors setting the transmitted CID.','bool','','1',0,0,'Dialplan and Operational','',0,0),('OUTBOUND_DIAL_UPDATE','1','Display Dialed Number on Calling Phone',0,'When set to true and when CONNECTEDLINE() capabilities are configured and supported by your handset, the number actually dialled will be updated on your handset in the CNUM field. This allows you to see the final manipulation of your number after outbound route and trunk dial manipulation rules have been applied. For example, if you have configured 7 digit dialing on a North America dialplan, the ultimate 10 or 11 digit transmission will be displayed back. Any \'Outbound Dial Prefixes\' configured at the trunk level will NOT be shown as these are foten analog line pauses (w) or other characters that distort the CNUM field on updates.','bool','','1',0,0,'Dialplan and Operational','',0,0),('PDFAUTHOR','www.issabel.org','tiff2pdf Author',0,'Author to pass to tiff2pdf\'s -a option','text','','www.issabel.org',1,1,'Styling and Logos','',0,0),('PHP_ERROR_HANDLER_OUTPUT','issabelpbxlog','PHP Error Log Output',0,'Where to send PHP errors, warnings and notices by the IssabelPBX PHP error handler. Set to \'dbug\', they will go to the Debug File regardless of whether dbug Loggin is disabled or not. Set to \'issabelpbxlog\' will send them to the IssabelPBX Log. Set to \'off\' and they will be ignored.','select','dbug,issabelpbxlog,off','issabelpbxlog',0,0,'System Setup','',0,-140),('POST_RELOAD','','POST_RELOAD Script',2,'Automatically execute a script after applying changes in the AMP admin. Set POST_RELOAD to the script you wish to execute after applying changes. If POST_RELOAD_DEBUG=true, you will see the output of the script in the web page.','text','','',1,0,'Developer and Customization','',1,-100),('POST_RELOAD_DEBUG','0','POST_RELOAD Debug Mode',2,'Display debug output for script used if POST_RELOAD is used.','bool','','0',0,0,'Developer and Customization','',0,-100),('PRE_RELOAD','','PRE_RELOAD Script',2,'Optional script to run just prior to doing an extension reload to Asterisk through the manager after pressing Apply Configuration Changes in the GUI.','text','','',1,0,'Developer and Customization','',1,-100),('QUEUES_EVENTS_MEMBER_STATUS_DEFAULT','0','Member Status Event Default',3,'Default state for AMI to emit the QueueMemberStatus event. This setting will only affect the default for NEW queues, it won\'t change existing queues or enfore the option on in new ones.','bool','','0',0,0,'Queues Module','queues',0,120),('QUEUES_EVENTS_WHEN_CALLED_DEFAULT','0','Agent Called Events Default',3,'Default state for AMI emit events related to an agent\'s call. This setting will only affect the default for NEW queues, it won\'t change existing queues or enfore the option on in new ones.','bool','','0',0,0,'Queues Module','queues',0,120),('QUEUES_HIDE_NOANSWER','1','Hide Queue No Answer Option',0,'It is possible for a queue to NOT Answer a call and still enter callers to the queue. The normal behavior is that all  allers are answered before entering the queue. If the call is not answered, it is possible that some early media delivery would still allow callers to hear recordings, MoH, etc. but this can be inconsistent and vary. Because of the volatility of this option, it is not displayed by default. If a queue is set to not answer, the setting will be displayed for that queue regardless of this setting.','bool','','1',0,0,'Queues Module','queues',0,50),('QUEUES_MIX_MONITOR','1','Use MixMonitor for Recordings',0,'Queues: monitor-type = MixMonitor. Setting true will use the MixMonitor application instead of Monitor so the concept of \'joining/mixing\' the in/out files now goes away when this is enabled.','bool','','1',0,0,'Queues Module','queues',0,40),('QUEUES_PESISTENTMEMBERS','1','Persistent Members',0,'Queues: persistentmembers. Store each dynamic member in each queue in the astdb so that when asterisk is restarted, each member will be automatically read into their recorded queues.','bool','','1',1,0,'Queues Module','queues',0,10),('QUEUES_SHARED_LASTCALL','1','Honor Wrapup Time Across Queues',0,'Queues: shared_lastcall, only valid with Asterisk 1.6+. This will make the lastcall and calls received be the same in members logged in more than one queue. This is useful to make the queue respect the wrapuptime of another queue for a shared member.','bool','','1',1,0,'Queues Module','queues',0,20),('QUEUES_UPDATECDR','0','Set Agent Name in CDR dstchannel',0,'Queues: updatecdr, only valid with Asterisk 1.6+. This option is implemented to mimic chan_agents behavior of populating CDR dstchannel field of a call with an agent name, which is set if available at the login time with AddQueueMember membername parameter, or with static members.','bool','','0',0,0,'Queues Module','queues',0,30),('REC_POLICY','caller','Call Recording Policy',0,'Call Recording Policy used to resove the winner in a conflict between two extensions when one wants a call recorded and the other does not, if both their priorities are also the same.','select','caller,callee','caller',0,0,'Dialplan and Operational','',0,-100),('RELOADCONFIRM','1','Require Confirm with Apply Changes',0,'When set to false, will bypass the confirm on Reload Box.','bool','','1',0,0,'GUI Behavior','',0,-100),('RINGTIMER','15','Ringtime Default',0,'Default number of seconds to ring phones before sending callers to voicemail or other extension destinations. This can be set per extension/user. Phones with no voicemail or other destination options will ring indefinitely.','select','0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120','15',0,0,'Dialplan and Operational','',1,0),('SERVERINTITLE','0','Include Server Name in Browser',0,'Precede browser title with the server name.','bool','','0',0,0,'GUI Behavior','',0,-100),('SHOWLANGUAGE','1','Show Language setting',0,'Show Language setting on menu . Defaults = false','bool','','1',0,0,'GUI Behavior','',0,-100),('SIPUSERAGENT','IPBX','SIP User Agent',10,'User Agent prefix','text','','IPBX',1,1,'Internal Use','',0,110),('SSHPORT','','Dashboard Non-Std SSH Port',2,'SSH port number configured on your system if not using the default port 22, this allows the dashboard monitoring to watch the poper port.','int','1,65535','',0,0,'System Setup','dashboard',1,0),('TCINTERVAL','60','Maintenance Polling Interval',1,'The polling interval in seconds used by the Time Conditions manintenace task, launched by an Asterisk call file used to update Time Conditions override states as well as keep custom device state hint values up-to-date when being used with BLF. A shorter interval will assure that BLF keys states are accurate. The interval should be less than the shortest configured span between two time condition states, so that a manual overide during such a period is properly reset when the new period starts.','select','60,120,180,240,300,600,900','60',0,0,'Time Condition Module','timeconditions',0,0),('TCMAINT','1','Enable Maintenance Polling',1,'If set to false, this will override the execution of the Time Conditons maintenace task launched by call files. If all the feature codes for time conditions are disabled, the maintenance task will not be launched anyhow. Setting this to false would be fairly un-common. You may want to set this temporarily if debugging a system to avoid the periodic dialplan running through the CLI that the maintenance task launches and can be distracting.','bool','','1',0,0,'Time Condition Module','timeconditions',0,0),('TIMEFORMAT','12 Hour Format','Speaking Clock Time Format',0,'Time format to use with the Speaking Clock.','select','12 Hour Format,24 Hour Format','12 Hour Format',0,0,'Dialplan and Operational','infoservices',0,0),('TONEZONE','us','Country Indication Tones',0,'Choose the country tonezone that you would like Asterisk to use when creating the different standard telephony tones such as ringing, busy, congetstion, etc.','fselect','a:54:{s:2:\"ao\";s:6:\"Angola\";s:2:\"ar\";s:9:\"Argentina\";s:2:\"au\";s:9:\"Australia\";s:2:\"at\";s:7:\"Austria\";s:2:\"be\";s:7:\"Belgium\";s:2:\"br\";s:6:\"Brazil\";s:2:\"bg\";s:8:\"Bulgaria\";s:2:\"cl\";s:5:\"Chile\";s:2:\"cn\";s:5:\"China\";s:2:\"co\";s:22:\"Colombia (Republic of)\";s:2:\"cr\";s:10:\"Costa Rica\";s:2:\"cz\";s:14:\"Czech Republic\";s:2:\"dk\";s:7:\"Denmark\";s:2:\"ee\";s:7:\"Estonia\";s:2:\"fi\";s:7:\"Finland\";s:2:\"fr\";s:6:\"France\";s:2:\"de\";s:7:\"Germany\";s:2:\"gr\";s:6:\"Greece\";s:2:\"hk\";s:9:\"Hong Kong\";s:2:\"hu\";s:7:\"Hungary\";s:2:\"in\";s:5:\"India\";s:2:\"ir\";s:4:\"Iran\";s:2:\"il\";s:6:\"Israel\";s:2:\"it\";s:5:\"Italy\";s:2:\"jp\";s:5:\"Japan\";s:2:\"ke\";s:19:\"Kenya (Republic of)\";s:2:\"lt\";s:9:\"Lithuania\";s:2:\"mo\";s:5:\"Macao\";s:2:\"my\";s:8:\"Malaysia\";s:2:\"mx\";s:6:\"Mexico\";s:2:\"nl\";s:11:\"Netherlands\";s:2:\"nz\";s:11:\"New Zealand\";s:2:\"no\";s:6:\"Norway\";s:2:\"pk\";s:8:\"Pakistan\";s:2:\"pa\";s:6:\"Panama\";s:3:\"phl\";s:11:\"Philippines\";s:2:\"pl\";s:6:\"Poland\";s:2:\"pt\";s:8:\"Portugal\";s:2:\"ro\";s:7:\"Romania\";s:2:\"ru\";s:18:\"Russian Federation\";s:2:\"sg\";s:9:\"Singapore\";s:2:\"za\";s:12:\"South Africa\";s:2:\"es\";s:5:\"Spain\";s:2:\"se\";s:6:\"Sweden\";s:2:\"ch\";s:11:\"Switzerland\";s:2:\"tw\";s:6:\"Taiwan\";s:2:\"tz\";s:29:\"Tanzania (United Republic of)\";s:2:\"th\";s:8:\"Thailand\";s:2:\"tr\";s:6:\"Turkey\";s:2:\"ug\";s:20:\"Uganda (Republic of)\";s:2:\"uk\";s:14:\"United Kingdom\";s:2:\"us\";s:29:\"United States / North America\";s:6:\"us-old\";s:39:\"United States Circa 1950/ North America\";s:2:\"ve\";s:25:\"Venezuela / South America\";}','us',0,0,'Dialplan and Operational','',1,0),('TRANSFER_CONTEXT','from-internal-xfer','Asterisk TRANSFER_CONTEXT Variable',9,'This is the Asterisk Channel Variable TRANSFER_CONTEXT. In general it should NOT be changed unless you really know what you are doing. It is used to do create slightly different \'views\' when a call is being transfered. An example is hiding the paging groups so a call isn\'t accidentally transfered into a page.','text','','from-internal-xfer',1,1,'Dialplan and Operational','',1,-100),('TRUNK_OPTIONS','T','Asterisk Outbound Trunk Dial Options',0,'Options to be passed to the Asterisk Dial Command when making outbound calls on your trunks when not part of an Intra-Company Route. The options are documented in Asterisk documentation, a subset of which are described here. The default options T and t allow the calling and called users to transfer a call with ##. It is HIGHLY DISCOURAGED to use the r option here as this will prevent early media from being delivered from the PSTN and can result in the inability to interact with some external IVRs','text','','T',0,0,'Dialplan and Operational','',1,0),('TRUNK_RING_TIMER','300','Trunk Dial Timeout',2,'How many seconds to try a call on your trunks before giving up. This should normally be a very long time and is usually only changed if you have some sort of problematic trunks. This is the Asterisk Dial Command timeout parameter.','int','0,86400','300',1,0,'Dialplan and Operational','',0,-100),('USEDEVSTATE','1','Enable Custom Device States',0,'If this is set, it assumes that you are running Asterisk 1.4 or higher and want to take advantage of the func_devstate.c backport available from Asterisk 1.6. This allows custom hints to be created to support BLF for server side feature codes such as daynight, followme, etc','bool','','1',0,0,'Dialplan and Operational','',0,-100),('USEGOOGLEDNSFORENUM','0','Use Google DNS for Enum',2,'Setting this flag will generate the required global variable so that enumlookup.agi will use Google DNS 8.8.8.8 when performing an ENUM lookup. Not all DNS deals with NAPTR record, but Google does. There is a drawback to this as Google tracks every lookup. If you are not comfortable with this, do not enable this setting. Please read Google FAQ about this: <b>http://code.google.com/speed/public-dns/faq.html#privacy</b>.','bool','','0',0,0,'Dialplan and Operational','',0,-100),('USEQUEUESTATE','0','Asterisk Queues Patch 15168 Installed',3,'Setting this flag will generate the required dialplan to integrate with the following Asterisk patch: <b>https://issues.asterisk.org/view.php?id=15168</b>. This setting is obsolete on Asterisk 1.8+ systems where the hint state is now standard and always used. This asterisk patch is only available on Asterisk 1.4, trying to use this setting on Asterisk 1.6 will break some queue behavior and should be avoided','bool','','0',0,0,'Queues Module','queues',0,100),('USERESMWIBLF','0','Create Voicemail Hints',3,'Setting this flag with generate the required dialplan to integrate with res_mwi_blf which is included with the Official IssabelPBX Distro. It allows users to subscribe to other voicemail box and be notified via BLF of changes.','bool','','0',0,0,'Voicemail Module','voicemail',0,100),('USE_GOOGLE_CDN_JS','0','Use Google Distribution Network for js Downloads',0,'Setting this to true will fetch system javascript libraries such as jQuery and jQuery-ui from ajax.googleapis.com. This can be advantageous if accessing remote or multiple different IssabelPBX systems since the libraries are only cached once in your browser. If external internet connections are problematic, setting this true could result in slow systems. IssabelPBX will always fallback to the locally available libraries if the CDN is not available.','bool','','0',0,0,'System Setup','',0,-100),('USE_ISSABELPBX_MENU_CONF','0','Use issabelpbx_menu.conf Configuration',0,'When set to true, the system will check for a issabelpbx_menu.conf file amongst the normal configuraiton files and if found, it will be used to define and remap the menu tabs and contents. See the template supplied with IssabelPBX for details on how to do this.','bool','','0',0,0,'GUI Behavior','',0,-100),('USE_PACKAGED_JS','1','Use Packaged Javascript Library ',2,'IssabelPBX packages several javascript libraries and components into a compressed file called libissabelpbx.javascript.js. By default this will be loaded instead of the individual uncompressed libraries. Setting this to false will force IssabelPBX to load all the libraries as individual uncompressed files. This is useful during development and debugging.','bool','','1',0,0,'Developer and Customization','',0,-100),('VIEW_BAD_REFFERER','views/bad_refferer.php','View: bad_refferer.php',1,'bad_refferer.php view. This should never be changed except for very advanced layout changes.','text','','views/bad_refferer.php',1,1,'Styling and Logos','',0,270),('VIEW_BETA_NOTICE','views/beta_notice.php','View: beta_notice.php',1,'beta_notice.php view. This should never be changed except for very advanced layout changes','text','','views/beta_notice.php',1,1,'Styling and Logos','',0,312),('VIEW_FOOTER','views/footer.php','View: issabelpbx.php',1,'footer.php view. This should never be changed except for very advanced layout changes','text','','views/footer.php',1,1,'Styling and Logos','',0,350),('VIEW_FOOTER_CONTENT','views/footer_content.php','View: footer_content.php',1,'footer_content.php view. This should never be changed except for very advanced layout changes','text','','views/footer_content.php',1,1,'Styling and Logos','',0,360),('VIEW_HEADER','views/header.php','View: header.php',1,'header.php view. This should never be changed except for very advanced layout changes','text','','views/header.php',1,1,'Styling and Logos','',0,340),('VIEW_ISSABELPBX','views/issabelpbx.php','View: issabelpbx.php',1,'issabelpbx.php view. This should never be changed except for very advanced layout changes.','text','','views/issabelpbx.php',1,1,'Styling and Logos','',0,190),('VIEW_ISSABELPBX_ADMIN','views/issabelpbx_admin.php','View: issabelpbx_admin.php',1,'issabelpbx_admin.php view. This should never be changed except for very advanced layout changes.','text','','views/issabelpbx_admin.php',1,1,'Styling and Logos','',0,180),('VIEW_ISSABELPBX_RELOAD','views/issabelpbx_reload.php','View: issabelpbx_reload.php',1,'issabelpbx_reload.php view. This should never be changed except for very advanced layout changes.','text','','views/issabelpbx_reload.php',1,1,'Styling and Logos','',0,200),('VIEW_ISSABELPBX_RELOADBAR','views/issabelpbx_reloadbar.php','View: issabelpbx_reloadbar.php',1,'issabelpbx_reloadbar.php view. This should never be changed except for very advanced layout changes.','text','','views/issabelpbx_reloadbar.php',1,1,'Styling and Logos','',0,210),('VIEW_LOGGEDOUT','views/loggedout.php','View: loggedout.php',1,'loggedout.php view. This should never be changed except for very advanced layout changes.','text','','views/loggedout.php',1,1,'Styling and Logos','',0,280),('VIEW_LOGIN','views/login.php','View: login.php',1,'login.php view. This should never be changed except for very advanced layout changes','text','','views/login.php',1,1,'Styling and Logos','',0,330),('VIEW_MENU','views/menu.php','View: menu.php',1,'menu.php view. This should never be changed except for very advanced layout changes','text','','views/menu.php',1,1,'Styling and Logos','',0,310),('VIEW_MENUITEM_DISABLED','views/menuitem_disabled.php','View: menuitem_disabled.php',1,'menuitem_disabled.php view. This should never be changed except for very advanced layout changes.','text','','views/menuitem_disabled.php',1,1,'Styling and Logos','',0,240),('VIEW_NOACCESS','views/noaccess.php','View: noaccess.php',1,'noaccess.php view. This should never be changed except for very advanced layout changes.','text','','views/noaccess.php',1,1,'Styling and Logos','',0,250),('VIEW_OBE','views/obe.php','View: obe.php',1,'obe.php view. This should never be changed except for very advanced layout changes','text','','views/obe.php',1,1,'Styling and Logos','',0,310),('VIEW_PANEL','views/panel.php','View: panel.php',1,'panel.php view. This should never be changed except for very advanced layout changes.','text','','views/panel.php',1,1,'Styling and Logos','',0,290),('VIEW_POPOVER_JS','views/popover_js.php','View: popover_js.php',1,'popover_js.php view. This should never be changed except for very advanced layout changes','text','','views/popover_js.php',1,1,'Styling and Logos','',0,355),('VIEW_REPORTS','views/reports.php','View: reports.php',1,'reports.php view. This should never be changed except for very advanced layout changes.','text','','views/reports.php',1,1,'Styling and Logos','',0,300),('VIEW_UNAUTHORIZED','views/unauthorized.php','View: unauthorized.php',1,'unauthorized.php view. This should never be changed except for very advanced layout changes.','text','','views/unauthorized.php',1,1,'Styling and Logos','',0,260),('VIEW_WELCOME','views/welcome.php','View: welcome.php',1,'welcome.php view. This should never be changed except for very advanced layout changes.','text','','views/welcome.php',1,1,'Styling and Logos','',0,220),('VIEW_WELCOME_NONMANAGER','views/welcome_nomanager.php','View: welcome_nomanager.php',1,'welcome_nomanager.php view. This should never be changed except for very advanced layout changes.','text','','views/welcome_nomanager.php',1,1,'Styling and Logos','',0,230),('VMX_CONTEXT','from-internal','VMX Default Context',9,'Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this.','text','','from-internal',1,0,'VmX Locater','',0,0),('VMX_LOOPDEST_CONTEXT','','VMX Default Loop Exceed Context',9,'Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this. The default location that a caller will be sent if they press an invalid options too many times, as defined by the Maximum Loops count.','text','','',1,0,'VmX Locater','',1,0),('VMX_LOOPDEST_EXT','dovm','VMX Default Loop Exceed Extension',9,'Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this. The default location that a caller will be sent if they press an invalid options too many times, as defined by the Maximum Loops count.','text','','dovm',1,0,'VmX Locater','',0,0),('VMX_LOOPDEST_PRI','1','VMX Default Loop Exceed Priority',9,'Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this. The default location that a caller will be sent if they press an invalid options too many times, as defined by the Maximum Loops count.','int','1,1000','1',1,0,'VmX Locater','',0,0),('VMX_PRI','1','VMX Default Priority',9,'Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this.','int','1,1000','1',1,0,'VmX Locater','',0,0),('VMX_TIMEDEST_CONTEXT','','VMX Default Timeout Context',9,'Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this. The default location that a caller will be sent if they don\'t press any key (timeout) or press # which is interpreted as a timeout. Set this to \'dovm\' to go to voicemail (default).','text','','',1,0,'VmX Locater','',1,0),('VMX_TIMEDEST_EXT','dovm','VMX Default Timeout Extension',9,'Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this. The default location that a caller will be sent if they don\'t press any key (timeout) or press # which is interpreted as a timeout. Set this to \'dovm\' to go to voicemail (default).','text','','dovm',1,0,'VmX Locater','',0,0),('VMX_TIMEDEST_PRI','1','VMX Default Timeout Priority',9,'Used to do extremely advanced and customized changes to the macro-vm VmX locater. Check the dialplan for a thorough understanding of how to use this. The default location that a caller will be sent if they don\'t press any key (timeout) or press # which is interpreted as a timeout. Set this to \'dovm\' to go to voicemail (default).','int','1,1000','1',1,0,'VmX Locater','',0,0),('VM_SHOW_IMAP','0','Provide IMAP Voicemail Fields',3,'Installations that have configured Voicemail with IMAP should set this to true so that the IMAP username and password fields are provided in the Voicemail setup screen for extensions. If an extension alread has these fields populated, they will be displayed even if this is set to false.','bool','','0',0,0,'Voicemail Module','voicemail',0,100),('WHICH_rm','/bin/rm','Path for rm',2,'The path to rm as auto-determined by the system. Overwrite as necessary.','text','','/bin/rm',1,0,'System Apps','',1,0),('XTNCONFLICTABORT','0','Abort Config Gen on Exten Conflict',3,'Setting either of these to true will result in retrieve_conf aborting during a reload if an extension conflict is detected or a destination is detected. It is usually better to allow the reload to go through and then correct the problem but these can be set if a more strict behavior is desired.','bool','','0',0,0,'GUI Behavior','',0,-100),('ZAP2DAHDICOMPAT','1','Convert ZAP Settings to DAHDi',0,'If set to true, IssabelPBX will check if you have chan_dahdi installed. If so, it will automatically use all your ZAP configuration settings (devices and trunks) and silently convert them, under the covers, to DAHDi so no changes are needed. The GUI will continue to refer to these as ZAP but it will use the proper DAHDi channels. This will also keep Zap Channel DIDs working.','bool','','0',1,0,'Dialplan and Operational','',0,-100),('HTTPSBINDADDRESS','0.0.0.0:8089','HTTPS Bind Address/Port',2,'Address and port to bind to for HTTPS. Default is 127.0.0.1:8089','text','','127.0.0.1:8089',0,0,'Asterisk Builtin mini-HTTP server','',0,0),('HTTPSCERTFILE','/etc/asterisk/keys/asterisk.pem','Certificate file',2,'Full path to certificate file for HTTPS.','text','','/etc/asterisk/keys/asterisk.pem',0,0,'Asterisk Builtin mini-HTTP server','',0,0),('HTTPSENABLED','1','Enable HTTPS support for the mini-HTTP Server',1,'Whether to enable HTTPS support for the Asterisk HTTP interface. Default is no.','bool','','0',0,0,'Asterisk Builtin mini-HTTP server','',0,0),('HTTPSPRIVATEKEY','','Private key file',2,'Full path to private key file for HTTPS. If empty, default is to look into certificate file for private key.','text','','',0,0,'Asterisk Builtin mini-HTTP server','',1,0);
/*!40000 ALTER TABLE `issabelpbx_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ivr_details`
--

DROP TABLE IF EXISTS `ivr_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivr_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `description` varchar(150) DEFAULT NULL,
  `announcement` varchar(25) DEFAULT NULL,
  `directdial` varchar(50) DEFAULT NULL,
  `invalid_loops` varchar(10) DEFAULT NULL,
  `invalid_retry_recording` varchar(25) DEFAULT NULL,
  `invalid_destination` varchar(50) DEFAULT NULL,
  `invalid_recording` varchar(25) DEFAULT NULL,
  `retvm` varchar(8) DEFAULT NULL,
  `timeout_time` int(11) DEFAULT NULL,
  `timeout_recording` varchar(25) DEFAULT NULL,
  `timeout_retry_recording` varchar(25) DEFAULT NULL,
  `timeout_destination` varchar(50) DEFAULT NULL,
  `timeout_loops` varchar(11) DEFAULT NULL,
  `timeout_append_announce` tinyint(1) NOT NULL DEFAULT '1',
  `invalid_append_announce` tinyint(1) NOT NULL DEFAULT '1',
  `timeout_ivr_ret` tinyint(1) NOT NULL DEFAULT '0',
  `invalid_ivr_ret` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ivr_details`
--

LOCK TABLES `ivr_details` WRITE;
/*!40000 ALTER TABLE `ivr_details` DISABLE KEYS */;
INSERT INTO `ivr_details` VALUES (1,'Unnamed',NULL,NULL,'ext-local','disabled','','','','',10,'','','','disabled',1,1,0,0);
/*!40000 ALTER TABLE `ivr_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ivr_entries`
--

DROP TABLE IF EXISTS `ivr_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivr_entries` (
  `ivr_id` int(11) NOT NULL,
  `selection` varchar(10) DEFAULT NULL,
  `dest` varchar(50) DEFAULT NULL,
  `ivr_ret` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ivr_entries`
--

LOCK TABLES `ivr_entries` WRITE;
/*!40000 ALTER TABLE `ivr_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `ivr_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `language_incoming`
--

DROP TABLE IF EXISTS `language_incoming`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `language_incoming` (
  `extension` varchar(50) DEFAULT NULL,
  `cidnum` varchar(50) DEFAULT NULL,
  `language` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `language_incoming`
--

LOCK TABLES `language_incoming` WRITE;
/*!40000 ALTER TABLE `language_incoming` DISABLE KEYS */;
/*!40000 ALTER TABLE `language_incoming` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `language_id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_code` varchar(50) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `dest` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`language_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logfile_logfiles`
--

DROP TABLE IF EXISTS `logfile_logfiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logfile_logfiles` (
  `name` varchar(25) NOT NULL DEFAULT '',
  `debug` varchar(25) DEFAULT NULL,
  `dtmf` varchar(25) DEFAULT NULL,
  `error` varchar(25) DEFAULT NULL,
  `fax` varchar(25) DEFAULT NULL,
  `notice` varchar(25) DEFAULT NULL,
  `verbose` varchar(25) DEFAULT NULL,
  `warning` varchar(25) DEFAULT NULL,
  `security` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logfile_logfiles`
--

LOCK TABLES `logfile_logfiles` WRITE;
/*!40000 ALTER TABLE `logfile_logfiles` DISABLE KEYS */;
INSERT INTO `logfile_logfiles` VALUES ('full','on','off','on','off','on','on','on','off'),('console','on','off','on','off','on','on','on','off'),('messages','off','off','on','off','on','off','on','on');
/*!40000 ALTER TABLE `logfile_logfiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logfile_settings`
--

DROP TABLE IF EXISTS `logfile_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logfile_settings` (
  `key` varchar(100) NOT NULL DEFAULT '',
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logfile_settings`
--

LOCK TABLES `logfile_settings` WRITE;
/*!40000 ALTER TABLE `logfile_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `logfile_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `manager`
--

DROP TABLE IF EXISTS `manager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `manager` (
  `manager_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(15) NOT NULL,
  `secret` varchar(50) DEFAULT NULL,
  `deny` varchar(255) DEFAULT NULL,
  `permit` varchar(255) DEFAULT NULL,
  `read` varchar(255) DEFAULT NULL,
  `write` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`manager_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `manager`
--

LOCK TABLES `manager` WRITE;
/*!40000 ALTER TABLE `manager` DISABLE KEYS */;
/*!40000 ALTER TABLE `manager` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `managersettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `managersettings` (
  `keyword` varchar(50) NOT NULL DEFAULT '',
  `data` varchar(255) NOT NULL DEFAULT '',
  `seq` tinyint(1) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`keyword`,`seq`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `managersettings`
--

LOCK TABLES `managersettings` WRITE;
/*!40000 ALTER TABLE `managersettings` DISABLE KEYS */;
/*!40000 ALTER TABLE `managersettings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meetme`
--

DROP TABLE IF EXISTS `meetme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meetme` (
  `exten` varchar(50) NOT NULL,
  `options` varchar(15) DEFAULT NULL,
  `userpin` varchar(50) DEFAULT NULL,
  `adminpin` varchar(50) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `joinmsg_id` int(11) DEFAULT NULL,
  `music` varchar(80) DEFAULT NULL,
  `users` tinyint(4) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meetme`
--

LOCK TABLES `meetme` WRITE;
/*!40000 ALTER TABLE `meetme` DISABLE KEYS */;
/*!40000 ALTER TABLE `meetme` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `miscapps`
--

DROP TABLE IF EXISTS `miscapps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `miscapps` (
  `miscapps_id` int(11) NOT NULL AUTO_INCREMENT,
  `ext` varchar(50) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `dest` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`miscapps_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `miscapps`
--

LOCK TABLES `miscapps` WRITE;
/*!40000 ALTER TABLE `miscapps` DISABLE KEYS */;
/*!40000 ALTER TABLE `miscapps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `miscdests`
--

DROP TABLE IF EXISTS `miscdests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `miscdests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `destdial` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `miscdests`
--

LOCK TABLES `miscdests` WRITE;
/*!40000 ALTER TABLE `miscdests` DISABLE KEYS */;
/*!40000 ALTER TABLE `miscdests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `module_xml`
--

DROP TABLE IF EXISTS `module_xml`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_xml` (
  `id` varchar(20) NOT NULL DEFAULT 'xml',
  `time` int(11) NOT NULL DEFAULT '0',
  `data` mediumblob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `module_xml`
--

LOCK TABLES `module_xml` WRITE;
/*!40000 ALTER TABLE `module_xml` DISABLE KEYS */;
INSERT INTO `module_xml` VALUES ('mod_serialized',1619991695,'a:68:{s:7:\"builtin\";a:13:{s:7:\"rawname\";s:7:\"builtin\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Basic\";s:4:\"name\";s:7:\"Builtin\";s:7:\"version\";s:7:\"2.3.0.2\";s:10:\"candisable\";s:2:\"no\";s:12:\"canuninstall\";s:2:\"no\";s:9:\"menuitems\";a:1:{s:8:\"modules1\";s:12:\"Module Admin\";}s:11:\"displayname\";s:7:\"Builtin\";s:5:\"items\";a:1:{s:8:\"modules1\";a:5:{s:4:\"name\";s:12:\"Module Admin\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;s:7:\"display\";s:7:\"modules\";}}s:6:\"status\";i:2;s:4:\"repo\";s:5:\"local\";s:7:\"license\";s:0:\"\";}s:3:\"ivr\";a:18:{s:7:\"rawname\";s:3:\"ivr\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:3:\"IVR\";s:7:\"version\";s:9:\"2.11.0.12\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:20:\"Inbound Call Control\";s:11:\"description\";s:219:\"Creates Digital Receptionist (aka Auto-Attendant, aka Interactive Voice Response) menus. These can be used to send callers to different locations (eg, Press 1 for sales) and/or allow direct-dialing of extension numbers.\";s:7:\"depends\";a:2:{s:7:\"version\";s:11:\"2.5.0alpha1\";s:6:\"module\";a:2:{i:0;s:19:\"recordings ge 3.3.8\";i:1;s:22:\"framework ge 2.11.0.47\";}}s:9:\"menuitems\";a:1:{s:3:\"ivr\";s:3:\"IVR\";}s:8:\"popovers\";a:1:{s:3:\"ivr\";a:2:{s:7:\"display\";s:3:\"ivr\";s:6:\"action\";s:3:\"add\";}}s:9:\"supported\";a:1:{s:7:\"version\";s:6:\"2.11.0\";}s:11:\"displayname\";s:3:\"IVR\";s:5:\"items\";a:1:{s:3:\"ivr\";a:4:{s:4:\"name\";s:3:\"IVR\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:9:\"2.11.0.12\";}s:7:\"javassh\";a:15:{s:7:\"rawname\";s:7:\"javassh\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:8:\"Java SSH\";s:7:\"version\";s:6:\"2.11.2\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:7:\"AGPLv3+\";s:11:\"licenselink\";s:40:\"http://www.gnu.org/licenses/agpl-3.0.txt\";s:8:\"category\";s:5:\"Admin\";s:11:\"description\";s:60:\"Provides a Java applet to access the system shell using SSH.\";s:9:\"menuitems\";a:1:{s:7:\"javassh\";s:8:\"Java SSH\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:8:\"Java SSH\";s:5:\"items\";a:1:{s:7:\"javassh\";a:4:{s:4:\"name\";s:8:\"Java SSH\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:6:\"2.11.2\";}s:7:\"pinsets\";a:18:{s:7:\"rawname\";s:7:\"pinsets\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:8:\"PIN Sets\";s:7:\"version\";s:8:\"2.11.0.9\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:8:\"Settings\";s:13:\"embedcategory\";s:34:\"Internal Options \n&\n Configuration\";s:11:\"description\";s:103:\"Allow creation of lists of PINs (numbers for passwords) that can be used by other modules (eg, trunks).\";s:9:\"menuitems\";a:1:{s:7:\"pinsets\";s:8:\"PIN Sets\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:7:\"depends\";a:1:{s:6:\"module\";s:4:\"core\";}s:11:\"displayname\";s:8:\"PIN Sets\";s:7:\"methods\";a:1:{s:10:\"get_config\";a:1:{i:481;a:1:{i:0;s:18:\"pinsets_get_config\";}}}s:5:\"items\";a:1:{s:7:\"pinsets\";a:4:{s:4:\"name\";s:8:\"PIN Sets\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Settings\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.9\";}s:11:\"conferences\";a:18:{s:7:\"rawname\";s:11:\"conferences\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:11:\"Conferences\";s:7:\"version\";s:8:\"2.11.0.6\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:34:\"Internal Options \n&\n Configuration\";s:11:\"description\";s:85:\"Allow creation of conference rooms (meet-me) where multiple people can talk together.\";s:7:\"depends\";a:2:{s:7:\"version\";s:11:\"2.5.0alpha1\";s:6:\"module\";s:19:\"recordings ge 3.3.8\";}s:9:\"menuitems\";a:1:{s:11:\"conferences\";s:11:\"Conferences\";}s:8:\"popovers\";a:1:{s:11:\"conferences\";a:1:{s:7:\"display\";s:11:\"conferences\";}}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:11:\"Conferences\";s:5:\"items\";a:1:{s:11:\"conferences\";a:4:{s:4:\"name\";s:11:\"Conferences\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.6\";}s:14:\"customcontexts\";a:16:{s:7:\"rawname\";s:14:\"customcontexts\";s:4:\"repo\";s:11:\"unsupported\";s:4:\"name\";s:16:\"Class of Service\";s:7:\"version\";s:8:\"2.11.0.2\";s:8:\"category\";s:12:\"Connectivity\";s:13:\"embedcategory\";s:5:\"Basic\";s:7:\"license\";s:6:\"GPLv2+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-2.0.txt\";s:11:\"description\";s:506:\"Creates custom contexts which can be used to allow limited access to dialplan applications. Allows for time restrictions on any dialplan access. Allows for pattern matching to allow/deny. Allows for failover destinations, and PIN protected failover. This can be very useful for multi-tennant systems. Inbound routing can be done using DID or zap channel routing, this module allows for selective outbound routing. House/public phones can be placed in a restricted context allowing them only internal calls.\";s:9:\"menuitems\";a:2:{s:14:\"customcontexts\";s:16:\"Class of Service\";s:19:\"customcontextsadmin\";s:22:\"Class of Service Admin\";}s:7:\"depends\";a:2:{s:7:\"version\";s:11:\"2.8.0alpha1\";s:6:\"module\";a:2:{i:0;s:4:\"core\";i:1;s:14:\"timeconditions\";}}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:16:\"Class of Service\";s:5:\"items\";a:2:{s:14:\"customcontexts\";a:4:{s:4:\"name\";s:16:\"Class of Service\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Connectivity\";s:4:\"sort\";i:0;}s:19:\"customcontextsadmin\";a:4:{s:4:\"name\";s:22:\"Class of Service Admin\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Connectivity\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.2\";}s:8:\"miscapps\";a:17:{s:7:\"rawname\";s:8:\"miscapps\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:17:\"Misc Applications\";s:7:\"version\";s:8:\"2.11.0.2\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:34:\"Internal Options \n&\n Configuration\";s:11:\"description\";s:108:\"Adds the ability to create feature codes that can go to any IssabelPBX destination (such as an IVR or queue)\";s:9:\"menuitems\";a:1:{s:8:\"miscapps\";s:17:\"Misc Applications\";}s:7:\"depends\";a:1:{s:7:\"version\";s:5:\"2.4.0\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:17:\"Misc Applications\";s:5:\"items\";a:1:{s:8:\"miscapps\";a:4:{s:4:\"name\";s:17:\"Misc Applications\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.2\";}s:8:\"daynight\";a:18:{s:7:\"rawname\";s:8:\"daynight\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:17:\"Call Flow Control\";s:7:\"version\";s:8:\"2.11.0.6\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:20:\"Inbound Call Control\";s:11:\"description\";s:146:\"Call Flow manual toggle control - allows for two destinations to be chosen and provides a feature code		that toggles between the two destinations.\";s:7:\"depends\";a:1:{s:7:\"version\";s:11:\"2.5.0alpha1\";}s:9:\"menuitems\";a:1:{s:8:\"daynight\";s:17:\"Call Flow Control\";}s:8:\"popovers\";a:1:{s:8:\"daynight\";a:1:{s:7:\"display\";s:8:\"daynight\";}}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:17:\"Call Flow Control\";s:5:\"items\";a:1:{s:8:\"daynight\";a:5:{s:4:\"name\";s:17:\"Call Flow Control\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;s:13:\"needsenginedb\";s:3:\"yes\";}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.6\";}s:7:\"parking\";a:17:{s:7:\"rawname\";s:7:\"parking\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:11:\"Parking Lot\";s:7:\"version\";s:9:\"2.11.0.16\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:34:\"Internal Options \n&\n Configuration\";s:11:\"description\";s:142:\"Manages parking lot extensions and other options.    Parking is a way of putting calls \"on hold\", and then picking them up from any extension.\";s:9:\"menuitems\";a:1:{s:7:\"parking\";s:7:\"Parking\";}s:7:\"depends\";a:3:{s:6:\"engine\";s:15:\"asterisk ge 1.8\";s:7:\"version\";s:4:\"2.11\";s:6:\"module\";s:17:\"core ge 2.11.0.48\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.11\";}s:11:\"displayname\";s:11:\"Parking Lot\";s:5:\"items\";a:1:{s:7:\"parking\";a:4:{s:4:\"name\";s:7:\"Parking\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:9:\"2.11.0.16\";}s:10:\"ringgroups\";a:18:{s:7:\"rawname\";s:10:\"ringgroups\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:11:\"Ring Groups\";s:7:\"version\";s:8:\"2.11.0.6\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:20:\"Inbound Call Control\";s:11:\"description\";s:317:\"Creates a group of extensions that all ring together. Extensions can be rung all at once, or in various \'hunt\' configurations. Additionally, external numbers are supported, and there is a call confirmation option where the callee has to confirm if they actually want to take the call before the caller is transferred.\";s:7:\"depends\";a:2:{s:7:\"version\";s:11:\"2.5.0alpha1\";s:6:\"module\";s:19:\"recordings ge 3.3.8\";}s:9:\"menuitems\";a:1:{s:10:\"ringgroups\";s:11:\"Ring Groups\";}s:8:\"popovers\";a:1:{s:10:\"ringgroups\";a:1:{s:7:\"display\";s:10:\"ringgroups\";}}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.11\";}s:11:\"displayname\";s:11:\"Ring Groups\";s:5:\"items\";a:1:{s:10:\"ringgroups\";a:4:{s:4:\"name\";s:11:\"Ring Groups\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.6\";}s:17:\"dialplaninjection\";a:16:{s:7:\"rawname\";s:17:\"dialplaninjection\";s:4:\"name\";s:18:\"Dialplan Injection\";s:7:\"version\";s:6:\"0.1.1n\";s:4:\"type\";s:5:\"setup\";s:9:\"publisher\";s:5:\"POSSA\";s:8:\"category\";s:8:\"Advanced\";s:13:\"embedcategory\";s:8:\"Advanced\";s:11:\"description\";s:77:\"Acts as a dialplan destination and can execute a variety of Asterisk commands\";s:9:\"menuitems\";a:1:{s:17:\"dialplaninjection\";s:18:\"Dialplan Injection\";}s:7:\"depends\";a:1:{s:6:\"module\";s:4:\"core\";}s:11:\"displayname\";s:18:\"Dialplan Injection\";s:5:\"items\";a:1:{s:17:\"dialplaninjection\";a:4:{s:4:\"name\";s:18:\"Dialplan Injection\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Advanced\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:4:\"repo\";s:5:\"local\";s:9:\"dbversion\";s:6:\"0.1.1n\";s:7:\"license\";s:0:\"\";}s:7:\"restart\";a:16:{s:7:\"rawname\";s:7:\"restart\";s:4:\"repo\";s:11:\"unsupported\";s:4:\"name\";s:18:\"Bulk Phone Restart\";s:7:\"version\";s:8:\"2.11.0.2\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:5:\"Admin\";s:7:\"depends\";a:1:{s:7:\"version\";s:5:\"2.5.0\";}s:11:\"description\";s:147:\"This module allows users to restart one or multiple phones that support being restarted via a SIP NOTIFY command through Asterisk\'s sip_notify.conf\";s:9:\"menuitems\";a:1:{s:7:\"restart\";s:13:\"Phone Restart\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:18:\"Bulk Phone Restart\";s:5:\"items\";a:1:{s:7:\"restart\";a:4:{s:4:\"name\";s:13:\"Phone Restart\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.2\";}s:11:\"outroutemsg\";a:17:{s:7:\"rawname\";s:11:\"outroutemsg\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:25:\"Route Congestion Messages\";s:7:\"version\";s:8:\"2.11.0.3\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:8:\"Settings\";s:13:\"embedcategory\";s:8:\"Advanced\";s:11:\"description\";s:154:\"Configures message or congestion tones played when all trunks are busy in a route. Allows different messages for Emergency Routes and Intra-Company Routes\";s:9:\"menuitems\";a:1:{s:11:\"outroutemsg\";s:25:\"Route Congestion Messages\";}s:7:\"depends\";a:1:{s:6:\"module\";s:19:\"recordings ge 3.3.8\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:25:\"Route Congestion Messages\";s:5:\"items\";a:1:{s:11:\"outroutemsg\";a:4:{s:4:\"name\";s:25:\"Route Congestion Messages\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Settings\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.3\";}s:9:\"speeddial\";a:13:{s:7:\"rawname\";s:9:\"speeddial\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:20:\"Speed Dial Functions\";s:7:\"version\";s:8:\"2.11.0.4\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:8:\"Settings\";s:7:\"depends\";a:1:{s:6:\"module\";s:9:\"phonebook\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:20:\"Speed Dial Functions\";s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.4\";}s:3:\"cdr\";a:16:{s:7:\"rawname\";s:3:\"cdr\";s:4:\"repo\";s:8:\"standard\";s:11:\"description\";s:63:\"Call Data Record report tools for viewing reports of your calls\";s:4:\"name\";s:11:\"CDR Reports\";s:7:\"version\";s:9:\"2.11.0.12\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:7:\"Reports\";s:9:\"menuitems\";a:1:{s:3:\"cdr\";s:11:\"CDR Reports\";}s:7:\"depends\";a:1:{s:6:\"module\";s:13:\"core ge 2.6.0\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.11\";}s:11:\"displayname\";s:11:\"CDR Reports\";s:5:\"items\";a:1:{s:3:\"cdr\";a:4:{s:4:\"name\";s:11:\"CDR Reports\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:7:\"Reports\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:9:\"2.11.0.12\";}s:11:\"pbdirectory\";a:14:{s:7:\"rawname\";s:11:\"pbdirectory\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:19:\"Phonebook Directory\";s:7:\"version\";s:8:\"2.11.0.5\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:5:\"Admin\";s:11:\"description\";s:55:\"Provides a dial-by-name directory for phonebook entries\";s:7:\"depends\";a:2:{s:7:\"version\";s:5:\"2.4.0\";s:6:\"module\";a:2:{i:0;s:9:\"phonebook\";i:1;s:9:\"speeddial\";}}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:19:\"Phonebook Directory\";s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.5\";}s:9:\"phonebook\";a:15:{s:7:\"rawname\";s:9:\"phonebook\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:9:\"Phonebook\";s:7:\"version\";s:8:\"2.11.0.2\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:11:\"description\";s:95:\"Provides a phonebook for IssabelPBX, it can be used as base for Caller ID Lookup and Speed Dial\";s:8:\"category\";s:5:\"Admin\";s:9:\"menuitems\";a:1:{s:9:\"phonebook\";s:18:\"Asterisk Phonebook\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:9:\"Phonebook\";s:5:\"items\";a:1:{s:9:\"phonebook\";a:5:{s:4:\"name\";s:18:\"Asterisk Phonebook\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;s:13:\"needsenginedb\";s:3:\"yes\";}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.2\";}s:11:\"iaxsettings\";a:16:{s:7:\"rawname\";s:11:\"iaxsettings\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:21:\"Asterisk IAX Settings\";s:7:\"version\";s:8:\"2.11.0.3\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:11:\"licenselink\";s:40:\"http://www.gnu.org/licenses/agpl-3.0.txt\";s:7:\"license\";s:6:\"AGPLv3\";s:8:\"category\";s:8:\"Settings\";s:13:\"embedcategory\";s:8:\"Advanced\";s:9:\"menuitems\";a:1:{s:11:\"iaxsettings\";s:21:\"Asterisk IAX Settings\";}s:11:\"description\";s:210:\"Use to configure Various Asterisk IAX Settings in the General section of iax.conf. The module assumes Asterisk version 1.4 or higher. Some settings may not exist in Asterisk 1.2 and will be ignored by Asterisk.\";s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:21:\"Asterisk IAX Settings\";s:5:\"items\";a:1:{s:11:\"iaxsettings\";a:4:{s:4:\"name\";s:21:\"Asterisk IAX Settings\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Settings\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.3\";}s:3:\"fax\";a:16:{s:7:\"rawname\";s:3:\"fax\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:17:\"Fax Configuration\";s:7:\"version\";s:9:\"2.11.0.10\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:8:\"Settings\";s:9:\"menuitems\";a:1:{s:3:\"fax\";s:17:\"Fax Configuration\";}s:11:\"description\";s:55:\"Adds configurations, options and GUI for inbound faxing\";s:7:\"depends\";a:1:{s:6:\"module\";s:22:\"framework ge 2.11.0.47\";}s:9:\"supported\";a:1:{s:7:\"version\";s:6:\"2.11.0\";}s:11:\"displayname\";s:17:\"Fax Configuration\";s:5:\"items\";a:1:{s:3:\"fax\";a:4:{s:4:\"name\";s:17:\"Fax Configuration\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Settings\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:9:\"2.11.0.10\";}s:13:\"weakpasswords\";a:16:{s:7:\"rawname\";s:13:\"weakpasswords\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:23:\"Weak Password Detection\";s:7:\"version\";s:8:\"2.11.0.1\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:7:\"Reports\";s:7:\"depends\";a:1:{s:7:\"version\";s:5:\"2.5.0\";}s:11:\"description\";s:80:\"This module detects weak SIP secrets and sets security notifications accordingly\";s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:9:\"menuitems\";a:1:{s:13:\"weakpasswords\";s:23:\"Weak Password Detection\";}s:11:\"displayname\";s:23:\"Weak Password Detection\";s:5:\"items\";a:1:{s:13:\"weakpasswords\";a:4:{s:4:\"name\";s:23:\"Weak Password Detection\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:7:\"Reports\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.1\";}s:4:\"disa\";a:18:{s:7:\"rawname\";s:4:\"disa\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:4:\"DISA\";s:7:\"version\";s:8:\"2.11.0.6\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:13:\"Remote Access\";s:9:\"menuitems\";a:1:{s:4:\"disa\";s:4:\"DISA\";}s:8:\"popovers\";a:1:{s:4:\"disa\";a:1:{s:7:\"display\";s:4:\"disa\";}}s:11:\"description\";s:264:\"DISA Allows you \'Direct Inward System Access\'. This gives you the ability to have an option on an IVR that gives you a dial tone, and you\'re able to dial out from the IssabelPBX machine as if you were connected to a standard extension. It appears as a Destination.\";s:7:\"depends\";a:1:{s:7:\"version\";s:5:\"2.4.0\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:4:\"DISA\";s:5:\"items\";a:1:{s:4:\"disa\";a:4:{s:4:\"name\";s:4:\"DISA\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.6\";}s:11:\"sipsettings\";a:19:{s:7:\"rawname\";s:11:\"sipsettings\";s:4:\"repo\";s:8:\"standard\";s:10:\"candisable\";s:2:\"no\";s:12:\"canuninstall\";s:2:\"no\";s:4:\"name\";s:21:\"Asterisk SIP Settings\";s:7:\"version\";s:8:\"2.11.1.0\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:7:\"AGPLv3+\";s:11:\"licenselink\";s:40:\"http://www.gnu.org/licenses/agpl-3.0.txt\";s:8:\"category\";s:8:\"Settings\";s:13:\"embedcategory\";s:8:\"Advanced\";s:9:\"menuitems\";a:2:{s:11:\"sipsettings\";s:21:\"Asterisk SIP Settings\";s:13:\"pjsipsettings\";s:23:\"Asterisk PJSIP Settings\";}s:11:\"description\";s:278:\"Use to configure Various Asterisk SIP Settings in the General section of sip.conf. Also includes an auto-configuration tool to determine NAT settings. The module assumes Asterisk version 1.4 or higher. Some settings may not exist in Asterisk 1.2 and will be ignored by Asterisk.\";s:7:\"depends\";a:1:{s:6:\"module\";s:23:\"core ge 2.11.0.0beta2.4\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.11\";}s:11:\"displayname\";s:21:\"Asterisk SIP Settings\";s:5:\"items\";a:2:{s:11:\"sipsettings\";a:4:{s:4:\"name\";s:21:\"Asterisk SIP Settings\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Settings\";s:4:\"sort\";i:0;}s:13:\"pjsipsettings\";a:4:{s:4:\"name\";s:23:\"Asterisk PJSIP Settings\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Settings\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.1.0\";}s:16:\"featurecodeadmin\";a:18:{s:7:\"rawname\";s:16:\"featurecodeadmin\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:18:\"Feature Code Admin\";s:7:\"version\";s:8:\"2.11.0.2\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:10:\"candisable\";s:2:\"no\";s:12:\"canuninstall\";s:2:\"no\";s:8:\"category\";s:5:\"Admin\";s:13:\"embedcategory\";s:5:\"Basic\";s:9:\"menuitems\";a:1:{s:16:\"featurecodeadmin\";s:13:\"Feature Codes\";}s:7:\"depends\";a:1:{s:7:\"version\";s:11:\"2.5.0alpha1\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:18:\"Feature Code Admin\";s:5:\"items\";a:1:{s:16:\"featurecodeadmin\";a:4:{s:4:\"name\";s:13:\"Feature Codes\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.2\";}s:7:\"vmblast\";a:18:{s:7:\"rawname\";s:7:\"vmblast\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:18:\"Voicemail Blasting\";s:7:\"version\";s:8:\"2.11.0.4\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:34:\"Internal Options \n&\n Configuration\";s:11:\"description\";s:123:\"Creates a group of extensions that calls a group of voicemail boxes and allows you to leave a message for them all at once.\";s:9:\"menuitems\";a:1:{s:7:\"vmblast\";s:18:\"Voicemail Blasting\";}s:8:\"popovers\";a:1:{s:7:\"vmblast\";a:1:{s:7:\"display\";s:7:\"vmblast\";}}s:7:\"depends\";a:1:{s:7:\"version\";s:5:\"2.4.0\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:18:\"Voicemail Blasting\";s:5:\"items\";a:1:{s:7:\"vmblast\";a:4:{s:4:\"name\";s:18:\"Voicemail Blasting\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.4\";}s:4:\"core\";a:21:{s:7:\"rawname\";s:4:\"core\";s:4:\"repo\";s:8:\"standard\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:5:\"Basic\";s:4:\"name\";s:4:\"Core\";s:7:\"version\";s:9:\"2.11.0.49\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:10:\"candisable\";s:2:\"no\";s:12:\"canuninstall\";s:2:\"no\";s:7:\"depends\";a:2:{s:6:\"module\";s:22:\"framework ge 2.11.0.30\";s:7:\"version\";s:12:\"2.11.0alpha0\";}s:12:\"requirements\";a:1:{s:4:\"file\";s:18:\"/usr/sbin/asterisk\";}s:9:\"menuitems\";a:10:{s:10:\"extensions\";s:10:\"Extensions\";s:5:\"users\";s:5:\"Users\";s:7:\"devices\";s:7:\"Devices\";s:3:\"did\";s:14:\"Inbound Routes\";s:13:\"dahdichandids\";s:18:\"DAHDI Channel DIDs\";s:7:\"routing\";s:15:\"Outbound Routes\";s:6:\"trunks\";s:6:\"Trunks\";s:16:\"advancedsettings\";s:17:\"Advanced Settings\";s:8:\"ampusers\";s:14:\"Administrators\";s:4:\"wiki\";s:18:\"IssabelPBX Support\";}s:8:\"popovers\";a:2:{s:10:\"extensions\";a:1:{s:7:\"display\";s:10:\"extensions\";}s:5:\"users\";a:1:{s:7:\"display\";s:5:\"users\";}}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.11\";}s:11:\"displayname\";s:4:\"Core\";s:7:\"methods\";a:1:{s:10:\"get_config\";a:1:{i:480;a:1:{i:0;s:18:\"core_do_get_config\";}}}s:5:\"items\";a:10:{s:10:\"extensions\";a:5:{s:4:\"name\";s:10:\"Extensions\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;s:13:\"needsenginedb\";s:3:\"yes\";}s:5:\"users\";a:5:{s:4:\"name\";s:5:\"Users\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;s:13:\"needsenginedb\";s:3:\"yes\";}s:7:\"devices\";a:5:{s:4:\"name\";s:7:\"Devices\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;s:13:\"needsenginedb\";s:3:\"yes\";}s:3:\"did\";a:4:{s:4:\"name\";s:14:\"Inbound Routes\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Connectivity\";s:4:\"sort\";i:0;}s:13:\"dahdichandids\";a:4:{s:4:\"name\";s:18:\"DAHDI Channel DIDs\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Connectivity\";s:4:\"sort\";i:0;}s:7:\"routing\";a:4:{s:4:\"name\";s:15:\"Outbound Routes\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Connectivity\";s:4:\"sort\";i:0;}s:6:\"trunks\";a:4:{s:4:\"name\";s:6:\"Trunks\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Connectivity\";s:4:\"sort\";i:0;}s:16:\"advancedsettings\";a:4:{s:4:\"name\";s:17:\"Advanced Settings\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Settings\";s:4:\"sort\";i:0;}s:8:\"ampusers\";a:4:{s:4:\"name\";s:14:\"Administrators\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;}s:4:\"wiki\";a:8:{s:4:\"name\";s:18:\"IssabelPBX Support\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;s:4:\"href\";s:22:\"http://www.issabel.com\";s:6:\"target\";s:6:\"_blank\";s:6:\"access\";s:3:\"all\";s:13:\"requires_auth\";s:5:\"false\";}}s:6:\"status\";i:2;s:9:\"dbversion\";s:9:\"2.11.0.49\";}s:9:\"queueprio\";a:18:{s:7:\"rawname\";s:9:\"queueprio\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:16:\"Queue Priorities\";s:7:\"version\";s:8:\"2.11.0.2\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:20:\"Inbound Call Control\";s:11:\"description\";s:73:\"Adds the ability to set a callers priority higher before entering a queue\";s:9:\"menuitems\";a:1:{s:9:\"queueprio\";s:16:\"Queue Priorities\";}s:8:\"popovers\";a:1:{s:9:\"queueprio\";a:1:{s:7:\"display\";s:9:\"queueprio\";}}s:7:\"depends\";a:1:{s:7:\"version\";s:11:\"2.5.0alpha1\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:16:\"Queue Priorities\";s:5:\"items\";a:1:{s:9:\"queueprio\";a:4:{s:4:\"name\";s:16:\"Queue Priorities\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.2\";}s:7:\"manager\";a:14:{s:7:\"rawname\";s:7:\"manager\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:22:\"Asterisk Manager Users\";s:7:\"version\";s:8:\"2.11.0.5\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:8:\"Settings\";s:9:\"menuitems\";a:1:{s:7:\"manager\";s:22:\"Asterisk Manager Users\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:22:\"Asterisk Manager Users\";s:5:\"items\";a:1:{s:7:\"manager\";a:4:{s:4:\"name\";s:22:\"Asterisk Manager Users\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Settings\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.5\";}s:9:\"blacklist\";a:16:{s:7:\"rawname\";s:9:\"blacklist\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:9:\"Blacklist\";s:7:\"version\";s:8:\"2.11.0.6\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:5:\"Admin\";s:13:\"embedcategory\";s:20:\"Inbound Call Control\";s:9:\"menuitems\";a:1:{s:9:\"blacklist\";s:9:\"Blacklist\";}s:7:\"depends\";a:1:{s:6:\"module\";s:15:\"core ge 2.5.1.2\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:9:\"Blacklist\";s:5:\"items\";a:1:{s:9:\"blacklist\";a:5:{s:4:\"name\";s:9:\"Blacklist\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;s:13:\"needsenginedb\";s:3:\"yes\";}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.6\";}s:11:\"callforward\";a:13:{s:7:\"rawname\";s:11:\"callforward\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:12:\"Call Forward\";s:7:\"version\";s:6:\"2.11.5\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:7:\"AGPLv3+\";s:11:\"licenselink\";s:40:\"http://www.gnu.org/licenses/agpl-3.0.txt\";s:11:\"description\";s:33:\"Provides callforward featurecodes\";s:8:\"category\";s:12:\"Applications\";s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:12:\"Call Forward\";s:6:\"status\";i:2;s:9:\"dbversion\";s:6:\"2.11.5\";}s:9:\"voicemail\";a:19:{s:7:\"rawname\";s:9:\"voicemail\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:9:\"Voicemail\";s:7:\"version\";s:8:\"2.11.1.7\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:10:\"candisable\";s:2:\"no\";s:12:\"canuninstall\";s:2:\"no\";s:11:\"description\";s:69:\"This module allows you to configure Voicemail for a user or extension\";s:8:\"category\";s:8:\"Settings\";s:13:\"embedcategory\";s:8:\"Advanced\";s:9:\"menuitems\";a:1:{s:9:\"voicemail\";s:15:\"Voicemail Admin\";}s:7:\"depends\";a:2:{s:7:\"version\";s:11:\"2.5.0alpha1\";s:6:\"module\";s:22:\"framework ge 2.11.0.47\";}s:9:\"supported\";a:1:{s:7:\"version\";s:6:\"2.11.0\";}s:11:\"displayname\";s:9:\"Voicemail\";s:5:\"items\";a:1:{s:9:\"voicemail\";a:4:{s:4:\"name\";s:15:\"Voicemail Admin\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Settings\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.1.7\";}s:12:\"fw_langpacks\";a:14:{s:7:\"rawname\";s:12:\"fw_langpacks\";s:7:\"modtype\";s:9:\"framework\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:31:\"IssabelPBX Localization Updates\";s:7:\"version\";s:6:\"2.11.2\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:11:\"description\";s:505:\"This module provides a facility to install new and updated localization translations for all components in IssabelPBX. Localization i18n translations are still kept with each module and other components such as the User Portal (ARI). This provides an easy ability to bring all components up-to-date without the need of publishing dozens of modules for every minor change. The localization updates used will be the latest available for all modules and will not consider the current version you are running.\";s:8:\"category\";s:5:\"Admin\";s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.11\";}s:11:\"displayname\";s:31:\"IssabelPBX Localization Updates\";s:6:\"status\";i:2;s:9:\"dbversion\";s:6:\"2.11.2\";}s:15:\"dynamicfeatures\";a:17:{s:7:\"rawname\";s:15:\"dynamicfeatures\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:16:\"Dynamic Features\";s:7:\"version\";s:8:\"2.11.0.0\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:34:\"Internal Options \n&\n Configuration\";s:11:\"description\";s:84:\"Adds the ability to configure custom dynamic features to be executed while on a call\";s:9:\"menuitems\";a:1:{s:15:\"dynamicfeatures\";s:16:\"Dynamic Features\";}s:7:\"depends\";a:1:{s:7:\"version\";s:6:\"2.11.0\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:16:\"Dynamic Features\";s:5:\"items\";a:1:{s:15:\"dynamicfeatures\";a:4:{s:4:\"name\";s:16:\"Dynamic Features\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.0\";}s:9:\"framework\";a:16:{s:7:\"rawname\";s:9:\"framework\";s:7:\"modtype\";s:9:\"framework\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:20:\"IssabelPBX Framework\";s:7:\"version\";s:9:\"2.11.0.49\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:10:\"candisable\";s:2:\"no\";s:12:\"canuninstall\";s:2:\"no\";s:11:\"description\";s:115:\"This module provides a facility to install bug fixes to the framework code that is not otherwise housed in a module\";s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.11\";}s:8:\"category\";s:5:\"Admin\";s:11:\"displayname\";s:20:\"IssabelPBX Framework\";s:6:\"status\";i:2;s:9:\"dbversion\";s:9:\"2.11.0.49\";}s:13:\"customappsreg\";a:18:{s:7:\"rawname\";s:13:\"customappsreg\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:19:\"Custom Applications\";s:7:\"version\";s:8:\"2.11.0.2\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:5:\"Admin\";s:13:\"embedcategory\";s:8:\"Advanced\";s:11:\"description\";s:147:\"Registry to add custom extensions and destinations that may be created and used so that the Extensions and Destinations Registry can include these.\";s:9:\"menuitems\";a:2:{s:12:\"customextens\";s:17:\"Custom Extensions\";s:11:\"customdests\";s:19:\"Custom Destinations\";}s:8:\"popovers\";a:1:{s:13:\"customappsreg\";a:1:{s:7:\"display\";s:11:\"customdests\";}}s:7:\"depends\";a:1:{s:7:\"version\";s:5:\"2.4.0\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:19:\"Custom Applications\";s:5:\"items\";a:2:{s:12:\"customextens\";a:4:{s:4:\"name\";s:17:\"Custom Extensions\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;}s:11:\"customdests\";a:4:{s:4:\"name\";s:19:\"Custom Destinations\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.2\";}s:6:\"backup\";a:16:{s:7:\"rawname\";s:6:\"backup\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:16:\"Backup & Restore\";s:7:\"version\";s:9:\"2.11.0.23\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:5:\"Admin\";s:11:\"description\";s:48:\"Backup & Restore for your IssabelPBX environment\";s:9:\"menuitems\";a:4:{s:6:\"backup\";s:16:\"Backup & Restore\";s:14:\"backup_servers\";s:26:\"Backup & Restore - Servers\";s:16:\"backup_templates\";s:28:\"Backup & Restore - Templates\";s:14:\"backup_restore\";s:26:\"Backup & Restore - Restore\";}s:7:\"depends\";a:1:{s:6:\"module\";s:4:\"core\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.11\";}s:11:\"displayname\";s:16:\"Backup & Restore\";s:5:\"items\";a:4:{s:6:\"backup\";a:5:{s:4:\"name\";s:16:\"Backup & Restore\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;s:13:\"needsenginedb\";s:3:\"yes\";}s:14:\"backup_servers\";a:6:{s:4:\"name\";s:26:\"Backup & Restore - Servers\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;s:13:\"needsenginedb\";s:3:\"yes\";s:6:\"hidden\";s:4:\"true\";}s:16:\"backup_templates\";a:6:{s:4:\"name\";s:28:\"Backup & Restore - Templates\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;s:13:\"needsenginedb\";s:3:\"yes\";s:6:\"hidden\";s:4:\"true\";}s:14:\"backup_restore\";a:6:{s:4:\"name\";s:26:\"Backup & Restore - Restore\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;s:13:\"needsenginedb\";s:3:\"yes\";s:6:\"hidden\";s:4:\"true\";}}s:6:\"status\";i:2;s:9:\"dbversion\";s:9:\"2.11.0.23\";}s:12:\"asterisk-cli\";a:16:{s:7:\"rawname\";s:12:\"asterisk-cli\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:12:\"Asterisk CLI\";s:7:\"version\";s:8:\"2.11.0.3\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:5:\"Admin\";s:11:\"description\";s:88:\"Provides an interface allowing you to run a command as if it was typed into Asterisk CLI\";s:9:\"menuitems\";a:1:{s:3:\"cli\";s:12:\"Asterisk CLI\";}s:7:\"depends\";a:1:{s:6:\"engine\";s:8:\"asterisk\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:12:\"Asterisk CLI\";s:5:\"items\";a:1:{s:3:\"cli\";a:4:{s:4:\"name\";s:12:\"Asterisk CLI\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.3\";}s:11:\"inventorydb\";a:12:{s:7:\"rawname\";s:11:\"inventorydb\";s:4:\"name\";s:9:\"Inventory\";s:7:\"version\";s:7:\"2.5.0.2\";s:4:\"type\";s:4:\"tool\";s:8:\"category\";s:17:\"Third Party Addon\";s:9:\"menuitems\";a:1:{s:11:\"inventorydb\";s:9:\"Inventory\";}s:11:\"displayname\";s:9:\"Inventory\";s:5:\"items\";a:1:{s:11:\"inventorydb\";a:4:{s:4:\"name\";s:9:\"Inventory\";s:4:\"type\";s:4:\"tool\";s:8:\"category\";s:17:\"Third Party Addon\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:4:\"repo\";s:5:\"local\";s:9:\"dbversion\";s:7:\"2.5.0.2\";s:7:\"license\";s:0:\"\";}s:12:\"announcement\";a:18:{s:7:\"rawname\";s:12:\"announcement\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:13:\"Announcements\";s:7:\"version\";s:8:\"2.11.0.5\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:20:\"Inbound Call Control\";s:11:\"description\";s:126:\"Plays back one of the system recordings or TTS (optionally allowing the user to skip it) and then goes to another destination.\";s:7:\"depends\";a:2:{s:7:\"version\";s:11:\"2.5.0alpha1\";s:6:\"module\";s:19:\"recordings ge 3.3.8\";}s:9:\"menuitems\";a:1:{s:12:\"announcement\";s:13:\"Announcements\";}s:8:\"popovers\";a:1:{s:12:\"announcement\";a:1:{s:7:\"display\";s:12:\"announcement\";}}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:13:\"Announcements\";s:5:\"items\";a:1:{s:12:\"announcement\";a:4:{s:4:\"name\";s:13:\"Announcements\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.5\";}s:9:\"miscdests\";a:18:{s:7:\"rawname\";s:9:\"miscdests\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:17:\"Misc Destinations\";s:7:\"version\";s:8:\"2.11.0.4\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:34:\"Internal Options \n&\n Configuration\";s:11:\"description\";s:190:\"Allows creating destinations that dial any local number (extensions, feature codes, outside phone numbers) that can be used by other modules (eg, IVR, time conditions) as a call destination.\";s:7:\"depends\";a:1:{s:7:\"version\";s:11:\"2.5.0alpha1\";}s:9:\"menuitems\";a:1:{s:9:\"miscdests\";s:17:\"Misc Destinations\";}s:8:\"popovers\";a:1:{s:9:\"miscdests\";a:1:{s:7:\"display\";s:9:\"miscdests\";}}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:17:\"Misc Destinations\";s:5:\"items\";a:1:{s:9:\"miscdests\";a:4:{s:4:\"name\";s:17:\"Misc Destinations\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.4\";}s:13:\"callrecording\";a:19:{s:7:\"rawname\";s:13:\"callrecording\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:14:\"Call Recording\";s:7:\"version\";s:9:\"2.11.0.10\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:10:\"candisable\";s:2:\"no\";s:12:\"canuninstall\";s:2:\"no\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:20:\"Inbound Call Control\";s:11:\"description\";s:50:\"Provides much of the call recording functionality.\";s:9:\"menuitems\";a:1:{s:13:\"callrecording\";s:14:\"Call Recording\";}s:8:\"popovers\";a:1:{s:13:\"callrecording\";a:1:{s:7:\"display\";s:13:\"callrecording\";}}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.11\";}s:11:\"displayname\";s:14:\"Call Recording\";s:5:\"items\";a:1:{s:13:\"callrecording\";a:4:{s:4:\"name\";s:14:\"Call Recording\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:9:\"2.11.0.10\";}s:10:\"customerdb\";a:12:{s:7:\"rawname\";s:10:\"customerdb\";s:4:\"name\";s:11:\"Customer DB\";s:7:\"version\";s:7:\"2.5.0.4\";s:4:\"type\";s:4:\"tool\";s:8:\"category\";s:17:\"Third Party Addon\";s:9:\"menuitems\";a:1:{s:10:\"customerdb\";s:11:\"Customer DB\";}s:11:\"displayname\";s:11:\"Customer DB\";s:5:\"items\";a:1:{s:10:\"customerdb\";a:4:{s:4:\"name\";s:11:\"Customer DB\";s:4:\"type\";s:4:\"tool\";s:8:\"category\";s:17:\"Third Party Addon\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:4:\"repo\";s:5:\"local\";s:9:\"dbversion\";s:7:\"2.5.0.4\";s:7:\"license\";s:0:\"\";}s:10:\"phpagiconf\";a:15:{s:7:\"rawname\";s:10:\"phpagiconf\";s:4:\"repo\";s:11:\"unsupported\";s:4:\"name\";s:13:\"PHPAGI Config\";s:7:\"version\";s:8:\"2.11.0.2\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:8:\"Settings\";s:9:\"menuitems\";a:1:{s:10:\"phpagiconf\";s:13:\"PHPAGI Config\";}s:7:\"depends\";a:1:{s:6:\"module\";s:15:\"manager ge1.0.4\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:13:\"PHPAGI Config\";s:5:\"items\";a:1:{s:10:\"phpagiconf\";a:4:{s:4:\"name\";s:13:\"PHPAGI Config\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Settings\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.2\";}s:15:\"managersettings\";a:16:{s:7:\"rawname\";s:15:\"managersettings\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:25:\"Asterisk Manager Settings\";s:7:\"version\";s:8:\"2.11.0.0\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:11:\"licenselink\";s:40:\"http://www.gnu.org/licenses/agpl-3.0.txt\";s:7:\"license\";s:6:\"AGPLv3\";s:8:\"category\";s:8:\"Settings\";s:13:\"embedcategory\";s:8:\"Advanced\";s:9:\"menuitems\";a:1:{s:15:\"managersettings\";s:25:\"Asterisk Manager Settings\";}s:11:\"description\";s:140:\"Use to configure Various Asterisk Manager Settings in the General section of manager.conf. The module assumes Asterisk version 11 or higher.\";s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:25:\"Asterisk Manager Settings\";s:5:\"items\";a:1:{s:15:\"managersettings\";a:4:{s:4:\"name\";s:25:\"Asterisk Manager Settings\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Settings\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.0\";}s:7:\"gabcast\";a:12:{s:7:\"rawname\";s:7:\"gabcast\";s:4:\"name\";s:7:\"Gabcast\";s:7:\"version\";s:7:\"2.5.0.2\";s:4:\"type\";s:4:\"tool\";s:8:\"category\";s:17:\"Third Party Addon\";s:9:\"menuitems\";a:1:{s:7:\"gabcast\";s:7:\"Gabcast\";}s:7:\"depends\";a:1:{s:7:\"version\";s:5:\"2.4.0\";}s:11:\"displayname\";s:7:\"Gabcast\";s:5:\"items\";a:1:{s:7:\"gabcast\";a:4:{s:4:\"name\";s:7:\"Gabcast\";s:4:\"type\";s:4:\"tool\";s:8:\"category\";s:17:\"Third Party Addon\";s:4:\"sort\";i:0;}}s:6:\"status\";i:0;s:4:\"repo\";s:5:\"local\";s:7:\"license\";s:0:\"\";}s:12:\"asteriskinfo\";a:17:{s:7:\"rawname\";s:12:\"asteriskinfo\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:13:\"Asterisk Info\";s:7:\"version\";s:9:\"2.11.0.89\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:7:\"Reports\";s:13:\"embedcategory\";s:8:\"Advanced\";s:11:\"description\";s:57:\"Provides a snapshot of the current Asterisk configuration\";s:9:\"menuitems\";a:1:{s:12:\"asteriskinfo\";s:13:\"Asterisk Info\";}s:7:\"depends\";a:2:{s:6:\"engine\";s:8:\"asterisk\";s:7:\"version\";s:8:\"2.5.0rc3\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:13:\"Asterisk Info\";s:5:\"items\";a:1:{s:12:\"asteriskinfo\";a:4:{s:4:\"name\";s:13:\"Asterisk Info\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:7:\"Reports\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:9:\"2.11.0.89\";}s:12:\"findmefollow\";a:17:{s:7:\"rawname\";s:12:\"findmefollow\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:9:\"Follow Me\";s:7:\"version\";s:8:\"2.11.0.6\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:7:\"depends\";a:2:{s:7:\"version\";s:11:\"2.5.0alpha1\";s:6:\"module\";s:19:\"recordings ge 3.3.8\";}s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:20:\"Inbound Call Control\";s:11:\"description\";s:358:\"Much like a ring group, but works on individual extensions. When someone calls the extension, it can be setup to ring for a number of seconds before trying to ring other extensions and/or external numbers, or to ring all at once, or in other various \'hunt\' configurations. Most commonly used to ring someone\'s cell phone if they don\'t answer their extension.\";s:9:\"menuitems\";a:1:{s:12:\"findmefollow\";s:9:\"Follow Me\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:9:\"Follow Me\";s:5:\"items\";a:1:{s:12:\"findmefollow\";a:5:{s:4:\"name\";s:9:\"Follow Me\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;s:13:\"needsenginedb\";s:3:\"yes\";}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.6\";}s:13:\"writequeuelog\";a:17:{s:7:\"rawname\";s:13:\"writequeuelog\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:27:\"Write a line into queue log\";s:7:\"version\";s:8:\"2.11.0.0\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:8:\"Advanced\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:11:\"description\";s:40:\"Adds the ability to write into queue_log\";s:9:\"menuitems\";a:1:{s:13:\"writequeuelog\";s:18:\"Write in Queue Log\";}s:7:\"depends\";a:1:{s:7:\"version\";s:5:\"2.5.0\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:27:\"Write a line into queue log\";s:5:\"items\";a:1:{s:13:\"writequeuelog\";a:4:{s:4:\"name\";s:18:\"Write in Queue Log\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.0\";}s:8:\"callback\";a:17:{s:7:\"rawname\";s:8:\"callback\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:8:\"Callback\";s:7:\"version\";s:8:\"2.11.0.4\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:13:\"Remote Access\";s:9:\"menuitems\";a:1:{s:8:\"callback\";s:8:\"Callback\";}s:8:\"popovers\";a:1:{s:8:\"callback\";a:1:{s:7:\"display\";s:8:\"callback\";}}s:7:\"depends\";a:1:{s:7:\"version\";s:5:\"2.4.0\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:8:\"Callback\";s:5:\"items\";a:1:{s:8:\"callback\";a:4:{s:4:\"name\";s:8:\"Callback\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.4\";}s:11:\"callwaiting\";a:13:{s:7:\"rawname\";s:11:\"callwaiting\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:12:\"Call Waiting\";s:11:\"description\";s:46:\"Provides an option to turn on/off call waiting\";s:8:\"category\";s:12:\"Applications\";s:7:\"version\";s:8:\"2.11.0.4\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:12:\"Call Waiting\";s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.4\";}s:9:\"languages\";a:18:{s:7:\"rawname\";s:9:\"languages\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:9:\"Languages\";s:7:\"version\";s:8:\"2.11.0.2\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:34:\"Internal Options \n&\n Configuration\";s:11:\"description\";s:96:\"Adds the ability to changes the language within a call flow and add language attribute to users.\";s:9:\"menuitems\";a:1:{s:9:\"languages\";s:9:\"Languages\";}s:8:\"popovers\";a:1:{s:9:\"languages\";a:1:{s:7:\"display\";s:9:\"languages\";}}s:7:\"depends\";a:1:{s:7:\"version\";s:11:\"2.5.0alpha1\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:9:\"Languages\";s:5:\"items\";a:1:{s:9:\"languages\";a:4:{s:4:\"name\";s:9:\"Languages\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.2\";}s:10:\"dundicheck\";a:16:{s:7:\"rawname\";s:10:\"dundicheck\";s:4:\"repo\";s:11:\"unsupported\";s:4:\"name\";s:21:\"DUNDi Lookup Registry\";s:7:\"version\";s:8:\"2.11.0.3\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:11:\"description\";s:334:\"This module will check all configured and enabled DUNDi trunks as part of the extension registry function, and report back conflicts if		other sites have the same extensions. This does not filter against the route patterns - it will take any number being created and		report a conflict if that trunk could be used to call that number.\";s:9:\"menuitems\";a:1:{s:10:\"dundicheck\";s:12:\"DUNDi Lookup\";}s:7:\"depends\";a:1:{s:7:\"version\";s:5:\"2.4.0\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:8:\"category\";s:5:\"Admin\";s:11:\"displayname\";s:21:\"DUNDi Lookup Registry\";s:5:\"items\";a:1:{s:10:\"dundicheck\";a:4:{s:4:\"name\";s:12:\"DUNDi Lookup\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.3\";}s:8:\"dynroute\";a:17:{s:7:\"rawname\";s:8:\"dynroute\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:14:\"Dynamic Routes\";s:7:\"version\";s:8:\"2.11.3.2\";s:9:\"publisher\";s:14:\"voipsupport.it\";s:7:\"license\";s:6:\"GPLv3+\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Advanced\";s:13:\"embedcategory\";s:20:\"Inbound Call Control\";s:11:\"description\";s:112:\"Allows to route call based on lookup in sql database and/or user input and to store results in channel variables\";s:7:\"depends\";a:2:{s:7:\"version\";s:6:\"ge2.11\";s:6:\"module\";b:0;}s:9:\"menuitems\";a:1:{s:8:\"dynroute\";s:14:\"Dynamic Routes\";}s:8:\"popovers\";a:1:{s:8:\"dynroute\";a:2:{s:7:\"display\";s:8:\"dynroute\";s:6:\"action\";s:3:\"add\";}}s:11:\"displayname\";s:14:\"Dynamic Routes\";s:5:\"items\";a:1:{s:8:\"dynroute\";a:4:{s:4:\"name\";s:14:\"Dynamic Routes\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Advanced\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.3.2\";}s:14:\"timeconditions\";a:18:{s:7:\"rawname\";s:14:\"timeconditions\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:15:\"Time Conditions\";s:7:\"version\";s:8:\"2.11.1.2\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:20:\"Inbound Call Control\";s:11:\"description\";s:238:\"Creates a condition where calls will go to one of two destinations (eg, an extension, IVR, ring group..) based on the time and/or date. This can be used for example to ring a receptionist during the day, or go directly to an IVR at night.\";s:7:\"depends\";a:1:{s:7:\"version\";s:11:\"2.5.0alpha1\";}s:9:\"menuitems\";a:2:{s:14:\"timeconditions\";s:15:\"Time Conditions\";s:10:\"timegroups\";s:11:\"Time Groups\";}s:8:\"popovers\";a:1:{s:14:\"timeconditions\";a:1:{s:7:\"display\";s:14:\"timeconditions\";}}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:15:\"Time Conditions\";s:5:\"items\";a:2:{s:14:\"timeconditions\";a:4:{s:4:\"name\";s:15:\"Time Conditions\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}s:10:\"timegroups\";a:4:{s:4:\"name\";s:11:\"Time Groups\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.1.2\";}s:10:\"recordings\";a:18:{s:7:\"rawname\";s:10:\"recordings\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:10:\"Recordings\";s:7:\"version\";s:7:\"3.4.0.4\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:12:\"canuninstall\";s:2:\"no\";s:8:\"category\";s:5:\"Admin\";s:13:\"embedcategory\";s:34:\"Internal Options \n&\n Configuration\";s:11:\"description\";s:76:\"Creates and manages system recordings, used by many other modules (eg, IVR).\";s:9:\"menuitems\";a:1:{s:10:\"recordings\";s:17:\"System Recordings\";}s:7:\"depends\";a:1:{s:6:\"module\";s:22:\"framework ge 2.11.0.47\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.11\";}s:11:\"displayname\";s:10:\"Recordings\";s:5:\"items\";a:1:{s:10:\"recordings\";a:4:{s:4:\"name\";s:17:\"System Recordings\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:7:\"3.4.0.4\";}s:7:\"dictate\";a:13:{s:7:\"rawname\";s:7:\"dictate\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:9:\"Dictation\";s:7:\"version\";s:8:\"2.11.0.3\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:11:\"description\";s:189:\"This uses the app_dictate module of Asterisk to let users record dictate into their phones. When complete, the dictations can be emailed to an email address specified in the extension page.\";s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:9:\"Dictation\";s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.3\";}s:7:\"phpinfo\";a:13:{s:7:\"rawname\";s:7:\"phpinfo\";s:4:\"repo\";s:8:\"extended\";s:4:\"name\";s:8:\"PHP Info\";s:7:\"version\";s:8:\"2.11.0.1\";s:9:\"publisher\";s:10:\"IssabelPBX\";s:7:\"license\";s:6:\"GPLv2+\";s:8:\"category\";s:7:\"Reports\";s:9:\"menuitems\";a:1:{s:7:\"phpinfo\";s:8:\"PHP Info\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:8:\"PHP Info\";s:5:\"items\";a:1:{s:7:\"phpinfo\";a:4:{s:4:\"name\";s:8:\"PHP Info\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:7:\"Reports\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.1\";}s:6:\"setcid\";a:17:{s:7:\"rawname\";s:6:\"setcid\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:12:\"Set CallerID\";s:7:\"version\";s:8:\"2.11.1.0\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:20:\"Inbound Call Control\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:11:\"description\";s:59:\"Adds the ability to change the CallerID within a call flow.\";s:9:\"menuitems\";a:1:{s:6:\"setcid\";s:12:\"Set CallerID\";}s:7:\"depends\";a:1:{s:7:\"version\";s:5:\"2.5.0\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:12:\"Set CallerID\";s:5:\"items\";a:1:{s:6:\"setcid\";a:4:{s:4:\"name\";s:12:\"Set CallerID\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.1.0\";}s:8:\"logfiles\";a:17:{s:7:\"rawname\";s:8:\"logfiles\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:17:\"Asterisk Logfiles\";s:12:\"canuninstall\";s:2:\"no\";s:7:\"version\";s:8:\"2.11.1.4\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:7:\"Reports\";s:13:\"embedcategory\";s:8:\"Advanced\";s:9:\"menuitems\";a:2:{s:8:\"logfiles\";s:17:\"Asterisk Logfiles\";s:17:\"logfiles_settings\";s:25:\"Asterisk Logfile Settings\";}s:9:\"supported\";a:1:{s:7:\"version\";s:6:\"2.11.0\";}s:7:\"depends\";a:1:{s:6:\"module\";s:22:\"framework ge 2.11.0.47\";}s:11:\"displayname\";s:17:\"Asterisk Logfiles\";s:5:\"items\";a:2:{s:8:\"logfiles\";a:4:{s:4:\"name\";s:17:\"Asterisk Logfiles\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:7:\"Reports\";s:4:\"sort\";i:0;}s:17:\"logfiles_settings\";a:4:{s:4:\"name\";s:25:\"Asterisk Logfile Settings\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Settings\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.1.4\";}s:13:\"bosssecretary\";a:14:{s:7:\"rawname\";s:13:\"bosssecretary\";s:4:\"name\";s:14:\"Boss Secretary\";s:7:\"version\";s:3:\"1.0\";s:4:\"repo\";s:8:\"extended\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:34:\"Internal Options \n&\n Configuration\";s:11:\"description\";s:436:\"The boss-secretary module creates a special ring group which includesone or more \"bosses\" and one or more secretaries\". When someone callsthe boss\' extension, the secretary (or secretaries) extension will ring only, allowing the secretary to answer his or her boss\' call. Only secretary ( or secretaries ) or \'chief extensions\' are authorized to call directly to boss extension. With feature code you can turn on or off secretary group.\";s:9:\"menuitems\";a:1:{s:13:\"bosssecretary\";s:14:\"Boss Secretary\";}s:11:\"displayname\";s:14:\"Boss Secretary\";s:5:\"items\";a:1:{s:13:\"bosssecretary\";a:4:{s:4:\"name\";s:14:\"Boss Secretary\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:3:\"1.0\";s:7:\"license\";s:0:\"\";}s:9:\"cidlookup\";a:17:{s:7:\"rawname\";s:9:\"cidlookup\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:15:\"CallerID Lookup\";s:7:\"version\";s:9:\"2.11.1.12\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:11:\"description\";s:114:\"Allows CallerID Lookup of incoming calls against different sources (OpenCNAM, MySQL, HTTP, ENUM, Phonebook Module)\";s:8:\"category\";s:5:\"Admin\";s:13:\"embedcategory\";s:20:\"Inbound Call Control\";s:9:\"menuitems\";a:1:{s:9:\"cidlookup\";s:23:\"CallerID Lookup Sources\";}s:7:\"depends\";a:2:{s:6:\"engine\";s:12:\"asterisk 1.6\";s:6:\"module\";s:26:\"framework ge 2.11.0.0rc1.6\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.11\";}s:11:\"displayname\";s:15:\"CallerID Lookup\";s:5:\"items\";a:1:{s:9:\"cidlookup\";a:4:{s:4:\"name\";s:23:\"CallerID Lookup Sources\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:5:\"Admin\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:9:\"2.11.1.12\";}s:12:\"trunkbalance\";a:18:{s:7:\"rawname\";s:12:\"trunkbalance\";s:4:\"name\";s:13:\"Trunk Balance\";s:7:\"version\";s:5:\"1.1.5\";s:9:\"publisher\";s:5:\"POSSA\";s:7:\"license\";s:6:\"GPLv2+\";s:10:\"candisable\";s:3:\"yes\";s:12:\"canuninstall\";s:3:\"yes\";s:4:\"repo\";s:8:\"extended\";s:8:\"category\";s:8:\"Advanced\";s:13:\"embedcategory\";s:8:\"Advanced\";s:7:\"depends\";a:2:{s:6:\"module\";a:2:{i:0;s:4:\"core\";i:1;s:20:\"timeconditions ge2.6\";}s:7:\"version\";s:6:\"ge 2.6\";}s:11:\"description\";s:96:\"Restrict outbound calls or balance calls over multiple trunks based on user specified parameters\";s:9:\"menuitems\";a:1:{s:12:\"trunkbalance\";s:13:\"Trunk Balance\";}s:4:\"info\";s:48:\"https://github.com/POSSA/freepbx-trunk-balancing\";s:11:\"displayname\";s:13:\"Trunk Balance\";s:5:\"items\";a:1:{s:12:\"trunkbalance\";a:4:{s:4:\"name\";s:13:\"Trunk Balance\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Advanced\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:5:\"1.1.5\";}s:12:\"infoservices\";a:15:{s:7:\"rawname\";s:12:\"infoservices\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:13:\"Info Services\";s:7:\"version\";s:8:\"2.11.0.3\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:10:\"candisable\";s:2:\"no\";s:12:\"canuninstall\";s:2:\"no\";s:8:\"category\";s:12:\"Applications\";s:11:\"description\";s:180:\"Provides a number of applications accessible by feature codes: company directory, call trace (last call information), echo test, speaking clock, and speak current extension number.\";s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.11\";}s:11:\"displayname\";s:13:\"Info Services\";s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.3\";}s:5:\"music\";a:18:{s:7:\"rawname\";s:5:\"music\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:13:\"Music on Hold\";s:7:\"version\";s:8:\"2.11.0.3\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:10:\"candisable\";s:2:\"no\";s:12:\"canuninstall\";s:2:\"no\";s:8:\"category\";s:8:\"Settings\";s:13:\"embedcategory\";s:34:\"Internal Options \n&\n Configuration\";s:11:\"description\";s:80:\"Uploading and management of sound files (wav, mp3) to be used for on-hold music.\";s:9:\"menuitems\";a:1:{s:5:\"music\";s:13:\"Music on Hold\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:13:\"Music on Hold\";s:5:\"items\";a:1:{s:5:\"music\";a:4:{s:4:\"name\";s:13:\"Music on Hold\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:8:\"Settings\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.3\";}s:6:\"paging\";a:19:{s:7:\"rawname\";s:6:\"paging\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:19:\"Paging and Intercom\";s:7:\"version\";s:9:\"2.11.0.11\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:34:\"Internal Options \n&\n Configuration\";s:7:\"depends\";a:2:{s:7:\"version\";s:6:\"2.11.0\";s:6:\"module\";s:22:\"framework ge 2.11.0.49\";}s:11:\"description\";s:352:\"Allows creation of paging groups to make announcements using the speaker built into most SIP phones.         Also creates an Intercom feature code that can be used as a prefix to talk directly to one person, as well as optional feature codes to block/allow intercom calls to all users as well as blocking specific users or only allowing specific users.\";s:9:\"menuitems\";a:1:{s:6:\"paging\";s:19:\"Paging and Intercom\";}s:12:\"requirements\";a:1:{s:6:\"module\";s:11:\"conferences\";}s:8:\"popovers\";a:1:{s:6:\"paging\";a:2:{s:7:\"display\";s:6:\"paging\";s:6:\"action\";s:3:\"add\";}}s:9:\"supported\";a:1:{s:7:\"version\";s:6:\"2.11.0\";}s:11:\"displayname\";s:19:\"Paging and Intercom\";s:5:\"items\";a:1:{s:6:\"paging\";a:4:{s:4:\"name\";s:19:\"Paging and Intercom\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:9:\"2.11.0.11\";}s:9:\"dashboard\";a:18:{s:7:\"rawname\";s:9:\"dashboard\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:16:\"System Dashboard\";s:7:\"version\";s:8:\"2.11.0.5\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:10:\"candisable\";s:2:\"no\";s:12:\"canuninstall\";s:2:\"no\";s:8:\"category\";s:7:\"Reports\";s:11:\"description\";s:117:\"Provides a system information dashboard, showing information about Calls, CPU, Memory, Disks, Network, and processes.\";s:9:\"menuitems\";a:1:{s:9:\"dashboard\";s:24:\"IssabelPBX System Status\";}s:7:\"depends\";a:1:{s:7:\"version\";s:10:\"2.3.0beta2\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:16:\"System Dashboard\";s:5:\"items\";a:1:{s:9:\"dashboard\";a:6:{s:4:\"name\";s:24:\"IssabelPBX System Status\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:7:\"Reports\";s:4:\"sort\";i:0;s:7:\"display\";s:5:\"index\";s:6:\"access\";s:3:\"all\";}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.5\";}s:12:\"donotdisturb\";a:13:{s:7:\"rawname\";s:12:\"donotdisturb\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:20:\"Do-Not-Disturb (DND)\";s:7:\"version\";s:8:\"2.11.0.3\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:11:\"description\";s:34:\"Provides donotdisturb featurecodes\";s:8:\"category\";s:12:\"Applications\";s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:20:\"Do-Not-Disturb (DND)\";s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.3\";}s:15:\"printextensions\";a:15:{s:7:\"rawname\";s:15:\"printextensions\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:16:\"Print Extensions\";s:7:\"version\";s:8:\"2.11.0.2\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv3+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-3.0.txt\";s:8:\"category\";s:7:\"Reports\";s:11:\"description\";s:130:\"Creates a printable list of extension numbers used throughout the system from all modules that provide an internal callable number\";s:9:\"menuitems\";a:1:{s:15:\"printextensions\";s:16:\"Print Extensions\";}s:9:\"supported\";a:1:{s:7:\"version\";s:4:\"2.10\";}s:11:\"displayname\";s:16:\"Print Extensions\";s:5:\"items\";a:1:{s:15:\"printextensions\";a:4:{s:4:\"name\";s:16:\"Print Extensions\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:7:\"Reports\";s:4:\"sort\";i:0;}}s:6:\"status\";i:2;s:9:\"dbversion\";s:8:\"2.11.0.2\";}s:6:\"queues\";a:18:{s:7:\"rawname\";s:6:\"queues\";s:4:\"repo\";s:8:\"standard\";s:4:\"name\";s:6:\"Queues\";s:7:\"version\";s:9:\"2.11.0.30\";s:9:\"publisher\";s:18:\"Issabel Foundation\";s:7:\"license\";s:6:\"GPLv2+\";s:11:\"licenselink\";s:39:\"http://www.gnu.org/licenses/gpl-2.0.txt\";s:8:\"category\";s:12:\"Applications\";s:13:\"embedcategory\";s:20:\"Inbound Call Control\";s:11:\"description\";s:198:\"Creates a queue where calls are placed on hold and answered on a first-in, first-out basis. Many options are available, including ring strategy for agents, caller announcements, max wait times, etc.\";s:7:\"depends\";a:2:{s:7:\"version\";s:11:\"2.5.0alpha1\";s:6:\"module\";a:3:{i:0;s:19:\"recordings ge 3.3.8\";i:1;s:21:\"core ge 2.11.0.0rc1.2\";i:2;s:22:\"framework ge 2.11.0.47\";}}s:9:\"menuitems\";a:1:{s:6:\"queues\";s:6:\"Queues\";}s:8:\"popovers\";a:1:{s:6:\"queues\";a:1:{s:7:\"display\";s:6:\"queues\";}}s:9:\"supported\";a:1:{s:7:\"version\";s:6:\"2.11.0\";}s:11:\"displayname\";s:6:\"Queues\";s:5:\"items\";a:1:{s:6:\"queues\";a:5:{s:4:\"name\";s:6:\"Queues\";s:4:\"type\";s:5:\"setup\";s:8:\"category\";s:12:\"Applications\";s:4:\"sort\";i:0;s:13:\"needsenginedb\";s:3:\"yes\";}}s:6:\"status\";i:2;s:9:\"dbversion\";s:9:\"2.11.0.30\";}}'),('extmap_serialized',1619991709,'a:77:{i:700;s:30:\"ParkPlus: ParkCall Default Lot\";i:701;s:32:\"ParkPlus: PickupSlot Default Lot\";i:702;s:32:\"ParkPlus: PickupSlot Default Lot\";i:703;s:32:\"ParkPlus: PickupSlot Default Lot\";i:704;s:32:\"ParkPlus: PickupSlot Default Lot\";i:720;s:26:\"ParkPlus: ParkCall Perrito\";i:721;s:28:\"ParkPlus: PickupSlot Perrito\";i:722;s:28:\"ParkPlus: PickupSlot Perrito\";i:723;s:28:\"ParkPlus: PickupSlot Perrito\";i:724;s:28:\"ParkPlus: PickupSlot Perrito\";s:3:\"*30\";s:57:\"Featurecode: blacklist_add (blacklist:Blacklist a number)\";s:3:\"*32\";s:65:\"Featurecode: blacklist_last (blacklist:Blacklist the last caller)\";s:3:\"*31\";s:76:\"Featurecode: blacklist_remove (blacklist:Remove a number from the blacklist)\";s:4:\"*154\";s:54:\"Featurecode: bsc_off (bosssecretary:Bosssecretary Off)\";s:4:\"*153\";s:52:\"Featurecode: bsc_on (bosssecretary:Bosssecretary On)\";s:4:\"*152\";s:60:\"Featurecode: bsc_toggle (bosssecretary:Bosssecretary Toggle)\";s:3:\"*72\";s:57:\"Featurecode: cfon (callforward:Call Forward All Activate)\";s:3:\"*73\";s:60:\"Featurecode: cfoff (callforward:Call Forward All Deactivate)\";s:4:\"*720\";s:68:\"Featurecode: cfpon (callforward:Call Forward All Prompting Activate)\";s:3:\"*74\";s:74:\"Featurecode: cfoff_any (callforward:Call Forward All Prompting Deactivate)\";s:3:\"*90\";s:59:\"Featurecode: cfbon (callforward:Call Forward Busy Activate)\";s:3:\"*91\";s:62:\"Featurecode: cfboff (callforward:Call Forward Busy Deactivate)\";s:4:\"*900\";s:70:\"Featurecode: cfbpon (callforward:Call Forward Busy Prompting Activate)\";s:3:\"*92\";s:76:\"Featurecode: cfboff_any (callforward:Call Forward Busy Prompting Deactivate)\";s:3:\"*52\";s:76:\"Featurecode: cfuon (callforward:Call Forward No Answer/Unavailable Activate)\";s:3:\"*53\";s:79:\"Featurecode: cfuoff (callforward:Call Forward No Answer/Unavailable Deactivate)\";s:4:\"*520\";s:87:\"Featurecode: cfupon (callforward:Call Forward No Answer/Unavailable Prompting Activate)\";s:4:\"*740\";s:56:\"Featurecode: cf_toggle (callforward:Call Forward Toggle)\";s:3:\"*70\";s:55:\"Featurecode: cwon (callwaiting:Call Waiting - Activate)\";s:3:\"*71\";s:58:\"Featurecode: cwoff (callwaiting:Call Waiting - Deactivate)\";s:3:\"*87\";s:56:\"Featurecode: conf_status (conferences:Conference Status)\";s:2:\"*8\";s:60:\"Featurecode: pickupexten (core:Asterisk General Call Pickup)\";i:555;s:35:\"Featurecode: chanspy (core:ChanSpy)\";s:2:\"**\";s:63:\"Featurecode: disconnect (core:In-Call Asterisk Disconnect Code)\";s:2:\"*2\";s:61:\"Featurecode: atxfer (core:In-Call Asterisk Attended Transfer)\";s:2:\"##\";s:61:\"Featurecode: blindxfer (core:In-Call Asterisk Blind Transfer)\";s:2:\"*1\";s:66:\"Featurecode: automon (core:In-Call Asterisk Toggle Call Recording)\";i:7777;s:52:\"Featurecode: simu_pstn (core:Simulate Incoming Call)\";s:3:\"*12\";s:42:\"Featurecode: userlogoff (core:User Logoff)\";s:3:\"*11\";s:40:\"Featurecode: userlogon (core:User Logon)\";i:888;s:37:\"Featurecode: zapbarge (core:ZapBarge)\";s:3:\"*28\";s:61:\"Featurecode: toggle-mode-all (daynight:All: Call Flow Toggle)\";s:3:\"*35\";s:60:\"Featurecode: senddictate (dictate:Email completed dictation)\";s:3:\"*34\";s:50:\"Featurecode: dodictate (dictate:Perform dictation)\";s:3:\"*78\";s:47:\"Featurecode: dnd_on (donotdisturb:DND Activate)\";s:3:\"*79\";s:50:\"Featurecode: dnd_off (donotdisturb:DND Deactivate)\";s:3:\"*76\";s:49:\"Featurecode: dnd_toggle (donotdisturb:DND Toggle)\";i:666;s:43:\"Featurecode: simu_fax (fax:Dial System FAX)\";s:3:\"*21\";s:59:\"Featurecode: fmf_toggle (findmefollow:Findme Follow Toggle)\";s:3:\"*69\";s:48:\"Featurecode: calltrace (infoservices:Call Trace)\";s:3:\"*43\";s:46:\"Featurecode: echotest (infoservices:Echo Test)\";s:3:\"*65\";s:65:\"Featurecode: speakextennum (infoservices:Speak Your Exten Number)\";s:3:\"*60\";s:56:\"Featurecode: speakingclock (infoservices:Speaking Clock)\";s:3:\"*85\";s:58:\"Featurecode: parkedcall (parking:Pickup ParkedCall Prefix)\";i:411;s:75:\"Featurecode: app-pbdirectory (pbdirectory:Phonebook dial-by-name directory)\";s:3:\"*47\";s:47:\"Featurecode: que_callers (queues:Queue Callers)\";s:3:\"*46\";s:57:\"Featurecode: que_pause_toggle (queues:Queue Pause Toggle)\";s:3:\"*45\";s:45:\"Featurecode: que_toggle (queues:Queue Toggle)\";s:3:\"*99\";s:54:\"Featurecode: record_check (recordings:Check Recording)\";s:3:\"*77\";s:52:\"Featurecode: record_save (recordings:Save Recording)\";s:3:\"*75\";s:57:\"Featurecode: setspeeddial (speeddial:Set user speed dial)\";s:2:\"*0\";s:55:\"Featurecode: callspeeddial (speeddial:Speeddial prefix)\";s:3:\"*27\";s:74:\"Featurecode: toggle-mode-all (timeconditions:All: Time Condition Override)\";s:3:\"*98\";s:53:\"Featurecode: dialvoicemail (voicemail:Dial Voicemail)\";s:1:\"*\";s:63:\"Featurecode: directdialvoicemail (voicemail:Direct Dial Prefix)\";s:3:\"*97\";s:49:\"Featurecode: myvoicemail (voicemail:My Voicemail)\";i:200;s:23:\"User Extension: Nicolas\";i:201;s:22:\"User Extension: German\";i:202;s:21:\"User Extension: Jimmy\";i:203;s:23:\"User Extension: Agustin\";i:204;s:21:\"User Extension: Alfio\";i:205;s:24:\"User Extension: Federico\";i:206;s:22:\"User Extension: Zoiper\";i:207;s:24:\"User Extension: Linphone\";i:208;s:21:\"User Extension: Boris\";i:209;s:26:\"User Extension: SIP Webrtc\";i:210;s:26:\"User Extension: sip normal\";}'),('repos_serialized',1619990943,'a:1:{s:8:\"standard\";i:1;}'),('module_repo',1619990943,'http://cloud.issabel.org,http://cloud2.issabel.org'),('installid',1619990943,'c03d289ba24e8ecf5ce9123491625ba9'),('type',1619990943,''),('xml',1619990943,'<xml>\n<module>\n	<rawname>daynight</rawname>\n	<repo>standard</repo>\n	<name>Call Flow Control</name>\n	<version>2.11.0.6</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Inbound Call Control</embedcategory>\n	<description>\n		Call Flow manual toggle control - allows for two destinations to be chosen and provides a feature code\n		that toggles between the two destinations.\n	</description>\n	<changelog>\n		*2.11.0.6* Initial Issabel Foundation release\n	</changelog>\n	<depends>\n		<version>2.5.0alpha1</version>\n	</depends>\n	<menuitems>\n		<daynight needsenginedb=\"yes\">Call Flow Control</daynight>\n	</menuitems>\n	<popovers>\n		<daynight>\n			<display>daynight</display>\n		</daynight>\n 	</popovers>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/daynight-2.11.0.6.tgz</location>\n        <md5sum>d9b951444150e1edd9454a2b165c714f</md5sum>\n</module>\n<module>\n	<rawname>trunkbalance</rawname>\n	<name>Trunk Balance</name>\n	<version>1.1.5</version>\n	<publisher>POSSA</publisher>\n	<license>GPLv2+</license>\n	<candisable>yes</candisable>\n	<canuninstall>yes</canuninstall>\n    <repo>extended</repo>\n	<category>Advanced</category>\n	<changelog>\n		*1.1.5* Accept Self Signed Certs in URL\n		*1.1.4* Remove auto check for updates\n		*1.1.2* Added trunk disable toggle\n		*1.1.1* Allow multiple matching and non-matching rules\n		*1.1.0* Additional flexibility for billing periods\n		*1.0.2* pass dialed number directly to dialout macro\n		*1.0.1* fixed max unique call logic\n		*1.0.0* revised to work with FreePBX 2.10\n		*0.0.4.3* Allow balance of DAHDI trunks and move to Github\n		*0.0.4.2* bug fix\n		*0.0.4.1* added timeconditions module dependency\n		*0.0.4* added time condition\n		*0.0.3.3* bug fix\n		*0.0.3.2* bug fix\n		*0.0.3* freePBX 2.6 dependency\n		*0.0.2* display bug with Freepbx 2.5; improved uninstall. \n		*0.0.1* alpha release\n	</changelog>	\n	<embedcategory>Advanced</embedcategory>\n	<depends>\n		<module>core</module>\n		<version>ge 2.6</version>\n		<module>timeconditions ge2.6</module>\n	</depends>\n	<description>Restrict outbound calls or balance calls over multiple trunks based on user specified parameters</description>\n	<menuitems>\n		<trunkbalance>Trunk Balance</trunkbalance>\n	</menuitems>\n	<info>https://github.com/POSSA/freepbx-trunk-balancing</info>\n        <location>release/2.11/trunkbalance-1.1.5.tgz</location>\n</module>\n<module>\n  <rawname>pbdirectory</rawname>\n  <repo>extended</repo>\n  <name>Phonebook Directory</name>\n  <version>2.11.0.5</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n  <category>Admin</category>\n        <location>release/2.11/pbdirectory-2.11.0.5.tgz</location>\n  <description>Provides a dial-by-name directory for phonebook entries</description>\n	<changelog>\n		*2.11.0.5* Issabel Foundation release\n		*2.9.0.0* Forked from FreePBX modules, original publisher Sangoma Technologies\n	</changelog>\n  <depends>\n    <version>2.4.0</version>\n    <module>phonebook</module>\n    <module>speeddial</module>\n  </depends>\n        <md5sum>c7bfe1d64db5ca90988f520294bfa218</md5sum>\n  <supported>\n    <version>2.10</version>\n  </supported>\n</module>\n<module>\n	<rawname>callwaiting</rawname>\n	<repo>standard</repo>\n	<name>Call Waiting</name>\n	<description>Provides an option to turn on/off call waiting</description>\n	<category>Applications</category>\n	<version>2.11.0.4</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<changelog>\n		*2.11.0.4* Initial Issabel Foundation release\n		*1.1* First release for 2.2\n	</changelog>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/callwaiting-2.11.0.4.tgz</location>\n        <md5sum>aa1e288a433366b7dd032e5024f87f28</md5sum>\n</module>\n<module>\n	<rawname>queues</rawname>\n	<repo>standard</repo>\n	<name>Queues</name>\n	<version>2.11.0.30</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv2+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-2.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Inbound Call Control</embedcategory>\n	<description>\n		Creates a queue where calls are placed on hold and answered on a first-in, first-out basis. Many options are available, including ring strategy for agents, caller announcements, max wait times, etc.\n	</description>\n	<changelog>\n        *2.11.0.30* Fix recordings being deleted when queue continue is selected\n        *2.11.0.29* Rebranding\n        *2.11.0.28* Set url parameter to variable QURL\n		*2.11.0.26* Initial Issabel Foundation release\n	</changelog>\n	<depends>\n		<version>2.5.0alpha1</version>\n		<module>recordings ge 3.3.8</module>\n        <module>core ge 2.11.0.0rc1.2</module>\n        <module>framework ge 2.11.0.47</module>\n	</depends>\n	<menuitems>\n		<queues needsenginedb=\"yes\">Queues</queues>\n	</menuitems>\n	<popovers>\n		<queues>\n			<display>queues</display>\n		</queues>\n	</popovers>\n	<supported>\n		<version>2.11.0</version>\n	</supported>\n        <location>release/2.11/queues-2.11.0.30.tgz</location>\n        <md5sum>a524c7984991a3739729c2469db1d33a</md5sum>\n</module>\n<module>\n    <rawname>dynamicfeatures</rawname>\n    <repo>extended</repo>\n    <name>Dynamic Features</name>\n    <version>2.11.0.0</version>\n    <publisher>Issabel Foundation</publisher>\n    <license>GPLv3+</license>\n    <licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Internal Options &amp; Configuration</embedcategory>\n    <description>\n        Adds the ability to configure custom dynamic features to be executed while on a call\n    </description>\n    <menuitems>\n        <dynamicfeatures>Dynamic Features</dynamicfeatures>\n    </menuitems>\n    <changelog>\n        *2.11.0.0* Initial Issabel Foundation release\n    </changelog>\n    <depends>\n        <version>2.11.0</version>\n    </depends>\n        <location>release/2.11/dynamicfeatures-2.11.0.0.tgz</location>\n        <md5sum>047d2271bfed7396ed6173b379d3b83b</md5sum>\n    <supported>\n        <version>2.10</version>\n    </supported>\n</module>\n<module>\n    <rawname>music</rawname>\n    <repo>standard</repo>\n    <name>Music on Hold</name>\n    <version>2.11.0.3</version>\n    <publisher>Issabel Foundation</publisher>\n    <license>GPLv3+</license>\n    <licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <candisable>no</candisable>\n    <canuninstall>no</canuninstall>\n    <category>Settings</category>\n    <embedcategory>Internal Options &amp; Configuration</embedcategory>\n    <description>Uploading and management of sound files (wav, mp3) to be used for on-hold music.</description>\n    <changelog>\n        *2.11.0.3* Initial Issabel Foundation release\n    </changelog>\n    <menuitems>\n        <music>Music on Hold</music>\n    </menuitems>\n        <location>release/2.11/music-2.11.0.3.tgz</location>\n        <md5sum>8bc39e892baab56322553f3f56b2577e</md5sum>\n    <supported>\n        <version>2.10</version>\n    </supported>\n</module>\n<module>\n    <rawname>ivr</rawname>\n    <repo>standard</repo>\n    <name>IVR</name>\n    <version>2.11.0.12</version>\n    <publisher>Issabel Foundation</publisher>\n    <license>GPLv3+</license>\n    <licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Inbound Call Control</embedcategory>\n    <description>\n        Creates Digital Receptionist (aka Auto-Attendant, aka Interactive Voice Response) menus. These can be used to send callers to different locations (eg, Press 1 for sales) and/or allow direct-dialing of extension numbers.\n    </description>\n    <changelog>\n        *2.11.0.12* Added spoken options support using Asterisk 16 ppeech functions\n        *2.11.0.11* Rebranding\n        *2.11.0.10* Initial Issabel Foundation release\n    </changelog>\n    <depends>\n        <version>2.5.0alpha1</version>\n        <module>recordings ge 3.3.8</module>\n        <module>framework ge 2.11.0.47</module>\n    </depends>\n    <menuitems>\n        <ivr>IVR</ivr>\n    </menuitems>\n    <popovers>\n        <ivr>\n            <display>ivr</display>\n            <action>add</action>\n        </ivr>\n    </popovers>\n    <supported>\n        <version>2.11.0</version>\n    </supported>\n        <location>release/2.11/ivr-2.11.0.12.tgz</location>\n        <md5sum>bdf52ef9550bf93e46f4a3946d79d458</md5sum>\n</module>\n<module>\n	<rawname>weakpasswords</rawname>\n	<repo>standard</repo>\n	<name>Weak Password Detection</name>\n	<version>2.11.0.1</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<category>Reports</category>\n	<changelog>\n		*2.11.0.1* Initial Issabel Foundation release\n	</changelog>\n	<depends>\n		<version>2.5.0</version>\n	</depends>\n	<description>This module detects weak SIP secrets and sets security notifications accordingly\n	</description>\n	<supported>\n		<version>2.10</version>\n	</supported>\n	<menuitems>\n		<weakpasswords>Weak Password Detection</weakpasswords>\n	</menuitems>\n        <location>release/2.11/weakpasswords-2.11.0.1.tgz</location>\n        <md5sum>7ed5e01ef3516d3d3b99c2116f3a71ec</md5sum>\n</module>\n<module>\n    <rawname>dialplaninjection</rawname>\n    <name>Dialplan Injection</name>\n    <version>0.1.1n</version>\n    <type>setup</type>\n    <publisher>POSSA</publisher>\n    <category>Advanced</category>\n    <embedcategory>Advanced</embedcategory>\n    <description>Acts as a dialplan destination and can execute a variety of Asterisk commands</description>\n    <menuitems>\n        <dialplaninjection>Dialplan Injection</dialplaninjection>\n    </menuitems>\n        <depends>\n                <module>core</module>\n        </depends>\n        <changelog>\n            Lorne Gaetz: Initial commit\n    </changelog>\n    <attention>\n        This is an advanced module, and you should not use it without understanding asterisk dialplans!\n        This is meant as a convenience tool for someone who would have had to resort to config editing.\n        If you experience problems with it, just disable it and no harm done.\n    </attention>\n</module>\n<module>\n	<rawname>javassh</rawname>\n	<repo>extended</repo>\n	<name>Java SSH</name>\n	<version>2.11.2</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>AGPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/agpl-3.0.txt</licenselink>\n	<category>Admin</category>\n	<description>Provides a Java applet to access the system shell using SSH.</description>\n	<menuitems>\n		<javassh>Java SSH</javassh>\n	</menuitems>\n	<changelog>\n		*2.11.2* Initial Issabel Foundation release\n	</changelog>\n        <location>release/2.11/javassh-2.11.2.tgz</location>\n        <md5sum>51b90ffd23d4456af91928733019119c</md5sum>\n  <supported>\n    <version>2.10</version>\n  </supported>\n</module>\n<module>\n  <rawname>timeconditions</rawname>\n  <repo>standard</repo>\n  <name>Time Conditions</name>\n  <version>2.11.1.2</version>\n  <publisher>Issabel Foundation</publisher>\n  <license>GPLv3+</license>\n  <licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n  <category>Applications</category>\n  <embedcategory>Inbound Call Control</embedcategory>\n  <description>\n    Creates a condition where calls will go to one of two destinations (eg, an extension, IVR, ring group..) based on the time and/or date. This can be used for example to ring a receptionist during the day, or go directly to an IVR at night.\n  </description>\n  <changelog>\n    *2.11.1.2* Add autofill holidays function\n    *2.11.1.1* Initial Issabel Foundation release\n  </changelog>\n  <depends>\n    <version>2.5.0alpha1</version>\n  </depends>\n  <menuitems>\n    <timeconditions>Time Conditions</timeconditions>\n    <timegroups>Time Groups</timegroups>\n  </menuitems>\n  <popovers>\n    <timeconditions>\n      <display>timeconditions</display>\n    </timeconditions>\n  </popovers>\n        <location>release/2.11/timeconditions-2.11.1.2.tgz</location>\n        <md5sum>abb6b77e4a82799f83475f7af34f64d1</md5sum>\n  <supported>\n    <version>2.10</version>\n  </supported>\n</module>\n<module>\n	<rawname>queuemetrics</rawname>\n	<name>QueueMetrics</name>\n	<version>2.11.0.3</version>\n	<repo>unsupported</repo>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<category>Settings</category>\n	<description>A module for QueueMetrics, that allows you to set if IVR selections should be logged to the queue logs.</description>\n	<depends>\n		<module>ivr ge 2.11.0.1</module>\n		<version>2.11.0alpha1</version>\n		<phpversion>5.3.0</phpversion>\n	</depends>\n	<menuitems>\n		<queuemetrics>QueueMetrics</queuemetrics>\n	</menuitems>\n	<changelog>\n		*2.11.0.3* Initial Issabel Foundation release\n		*2.11.0.2* Include license file\n		*2.11.0.1* Packaging of ver 2.11.0.1\n		*2.11.0.0* Move from contributed to 2.11 branch\n		*2.10.0.3* Fix QueueMetrics naming\n		*2.10.0.2* Need to log to NONE for queuename for QueueMetrics\n		*2.10.0.1* Change the way data is logged for QueueMetrics\n		*2.10.0.0* First Implementation\n	</changelog>\n	<supported>\n		<version>2.11</version>\n	</supported>\n        <location>release/2.11/queuemetrics-2.11.0.3.tgz</location>\n        <md5sum>015d4ea6921769da33354107fde1afb4</md5sum>\n</module>\n<module>\n	<rawname>infoservices</rawname>\n	<repo>standard</repo>\n	<name>Info Services</name>\n	<version>2.11.0.3</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<candisable>no</candisable>\n	<canuninstall>no</canuninstall>\n	<category>Applications</category>\n	<description>Provides a number of applications accessible by feature codes: company directory, call trace (last call information), echo test, speaking clock, and speak current extension number.</description>\n	<changelog>\n		*2.11.0.3* Initial Issabel Foundation release\n	</changelog>\n	<supported>\n		<version>2.11</version>\n	</supported>\n        <location>release/2.11/infoservices-2.11.0.3.tgz</location>\n        <md5sum>62751bc58a996583b3fe95f1c493c94a</md5sum>\n</module>\n<module>\n	<rawname>iaxsettings</rawname>\n	<repo>standard</repo>\n	<name>Asterisk IAX Settings</name>\n	<version>2.11.0.3</version>\n	<publisher>Issabel Foundation</publisher>\n	<licenselink>http://www.gnu.org/licenses/agpl-3.0.txt</licenselink>\n	<license>AGPLv3</license>\n    <category>Settings</category>\n    <embedcategory>Advanced</embedcategory>\n	<menuitems>\n		<iaxsettings>Asterisk IAX Settings</iaxsettings>\n	</menuitems>\n	<description>\n		Use to configure Various Asterisk IAX Settings in the General section of iax.conf. The module assumes Asterisk version 1.4 or higher. Some settings may not exist in Asterisk 1.2 and will be ignored by Asterisk.\n	</description>\n	<changelog>\n		*2.11.0.3* Initial Issabel Foundation release\n	</changelog>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/iaxsettings-2.11.0.3.tgz</location>\n        <md5sum>9af6b5391200627e9c7dceaed5ac6a65</md5sum>\n</module>\n<module>\n	<rawname>conferences</rawname>\n	<repo>standard</repo>\n	<name>Conferences</name>\n	<version>2.11.0.6</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Internal Options &amp; Configuration</embedcategory>\n	<description>Allow creation of conference rooms (meet-me) where multiple people can talk together.</description>\n	<changelog>\n		*2.11.0.6* Initial Issabel Foundation release\n	</changelog>\n	<depends>\n		<version>2.5.0alpha1</version>\n		<module>recordings ge 3.3.8</module>\n	</depends>\n	<menuitems>\n		<conferences>Conferences</conferences>\n	</menuitems>\n	<popovers>\n		<conferences>\n			<display>conferences</display>\n		</conferences>\n 	</popovers>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/conferences-2.11.0.6.tgz</location>\n        <md5sum>b7b33ea0ab781d8e165b533dd72d852b</md5sum>\n</module>\n<module>\n	<rawname>asterisk-cli</rawname>\n	<repo>extended</repo>\n	<name>Asterisk CLI</name>\n	<version>2.11.0.3</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<category>Admin</category>\n	<description>Provides an interface allowing you to run a command as if it was typed into Asterisk CLI</description>\n	<menuitems>\n		<cli>Asterisk CLI</cli>\n	</menuitems>\n	<depends>\n		<engine>asterisk</engine>\n	</depends>\n        <location>release/2.11/asterisk-cli-2.11.0.3.tgz</location>\n        <md5sum>ba056c7eed0a3543eb7f3461e4dd7e5a</md5sum>\n	<changelog>\n		*2.11.0.3* Initial Issabel Foundation release\n	</changelog>\n	<supported>\n		<version>2.10</version>\n	</supported>\n</module>\n<module>\n	<rawname>dundicheck</rawname>\n	<repo>unsupported</repo>\n	<name>DUNDi Lookup Registry</name>\n	<version>2.11.0.3</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<changelog>\n		*2.11.0.3* Initial Issabel Foundation release\n	</changelog>\n	<description>\n		This module will check all configured and enabled DUNDi trunks as part of the extension registry function, and report back conflicts if\n		other sites have the same extensions. This does not filter against the route patterns - it will take any number being created and\n		report a conflict if that trunk could be used to call that number.\n	</description>\n	<menuitems>\n		<dundicheck>DUNDi Lookup</dundicheck>\n	</menuitems>\n	<depends>\n		<version>2.4.0</version>\n	</depends>\n	<supported>\n		<version>2.10</version>\n	</supported>\n	<category>Admin</category>\n        <location>release/2.11/dundicheck-2.11.0.3.tgz</location>\n        <md5sum>c898abf4f81bb48b0c5ae341570422ca</md5sum>\n</module>\n<module>\n        <rawname>writequeuelog</rawname>\n        <repo>standard</repo>\n        <name>Write a line into queue log</name>\n        <version>2.11.0.0</version>\n        <category>Applications</category>\n        <embedcategory>Advanced</embedcategory>\n        <publisher>Issabel Foundation</publisher>\n        <license>GPLv3+</license>\n        <licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n        <description>\n            Adds the ability to write into queue_log\n        </description>\n        <menuitems>\n                <writequeuelog>Write in Queue Log</writequeuelog>\n        </menuitems>\n        <changelog>\n                *2.11.1.0*  Initial Release\n        </changelog>\n        <depends>\n                <version>2.5.0</version>\n        </depends>\n        <supported>\n                <version>2.10</version>\n        </supported>\n        <location>release/2.11/writequeuelog-2.11.0.0.tgz</location>\n        <md5sum>f1897339d16dbed9a28e20c36623e5bf</md5sum>\n</module>\n<module>\n	<rawname>backup</rawname>\n	<repo>standard</repo>\n	<name>Backup &amp; Restore</name>\n	<version>2.11.0.23</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<category>Admin</category>\n	<description>Backup &amp; Restore for your IssabelPBX environment</description>\n	<menuitems>\n		<backup needsenginedb=\"yes\">Backup &amp; Restore</backup>\n		<backup_servers needsenginedb=\"yes\" hidden=\"true\">Backup &amp; Restore - Servers</backup_servers>\n		<backup_templates needsenginedb=\"yes\" hidden=\"true\">Backup &amp; Restore - Templates</backup_templates>\n		<backup_restore needsenginedb=\"yes\" hidden=\"true\">Backup &amp; Restore - Restore</backup_restore>\n	</menuitems>\n	<depends>\n		<module>core</module>\n	</depends>\n	<changelog>\n        *2.11.0.23* Rebranding\n		*2.11.0.22* Initial Issabel Foundation release\n	</changelog>\n	<supported>\n		<version>2.11</version>\n	</supported>\n        <location>release/2.11/backup-2.11.0.23.tgz</location>\n</module>\n<module>\n	<rawname>donotdisturb</rawname>\n	<repo>standard</repo>\n	<name>Do-Not-Disturb (DND)</name>\n	<version>2.11.0.3</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<changelog>\n		*2.11.0.3* Initial Issabel Foundation release\n	</changelog>\n	<description>Provides donotdisturb featurecodes</description>\n	<category>Applications</category>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/donotdisturb-2.11.0.3.tgz</location>\n        <md5sum>b006e556a1729682ffef7a6d70350cde</md5sum>\n</module>\n<module>\n	<rawname>featurecodeadmin</rawname>\n	<repo>standard</repo>\n	<name>Feature Code Admin</name>\n	<version>2.11.0.2</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<candisable>no</candisable>\n	<canuninstall>no</canuninstall>\n    <category>Admin</category>\n    <embedcategory>Basic</embedcategory>\n	<menuitems>\n		<featurecodeadmin>Feature Codes</featurecodeadmin>\n	</menuitems>\n        <location>release/2.11/featurecodeadmin-2.11.0.2.tgz</location>\n	<changelog>\n		*2.11.0.2* Initial Issabel Foundation release\n	</changelog>\n	<depends>\n		<version>2.5.0alpha1</version>\n	</depends>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <md5sum>32e039a754c68c399e567da0408ae2b1</md5sum>\n</module>\n<module>\n	<rawname>accountcodepreserve</rawname>\n	<repo>extended</repo>\n	<name>Preserve Accountcode</name>\n	<version>2.11.0.0</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv2</license>\n	<type>setup</type>\n	<category>Admin</category>\n	<description>This module preservers the first callee\'s account code, that has an accountcode, that it encounters. This preserved accoutcode will be used to set the CDR(accountcode) for any outbound calls that result from any type of redirected call (CF, VmX, Follow-Me, etc.). The account code for each user is pulled out of their associated device settings, which means this is only supported in extension mode and would have to be updated for deviceanduser mode, requiring a new accountcode field to be defined for the user on the extension/user screen.\n	</description>\n	<changelog>\n		*2.11.0.0* Initial Issabel Foundation release\n		*2.5.0* Initial Release\n	</changelog>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/accountcodepreserve-2.11.0.0.tgz</location>\n        <md5sum>95d237021fdedfe3ffb86b6287c01ec5</md5sum>\n</module>\n<module>\n    <rawname>queueprio</rawname>\n    <repo>extended</repo>\n    <name>Queue Priorities</name>\n    <version>2.11.0.2</version>\n    <publisher>Issabel Foundation</publisher>\n    <license>GPLv3+</license>\n    <licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Inbound Call Control</embedcategory>\n    <description>\n        Adds the ability to set a callers priority higher before entering a queue\n    </description>\n    <menuitems>\n        <queueprio>Queue Priorities</queueprio>\n    </menuitems>\n    <popovers>\n        <queueprio>\n            <display>queueprio</display>\n        </queueprio>\n    </popovers>\n    <changelog>\n        *2.11.0.2* Initial Issabel Foundation release\n    </changelog>\n    <depends>\n        <version>2.5.0alpha1</version>\n    </depends>\n        <location>release/2.11/queueprio-2.11.0.2.tgz</location>\n        <md5sum>f3f8a5b94fb0a0fe9a27a83f6621132e</md5sum>\n    <supported>\n        <version>2.10</version>\n    </supported>\n</module>\n<module>\n	<rawname>blacklist</rawname>\n	<repo>standard</repo>\n	<name>Blacklist</name>\n	<version>2.11.0.6</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Admin</category>\n    <embedcategory>Inbound Call Control</embedcategory>\n	<menuitems>\n		<blacklist needsenginedb=\"yes\">Blacklist</blacklist>\n	</menuitems>\n	<changelog>\n		*2.11.0.6* Initial Issabel Foundation release\n	</changelog>\n	<depends>\n		<module>core ge 2.5.1.2</module>\n	</depends>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/blacklist-2.11.0.6.tgz</location>\n        <md5sum>3638160171d4ebfb692c11a2f88c9b10</md5sum>\n</module>\n<module>\n	<rawname>inventorydb</rawname>\n	<name>Inventory</name>\n	<version>2.5.0.2</version>\n	<type>tool</type>\n	<category>Third Party Addon</category>\n	<menuitems>\n		<inventorydb>Inventory</inventorydb>\n	</menuitems>\n	<changelog>\n		*2.5.0.2* localization updates\n		*2.5.0.1* localization, Swedish\n		*2.5.0* #2845 tabindex\n		*2.4.0.1* #2645 API error - NOTICE: This module will be removed from future versions\n		*2.4.0* bumped for 2.4\n		*1.1.0* Added SQLite3 support. Fixes ticket:1783, bump for rc1\n		*1.0.3* Add he_IL translation\n	</changelog>\n        <location>release/2.11/inventorydb-2.5.0.2.tgz</location>\n        <md5sum>6c4e97b7bdfbad905f83c29d129b1bbc</md5sum>\n</module>\n\n<module>\n	<rawname>fax</rawname>\n	<repo>standard</repo>\n	<name>Fax Configuration</name>\n	<version>2.11.0.10</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<category>Settings</category>\n	<menuitems>\n		<fax>Fax Configuration</fax>\n	</menuitems>\n	<description>Adds configurations, options and GUI for inbound faxing</description>\n	<changelog>\n        *2.11.0.10* Rebranding\n		*2.11.0.9* Initial Issabel Foundation release\n	</changelog>\n	<depends>\n        <module>framework ge 2.11.0.47</module>\n	</depends>\n	<supported>\n        <version>2.11.0</version>\n	</supported>\n        <location>release/2.11/fax-2.11.0.10.tgz</location>\n        <md5sum>11a918dc1f4e163fb56ee17d903e85db</md5sum>\n</module>\n<module>\n	<rawname>voicemail</rawname>\n	<repo>standard</repo>\n	<name>Voicemail</name>\n	<version>2.11.1.7</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<candisable>no</candisable>\n	<canuninstall>no</canuninstall>\n	<changelog>\n        *2.11.1.7* Rebranding\n		*2.11.1.6* Initial Issabel Foundation release\n	</changelog>\n	<description>This module allows you to configure Voicemail for a user or extension</description>\n    <category>Settings</category>\n    <embedcategory>Advanced</embedcategory>\n	<menuitems>\n		<voicemail>Voicemail Admin</voicemail>\n	</menuitems>\n	<depends>\n        <version>2.5.0alpha1</version>\n        <module>framework ge 2.11.0.47</module>\n	</depends>\n	<supported>\n		<version>2.11.0</version>\n	</supported>\n        <location>release/2.11/voicemail-2.11.1.7.tgz</location>\n        <md5sum>fcb6cb5b1d82faa62a0993edd9c06393</md5sum>\n</module>\n<module>\n	<rawname>manager</rawname>\n	<repo>standard</repo>\n	<name>Asterisk Manager Users</name>\n	<version>2.11.0.5</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<category>Settings</category>\n	<menuitems>\n		<manager>Asterisk Manager Users</manager>\n	</menuitems>\n	<changelog>\n		*2.11.0.5* Initial Issabel Foundation release\n	</changelog>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/manager-2.11.0.5.tgz</location>\n        <md5sum>d1443f8aa5bc98afae791ce4392b0835</md5sum>\n</module>\n<module>\n    <rawname>core</rawname>\n    <repo>standard</repo>\n    <category>Applications</category>\n    <embedcategory>Basic</embedcategory>\n    <name>Core</name>\n    <version>2.11.0.48</version>\n    <publisher>Issabel Foundation</publisher>\n    <license>GPLv3+</license>\n    <licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <candisable>no</candisable>\n    <canuninstall>no</canuninstall>\n    <changelog>\n        *2.11.0.48* Multi Park support\n        *2.11.0.47* Bump release for Aug 2018 .iso and rebranding\n        *2.11.0.45* Added options for features and manager settings\n        *2.11.0.35* Initial Issabel Foundation release\n    </changelog>\n    <depends>\n        <module>framework ge 2.11.0.30</module>\n        <version>2.11.0alpha0</version>\n    </depends>\n    <requirements>\n        <file>/usr/sbin/asterisk</file>\n    </requirements>\n    <menuitems>\n        <extensions needsenginedb=\"yes\">Extensions</extensions>\n        <users needsenginedb=\"yes\">Users</users>\n        <devices needsenginedb=\"yes\">Devices</devices>\n        <did category=\"Connectivity\">Inbound Routes</did>\n        <dahdichandids category=\"Connectivity\">DAHDI Channel DIDs</dahdichandids>\n        <routing category=\"Connectivity\">Outbound Routes</routing>\n        <trunks category=\"Connectivity\">Trunks</trunks>\n        <advancedsettings category=\"Settings\">Advanced Settings</advancedsettings>\n        <ampusers category=\"Admin\">Administrators</ampusers>\n        <wiki category=\"Admin\" requires_auth=\"false\" href=\"http://www.issabel.com\" target=\"_blank\" access=\"all\">IssabelPBX Support</wiki>\n    </menuitems>\n    <popovers>\n        <extensions>\n            <display>extensions</display>\n        </extensions>\n        <users>\n            <display>users</display>\n        </users>\n     </popovers>\n    <methods>\n        <get_config pri=\"480\">core_do_get_config</get_config>\n    </methods>\n     <supported>\n         <version>2.11</version>\n    </supported>\n        <location>release/2.11/core-2.11.0.48.tgz</location>\n        <md5sum>77d54670bcdf36e2f540d304df5461d9</md5sum>\n</module>\n<module>\n	<rawname>customcontexts</rawname>\n	<repo>unsupported</repo>\n	<name>Class of Service</name>\n	<version>2.11.0.2</version>\n    <category>Connectivity</category>\n    <embedcategory>Basic</embedcategory>\n	<license>GPLv2+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-2.0.txt</licenselink>\n	<description>\n		Creates custom contexts which can be used to allow limited access to dialplan applications. Allows for time restrictions on any dialplan access. Allows for pattern matching to allow/deny. Allows for failover destinations, and PIN protected failover. This can be very useful for multi-tennant systems. Inbound routing can be done using DID or zap channel routing, this module allows for selective outbound routing. House/public phones can be placed in a restricted context allowing them only internal calls.\n	</description>\n	<menuitems>\n		<customcontexts>Class of Service</customcontexts>\n		<customcontextsadmin>Class of Service Admin</customcontextsadmin>\n	</menuitems>\n	<depends>\n		<version>2.8.0alpha1</version>\n		<module>core</module>\n		<module>timeconditions</module>\n	</depends>\n	<changelog>\n		*2.11.0.2* Initial Issabel Foundation release\n	</changelog>\n	<attention>\n		This is an advanced module, and you should not use it without understanding asterisk dialplans! This is meant as a convenience tool for someone who would have had to resort to config editing. If you experience problems with it, just disable it and no harm done. REMEMBER! Any device placed in a restricted context will have no access to the dialplan if this module is disabled until it is placed in a normal context!\n	</attention>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/customcontexts-2.11.0.2.tgz</location>\n        <md5sum>30f4673ac6ccb235b8194de47e15dd5d</md5sum>\n</module>\n<module>\n	<rawname>cdr</rawname>\n	<repo>standard</repo>\n	<description>Call Data Record report tools for viewing reports of your calls</description>\n	<name>CDR Reports</name>\n	<version>2.11.0.12</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<category>Reports</category>\n	<menuitems>\n		<cdr>CDR Reports</cdr>\n	</menuitems>\n	<changelog>\n		*2.11.0.12* Initial Issabel Foundation release\n	</changelog>\n	<depends>\n		<module>core ge 2.6.0</module>\n	</depends>\n	<supported>\n		<version>2.11</version>\n	</supported>\n        <location>release/2.11/cdr-2.11.0.12.tgz</location>\n        <md5sum>d8eb6038194c491a7899aa62fcef412f</md5sum>\n</module>\n<module>\n    <rawname>announcement</rawname>\n    <repo>standard</repo>\n    <name>Announcements</name>\n    <version>2.11.0.5</version>\n    <publisher>Issabel Foundation</publisher>\n    <license>GPLv3+</license>\n    <licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <changelog>\n        *2.11.0.5* Adds Text to Speech support via PicoTTS\n        *2.11.0.4* Initial Issabel Foundation release\n    </changelog>\n    <category>Applications</category>\n    <embedcategory>Inbound Call Control</embedcategory>\n    <description>\n        Plays back one of the system recordings or TTS (optionally allowing the user to skip it) and then goes to another destination.\n    </description>\n    <depends>\n        <version>2.5.0alpha1</version>\n        <module>recordings ge 3.3.8</module>\n    </depends>\n    <menuitems>\n        <announcement>Announcements</announcement>\n    </menuitems>\n    <popovers>\n        <announcement>\n            <display>announcement</display>\n        </announcement>\n     </popovers>\n    <supported>\n        <version>2.10</version>\n    </supported>\n        <location>release/2.11/announcement-2.11.0.5.tgz</location>\n        <md5sum>98f99ffb3aca6b5ff409c03830181494</md5sum>\n</module>\n<module>\n  <rawname>speeddial</rawname>\n  <repo>extended</repo>\n  <name>Speed Dial Functions</name>\n  <version>2.11.0.4</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<changelog>\n		*2.11.0.4* Initial Issabel Foundation release\n	</changelog>\n  <category>Settings</category>\n  <depends>\n    <module>phonebook</module>\n  </depends>\n        <location>release/2.11/speeddial-2.11.0.4.tgz</location>\n        <md5sum>39c2ad1c77fd4ab8a263360d1626aea9</md5sum>\n  <supported>\n    <version>2.10</version>\n  </supported>\n</module>\n<module>\n  <rawname>phpinfo</rawname>\n  <repo>extended</repo>\n  <name>PHP Info</name>\n  <version>2.11.0.1</version>\n  <publisher>IssabelPBX</publisher>\n  <license>GPLv2+</license>\n  <changelog>\n	*2.11.0.1* Initial Issabel Foundation release\n  </changelog>\n  <category>Reports</category>\n  <menuitems>\n    <phpinfo>PHP Info</phpinfo>\n  </menuitems>\n        <location>release/2.11/phpinfo-2.11.0.1.tgz</location>\n        <md5sum>000ec68e16dd39753e04c9bb9d2154db</md5sum>\n  <supported>\n    <version>2.10</version>\n  </supported>\n</module>\n<module>\n	<rawname>asteriskinfo</rawname>\n	<repo>standard</repo>\n	<name>Asterisk Info</name>\n	<version>2.11.0.89</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Reports</category>\n    <embedcategory>Advanced</embedcategory>\n	<description>\n		Provides a snapshot of the current Asterisk configuration\n	</description>\n	<menuitems>\n		<asteriskinfo>Asterisk Info</asteriskinfo>\n	</menuitems>\n	<depends>\n		<engine>asterisk</engine>\n		<version>2.5.0rc3</version>\n	</depends>\n	<changelog>\n		*2.11.0.9* Initial Issabel Foundation release\n		*0.1.0* Initial release\n	</changelog>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/asteriskinfo-2.11.0.89.tgz</location>\n        <md5sum>78be9997f6eea8d7b3252a46a1160dc6</md5sum>\n</module>\n<module>\n  <rawname>printextensions</rawname>\n  <repo>standard</repo>\n  <name>Print Extensions</name>\n  <version>2.11.0.2</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n  <category>Reports</category>\n  <description>Creates a printable list of extension numbers used throughout the system from all modules that provide an internal callable number</description>\n  <menuitems>\n    <printextensions>Print Extensions</printextensions>\n  </menuitems>\n	<changelog>\n		*2.11.0.2* Initial Issabel Foundation release\n	</changelog>\n        <location>release/2.11/printextensions-2.11.0.2.tgz</location>\n        <md5sum>fd26be22d1c75a2bc4a4828beb2e0e8d</md5sum>\n  <supported>\n    <version>2.10</version>\n  </supported>\n</module>\n<module>\n	<rawname>tts</rawname>\n	<name>Text To Speech</name>\n	<version>2.11.0.10</version>\n	<repo>extended</repo>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<type>setup</type>\n	<description>Allows you to configure text to speech, and is derived from texttospeech provided in contributed modules.</description>\n	<category>Applications</category>\n	<menuitems>\n		<tts>Text To Speech</tts>\n	</menuitems>\n	<depends>\n		<engine>asterisk ge 1.6</engine>\n		<module>ttsengines</module>\n	</depends>\n        <location>release/2.11/tts-2.11.0.10.tgz</location>\n	<changelog>\n		2.11.0.10 Initial Issabel Foundation release\n	</changelog>\n	<popovers>\n		<tts>\n			<display>tts</display>\n		</tts>\n	</popovers>\n	<supported>\n		<version>2.11</version>\n	</supported>\n        <md5sum>981fea779c6b6eebe4ebfb3c1461a279</md5sum>\n</module>\n<module>\n	<rawname>dahdiconfig</rawname>\n	<repo>extended</repo>\n	<category>Connectivity</category>\n	<name>DAHDi Config</name>\n	<version>2.11.53</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<candisable>yes</candisable>\n	<canuninstall>yes</canuninstall>\n	<changelog>\n			*2.11.53* Initial Issabel Foundation release\n	</changelog>\n	<supported>\n		<version>2.10</version>\n	</supported>\n	<depends>\n		<phpversion>5.3.0</phpversion>\n		<version>2.10</version>\n	</depends>\n	<menuitems>\n		<dahdi needsenginedb=\"yes\">DAHDi Config</dahdi>\n	</menuitems>\n        <location>release/2.11/dahdiconfig-2.11.53.tgz</location>\n        <md5sum>87b7955672749e699de808be10df68e4</md5sum>\n</module>\n<module>\n	<rawname>framework</rawname>\n	<modtype>framework</modtype>\n	<repo>standard</repo>\n	<name>IssabelPBX Framework</name>\n	<version>2.11.0.48</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<candisable>no</candisable>\n	<canuninstall>no</canuninstall>\n	<changelog>\n        *2.11.0.48* Multi Park, ODBC functions, Supports for Asterisk 16\n        *2.11.0.47* Bump release for Aug 2018 iso and rebranding\n        *2.11.0.46* Add default res_parking.conf used in asterisk 13\n		*2.11.0.45* Update release\n		*2.11.0.44* GitHub release\n		*2.11.0.43* Initial Issabel Foundation release\n	</changelog>\n	<description>\n		This module provides a facility to install bug fixes to the framework code that is not otherwise housed in a module\n	</description>\n	<supported>\n		<version>2.11</version>\n	</supported>\n	<category>Admin</category>\n        <location>release/2.11/framework-2.11.0.48.tgz</location>\n        <md5sum>ad1fa0310893be1148ceab7bd7edc32f</md5sum>\n</module>\n<module>\n	<rawname>cidlookup</rawname>\n	<repo>standard</repo>\n	<name>CallerID Lookup</name>\n	<version>2.11.1.12</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<description>Allows CallerID Lookup of incoming calls against different sources (OpenCNAM, MySQL, HTTP, ENUM, Phonebook Module)</description>\n    <category>Admin</category>\n    <embedcategory>Inbound Call Control</embedcategory>\n	<changelog>\n		*2.11.1.12* Initial Issabel Foundation release\n	</changelog>\n	<menuitems>\n		<cidlookup>CallerID Lookup Sources</cidlookup>\n	</menuitems>\n	<depends>\n		<engine>asterisk 1.6</engine>\n		<module>framework ge 2.11.0.0rc1.6</module>\n	</depends>\n	<supported>\n		<version>2.11</version>\n	</supported>\n        <location>release/2.11/cidlookup-2.11.1.12.tgz</location>\n        <md5sum>e17efc84bf3813c84b92a925eec7a2c5</md5sum>\n</module>\n<module>\n	<rawname>vmblast</rawname>\n	<repo>standard</repo>\n	<name>Voicemail Blasting</name>\n	<version>2.11.0.4</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Internal Options &amp; Configuration</embedcategory>\n	<description>\n		Creates a group of extensions that calls a group of voicemail boxes and allows you to leave a message for them all at once.\n	</description>\n	<changelog>\n		*2.11.0.4* Initial Issabel Foundation release\n	</changelog>\n	<menuitems>\n		<vmblast>Voicemail Blasting</vmblast>\n	</menuitems>\n	<popovers>\n		<vmblast>\n			<display>vmblast</display>\n		</vmblast>\n 	</popovers>\n	<depends>\n		<version>2.4.0</version>\n	</depends>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/vmblast-2.11.0.4.tgz</location>\n        <md5sum>9c2a9300c603b4dccd6e6272e9082d7a</md5sum>\n</module>\n<module>\n    <rawname>dynroute</rawname>\n    <repo>standard</repo>\n    <name>Dynamic Routes</name>\n    <version>2.11.3.2</version>\n    <publisher>voipsupport.it</publisher>\n    <license>GPLv3+</license>\n    <type>setup</type>\n    <category>Advanced</category>\n    <embedcategory>Inbound Call Control</embedcategory>\n    <description>\n        Allows to route call based on lookup in sql database and/or user input and to store results in channel variables\n    </description>\n    <changelog>\n        See CHANGES.txt file in module directory for full details.\n        *2.11.3.2* Bug fix\n        *2.11.3.1* Bug fix\n        *2.11.3.0* Asterisk variable source type\n        *2.11.2.0* AGI support\n        *2.11.1.0* Web service support\n        *2.11.0.0* ODBC support, popover compatibility, GUI cleanup, Updated Italian, Dutch translations\n        *2.10.0.2* Bug fix for db server name that was not saved, cleanups, dutch translations\n        *2.10.0.1* Security: prevent scripts from being called directly.\n        Updated uninstall from sql to php script, cosmetic and tooltip corrections.\n        *2.10.0.0* Only update is to change Category to be compatible with new menu categories (now under Applications instead of Inbound Call Control)\n        *2.8.0.0* Added ability to get dtmf input (with optional announcement) and storage of input and/or sql result in channel variables for use in further dynamic routes or custom extensions.\n        *2.6.0.2* First Release\n    </changelog>\n    <depends>\n        <version>ge2.11</version>\n        <module></module>\n    </depends>\n    <menuitems>\n        <dynroute>Dynamic Routes</dynroute>\n    </menuitems>\n        <popovers>\n                <dynroute>\n                        <display>dynroute</display>\n            <action>add</action>\n                </dynroute>\n        </popovers>\n        <location>release/2.11/dynroute-2.11.3.2.tgz</location>\n        <md5sum>0c7e3e6c0cf0e0ecad604e26af634878</md5sum>\n</module>\n<module>\n	<rawname>dashboard</rawname>\n	<repo>standard</repo>\n	<name>System Dashboard</name>\n	<version>2.11.0.5</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<candisable>no</candisable>\n	<canuninstall>no</canuninstall>\n	<category>Reports</category>\n	<description>\n		Provides a system information dashboard, showing information about Calls, CPU, Memory, Disks, Network, and processes.\n	</description>\n	<menuitems>\n		<dashboard display=\"index\" access=\"all\">IssabelPBX System Status</dashboard>\n	</menuitems>\n	<depends>\n		<version>2.3.0beta2</version>\n	</depends>\n	<changelog>\n		*2.11.0.5* Initial Issabel Foundation release\n	</changelog>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/dashboard-2.11.0.5.tgz</location>\n        <md5sum>f9746dda13e9df9adff71700fbcab253</md5sum>\n</module>\n<module>\n	<rawname>callforward</rawname>\n	<repo>standard</repo>\n	<name>Call Forward</name>\n	<version>2.11.5</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>AGPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/agpl-3.0.txt</licenselink>\n	<changelog>\n		*2.11.5* Initial Issabel Foundation release\n	</changelog>\n	<description>Provides callforward featurecodes</description>\n	<category>Applications</category>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/callforward-2.11.5.tgz</location>\n        <md5sum>6248a13b89e9c9345799096347b3f8bf</md5sum>\n</module>\n<module>\n	<rawname>miscdests</rawname>\n	<repo>extended</repo>\n	<name>Misc Destinations</name>\n	<version>2.11.0.4</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Internal Options &amp; Configuration</embedcategory>\n	<description>Allows creating destinations that dial any local number (extensions, feature codes, outside phone numbers) that can be used by other modules (eg, IVR, time conditions) as a call destination.</description>\n	<changelog>\n		*2.11.0.4* Initial Issabel Foundation release\n	</changelog>\n	<depends>\n		<version>2.5.0alpha1</version>\n	</depends>\n	<menuitems>\n		<miscdests>Misc Destinations</miscdests>\n	</menuitems>\n	<popovers>\n		<miscdests>\n			<display>miscdests</display>\n		</miscdests>\n	</popovers>\n        <location>release/2.11/miscdests-2.11.0.4.tgz</location>\n        <md5sum>6736d35eb781351534e7dc348f49d8e6</md5sum>\n	<supported>\n		<version>2.10</version>\n	</supported>\n</module>\n<module>\n  <rawname>restart</rawname>\n  <repo>unsupported</repo>\n  <name>Bulk Phone Restart</name>\n  <version>2.11.0.2</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n  <category>Admin</category>\n	<changelog>\n		*2.11.0.2* Initial Issabel Foundation release\n		*2.6.0.0* Initial release\n	</changelog>\n  <depends>\n    <version>2.5.0</version>\n  </depends>\n  <description>This module allows users to restart one or multiple phones that support being restarted via a SIP NOTIFY command through Asterisk\'s sip_notify.conf \n	</description>\n  <menuitems>\n    <restart>Phone Restart</restart>\n  </menuitems>\n        <location>release/2.11/restart-2.11.0.2.tgz</location>\n        <md5sum>3dd2a9e0253544c662c94648d2021bb5</md5sum>\n  <supported>\n    <version>2.10</version>\n  </supported>\n</module>\n<module>\n	<rawname>bosssecretary</rawname>\n	<name>Boss Secretary</name>\n	<version>1.0</version>\n	<changelog>\n		*1.0* Module changed to new dialpan. Module is now compatible with Asterisk 1.6X and newer with all BLF functionality.\n	</changelog>\n    <repo>extended</repo>\n	<publisher>Issabel Foundation</publisher>\n	<category>Applications</category>\n    <embedcategory>Internal Options &amp; Configuration</embedcategory>\n	<description>\n		The boss-secretary module creates a special ring group which includes\none or more \"bosses\" and one or more secretaries\". When someone calls\nthe boss\' extension, the secretary (or secretaries) extension will ring only, allowing the secretary to answer his or her boss\' call. Only secretary ( or secretaries ) or \'chief extensions\' are authorized to call directly to boss extension. With feature code you can turn on or off secretary group. \n	</description>\n	<menuitems>\n		<bosssecretary>Boss Secretary</bosssecretary>\n	</menuitems>\n</module>\n<module>\n	<rawname>fw_fop</rawname>\n	<repo>unsupported</repo>\n	<name>IssabelPBX FOP Framework</name>\n	<version>2.10.0.3</version>\n	<publisher>IssabelPBX</publisher>\n	<license>GPLv2+</license>\n	<menuitems>\n		<fw_fop href=\"modules/fw_fop/\" target=\"_fop\" access=\"all\">FOP Panel</fw_fop>\n	</menuitems>\n	<changelog>\n		*2.10.0.3* #5818\n		*2.10.0.2* #5496\n		*2.10.0.1* fix for fw_fop_parse_zapata\n		*2.10.0.0* upgrade xml info, category, support info\n		*2.10.0beta2.0* moving FOP to full module\n		*2.10.0beta1.0* beta1 release, see svn log\n		*2.9.0.1* #4401, #4914, #4404 \n		*2.9.0.0* 2.9 Update\n	</changelog>\n	<description>\n		This module provides the original FOP (Flash Operator Panel) which is known to have issues on Asterisk 1.8 and is no longer supported in favor of FOP2.\n	</description>\n	<category>Admin</category>\n	<depends>\n		<version>2.9.0beta2</version>\n	</depends>\n        <location>release/2.11/fw_fop-2.10.0.3.tgz</location>\n        <md5sum>85af7e6568ec89f8cd31a27dda42fc01</md5sum>\n</module>\n<module>\n	<rawname>dictate</rawname>\n	<repo>extended</repo>\n	<name>Dictation</name>\n	<version>2.11.0.3</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<category>Applications</category>\n	<changelog>\n		*2.11.0.3* Initial Issabel Foundation release\n	</changelog>\n	<description>This uses the app_dictate module of Asterisk to let users record dictate into their phones. When complete, the dictations can be emailed to an email address specified in the extension page.</description>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/dictate-2.11.0.3.tgz</location>\n        <md5sum>967c00998438a7f0cee9863101ace629</md5sum>\n</module>\n<module>\n	<rawname>asternic</rawname>\n	<name>Asternic</name>\n	<version>2.11.0.0</version>\n	<repo>unsupported</repo>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<category>Settings</category>\n	<description>A module for Asternic Call Center Stats PRO, that allows you to set if IVR selections should be logged to the queue logs.</description>\n	<depends>\n		<module>ivr ge 2.11.0.1</module>\n		<version>2.11.0alpha1</version>\n		<phpversion>5.3.0</phpversion>\n	</depends>\n	<menuitems>\n		<asternic>Asternic</asternic>\n	</menuitems>\n	<changelog>\n		*2.11.0.0* Initial Issabel Foundation release\n	</changelog>\n	<supported>\n		<version>2.11</version>\n	</supported>\n        <location>release/2.11/asternic-2.11.0.0.tgz</location>\n        <md5sum>e1fb9c4d68eecfca3a4da98fc85ce66d</md5sum>\n</module>\n<module>\n	<rawname>bulkextensions</rawname>\n	<repo>extended</repo>\n    <name>Bulk Extensions</name>\n	<description>Bulk Extensions uses CSV files to import and export extensions.</description>\n	<version>2.11.0.7</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<category>Applications</category>\n	<menuitems>\n		<bulkextensions>Bulk Extensions</bulkextensions>\n	</menuitems>\n	<depends>\n		<version>ge2.10</version>\n	</depends>\n        <location>release/2.11/bulkextensions-2.11.0.7.tgz</location>\n	<info/>\n	<changelog>\n		*2.11.0.7* Initial Issabel Foundation release\n		*2.5.0.5* Add permit/deny fields provided by 4Colo. Fixed a small bug in table.csv. Fixed spelling error in template.csv. Added localization for table.csv. See CHANGES for how-to.\n		*0.1*	First release\n	</changelog>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <md5sum>2e53632661bc4824f7cf2935cefe75a0</md5sum>\n</module>\n<module>\n	<rawname>campon</rawname>\n	<repo>standard</repo>\n	<name>Camp-On</name>\n	<version>2.11.0.2</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<category>Settings</category>\n	<changelog>\n		*2.11.0.3* Initial Issabel Foundation release\n	</changelog>\n	<description>This module implements the Call Completion Supplemental Services (CCSS) often referred to as Call Camping or Camp-On. It allows a caller to request the system call them back when a busy or non-responding extension becomes available. Requires Asterisk 1.8 or higher.\n	</description>\n	<depends>\n		<engine>asterisk 1.8</engine>\n	</depends>\n	<supported>\n		<version>2.9</version>\n	</supported>\n        <location>release/2.11/campon-2.11.0.2.tgz</location>\n        <md5sum>7f2488575d81239ff3e58a6fa626d543</md5sum>\n</module>\n<module>\n	<rawname>sipsettings</rawname>\n	<repo>standard</repo>\n	<candisable>no</candisable>\n	<canuninstall>no</canuninstall>\n	<name>Asterisk SIP Settings</name>\n	<version>2.11.0.9</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>AGPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/agpl-3.0.txt</licenselink>\n    <category>Settings</category>\n    <embedcategory>Advanced</embedcategory>\n	<menuitems>\n		<sipsettings>Asterisk SIP Settings</sipsettings>\n	</menuitems>\n	<description>\n		Use to configure Various Asterisk SIP Settings in the General section of sip.conf. Also includes an auto-configuration tool to determine NAT settings. The module assumes Asterisk version 1.4 or higher. Some settings may not exist in Asterisk 1.2 and will be ignored by Asterisk.\n	</description>\n	<changelog>\n		*2.11.0.9* Initial Issabel Foundation release\n	</changelog>\n    <depends>\n        <module>core ge 2.11.0.0beta2.4</module>\n    </depends>\n	<supported>\n		<version>2.11</version>\n	</supported>\n        <location>release/2.11/sipsettings-2.11.0.9.tgz</location>\n        <md5sum>ce4cd15d926ae9276e86e4d72d57accf</md5sum>\n</module>\n<module>\n  <rawname>phpagiconf</rawname>\n  <repo>unsupported</repo>\n  <name>PHPAGI Config</name>\n  <version>2.11.0.2</version>\n  <publisher>Issabel Foundation</publisher>\n  <license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n  <category>Settings</category>\n  <menuitems>\n    <phpagiconf>PHPAGI Config</phpagiconf>\n  </menuitems>\n  <depends>\n    <module>manager ge1.0.4</module>\n  </depends>\n  <changelog>\n    *2.11.0.2* Initial Issabel Foundation release\n  </changelog>\n        <location>release/2.11/phpagiconf-2.11.0.2.tgz</location>\n        <md5sum>df37b454a9396ba53f1f5df81b25ada1</md5sum>\n  <supported>\n    <version>2.10</version>\n  </supported>\n</module>\n<module>\n	<rawname>fw_langpacks</rawname>\n	<modtype>framework</modtype>\n	<repo>extended</repo>\n	<name>IssabelPBX Localization Updates</name>\n	<version>2.11.2</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<changelog>\n		*2.11.2* Initial Issabel Foundation release\n	</changelog>\n	<description>\n		This module provides a facility to install new and updated localization translations for all components in IssabelPBX. Localization i18n translations are still kept with each module and other components such as the User Portal (ARI). This provides an easy ability to bring all components up-to-date without the need of publishing dozens of modules for every minor change. The localization updates used will be the latest available for all modules and will not consider the current version you are running.\n	</description>\n	<category>Admin</category>\n        <location>release/2.11/fw_langpacks-2.11.2.tgz</location>\n        <md5sum>35fdd36e3cb134bd3462cd5808207120</md5sum>\n	<supported>\n		<version>2.11</version>\n	</supported>\n</module>\n<module>\n	<rawname>findmefollow</rawname>\n	<repo>standard</repo>\n	<name>Follow Me</name>\n	<version>2.11.0.6</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<changelog>\n		*2.11.0.6* Initial Issabel Foundation release\n	</changelog>\n	<depends>\n		<version>2.5.0alpha1</version>\n		<module>recordings ge 3.3.8</module>\n	</depends>\n    <category>Applications</category>\n    <embedcategory>Inbound Call Control</embedcategory>\n	<description>\n		Much like a ring group, but works on individual extensions. When someone calls the extension, it can be setup to ring for a number of seconds before trying to ring other extensions and/or external numbers, or to ring all at once, or in other various \'hunt\' configurations. Most commonly used to ring someone\'s cell phone if they don\'t answer their extension.\n	</description>\n	<menuitems>\n		<findmefollow needsenginedb=\"yes\">Follow Me</findmefollow>\n	</menuitems>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/findmefollow-2.11.0.6.tgz</location>\n        <md5sum>a7df4c8450760ea9e21b3437bb86206d</md5sum>\n</module>\n<module>\n	<rawname>digium_phones</rawname>\n	<repo>standard</repo>\n	<type>setup</type>\n	<category>Connectivity</category>\n	<name>Digium Phones Config</name>\n	<version>2.11.3.3</version>\n	<publisher>Digium</publisher>\n	<license>GPLv2</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<candisable>yes</candisable>\n	<canuninstall>yes</canuninstall>\n	<changelog>\n        *2.11.3.3* Rebranding\n		*2.11.3.2* IssabelPBX initial release\n		*2.11.0.1* FreePBX/Schmoozecom Release\n		*0.1* initial commit\n	</changelog>\n	<depends>\n		<phpversion>5.2.0</phpversion>\n		<version>2.10</version>\n        <module>framework ge 2.11.0.47</module>\n    </depends>\n	<menuitems>\n		<digium_phones needsenginedb=\"yes\">Digium Phones</digium_phones>\n	</menuitems>\n	<supported>\n		<version>2.11.0</version>\n	</supported>\n        <location>release/2.11/digium_phones-2.11.3.3.tgz</location>\n        <md5sum>f1dbf298e09c54ef69078e79104f6997</md5sum>\n</module>\n<module>\n    <rawname>recordings</rawname>\n    <repo>standard</repo>\n    <name>Recordings</name>\n    <version>3.4.0.4</version>\n    <publisher>Issabel Foundation</publisher>\n    <license>GPLv3+</license>\n    <licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <canuninstall>no</canuninstall>\n    <category>Admin</category>\n    <embedcategory>Internal Options &amp; Configuration</embedcategory>\n    <description>Creates and manages system recordings, used by many other modules (eg, IVR).</description>\n    <changelog>\n        *3.4.0.4* Adds recording from browser\n        *3.4.0.3* Initial Issabel Foundation release\n    </changelog>\n    <menuitems>\n        <recordings>System Recordings</recordings>\n    </menuitems>\n    <depends>\n       <module>framework ge 2.11.0.47</module>\n    </depends>\n    <supported>\n        <version>2.11</version>\n    </supported>\n        <location>release/2.11/recordings-3.4.0.4.tgz</location>\n        <md5sum>f7f1dc5521bdcddc444620a81d300176</md5sum>\n</module>\n<module>\n    <rawname>motif</rawname>\n    <name>Google Voice/Chan Motif</name>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <type>setup</type>\n    <repo>standard</repo>\n    <category>Connectivity</category>\n	<version>2.11.17</version>\n    <description>\n        Manage Google Voice Trunks with Chan Motif\n    </description>\n    <changelog>\n	*2.11.17* Initial Issabel Foundation release\n    </changelog>\n    <depends>\n        <engine>asterisk 11</engine>\n        <module>core ge 2.11.0.0beta2.4</module>\n    </depends>\n    <menuitems>\n        <motif>Google Voice (Motif)</motif>\n    </menuitems>\n	<supported>\n		<version>2.11</version>\n	</supported>\n        <location>release/2.11/motif-2.11.17.tgz</location>\n        <md5sum>b9b9eff259a0d7918e77abba78c1eda5</md5sum>\n</module>\n<module>\n	<rawname>pinsets</rawname>\n	<repo>standard</repo>\n	<name>PIN Sets</name>\n	<version>2.11.0.9</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Settings</category>\n    <embedcategory>Internal Options &amp; Configuration</embedcategory>\n	<description>Allow creation of lists of PINs (numbers for passwords) that can be used by other modules (eg, trunks).</description>\n	<changelog>\n		*2.11.0.9* Initial Issabel Foundation release\n	</changelog>\n	<menuitems>\n		<pinsets>PIN Sets</pinsets>\n	</menuitems>\n	<supported>\n		<version>2.10</version>\n	</supported>\n	<depends>\n		<module>core</module>\n	</depends>\n        <location>release/2.11/pinsets-2.11.0.9.tgz</location>\n	<methods>\n		<get_config pri=\"481\">pinsets_get_config</get_config>\n	</methods>\n        <md5sum>f1fc09766e4373bafd7ea553f82b90fe</md5sum>\n</module>\n<module>\n  <rawname>phonebook</rawname>\n  <repo>extended</repo>\n  <name>Phonebook</name>\n  <version>2.11.0.2</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n  <description>Provides a phonebook for IssabelPBX, it can be used as base for Caller ID Lookup and Speed Dial</description>\n  <category>Admin</category>\n  <menuitems>\n    <phonebook needsenginedb=\"yes\">Asterisk Phonebook</phonebook>\n  </menuitems>\n        <location>release/2.11/phonebook-2.11.0.2.tgz</location>\n        <md5sum>667c2f1d7a490e55ae54b23db1194ac7</md5sum>\n  <changelog>\n  		*2.11.0.2* Initial Issabel Foundation release\n	</changelog>\n  <supported>\n    <version>2.10</version>\n  </supported>\n</module>\n<module>\n        <rawname>setcid</rawname>\n        <repo>standard</repo>\n        <name>Set CallerID</name>\n        <version>2.11.1.0</version>\n        <category>Applications</category>\n        <embedcategory>Inbound Call Control</embedcategory>\n        <publisher>Issabel Foundation</publisher>\n        <license>GPLv3+</license>\n        <licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n        <description>\n                Adds the ability to change the CallerID within a call flow.\n        </description>\n        <menuitems>\n                <setcid>Set CallerID</setcid>\n        </menuitems>\n        <changelog>\n                *2.11.1.0* Add custom Variables support\n                *2.11.0.4* Initial Issabel Foundation release\n        </changelog>\n        <depends>\n                <version>2.5.0</version>\n        </depends>\n        <supported>\n                <version>2.10</version>\n        </supported>\n        <location>release/2.11/setcid-2.11.1.0.tgz</location>\n        <md5sum>3877aaaef9cd3348ee5903c68a7f7a8b</md5sum>\n</module>\n<module>\n	<rawname>managersettings</rawname>\n	<repo>standard</repo>\n	<name>Asterisk Manager Settings</name>\n	<version>2.11.0.0</version>\n	<publisher>Issabel Foundation</publisher>\n	<licenselink>http://www.gnu.org/licenses/agpl-3.0.txt</licenselink>\n	<license>AGPLv3</license>\n    <category>Settings</category>\n    <embedcategory>Advanced</embedcategory>\n	<menuitems>\n		<managersettings>Asterisk Manager Settings</managersettings>\n	</menuitems>\n	<description>\n		Use to configure Various Asterisk Manager Settings in the General section of manager.conf. The module assumes Asterisk version 11 or higher.\n	</description>\n	<changelog>\n		*2.11.0.0* Initial Issabel Foundation release\n	</changelog>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/managersettings-2.11.0.0.tgz</location>\n        <md5sum>548af5d87d3efd79cf77431fa713a66e</md5sum>\n</module>\n<module>\n	<rawname>logfiles</rawname>\n	<repo>standard</repo>\n	<name>Asterisk Logfiles</name>\n	<canuninstall>no</canuninstall>\n	<version>2.11.1.4</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<changelog>\n        *2.11.1.4* Rebranding\n		*2.11.1.3* Initial Issabel Foundation release\n	</changelog>\n	<category>Reports</category>\n	<embedcategory>Advanced</embedcategory>\n	<menuitems>\n		<logfiles>Asterisk Logfiles</logfiles>\n		<logfiles_settings category=\"Settings\">Asterisk Logfile Settings</logfiles_settings>\n	</menuitems>\n	<supported>\n		<version>2.11.0</version>\n    </supported>\n    <depends>\n       <module>framework ge 2.11.0.47</module>\n    </depends>\n        <location>release/2.11/logfiles-2.11.1.4.tgz</location>\n        <md5sum>c69dbe1de5481980a8c67c0d59d82229</md5sum>\n</module>\n<module>\n	<rawname>disa</rawname>\n	<repo>extended</repo>\n	<name>DISA</name>\n	<version>2.11.0.6</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Remote Access</embedcategory>\n	<menuitems>\n		<disa>DISA</disa>\n	</menuitems>\n	<popovers>\n		<disa>\n			<display>disa</display>\n		</disa>\n 	</popovers>\n	<description>DISA Allows you \'Direct Inward System Access\'. This gives you the ability to have an option on an IVR that gives you a dial tone, and you\'re able to dial out from the IssabelPBX machine as if you were connected to a standard extension. It appears as a Destination.</description>\n	<changelog>\n		*2.11.0.6* Initial Issabel Foundation release\n	</changelog>\n	<depends>\n		<version>2.4.0</version>\n	</depends>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/disa-2.11.0.6.tgz</location>\n        <md5sum>bf9ccaa4f81925748b63ab0fcbf50912</md5sum>\n</module>\n<module>\n    <rawname>outroutemsg</rawname>\n    <repo>standard</repo>\n    <name>Route Congestion Messages</name>\n    <version>2.11.0.3</version>\n    <publisher>Issabel Foundation</publisher>\n    <license>GPLv3+</license>\n    <licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Settings</category>\n    <embedcategory>Advanced</embedcategory>\n    <description>Configures message or congestion tones played when all trunks are busy in a route. Allows different messages for Emergency Routes and Intra-Company Routes\n    </description>\n    <menuitems>\n        <outroutemsg>Route Congestion Messages</outroutemsg>\n    </menuitems>\n    <changelog>\n        *2.11.0.3* Add HangupCause check for unallocated numbers and info tone\n        *2.11.0.2* Initial Issabel Foundation release\n    </changelog>\n    <depends>\n        <module>recordings ge 3.3.8</module>\n    </depends>\n        <location>release/2.11/outroutemsg-2.11.0.3.tgz</location>\n        <md5sum>279f092d2132d8b427cb371f3336e757</md5sum>\n    <supported>\n        <version>2.10</version>\n    </supported>\n</module>\n<module>\n	<rawname>ringgroups</rawname>\n	<repo>standard</repo>\n	<name>Ring Groups</name>\n	<version>2.11.0.6</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Inbound Call Control</embedcategory>\n	<description>\n		Creates a group of extensions that all ring together. Extensions can be rung all at once, or in various \'hunt\' configurations. Additionally, external numbers are supported, and there is a call confirmation option where the callee has to confirm if they actually want to take the call before the caller is transferred.\n	</description>\n	<changelog>\n		*2.11.0.6* Initial Issabel Foundation release\n	</changelog>\n	<depends>\n		<version>2.5.0alpha1</version>\n		<module>recordings ge 3.3.8</module>\n	</depends>\n	<menuitems>\n		<ringgroups>Ring Groups</ringgroups>\n	</menuitems>\n	<popovers>\n		<ringgroups>\n			<display>ringgroups</display>\n		</ringgroups>\n 	</popovers>\n	<supported>\n		<version>2.11</version>\n	</supported>\n        <location>release/2.11/ringgroups-2.11.0.6.tgz</location>\n        <md5sum>24b4c2743d49b49c888405b36d70d641</md5sum>\n</module>\n<module>\n	<rawname>customerdb</rawname>\n	<name>Customer DB</name>\n	<version>2.5.0.4</version>\n	<type>tool</type>\n	<category>Third Party Addon</category>\n	<menuitems>\n		<customerdb>Customer DB</customerdb>\n	</menuitems>\n	<changelog>\n		*2.5.0.4* localization updates\n		*2.5.0.3* localization enclosures\n		*2.5.0.2* #2987 sqlite3 install script changes\n		*2.5.0.1* #2781 allow sqlite table creation\n		*2.5.0* #2845 tabindex\n		*2.4.0* it translations, bump for 2.4\n		*1.2.3.1* bump for rc1\n		*1.2.3* Add he_IL translation\n	</changelog>\n        <location>release/2.11/customerdb-2.5.0.4.tgz</location>\n        <md5sum>4cd70ff6e38632bc0a9aa2d1dcc37745</md5sum>\n</module>\n\n<module>\n	<rawname>hotelwakeup</rawname>\n	<name>Wake Up Calls</name>\n	<repo>standard</repo>\n	<version>2.11.3</version>\n	<license>GPLv2</license>\n	<publisher>POSSA</publisher>\n	<category>Applications</category>\n		<description>IssabelPBX module for generating reminder and wakeup calls</description>\n	<info>https://github.com/POSSA/Hotel-Style-Wakeup-Calls</info>\n	 <changelog>\n		 *2.11.3* Initial Issabel Foundation release\n	</changelog>\n	<menuitems>\n		<hotelwakeup>Wake Up Calls</hotelwakeup>\n	</menuitems>\n	<depends>\n		<module>ivr gt1.0</module>\n		<version>ge 2.9</version>\n		<engine>asterisk 1.2</engine>\n	</depends>\n	<supported>\n		<version>2.11</version>\n	</supported>\n        <location>release/2.11/hotelwakeup-2.11.3.tgz</location>\n        <md5sum>8cc536cd850013c65c55ce85d8ce94f8</md5sum>\n</module>\n<module>\n    <rawname>paging</rawname>\n    <repo>standard</repo>\n    <name>Paging and Intercom</name>\n    <version>2.11.0.10</version>\n    <publisher>Issabel Foundation</publisher>\n    <license>GPLv3+</license>\n    <licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Internal Options &amp; Configuration</embedcategory>\n    <changelog>\n        *2.11.0.10* Rebranding\n        *2.11.0.9* Initial Issabel Foundation release\n    </changelog>\n    <depends>\n        <version>2.11.0</version>\n        <module>framework ge 2.11.0.47</module>\n    </depends>\n    <description>Allows creation of paging groups to make announcements using the speaker built into most SIP phones. \n        Also creates an Intercom feature code that can be used as a prefix to talk directly to one person, as well as optional feature codes to block/allow intercom calls to all users as well as blocking specific users or only allowing specific users.</description>\n    <menuitems>\n        <paging>Paging and Intercom</paging>\n    </menuitems>\n    <requirements>\n        <module>conferences</module>\n    </requirements>\n    <popovers>\n        <paging>\n            <display>paging</display>\n            <action>add</action>\n        </paging>\n    </popovers>\n        <location>release/2.11/paging-2.11.0.10.tgz</location>\n        <md5sum>154ed1f3baff828a67f2bad663e81e6d</md5sum>\n    <supported>\n		<version>2.11.0</version>\n    </supported>\n</module>\n<module>\n  <rawname>extensionsettings</rawname>\n  <repo>unsupported</repo>\n  <name>Extension Settings</name>\n  <version>2.11.0.2</version>\n  <publisher>Mikael Carlsson</publisher>\n  <license>GPLv2</license>\n  <category>Settings</category>\n  <description>Creates a list of all extensions and their current settings for CW, CF, CFB, CFU, VMXB and VMXU</description>\n  <menuitems>\n    <extensionsettings>Extension Settings</extensionsettings>\n  </menuitems>\n  <changelog>\n		*2.11.0.2* Initial Issabel Foundation release\n	</changelog>\n        <md5sum>c2253809614e923ad16126c38cb81258</md5sum>\n        <location>release/2.11/extensionsettings-2.11.0.2.tgz</location>\n  <supported>\n    <version>2.10</version>\n  </supported>\n</module>\n<module>\n    <rawname>miscapps</rawname>\n    <repo>extended</repo>\n    <name>Misc Applications</name>\n    <version>2.11.0.2</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Internal Options &amp; Configuration</embedcategory>\n    <description>\n		Adds the ability to create feature codes that can go to any IssabelPBX destination (such as an IVR or queue)\n	</description>\n    <menuitems>\n        <miscapps>Misc Applications</miscapps>\n    </menuitems>\n    <changelog>\n  		*2.11.0.2* Initial Issabel Foundation release\n	</changelog>\n    <depends>\n        <version>2.4.0</version>\n    </depends>\n        <location>release/2.11/miscapps-2.11.0.2.tgz</location>\n        <md5sum>8c88bb73e52fd41cebc86a30c5d60bd2</md5sum>\n    <supported>\n        <version>2.10</version>\n    </supported>\n</module>\n<module>\n	<rawname>bulkdids</rawname>\n	<repo>extended</repo>\n	<name>Bulk DIDs</name>\n	<description>Bulk DIDs uses CSV files to import bulk DIDs with a destination.</description>\n	<version>2.11.1.4</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<category>Applications</category>\n	<menuitems>\n		<bulkdids>Bulk DIDs</bulkdids>\n	</menuitems>\n	<depends>\n		<version>ge2.10alpha0</version>\n	</depends>\n        <location>release/2.11/bulkdids-2.11.1.4.tgz</location>\n	<info/>\n	<changelog>\n		*2.11.1.4* Initial Issabel Foundation release\n	</changelog>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <md5sum>61813f3b3e96d0677b93520fadfbc5b9</md5sum>\n</module>\n<module>\n	<rawname>directory</rawname>\n	<repo>standard</repo>\n	<name>Directory</name>\n	<version>2.11.0.5</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<type>setup</type>\n	<category>Applications</category>\n	<menuitems>\n		<directory>Directory</directory>\n	</menuitems>\n	<popovers>\n		<directory>\n			<display>directory</display>\n			<action>add</action>\n		</directory>\n 	</popovers>\n	<depends>\n		<version>2.8.0alpha1</version>\n		<module>recordings ge 3.3.8</module>\n	</depends>\n	<changelog>\n		*2.11.0.5* Initial Issabel Foundation release\n	</changelog>\n	<supported>\n		<version>2.11</version>\n	</supported>\n        <location>release/2.11/directory-2.11.0.5.tgz</location>\n        <md5sum>fcdf1f60efd15ab2a611dcf77f6c946c</md5sum>\n</module>\n<module>\n	<rawname>customappsreg</rawname>\n	<repo>standard</repo>\n	<name>Custom Applications</name>\n	<version>2.11.0.2</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Admin</category>\n    <embedcategory>Advanced</embedcategory>\n	<description>\n		Registry to add custom extensions and destinations that may be created and used so that the Extensions and Destinations Registry can include these.\n	</description>\n	<menuitems>\n		<customextens>Custom Extensions</customextens>\n		<customdests>Custom Destinations</customdests>\n	</menuitems>\n	<popovers>\n		<customappsreg>\n			<display>customdests</display>\n		</customappsreg>\n 	</popovers>\n	<changelog>\n		*2.11.0.2* Initial Issabel Foundation release\n	</changelog>\n  <depends>\n    <version>2.4.0</version>\n  </depends>\n        <location>release/2.11/customappsreg-2.11.0.2.tgz</location>\n        <md5sum>16bec09eafa00acc3f6f21deadb0480a</md5sum>\n  <supported>\n    <version>2.10</version>\n  </supported>\n</module>\n<module>\n    <rawname>parking</rawname>\n    <repo>standard</repo>\n    <name>Parking Lot</name>\n    <version>2.11.0.16</version>\n    <publisher>Issabel Foundation</publisher>\n    <license>GPLv3+</license>\n    <licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Internal Options &amp; Configuration</embedcategory>\n    <description>Manages parking lot extensions and other options.\n    Parking is a way of putting calls \"on hold\", and then picking them up from any extension.\n    </description>\n    <changelog>\n        *2.11.0.16* Add support for mutiple parking lots\n        *2.11.0.15* Initial Issabel Foundation release\n    </changelog>\n    <menuitems>\n        <parking>Parking</parking>\n    </menuitems>\n    <depends>\n        <engine>asterisk ge 1.8</engine>\n        <version>2.11</version>\n        <module>core ge 2.11.0.48</module>\n    </depends>\n    <supported>\n        <version>2.11</version>\n    </supported>\n        <location>release/2.11/parking-2.11.0.16.tgz</location>\n        <md5sum>c755d0a3723a95e05e610c1b368d4cec</md5sum>\n</module>\n<module>\n	<rawname>superfecta</rawname>\n	<repo>extended</repo>\n	<name>CID Superfecta</name>\n	<version>2.11.18</version>\n	<type>setup</type>\n	<category>Admin</category>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv2+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-2.0.txt</licenselink>\n	<description>\n		Provides simultaneous use of, and complete control over multiple caller id data sources.\n	</description>\n	<changelog>\n        *2.11.18* Rebranding Changes\n		*2.11.17* Initial Issabel Foundation release\n	</changelog>\n	<menuitems>\n		<superfecta>CID Superfecta</superfecta>\n	</menuitems>\n	<info>https://github.com/POSSA/Caller-ID-Superfecta/wiki</info>\n	<supported>\n		<version>2.11.0</version>\n    </supported>\n	<depends>\n        <module>framework ge 2.11.0.47</module>\n	</depends>\n        <location>release/2.11/superfecta-2.11.18.tgz</location>\n        <md5sum>c52858e1a33f4697f223ec2cd26d21f3</md5sum>\n</module>\n<module>\n	<rawname>callrecording</rawname>\n	<repo>standard</repo>\n	<name>Call Recording</name>\n	<version>2.11.0.10</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n	<candisable>no</candisable>\n	<canuninstall>no</canuninstall>\n	<category>Applications</category>\n    <embedcategory>Inbound Call Control</embedcategory>\n	<description>\n		Provides much of the call recording functionality.\n	</description>\n	<menuitems>\n		<callrecording>Call Recording</callrecording>\n	</menuitems>\n 	<popovers>\n		<callrecording>\n			<display>callrecording</display>\n		</callrecording>\n 	</popovers>\n	<changelog>\n		*2.11.0.10* Embed in Issabel Menu\n		*2.11.0.9* Initial Issabel Foundation release\n	</changelog>\n	<supported>\n		<version>2.11</version>\n	</supported>\n        <location>release/2.11/callrecording-2.11.0.10.tgz</location>\n        <md5sum>f04b934f672c1e1ee462d50fbdf68ba7</md5sum>\n</module>\n<module>\n    <rawname>gabcast</rawname>\n    <name>Gabcast</name>\n    <version>2.5.0.2</version>\n    <type>tool</type>\n    <category>Third Party Addon</category>\n    <menuitems>\n        <gabcast>Gabcast</gabcast>\n    </menuitems>\n    <changelog>\n        *2.5.0.2* localization updates\n        *2.5.0.1* added localization ability\n        *2.5.0* localization fixes\n        *2.4.0.1* added depends on 2.4.0\n        *2.4.0* add dest registry, fix rnav formating\n        *1.2.5.1* bump for rc1\n        *1.2.5* #2070 fix proper use of script tags\n        *1.2.4* changed ${CALLERID(number)} to ${AMPUSER} to accomodate CID number masquerading\n        *1.2.3* Add he_IL translation\n        *1.2.2* Fix issue where you were unable to add a channel \n    </changelog>\n    <depends>\n        <version>2.4.0</version>\n    </depends>\n        <location>release/2.11/gabcast-2.5.0.2.tgz</location>\n        <md5sum>1bfb8f2e901a3c241c5073c9f6467d0d</md5sum>\n</module>\n<module>\n	<rawname>callback</rawname>\n	<repo>extended</repo>\n	<name>Callback</name>\n	<version>2.11.0.4</version>\n	<publisher>Issabel Foundation</publisher>\n	<license>GPLv3+</license>\n	<licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Remote Access</embedcategory>\n	<menuitems>\n		<callback>Callback</callback>\n	</menuitems>\n	<popovers>\n		<callback>\n			<display>callback</display>\n		</callback>\n 	</popovers>\n	<changelog>\n		*2.11.0.4* Initial Issabel Foundation release\n	</changelog>\n	<depends>\n		<version>2.4.0</version>\n	</depends>\n	<supported>\n		<version>2.10</version>\n	</supported>\n        <location>release/2.11/callback-2.11.0.4.tgz</location>\n        <md5sum>24cbbf1f49918d9319511406251ed5b2</md5sum>\n</module>\n<module>\n    <rawname>languages</rawname>\n    <repo>extended</repo>\n    <name>Languages</name>\n    <version>2.11.0.2</version>\n    <publisher>Issabel Foundation</publisher>\n    <license>GPLv3+</license>\n    <licenselink>http://www.gnu.org/licenses/gpl-3.0.txt</licenselink>\n    <category>Applications</category>\n    <embedcategory>Internal Options &amp; Configuration</embedcategory>\n    <description>\n        Adds the ability to changes the language within a call flow and add language attribute to users.\n    </description>\n    <menuitems>\n        <languages>Languages</languages>\n    </menuitems>\n    <popovers>\n        <languages>\n            <display>languages</display>\n        </languages>\n    </popovers>\n    <changelog>\n        *2.11.0.2* Initial Issabel Foundation release\n    </changelog>\n    <depends>\n        <version>2.5.0alpha1</version>\n    </depends>\n        <location>release/2.11/languages-2.11.0.2.tgz</location>\n        <md5sum>c1ebf0621cfd27e584637693a712a495</md5sum>\n    <supported>\n        <version>2.10</version>\n    </supported>\n</module>\n</xml>\n\n');
/*!40000 ALTER TABLE `module_xml` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modulename` varchar(50) NOT NULL DEFAULT '',
  `version` varchar(20) NOT NULL DEFAULT '',
  `enabled` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,'timeconditions','2.11.1.2',1),(2,'core','2.11.0.49',1),(3,'ivr','2.11.0.12',1),(4,'javassh','2.11.2',1),(5,'pinsets','2.11.0.9',1),(6,'conferences','2.11.0.6',1),(7,'customcontexts','2.11.0.2',1),(8,'miscapps','2.11.0.2',1),(9,'daynight','2.11.0.6',1),(10,'parking','2.11.0.16',1),(11,'ringgroups','2.11.0.6',1),(12,'dialplaninjection','0.1.1n',1),(13,'restart','2.11.0.2',1),(14,'outroutemsg','2.11.0.3',1),(15,'speeddial','2.11.0.4',1),(16,'cdr','2.11.0.12',1),(17,'pbdirectory','2.11.0.5',1),(18,'phonebook','2.11.0.2',1),(19,'iaxsettings','2.11.0.3',1),(20,'fax','2.11.0.10',1),(21,'weakpasswords','2.11.0.1',1),(22,'disa','2.11.0.6',1),(23,'sipsettings','2.11.1.0',1),(24,'featurecodeadmin','2.11.0.2',1),(25,'vmblast','2.11.0.4',1),(26,'queueprio','2.11.0.2',1),(27,'manager','2.11.0.5',1),(28,'blacklist','2.11.0.6',1),(29,'callforward','2.11.5',1),(30,'voicemail','2.11.1.7',1),(31,'fw_langpacks','2.11.2',1),(32,'dynamicfeatures','2.11.0.0',1),(33,'framework','2.11.0.49',1),(34,'customappsreg','2.11.0.2',1),(35,'backup','2.11.0.23',1),(36,'asterisk-cli','2.11.0.3',1),(37,'inventorydb','2.5.0.2',1),(38,'announcement','2.11.0.5',1),(39,'miscdests','2.11.0.4',1),(40,'callrecording','2.11.0.10',1),(41,'customerdb','2.5.0.4',1),(42,'phpagiconf','2.11.0.2',1),(43,'managersettings','2.11.0.0',1),(45,'asteriskinfo','2.11.0.89',1),(46,'findmefollow','2.11.0.6',1),(47,'writequeuelog','2.11.0.0',1),(48,'callback','2.11.0.4',1),(49,'callwaiting','2.11.0.4',1),(50,'languages','2.11.0.2',1),(51,'dundicheck','2.11.0.3',1),(52,'dynroute','2.11.3.2',1),(53,'recordings','3.4.0.4',1),(54,'dictate','2.11.0.3',1),(55,'phpinfo','2.11.0.1',1),(56,'setcid','2.11.1.0',1),(57,'logfiles','2.11.1.4',1),(58,'bosssecretary','1.0',1),(59,'cidlookup','2.11.1.12',1),(60,'trunkbalance','1.1.5',1),(61,'infoservices','2.11.0.3',1),(62,'music','2.11.0.3',1),(63,'paging','2.11.0.11',1),(64,'dashboard','2.11.0.5',1),(65,'donotdisturb','2.11.0.3',1),(66,'printextensions','2.11.0.2',1),(67,'queues','2.11.0.30',1);
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `module` varchar(24) NOT NULL DEFAULT '',
  `id` varchar(24) NOT NULL DEFAULT '',
  `level` int(11) NOT NULL DEFAULT '0',
  `display_text` varchar(255) NOT NULL DEFAULT '',
  `extended_text` blob NOT NULL,
  `link` varchar(255) NOT NULL DEFAULT '',
  `reset` tinyint(4) NOT NULL DEFAULT '0',
  `candelete` tinyint(4) NOT NULL DEFAULT '0',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`module`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `outbound_route_patterns`
--

DROP TABLE IF EXISTS `outbound_route_patterns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `outbound_route_patterns` (
  `route_id` int(11) NOT NULL,
  `match_pattern_prefix` varchar(60) NOT NULL DEFAULT '',
  `match_pattern_pass` varchar(60) NOT NULL DEFAULT '',
  `match_cid` varchar(60) NOT NULL DEFAULT '',
  `prepend_digits` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`route_id`,`match_pattern_prefix`,`match_pattern_pass`,`match_cid`,`prepend_digits`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `outbound_route_patterns`
--

LOCK TABLES `outbound_route_patterns` WRITE;
/*!40000 ALTER TABLE `outbound_route_patterns` DISABLE KEYS */;
INSERT INTO `outbound_route_patterns` VALUES (1,'9','.','','');
/*!40000 ALTER TABLE `outbound_route_patterns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `outbound_route_sequence`
--

DROP TABLE IF EXISTS `outbound_route_sequence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `outbound_route_sequence` (
  `route_id` int(11) NOT NULL,
  `seq` int(11) NOT NULL,
  PRIMARY KEY (`route_id`,`seq`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `outbound_route_sequence`
--

LOCK TABLES `outbound_route_sequence` WRITE;
/*!40000 ALTER TABLE `outbound_route_sequence` DISABLE KEYS */;
INSERT INTO `outbound_route_sequence` VALUES (1,0);
/*!40000 ALTER TABLE `outbound_route_sequence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `outbound_route_trunks`
--

DROP TABLE IF EXISTS `outbound_route_trunks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `outbound_route_trunks` (
  `route_id` int(11) NOT NULL,
  `trunk_id` int(11) NOT NULL,
  `seq` int(11) NOT NULL,
  PRIMARY KEY (`route_id`,`trunk_id`,`seq`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `outbound_route_trunks`
--

LOCK TABLES `outbound_route_trunks` WRITE;
/*!40000 ALTER TABLE `outbound_route_trunks` DISABLE KEYS */;
INSERT INTO `outbound_route_trunks` VALUES (1,1,0);
/*!40000 ALTER TABLE `outbound_route_trunks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `outbound_routes`
--

DROP TABLE IF EXISTS `outbound_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `outbound_routes` (
  `route_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `outcid` varchar(40) DEFAULT NULL,
  `outcid_mode` varchar(20) DEFAULT NULL,
  `password` varchar(30) DEFAULT NULL,
  `emergency_route` varchar(4) DEFAULT NULL,
  `intracompany_route` varchar(4) DEFAULT NULL,
  `mohclass` varchar(80) DEFAULT NULL,
  `time_group_id` int(11) DEFAULT NULL,
  `dest` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`route_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `outbound_routes`
--

LOCK TABLES `outbound_routes` WRITE;
/*!40000 ALTER TABLE `outbound_routes` DISABLE KEYS */;
INSERT INTO `outbound_routes` VALUES (1,'9_outside','','','','','','',NULL,NULL);
/*!40000 ALTER TABLE `outbound_routes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `outroutemsg`
--

DROP TABLE IF EXISTS `outroutemsg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `outroutemsg` (
  `keyword` varchar(40) NOT NULL DEFAULT '',
  `data` varchar(10) NOT NULL,
  PRIMARY KEY (`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `outroutemsg`
--

LOCK TABLES `outroutemsg` WRITE;
/*!40000 ALTER TABLE `outroutemsg` DISABLE KEYS */;
/*!40000 ALTER TABLE `outroutemsg` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paging_autoanswer`
--

DROP TABLE IF EXISTS `paging_autoanswer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paging_autoanswer` (
  `useragent` varchar(255) NOT NULL,
  `var` varchar(20) NOT NULL,
  `setting` varchar(255) NOT NULL,
  PRIMARY KEY (`useragent`,`var`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paging_autoanswer`
--

LOCK TABLES `paging_autoanswer` WRITE;
/*!40000 ALTER TABLE `paging_autoanswer` DISABLE KEYS */;
INSERT INTO `paging_autoanswer` VALUES ('default','CALLINFO','Call-Info: <uri>\\;answer-after=0'),('default','ALERTINFO','Alert-Info: Ring Answer'),('default','SIPURI','intercom=true'),('Mitel','CALLINFO','Call-Info: <sip:broadworks.net>\\;answer-after=0'),('Panasonic','ALERTINFO','Alert-Info: Intercom'),('Polycom','ALERTINFO','Alert-Info: info=Auto Answer'),('Digium','ALERTINFO','Alert-Info: ring-answer');
/*!40000 ALTER TABLE `paging_autoanswer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paging_config`
--

DROP TABLE IF EXISTS `paging_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paging_config` (
  `page_group` varchar(255) NOT NULL DEFAULT '',
  `force_page` int(1) NOT NULL,
  `duplex` int(1) NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`page_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paging_config`
--

LOCK TABLES `paging_config` WRITE;
/*!40000 ALTER TABLE `paging_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `paging_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paging_groups`
--

DROP TABLE IF EXISTS `paging_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paging_groups` (
  `page_number` varchar(50) NOT NULL DEFAULT '',
  `ext` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`page_number`,`ext`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paging_groups`
--

LOCK TABLES `paging_groups` WRITE;
/*!40000 ALTER TABLE `paging_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `paging_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parkplus`
--

DROP TABLE IF EXISTS `parkplus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parkplus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `defaultlot` varchar(10) NOT NULL DEFAULT 'no',
  `type` varchar(10) NOT NULL DEFAULT 'public',
  `name` varchar(40) NOT NULL DEFAULT '',
  `parkext` varchar(40) NOT NULL DEFAULT '',
  `parkpos` varchar(40) NOT NULL DEFAULT '',
  `numslots` int(11) NOT NULL DEFAULT '4',
  `parkingtime` int(11) NOT NULL DEFAULT '45',
  `parkedmusicclass` varchar(100) NOT NULL DEFAULT 'default',
  `generatefc` varchar(10) NOT NULL DEFAULT 'yes',
  `generatehints` varchar(10) NOT NULL DEFAULT 'yes',
  `findslot` varchar(10) NOT NULL DEFAULT 'first',
  `parkedplay` varchar(10) NOT NULL DEFAULT 'both',
  `parkedcalltransfers` varchar(10) NOT NULL DEFAULT 'caller',
  `parkedcallreparking` varchar(10) NOT NULL DEFAULT 'caller',
  `alertinfo` varchar(254) NOT NULL DEFAULT '',
  `cidpp` varchar(100) NOT NULL DEFAULT '',
  `autocidpp` varchar(10) NOT NULL DEFAULT 'none',
  `announcement_id` int(11) DEFAULT NULL,
  `comebacktoorigin` varchar(10) NOT NULL DEFAULT 'yes',
  `dest` varchar(100) NOT NULL DEFAULT 'app-blackhole,hangup,1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parkplus`
--

LOCK TABLES `parkplus` WRITE;
/*!40000 ALTER TABLE `parkplus` DISABLE KEYS */;
INSERT INTO `parkplus` VALUES (1,'yes','public','Default Lot','700','701',4,45,'default','yes','yes','first','both','caller','caller','','','none',NULL,'yes','app-blackhole,hangup,1');
/*!40000 ALTER TABLE `parkplus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phpagiconf`
--

DROP TABLE IF EXISTS `phpagiconf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phpagiconf` (
  `phpagiid` int(11) NOT NULL AUTO_INCREMENT,
  `debug` tinyint(1) DEFAULT NULL,
  `error_handler` tinyint(1) DEFAULT NULL,
  `err_email` varchar(50) DEFAULT NULL,
  `hostname` varchar(255) DEFAULT NULL,
  `tempdir` varchar(255) DEFAULT NULL,
  `festival_text2wave` varchar(255) DEFAULT NULL,
  `asman_server` varchar(255) DEFAULT NULL,
  `asman_port` int(11) NOT NULL,
  `asman_user` varchar(50) DEFAULT NULL,
  `asman_secret` varchar(255) DEFAULT NULL,
  `cepstral_swift` varchar(255) DEFAULT NULL,
  `cepstral_voice` varchar(50) DEFAULT NULL,
  `setuid` tinyint(1) DEFAULT NULL,
  `basedir` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`phpagiid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phpagiconf`
--

LOCK TABLES `phpagiconf` WRITE;
/*!40000 ALTER TABLE `phpagiconf` DISABLE KEYS */;
/*!40000 ALTER TABLE `phpagiconf` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pinset_usage`
--

DROP TABLE IF EXISTS `pinset_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pinset_usage` (
  `pinsets_id` int(11) NOT NULL,
  `dispname` varchar(30) NOT NULL DEFAULT '',
  `foreign_id` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`dispname`,`foreign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pinset_usage`
--

LOCK TABLES `pinset_usage` WRITE;
/*!40000 ALTER TABLE `pinset_usage` DISABLE KEYS */;
/*!40000 ALTER TABLE `pinset_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pinsets`
--

DROP TABLE IF EXISTS `pinsets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pinsets` (
  `pinsets_id` int(11) NOT NULL AUTO_INCREMENT,
  `passwords` longtext,
  `description` varchar(50) DEFAULT NULL,
  `addtocdr` tinyint(1) DEFAULT NULL,
  `deptname` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`pinsets_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pinsets`
--

LOCK TABLES `pinsets` WRITE;
/*!40000 ALTER TABLE `pinsets` DISABLE KEYS */;
/*!40000 ALTER TABLE `pinsets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queueprio`
--

DROP TABLE IF EXISTS `queueprio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queueprio` (
  `queueprio_id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_priority` varchar(50) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `dest` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`queueprio_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queueprio`
--

LOCK TABLES `queueprio` WRITE;
/*!40000 ALTER TABLE `queueprio` DISABLE KEYS */;
/*!40000 ALTER TABLE `queueprio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queues_config`
--

DROP TABLE IF EXISTS `queues_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queues_config` (
  `extension` varchar(20) NOT NULL DEFAULT '',
  `descr` varchar(35) NOT NULL DEFAULT '',
  `grppre` varchar(100) NOT NULL DEFAULT '',
  `alertinfo` varchar(254) NOT NULL DEFAULT '',
  `ringing` tinyint(1) NOT NULL DEFAULT '0',
  `maxwait` varchar(8) NOT NULL DEFAULT '',
  `password` varchar(20) NOT NULL DEFAULT '',
  `ivr_id` varchar(8) NOT NULL DEFAULT '0',
  `dest` varchar(50) NOT NULL DEFAULT '',
  `cwignore` tinyint(1) NOT NULL DEFAULT '0',
  `qregex` varchar(255) DEFAULT NULL,
  `agentannounce_id` int(11) DEFAULT NULL,
  `joinannounce_id` int(11) DEFAULT NULL,
  `queuewait` tinyint(1) DEFAULT '0',
  `use_queue_context` tinyint(1) DEFAULT '0',
  `togglehint` tinyint(1) DEFAULT '0',
  `qnoanswer` tinyint(1) DEFAULT '0',
  `callconfirm` tinyint(1) DEFAULT '0',
  `callconfirm_id` int(11) DEFAULT NULL,
  `monitor_type` varchar(5) DEFAULT NULL,
  `monitor_heard` int(11) DEFAULT NULL,
  `monitor_spoken` int(11) DEFAULT NULL,
  `callback_id` varchar(8) NOT NULL DEFAULT '',
  `destcontinue` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`extension`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queues_config`
--

LOCK TABLES `queues_config` WRITE;
/*!40000 ALTER TABLE `queues_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `queues_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queues_details`
--

DROP TABLE IF EXISTS `queues_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queues_details` (
  `id` varchar(45) NOT NULL DEFAULT '-1',
  `keyword` varchar(30) NOT NULL DEFAULT '',
  `data` varchar(150) NOT NULL DEFAULT '',
  `flags` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`keyword`,`data`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queues_details`
--

LOCK TABLES `queues_details` WRITE;
/*!40000 ALTER TABLE `queues_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `queues_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recordings`
--

DROP TABLE IF EXISTS `recordings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recordings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `displayname` varchar(50) DEFAULT NULL,
  `filename` blob,
  `description` varchar(254) DEFAULT NULL,
  `fcode` tinyint(1) DEFAULT '0',
  `fcode_pass` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recordings`
--

LOCK TABLES `recordings` WRITE;
/*!40000 ALTER TABLE `recordings` DISABLE KEYS */;
INSERT INTO `recordings` VALUES (1,'__invalid','install done','',0,NULL);
/*!40000 ALTER TABLE `recordings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ringgroups`
--

DROP TABLE IF EXISTS `ringgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ringgroups` (
  `grpnum` varchar(20) NOT NULL,
  `strategy` varchar(50) NOT NULL,
  `grptime` smallint(6) NOT NULL,
  `grppre` varchar(100) DEFAULT NULL,
  `grplist` varchar(255) NOT NULL,
  `annmsg_id` int(11) DEFAULT NULL,
  `postdest` varchar(255) DEFAULT NULL,
  `description` varchar(35) NOT NULL,
  `alertinfo` varchar(255) DEFAULT NULL,
  `remotealert_id` int(11) DEFAULT NULL,
  `needsconf` varchar(10) DEFAULT NULL,
  `toolate_id` int(11) DEFAULT NULL,
  `ringing` varchar(80) DEFAULT NULL,
  `cwignore` varchar(10) DEFAULT NULL,
  `cfignore` varchar(10) DEFAULT NULL,
  `cpickup` varchar(10) DEFAULT NULL,
  `recording` varchar(10) DEFAULT 'dontcare',
  PRIMARY KEY (`grpnum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ringgroups`
--

LOCK TABLES `ringgroups` WRITE;
/*!40000 ALTER TABLE `ringgroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `ringgroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setcid`
--

DROP TABLE IF EXISTS `setcid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setcid` (
  `cid_id` int(11) NOT NULL AUTO_INCREMENT,
  `cid_name` varchar(150) DEFAULT NULL,
  `cid_num` varchar(150) DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL,
  `dest` varchar(255) DEFAULT NULL,
  `variables` text,
  PRIMARY KEY (`cid_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setcid`
--

LOCK TABLES `setcid` WRITE;
/*!40000 ALTER TABLE `setcid` DISABLE KEYS */;
/*!40000 ALTER TABLE `setcid` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sip`
--

DROP TABLE IF EXISTS `sip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sip` (
  `id` varchar(20) NOT NULL DEFAULT '-1',
  `keyword` varchar(30) NOT NULL DEFAULT '',
  `data` varchar(255) NOT NULL,
  `flags` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sip`
--

LOCK TABLES `sip` WRITE;
/*!40000 ALTER TABLE `sip` DISABLE KEYS */;
/*!40000 ALTER TABLE `sip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sipsettings`
--

DROP TABLE IF EXISTS `sipsettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sipsettings` (
  `keyword` varchar(50) NOT NULL DEFAULT '',
  `data` varchar(255) NOT NULL DEFAULT '',
  `seq` tinyint(1) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`keyword`,`seq`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sipsettings`
--

LOCK TABLES `sipsettings` WRITE;
/*!40000 ALTER TABLE `sipsettings` DISABLE KEYS */;
INSERT INTO `sipsettings` VALUES ('ulaw','1',0,1),('alaw','1',1,1),('slin','',2,1),('g726','',3,1),('gsm','1',4,1),('g729','',5,1),('ilbc','',6,1),('g723','',7,1),('g726aal2','',8,1),('adpcm','',9,1),('lpc10','',10,1),('speex','',11,1),('g722','',12,1),('rtpstart','10000',0,0),('rtpend','20000',1,0);
/*!40000 ALTER TABLE `sipsettings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeconditions`
--

DROP TABLE IF EXISTS `timeconditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeconditions` (
  `timeconditions_id` int(11) NOT NULL AUTO_INCREMENT,
  `displayname` varchar(50) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `truegoto` varchar(50) DEFAULT NULL,
  `falsegoto` varchar(50) DEFAULT NULL,
  `deptname` varchar(50) DEFAULT NULL,
  `generate_hint` tinyint(1) DEFAULT '0',
  `priority` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`timeconditions_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeconditions`
--

LOCK TABLES `timeconditions` WRITE;
/*!40000 ALTER TABLE `timeconditions` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeconditions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timegroups_details`
--

DROP TABLE IF EXISTS `timegroups_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timegroups_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timegroupid` int(11) NOT NULL DEFAULT '0',
  `time` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timegroups_details`
--

LOCK TABLES `timegroups_details` WRITE;
/*!40000 ALTER TABLE `timegroups_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `timegroups_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timegroups_groups`
--

DROP TABLE IF EXISTS `timegroups_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timegroups_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `display` (`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timegroups_groups`
--

LOCK TABLES `timegroups_groups` WRITE;
/*!40000 ALTER TABLE `timegroups_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `timegroups_groups` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `trunkbalance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trunkbalance` (
  `trunkbalance_id` int(11) NOT NULL AUTO_INCREMENT,
  `desttrunk_id` int(11) DEFAULT '0',
  `disabled` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dialpattern` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dp_andor` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notdialpattern` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notdp_andor` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_cycle` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billingtime` time DEFAULT NULL,
  `billing_day` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billingdate` smallint(6) DEFAULT '0',
  `billingperiod` int(11) DEFAULT '0',
  `endingdate` datetime DEFAULT NULL,
  `count_inbound` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `count_unanswered` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loadratio` int(11) DEFAULT '1',
  `maxtime` int(11) DEFAULT '-1',
  `maxnumber` int(11) DEFAULT '-1',
  `maxidentical` int(11) DEFAULT '-1',
  `timegroup_id` int(11) DEFAULT '-1',
  `url` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_timeout` int(11) DEFAULT '10',
  `regex` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`trunkbalance_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trunk_dialpatterns`
--

DROP TABLE IF EXISTS `trunk_dialpatterns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trunk_dialpatterns` (
  `trunkid` int(11) NOT NULL DEFAULT '0',
  `match_pattern_prefix` varchar(50) NOT NULL DEFAULT '',
  `match_pattern_pass` varchar(50) NOT NULL DEFAULT '',
  `prepend_digits` varchar(50) NOT NULL DEFAULT '',
  `seq` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`trunkid`,`match_pattern_prefix`,`match_pattern_pass`,`prepend_digits`,`seq`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trunk_dialpatterns`
--

LOCK TABLES `trunk_dialpatterns` WRITE;
/*!40000 ALTER TABLE `trunk_dialpatterns` DISABLE KEYS */;
/*!40000 ALTER TABLE `trunk_dialpatterns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trunks`
--

DROP TABLE IF EXISTS `trunks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trunks` (
  `trunkid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `tech` varchar(20) NOT NULL,
  `outcid` varchar(40) NOT NULL DEFAULT '',
  `keepcid` varchar(4) DEFAULT 'off',
  `maxchans` varchar(6) DEFAULT '',
  `failscript` varchar(255) NOT NULL DEFAULT '',
  `dialoutprefix` varchar(255) NOT NULL DEFAULT '',
  `channelid` varchar(255) NOT NULL DEFAULT '',
  `usercontext` varchar(255) DEFAULT NULL,
  `provider` varchar(40) DEFAULT NULL,
  `disabled` varchar(4) DEFAULT 'off',
  `continue` varchar(4) DEFAULT 'off',
  PRIMARY KEY (`trunkid`,`tech`,`channelid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trunks`
--

LOCK TABLES `trunks` WRITE;
/*!40000 ALTER TABLE `trunks` DISABLE KEYS */;
INSERT INTO `trunks` VALUES (1,'','dahdi','','','','','','g0','',NULL,'off','off');
/*!40000 ALTER TABLE `trunks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `extension` varchar(20) NOT NULL DEFAULT '',
  `password` varchar(20) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `voicemail` varchar(50) DEFAULT NULL,
  `ringtimer` int(3) DEFAULT NULL,
  `noanswer` varchar(100) DEFAULT NULL,
  `recording` varchar(50) DEFAULT NULL,
  `outboundcid` varchar(50) DEFAULT NULL,
  `sipname` varchar(50) DEFAULT NULL,
  `mohclass` varchar(80) DEFAULT 'default',
  `noanswer_cid` varchar(20) DEFAULT '',
  `busy_cid` varchar(20) DEFAULT '',
  `chanunavail_cid` varchar(20) DEFAULT '',
  `noanswer_dest` varchar(255) DEFAULT '',
  `busy_dest` varchar(255) DEFAULT '',
  `chanunavail_dest` varchar(255) DEFAULT '',
  PRIMARY KEY (`extension`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vmblast`
--

DROP TABLE IF EXISTS `vmblast`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vmblast` (
  `grpnum` int(11) NOT NULL,
  `description` varchar(35) NOT NULL,
  `audio_label` int(11) NOT NULL DEFAULT '-1',
  `password` varchar(20) NOT NULL,
  PRIMARY KEY (`grpnum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vmblast`
--

LOCK TABLES `vmblast` WRITE;
/*!40000 ALTER TABLE `vmblast` DISABLE KEYS */;
/*!40000 ALTER TABLE `vmblast` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vmblast_groups`
--

DROP TABLE IF EXISTS `vmblast_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vmblast_groups` (
  `grpnum` varchar(50) NOT NULL DEFAULT '',
  `ext` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`grpnum`,`ext`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vmblast_groups`
--

LOCK TABLES `vmblast_groups` WRITE;
/*!40000 ALTER TABLE `vmblast_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `vmblast_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voicemail_admin`
--

DROP TABLE IF EXISTS `voicemail_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voicemail_admin` (
  `variable` varchar(30) NOT NULL DEFAULT '',
  `value` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`variable`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voicemail_admin`
--

LOCK TABLES `voicemail_admin` WRITE;
/*!40000 ALTER TABLE `voicemail_admin` DISABLE KEYS */;
INSERT INTO `voicemail_admin` VALUES ('OPERATOR_XTN',''),('VM_OPTS',''),('VM_GAIN',''),('VM_DDTYPE','u'),('VMX_OPTS_LOOP',''),('VMX_OPTS_DOVM',''),('VMX_TIMEOUT','2'),('VMX_REPEAT','1'),('VMX_LOOPS','1');
/*!40000 ALTER TABLE `voicemail_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `writequeuelog`
--

DROP TABLE IF EXISTS `writequeuelog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `writequeuelog` (
  `qlog_id` int(11) NOT NULL AUTO_INCREMENT,
  `qlog_description` varchar(250) DEFAULT NULL,
  `qlog_uniqueid` varchar(150) DEFAULT NULL,
  `qlog_queue` varchar(250) DEFAULT NULL,
  `qlog_agent` varchar(250) DEFAULT NULL,
  `qlog_event` varchar(150) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `dest` varchar(255) DEFAULT NULL,
  `qlog_extra` text,
  PRIMARY KEY (`qlog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `writequeuelog`
--

LOCK TABLES `writequeuelog` WRITE;
/*!40000 ALTER TABLE `writequeuelog` DISABLE KEYS */;
/*!40000 ALTER TABLE `writequeuelog` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;


DROP TABLE IF EXISTS `pjsipsettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pjsipsettings` (
  `keyword` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `data` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `seq` tinyint(1) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`keyword`,`seq`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

CREATE OR REPLACE VIEW `alldestinations` AS SELECT `users`.`extension` AS `extension`,`users`.`name` AS `name`,'from-did-direct' AS `context`,'extension' AS `type` from `users` UNION SELECT `queues_config`.`extension` AS `extension`,`queues_config`.`descr` AS `descr`,'ext-queues' AS `context`,'queue' AS `type` from `queues_config` UNION SELECT `ringgroups`.`grpnum` AS `grpnum`,`ringgroups`.`description` AS `description`,'ext-group' AS `context`,'ringgroup' AS `type` from `ringgroups`;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-04 23:04:41

