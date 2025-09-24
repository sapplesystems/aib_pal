-- MySQL dump 10.13  Distrib 5.7.20, for Linux (x86_64)
--
-- Host: localhost    Database: aibhistorical
-- ------------------------------------------------------
-- Server version	5.7.20-0ubuntu0.16.04.1

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
-- Table structure for table `advertisements`
--

DROP TABLE IF EXISTS `advertisements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advertisements` (
  `record_id` bigint(16) NOT NULL AUTO_INCREMENT,
  `item_id` bigint(16) NOT NULL,
  `ad_title` varchar(255) COLLATE ascii_bin NOT NULL,
  `ad_url` varchar(255) COLLATE ascii_bin NOT NULL,
  `ad_sort_order` varchar(32) COLLATE ascii_bin NOT NULL,
  `ad_alt_title` varchar(255) COLLATE ascii_bin NOT NULL,
  `inherit_flag` char(2) COLLATE ascii_bin NOT NULL,
  `original_file` varchar(255) COLLATE ascii_bin NOT NULL,
  `disable_flag` char(1) COLLATE ascii_bin NOT NULL,
  `record_ref` bigint(16) DEFAULT NULL,
  PRIMARY KEY (`record_id`)
) ENGINE=MyISAM AUTO_INCREMENT=261 DEFAULT CHARSET=ascii COLLATE=ascii_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advertisements`
--

LOCK TABLES `advertisements` WRITE;
/*!40000 ALTER TABLE `advertisements` DISABLE KEYS */;
INSERT INTO `advertisements` VALUES (249,76572,'Advertisement%20for%20leaf%20node%20definition','https%3A%2F%2Fwww.archiveinabox.com','01','Alternate%20title%20for%20leaf%20node','N','mor_comm_6.jpg','N',-1),(250,76572,'Updated%20Advertisement%20for%20leaf%20node','https%3A%2F%2Fwww.archiveinabox.com%2Fupdate','02','Alternate%20title%20for%20leaf%20node%2C%20updated','N','mor_comm_6.jpg','N',-1),(251,56581,'Ad%20for%20Uploader%20Test','https%3A%2F%2Fwww.archiveinabox.com%2Fbrowse%3F56581','01','Alternate%20title%20for%20node%2056581','Y','','N',250),(252,56519,'Ad%20for%20Test%20Archive','https%3A%2F%2Fwww.archiveinabox.com%2Fbrowse%3F56519','01','Alternate%20title%20for%20node%2056519','Y','','N',250),(253,56520,'Ad%20for%20Test%20Collection','https%3A%2F%2Fwww.archiveinabox.com%2Fbrowse%3F56520','01','Alternate%20title%20for%20node%2056520','Y','','N',250),(254,71445,'Ad%20for%20Import%20A','https%3A%2F%2Fwww.archiveinabox.com%2Fbrowse%3F71445','01','Alternate%20title%20for%20node%2071445','Y','','N',250),(255,76572,'Advertisement%20for%20leaf%20node%20definition','https%3A%2F%2Fwww.archiveinabox.com','01','Alternate%20title%20for%20leaf%20node','N','mor_comm_6.jpg','N',-1),(256,76572,'Updated%20Advertisement%20for%20leaf%20node','https%3A%2F%2Fwww.archiveinabox.com%2Fupdate','02','Alternate%20title%20for%20leaf%20node%2C%20updated','N','mor_comm_6.jpg','N',-1),(257,56581,'Ad%20for%20Uploader%20Test','https%3A%2F%2Fwww.archiveinabox.com%2Fbrowse%3F56581','01','Alternate%20title%20for%20node%2056581','Y','','N',256),(258,56519,'Ad%20for%20Test%20Archive','https%3A%2F%2Fwww.archiveinabox.com%2Fbrowse%3F56519','01','Alternate%20title%20for%20node%2056519','Y','','N',256),(259,56520,'Ad%20for%20Test%20Collection','https%3A%2F%2Fwww.archiveinabox.com%2Fbrowse%3F56520','01','Alternate%20title%20for%20node%2056520','Y','','N',256),(260,71445,'Ad%20for%20Import%20A','https%3A%2F%2Fwww.archiveinabox.com%2Fbrowse%3F71445','01','Alternate%20title%20for%20node%2071445','Y','','N',256);
/*!40000 ALTER TABLE `advertisements` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-09-24 10:51:54
