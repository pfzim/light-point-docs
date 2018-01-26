-- MySQL dump 10.16  Distrib 10.1.21-MariaDB, for Win32 (AMD64)
--
-- Host: localhost    Database: localhost
-- ------------------------------------------------------
-- Server version	10.1.21-MariaDB

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
-- Table structure for table `lpd_access`
--

DROP TABLE IF EXISTS `lpd_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lpd_access` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dn` varchar(1024) NOT NULL DEFAULT '',
  `oid` int(10) unsigned NOT NULL DEFAULT '0',
  `allow_bits` binary(32) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lpd_access`
--

LOCK TABLES `lpd_access` WRITE;
/*!40000 ALTER TABLE `lpd_access` DISABLE KEYS */;
/*!40000 ALTER TABLE `lpd_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lpd_docs`
--

DROP TABLE IF EXISTS `lpd_docs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lpd_docs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modify_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(512) NOT NULL DEFAULT '',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0',
  `status` int(10) unsigned NOT NULL DEFAULT '0',
  `bis_unit` int(10) unsigned NOT NULL DEFAULT '0',
  `reg_upr` int(10) unsigned NOT NULL DEFAULT '0',
  `reg_otd` int(10) unsigned NOT NULL DEFAULT '0',
  `contr_name` varchar(45) NOT NULL DEFAULT '',
  `order` varchar(45) NOT NULL DEFAULT '',
  `order_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `doc_type` int(10) unsigned NOT NULL DEFAULT '0',
  `info` varchar(2048) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lpd_docs`
--

LOCK TABLES `lpd_docs` WRITE;
/*!40000 ALTER TABLE `lpd_docs` DISABLE KEYS */;
/*!40000 ALTER TABLE `lpd_docs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lpd_files`
--

DROP TABLE IF EXISTS `lpd_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lpd_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `create_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modify_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lpd_files`
--

LOCK TABLES `lpd_files` WRITE;
/*!40000 ALTER TABLE `lpd_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `lpd_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lpd_files_history`
--

DROP TABLE IF EXISTS `lpd_files_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lpd_files_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `modify_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `deleted` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lpd_files_history`
--

LOCK TABLES `lpd_files_history` WRITE;
/*!40000 ALTER TABLE `lpd_files_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `lpd_files_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lpd_sections`
--

DROP TABLE IF EXISTS `lpd_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lpd_sections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `priority` int(10) unsigned NOT NULL DEFAULT '16000',
  `deleted` varchar(45) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lpd_sections`
--

LOCK TABLES `lpd_sections` WRITE;
/*!40000 ALTER TABLE `lpd_sections` DISABLE KEYS */;
/*!40000 ALTER TABLE `lpd_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lpd_users`
--

DROP TABLE IF EXISTS `lpd_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lpd_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(1024) NOT NULL DEFAULT '',
  `sid` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lpd_users`
--

LOCK TABLES `lpd_users` WRITE;
/*!40000 ALTER TABLE `lpd_users` DISABLE KEYS */;
INSERT INTO `lpd_users` VALUES (1,'zimin_test','5a6b233e61108');
/*!40000 ALTER TABLE `lpd_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-01-26 17:41:35
