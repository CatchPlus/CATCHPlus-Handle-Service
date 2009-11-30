-- MySQL dump 10.9
--
-- Host: localhost    Database: portal
-- ------------------------------------------------------
-- Server version	4.1.22

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Database`
--

DROP TABLE IF EXISTS `Database`;
CREATE TABLE `Database` (
  `database_id` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `version` varchar(255) NOT NULL default '',
  `user_id` bigint(20) unsigned NOT NULL default '0',
  `is_shared` tinyint(1) unsigned NOT NULL default '0',
  `checksum` varchar(32) NOT NULL default '',
  `type` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`database_id`),
  UNIQUE KEY `type` (`type`,`name`,`version`,`user_id`),
  KEY `version` (`version`),
  KEY `user_id` (`user_id`),
  KEY `is_shared` (`is_shared`),
  KEY `checksum` (`checksum`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=ascii;

--
-- Table structure for table `Job`
--

DROP TABLE IF EXISTS `Job`;
CREATE TABLE `Job` (
  `job_id` bigint(20) unsigned NOT NULL default '0',
  `user_id` bigint(20) unsigned NOT NULL default '0',
  `token_url` text character set ascii,
  `error` longtext,
  PRIMARY KEY  (`job_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `JobId`
--

DROP TABLE IF EXISTS `JobId`;
CREATE TABLE `JobId` (
  `job_id` bigint(20) NOT NULL auto_increment,
  PRIMARY KEY  (`job_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Table structure for table `ProxyDNMap`
--

DROP TABLE IF EXISTS `ProxyDNMap`;
CREATE TABLE `ProxyDNMap` (
  `apache_dn` text NOT NULL,
  `grst_dn` text NOT NULL,
  `apache_dn_md5` varchar(32) character set ascii NOT NULL default '',
  PRIMARY KEY  (`apache_dn_md5`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `Token`
--

DROP TABLE IF EXISTS `Token`;
CREATE TABLE `Token` (
  `token_id` bigint(20) unsigned NOT NULL default '0',
  `user_id` bigint(20) unsigned NOT NULL default '0',
  `error` longtext NOT NULL,
  `token_url` text character set ascii NOT NULL,
  PRIMARY KEY  (`token_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `User`
--

DROP TABLE IF EXISTS `User`;
CREATE TABLE `User` (
  `user_id` bigint(20) NOT NULL auto_increment,
  `user_dn` text NOT NULL,
  `user_dn_md5` varchar(32) character set ascii NOT NULL default '',
  `proxy_server` text character set ascii,
  `proxy_username` text character set ascii,
  `proxy_password` text character set ascii,
  `user_email` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `user_dn_md5` (`user_dn_md5`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

