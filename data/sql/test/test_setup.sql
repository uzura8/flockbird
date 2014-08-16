-- MySQL dump 10.13  Distrib 5.5.28, for osx10.8 (i386)
--
-- Host: localhost    Database: test_uzuralabo
-- ------------------------------------------------------
-- Server version	5.5.19-log

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

--
-- Table structure for table `admin_user`
--

DROP TABLE IF EXISTS `admin_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `group` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `last_login` datetime NOT NULL,
  `login_hash` varchar(255) NOT NULL,
  `profile_fields` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE_idx` (`username`),
  UNIQUE KEY `email_UNIQUE_idx` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_user`
--

LOCK TABLES `admin_user` WRITE;
/*!40000 ALTER TABLE `admin_user` DISABLE KEYS */;
INSERT INTO `admin_user` VALUES (1,'admin','DsquwM8wYGIuc0SZ+hbXjkNWkkVNT0eWXR/ab8E8k5I=',100,'admin@example.icom','0000-00-00 00:00:00','','a:0:{}','2014-08-15 23:43:38','2014-08-15 23:43:38');
/*!40000 ALTER TABLE `admin_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `album`
--

DROP TABLE IF EXISTS `album`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `body` text,
  `public_flag` tinyint(2) NOT NULL DEFAULT '0',
  `cover_album_image_id` int(11) DEFAULT NULL,
  `foreign_table` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_at_idx` (`created_at`),
  KEY `member_id_created_at_idx` (`member_id`,`created_at`),
  KEY `public_flag_craeted_at_idx` (`public_flag`,`created_at`),
  KEY `member_id_foreign_table_idx` (`member_id`,`foreign_table`),
  KEY `member_id_idx` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `album`
--

LOCK TABLES `album` WRITE;
/*!40000 ALTER TABLE `album` DISABLE KEYS */;
/*!40000 ALTER TABLE `album` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `album_image`
--

DROP TABLE IF EXISTS `album_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `album_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) NOT NULL,
  `file_id` varchar(255) NOT NULL,
  `name` text,
  `public_flag` tinyint(2) NOT NULL DEFAULT '0',
  `comment_count` int(11) NOT NULL DEFAULT '0',
  `shot_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `sort_datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `album_id_public_flag_id` (`album_id`,`public_flag`,`id`),
  KEY `album_id_public_flag_shot_at` (`album_id`,`public_flag`,`shot_at`),
  KEY `sort_datetime_idx` (`sort_datetime`),
  KEY `file_id_idx` (`file_id`),
  CONSTRAINT `album_image_album_id_album_id` FOREIGN KEY (`album_id`) REFERENCES `album` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `album_image`
--

LOCK TABLES `album_image` WRITE;
/*!40000 ALTER TABLE `album_image` DISABLE KEYS */;
/*!40000 ALTER TABLE `album_image` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `album_image_comment`
--

DROP TABLE IF EXISTS `album_image_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `album_image_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_image_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `album_image_id_created_at` (`album_image_id`,`created_at`),
  KEY `album_image_id_idx` (`album_image_id`),
  CONSTRAINT `album_image_comment_album_image_id_album_image_id` FOREIGN KEY (`album_image_id`) REFERENCES `album_image` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `album_image_comment`
--

LOCK TABLES `album_image_comment` WRITE;
/*!40000 ALTER TABLE `album_image_comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `album_image_comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `ua` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file`
--

DROP TABLE IF EXISTS `file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'File name',
  `path` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'File path',
  `type` varchar(256) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Type of this file',
  `filesize` int(11) NOT NULL DEFAULT '0' COMMENT 'File size',
  `original_filename` text COLLATE utf8_unicode_ci COMMENT 'Original filename',
  `member_id` int(11) DEFAULT NULL,
  `exif` text COLLATE utf8_unicode_ci,
  `shot_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE_idx` (`name`),
  KEY `member_id_idx` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Saves informations of files uploaded';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file`
--

LOCK TABLES `file` WRITE;
/*!40000 ALTER TABLE `file` DISABLE KEYS */;
/*!40000 ALTER TABLE `file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_tmp`
--

DROP TABLE IF EXISTS `file_tmp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_tmp` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'File name',
  `path` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'File path',
  `type` varchar(256) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Type of this file',
  `filesize` int(11) NOT NULL DEFAULT '0' COMMENT 'File size',
  `original_filename` text COLLATE utf8_unicode_ci COMMENT 'Original filename',
  `member_id` int(11) DEFAULT NULL,
  `user_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: site member, 1:admin_user',
  `description` text COLLATE utf8_unicode_ci,
  `exif` text COLLATE utf8_unicode_ci,
  `shot_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE_idx` (`name`),
  KEY `name_user_type_member_id_idx` (`name`,`user_type`,`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Saves informations of temporary files uploaded';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_tmp`
--

LOCK TABLES `file_tmp` WRITE;
/*!40000 ALTER TABLE `file_tmp` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_tmp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_tmp_config`
--

DROP TABLE IF EXISTS `file_tmp_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_tmp_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `file_tmp_id` int(11) NOT NULL COMMENT 'file_tmp id',
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Configuration name',
  `value` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Configuration value',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `file_tmp_id_name_UNIQUE_idx` (`file_tmp_id`,`name`),
  KEY `file_tmp_id_idx` (`file_tmp_id`),
  CONSTRAINT `file_tmp_config_file_tmp_id_file_tmp_id` FOREIGN KEY (`file_tmp_id`) REFERENCES `file_tmp` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Saves configurations of each temporary files';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_tmp_config`
--

LOCK TABLES `file_tmp_config` WRITE;
/*!40000 ALTER TABLE `file_tmp_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_tmp_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member`
--

DROP TABLE IF EXISTS `member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `last_login` datetime DEFAULT NULL,
  `login_hash` varchar(255) DEFAULT NULL,
  `file_id` varchar(255) DEFAULT NULL,
  `filesize_total` int(11) NOT NULL DEFAULT '0' COMMENT 'Total file size',
  `register_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: normal, 1:facebook, 2:twitter, 3:google',
  `sex` varchar(16) DEFAULT NULL,
  `sex_public_flag` tinyint(2) NOT NULL DEFAULT '0',
  `birthyear` int(4) DEFAULT NULL,
  `birthyear_public_flag` tinyint(2) NOT NULL DEFAULT '0',
  `birthday` varchar(5) DEFAULT NULL,
  `birthday_public_flag` tinyint(2) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `birthday_id` (`birthday`,`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member`
--

LOCK TABLES `member` WRITE;
/*!40000 ALTER TABLE `member` DISABLE KEYS */;
INSERT INTO `member` VALUES (1,'初期メンバー',NULL,NULL,NULL,0,0,'',0,0,0,NULL,0,'2014-08-15 23:43:06','2014-08-15 23:43:06'),(2,'メンバー2',NULL,NULL,NULL,0,0,'',0,0,0,NULL,0,'2014-08-15 23:43:17','2014-08-15 23:43:17'),(3,'メンバー3',NULL,NULL,NULL,0,0,'',0,0,0,NULL,0,'2014-08-15 23:43:24','2014-08-15 23:43:24');
/*!40000 ALTER TABLE `member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_auth`
--

DROP TABLE IF EXISTS `member_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id_UNIQUE_idx` (`member_id`),
  UNIQUE KEY `email_UNIQUE_idx` (`email`),
  CONSTRAINT `member_auth_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_auth`
--

LOCK TABLES `member_auth` WRITE;
/*!40000 ALTER TABLE `member_auth` DISABLE KEYS */;
INSERT INTO `member_auth` VALUES (1,1,'sample@example.com','DsquwM8wYGIuc0SZ+hbXjkNWkkVNT0eWXR/ab8E8k5I=','2014-08-15 23:43:06','2014-08-15 23:43:06'),(2,2,'sample2@example.com','DsquwM8wYGIuc0SZ+hbXjkNWkkVNT0eWXR/ab8E8k5I=','2014-08-15 23:43:17','2014-08-15 23:43:17'),(3,3,'sample3@example.com','DsquwM8wYGIuc0SZ+hbXjkNWkkVNT0eWXR/ab8E8k5I=','2014-08-15 23:43:24','2014-08-15 23:43:24');
/*!40000 ALTER TABLE `member_auth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_config`
--

DROP TABLE IF EXISTS `member_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `member_id` int(11) NOT NULL COMMENT 'Member id',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT 'Configuration name',
  `value` text NOT NULL COMMENT 'Configuration value',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id_name_UNIQUE_idx` (`member_id`,`name`),
  KEY `member_id_idx` (`member_id`),
  CONSTRAINT `member_config_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_config`
--

LOCK TABLES `member_config` WRITE;
/*!40000 ALTER TABLE `member_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_email_pre`
--

DROP TABLE IF EXISTS `member_email_pre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_email_pre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id_UNIQUE_idx` (`member_id`),
  UNIQUE KEY `token_UNIQUE_idx` (`token`),
  KEY `email_idx` (`email`),
  CONSTRAINT `member_email_pre_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_email_pre`
--

LOCK TABLES `member_email_pre` WRITE;
/*!40000 ALTER TABLE `member_email_pre` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_email_pre` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_follow_timeline`
--

DROP TABLE IF EXISTS `member_follow_timeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_follow_timeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `timeline_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id_timeline_id_UNIQUE_idx` (`member_id`,`timeline_id`),
  KEY `member_follow_timeline_timeline_id_timeline_id` (`timeline_id`),
  CONSTRAINT `member_follow_timeline_member_id_timeline_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `member_follow_timeline_timeline_id_timeline_id` FOREIGN KEY (`timeline_id`) REFERENCES `timeline` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_follow_timeline`
--

LOCK TABLES `member_follow_timeline` WRITE;
/*!40000 ALTER TABLE `member_follow_timeline` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_follow_timeline` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_oauth`
--

DROP TABLE IF EXISTS `member_oauth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_oauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `oauth_provider_id` tinyint(2) NOT NULL,
  `uid` varchar(50) NOT NULL,
  `token` varchar(255) NOT NULL,
  `secret` varchar(255) DEFAULT NULL,
  `expires` int(11) DEFAULT NULL,
  `service_name` varchar(255) DEFAULT NULL,
  `service_url` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `oauth_provider_id_uid_idx` (`oauth_provider_id`,`uid`),
  KEY `oauth_provider_id_uid_member_idx` (`oauth_provider_id`,`uid`,`member_id`),
  KEY `member_oauth_member_id_member_id` (`member_id`),
  CONSTRAINT `member_oauth_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `oauth_provider_id_oauth_provider_id` FOREIGN KEY (`oauth_provider_id`) REFERENCES `oauth_provider` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_oauth`
--

LOCK TABLES `member_oauth` WRITE;
/*!40000 ALTER TABLE `member_oauth` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_oauth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_password_pre`
--

DROP TABLE IF EXISTS `member_password_pre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_password_pre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id_UNIQUE_idx` (`member_id`),
  UNIQUE KEY `token_UNIQUE_idx` (`token`),
  CONSTRAINT `member_password_pre_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_password_pre`
--

LOCK TABLES `member_password_pre` WRITE;
/*!40000 ALTER TABLE `member_password_pre` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_password_pre` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_pre`
--

DROP TABLE IF EXISTS `member_pre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_pre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_UNIQUE_idx` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_pre`
--

LOCK TABLES `member_pre` WRITE;
/*!40000 ALTER TABLE `member_pre` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_pre` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_profile`
--

DROP TABLE IF EXISTS `member_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `member_id` int(11) NOT NULL COMMENT 'Member id',
  `profile_id` int(11) NOT NULL COMMENT 'Profile id',
  `profile_option_id` int(11) DEFAULT NULL COMMENT 'Profile option id',
  `value` text NOT NULL COMMENT 'Text content for this profile item',
  `public_flag` tinyint(4) DEFAULT NULL COMMENT 'Public flag',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id_idx` (`member_id`),
  KEY `profile_id_idx` (`profile_id`),
  KEY `profile_option_id_idx` (`profile_option_id`),
  CONSTRAINT `member_profile_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `member_profile_profile_id_profile_id` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`id`) ON DELETE CASCADE,
  CONSTRAINT `member_profile_profile_option_id_profile_option_id` FOREIGN KEY (`profile_option_id`) REFERENCES `profile_option` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Saves informations of every member''''s profile';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_profile`
--

LOCK TABLES `member_profile` WRITE;
/*!40000 ALTER TABLE `member_profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_relation`
--

DROP TABLE IF EXISTS `member_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `member_id_to` int(11) NOT NULL COMMENT 'Target member id',
  `member_id_from` int(11) NOT NULL COMMENT 'Subject member id',
  `is_follow` tinyint(1) DEFAULT NULL COMMENT 'The subject member is followed the target',
  `is_friend` tinyint(1) DEFAULT NULL COMMENT 'The members are friends',
  `is_friend_pre` tinyint(1) DEFAULT NULL COMMENT 'The members are going to be friends',
  `is_access_block` tinyint(1) DEFAULT NULL COMMENT 'The subject member is blocked the target',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id_to_from_UNIQUE_idx` (`member_id_to`,`member_id_from`),
  UNIQUE KEY `member_id_from_to_UNIQUE_idx` (`member_id_from`,`member_id_to`),
  KEY `member_id_to_idx` (`member_id_to`),
  KEY `member_id_from_idx` (`member_id_from`),
  CONSTRAINT `member_relationship_member_id_from_member_id` FOREIGN KEY (`member_id_from`) REFERENCES `member` (`id`) ON DELETE CASCADE,
  CONSTRAINT `member_relationship_member_id_to_member_id` FOREIGN KEY (`member_id_to`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Saves ralationships of each members';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_relation`
--

LOCK TABLES `member_relation` WRITE;
/*!40000 ALTER TABLE `member_relation` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_relation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration`
--

DROP TABLE IF EXISTS `migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration` (
  `name` varchar(50) NOT NULL,
  `type` varchar(25) NOT NULL,
  `version` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration`
--

LOCK TABLES `migration` WRITE;
/*!40000 ALTER TABLE `migration` DISABLE KEYS */;
INSERT INTO `migration` VALUES ('default','app',4);
/*!40000 ALTER TABLE `migration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_category_id` int(11) DEFAULT NULL COMMENT 'News category id',
  `slug` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Identified news (ASCII)',
  `title` text NOT NULL,
  `body` text NOT NULL,
  `importance_level` tinyint(2) NOT NULL DEFAULT '0',
  `is_published` tinyint(2) NOT NULL DEFAULT '0',
  `published_at` datetime DEFAULT NULL,
  `users_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug_UNIQUE_idx` (`slug`),
  KEY `created_at_idx` (`created_at`),
  KEY `published_at_idx` (`published_at`),
  KEY `is_published_published_at_idx` (`is_published`,`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_category`
--

DROP TABLE IF EXISTS `news_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `name` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Identified news category name (ASCII)',
  `label` text NOT NULL,
  `sort_order` int(11) DEFAULT NULL COMMENT 'Order to sort',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE_idx` (`name`),
  KEY `sort_order_idx` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Saves categories of news';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_category`
--

LOCK TABLES `news_category` WRITE;
/*!40000 ALTER TABLE `news_category` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_file`
--

DROP TABLE IF EXISTS `news_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `file_id` varchar(255) NOT NULL,
  `name` text,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `news_id_created_at` (`news_id`,`created_at`),
  KEY `news_id_idx` (`news_id`),
  KEY `file_id_idx` (`file_id`),
  CONSTRAINT `news_file_news_id_news_id` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_file`
--

LOCK TABLES `news_file` WRITE;
/*!40000 ALTER TABLE `news_file` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_file` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_image`
--

DROP TABLE IF EXISTS `news_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `file_id` varchar(255) NOT NULL,
  `name` text,
  `shot_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `news_id_created_at` (`news_id`,`created_at`),
  KEY `news_id_idx` (`news_id`),
  KEY `file_id_idx` (`file_id`),
  CONSTRAINT `news_image_news_id_news_id` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_image`
--

LOCK TABLES `news_image` WRITE;
/*!40000 ALTER TABLE `news_image` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_image` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_link`
--

DROP TABLE IF EXISTS `news_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `uri` text NOT NULL,
  `label` text,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `news_id_idx` (`news_id`),
  CONSTRAINT `news_link_news_id_news_id` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_link`
--

LOCK TABLES `news_link` WRITE;
/*!40000 ALTER TABLE `news_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `note`
--

DROP TABLE IF EXISTS `note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `body` text NOT NULL,
  `public_flag` tinyint(2) NOT NULL DEFAULT '0',
  `is_published` tinyint(2) NOT NULL DEFAULT '0',
  `published_at` datetime DEFAULT NULL,
  `comment_count` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `sort_datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `published_at_idx` (`published_at`),
  KEY `member_id_is_published_published_at_public_flag_idx` (`member_id`,`is_published`,`published_at`,`public_flag`),
  KEY `member_id_created_at_idx` (`member_id`,`created_at`),
  KEY `public_flag_craeted_at_idx` (`public_flag`,`created_at`),
  KEY `is_published_published_at_public_flag_idx` (`is_published`,`published_at`,`public_flag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `note`
--

LOCK TABLES `note` WRITE;
/*!40000 ALTER TABLE `note` DISABLE KEYS */;
/*!40000 ALTER TABLE `note` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `note_album_image`
--

DROP TABLE IF EXISTS `note_album_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `note_album_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_id` int(11) NOT NULL,
  `album_image_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `note_id_idx` (`note_id`),
  KEY `note_album_image_album_image_id_album_image_id` (`album_image_id`),
  CONSTRAINT `note_album_image_note_id_note_id` FOREIGN KEY (`note_id`) REFERENCES `note` (`id`) ON DELETE CASCADE,
  CONSTRAINT `note_album_image_album_image_id_album_image_id` FOREIGN KEY (`album_image_id`) REFERENCES `album_image` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `note_album_image`
--

LOCK TABLES `note_album_image` WRITE;
/*!40000 ALTER TABLE `note_album_image` DISABLE KEYS */;
/*!40000 ALTER TABLE `note_album_image` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `note_comment`
--

DROP TABLE IF EXISTS `note_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `note_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `note_id_created_at` (`note_id`,`created_at`),
  KEY `note_id_idx` (`note_id`),
  KEY `member_id_idx` (`member_id`),
  CONSTRAINT `note_comment_note_id_note_id` FOREIGN KEY (`note_id`) REFERENCES `note` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `note_comment`
--

LOCK TABLES `note_comment` WRITE;
/*!40000 ALTER TABLE `note_comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `note_comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_provider`
--

DROP TABLE IF EXISTS `oauth_provider`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_provider` (
  `id` tinyint(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE_idx` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_provider`
--

LOCK TABLES `oauth_provider` WRITE;
/*!40000 ALTER TABLE `oauth_provider` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_provider` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `body` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile`
--

DROP TABLE IF EXISTS `profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `name` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Identified profile name (ASCII)',
  `caption` text NOT NULL,
  `display_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: display at detail, 2:display at summery, 3:always',
  `information` text,
  `placeholder` text,
  `is_required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'This is a required',
  `is_unique` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Cannot select duplicate item',
  `is_edit_public_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Settable public flag',
  `default_public_flag` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Default of public flag',
  `is_disp_regist` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Shows when registeration',
  `is_disp_config` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Shows when edit',
  `is_disp_search` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Shows when searching',
  `form_type` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Form type to input/select',
  `value_type` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Type of input value',
  `value_regexp` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT 'Regular expression',
  `value_min` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Minimum value',
  `value_max` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Maximum value',
  `sort_order` int(11) DEFAULT NULL COMMENT 'Order to sort',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE_idx` (`name`),
  KEY `display_type_id` (`display_type`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Saves input/select items for the member profile';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile`
--

LOCK TABLES `profile` WRITE;
/*!40000 ALTER TABLE `profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile_option`
--

DROP TABLE IF EXISTS `profile_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `label` text NOT NULL COMMENT 'Choice',
  `profile_id` int(11) NOT NULL COMMENT 'Profile id',
  `sort_order` int(11) DEFAULT NULL COMMENT 'Order to sort',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_id_idx` (`profile_id`),
  CONSTRAINT `profile_option_profile_id_profile_id` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Saves options of profile items';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile_option`
--

LOCK TABLES `profile_option` WRITE;
/*!40000 ALTER TABLE `profile_option` DISABLE KEYS */;
/*!40000 ALTER TABLE `profile_option` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_config`
--

DROP TABLE IF EXISTS `site_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Serial number',
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Configuration name',
  `value` text COLLATE utf8_unicode_ci COMMENT 'Configuration value',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE_idx` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Saves configurations of this site';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_config`
--

LOCK TABLES `site_config` WRITE;
/*!40000 ALTER TABLE `site_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline`
--

DROP TABLE IF EXISTS `timeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `member_id_to` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `page_id` int(11) DEFAULT NULL,
  `type` tinyint(2) NOT NULL DEFAULT '0',
  `body` text,
  `foreign_table` varchar(20) DEFAULT NULL COMMENT 'Reference table name',
  `foreign_id` int(11) DEFAULT NULL COMMENT 'The id of reference table',
  `source` varchar(64) DEFAULT NULL COMMENT 'The source caption',
  `source_uri` text COMMENT 'The source URI',
  `public_flag` tinyint(2) NOT NULL DEFAULT '0',
  `comment_count` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `sort_datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id_idx` (`member_id`),
  KEY `group_id_idx` (`group_id`),
  KEY `page_id_idx` (`page_id`),
  KEY `foreign_table_foreign_id_type_idx` (`foreign_table`,`foreign_id`,`type`),
  CONSTRAINT `timeline_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline`
--

LOCK TABLES `timeline` WRITE;
/*!40000 ALTER TABLE `timeline` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_cache`
--

DROP TABLE IF EXISTS `timeline_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeline_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timeline_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `member_id_to` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `page_id` int(11) DEFAULT NULL,
  `is_follow` tinyint(1) NOT NULL DEFAULT '0',
  `public_flag` tinyint(2) NOT NULL DEFAULT '0',
  `type` tinyint(2) NOT NULL DEFAULT '0',
  `comment_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `timeline_id_is_follow_UNIQUE_idx` (`timeline_id`,`is_follow`),
  KEY `timeline_id_idx` (`timeline_id`),
  CONSTRAINT `timeline_cache_timeline_id_timeline_id` FOREIGN KEY (`timeline_id`) REFERENCES `timeline` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_cache`
--

LOCK TABLES `timeline_cache` WRITE;
/*!40000 ALTER TABLE `timeline_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_child_data`
--

DROP TABLE IF EXISTS `timeline_child_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeline_child_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timeline_id` int(11) NOT NULL,
  `foreign_table` varchar(20) DEFAULT NULL COMMENT 'Reference table name',
  `foreign_id` int(11) DEFAULT NULL COMMENT 'The id of reference table',
  PRIMARY KEY (`id`),
  KEY `timeline_id_idx` (`timeline_id`),
  KEY `foreign_table_foreign_id_timeline_id_idx` (`foreign_table`,`foreign_id`,`timeline_id`),
  CONSTRAINT `timeline_child_data_timeline_id_timeline_id` FOREIGN KEY (`timeline_id`) REFERENCES `timeline` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_child_data`
--

LOCK TABLES `timeline_child_data` WRITE;
/*!40000 ALTER TABLE `timeline_child_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_child_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeline_comment`
--

DROP TABLE IF EXISTS `timeline_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeline_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timeline_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `body` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `timeline_id_created_at` (`timeline_id`,`created_at`),
  CONSTRAINT `timeline_comment_timeline_id_timeline_id` FOREIGN KEY (`timeline_id`) REFERENCES `timeline` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeline_comment`
--

LOCK TABLES `timeline_comment` WRITE;
/*!40000 ALTER TABLE `timeline_comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeline_comment` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-08-15 23:44:38
