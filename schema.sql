-- MySQL dump 10.16  Distrib 10.1.26-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: ads
-- ------------------------------------------------------
-- Server version	10.1.26-MariaDB-0+deb9u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ad_length`
--

DROP TABLE IF EXISTS `ad_length`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ad_length` (
  `ad_length_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `length_s` int(11) unsigned NOT NULL DEFAULT '120',
  `start` time DEFAULT NULL,
  `end` time DEFAULT NULL,
  PRIMARY KEY (`ad_length_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ad_played`
--

DROP TABLE IF EXISTS `ad_played`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ad_played` (
  `played_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ad_id` int(11) unsigned NOT NULL,
  `played` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`played_id`),
  UNIQUE KEY `uk_id_date` (`ad_id`,`played`),
  CONSTRAINT `ad_played_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`ad_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7306 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ad_type`
--

DROP TABLE IF EXISTS `ad_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ad_type` (
  `ad_type_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'Name of ad type',
  `plays_allowed` int(11) NOT NULL COMMENT 'Number of plays per day in core hours',
  `fudge_factor` int(11) NOT NULL DEFAULT '0' COMMENT 'Number to seconds to remove from next play time',
  `multiplier` int(11) NOT NULL DEFAULT '1' COMMENT 'Alter randomiser calaculation to make this more popular',
  PRIMARY KEY (`ad_type_id`),
  UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ads`
--

DROP TABLE IF EXISTS `ads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ads` (
  `ad_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL COMMENT 'Unique name of the advert',
  `length_s` int(11) NOT NULL COMMENT 'Length of ad in seconds',
  `path` varchar(255) NOT NULL COMMENT 'Location on file system of audio file',
  `start` datetime NOT NULL COMMENT 'Start date/time of the ad',
  `end` datetime NOT NULL COMMENT 'End date/time of the ad',
  `genre_id` int(11) unsigned NOT NULL COMMENT 'Genre of the ad to stop same type ads playing back to back',
  `multiplier` int(11) NOT NULL DEFAULT '1' COMMENT 'Makes ad more likely to be selected',
  `ad_type_id` int(11) unsigned NOT NULL COMMENT 'Unique ID',
  `cust_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`ad_id`),
  UNIQUE KEY `uk_name` (`name`),
  KEY `genre_id` (`genre_id`),
  KEY `k_ad_type_id` (`ad_type_id`),
  KEY `fk_customer` (`cust_id`),
  CONSTRAINT `ads_ibfk_1` FOREIGN KEY (`genre_id`) REFERENCES `genre` (`genre_id`),
  CONSTRAINT `ads_ibfk_2` FOREIGN KEY (`ad_type_id`) REFERENCES `ad_type` (`ad_type_id`),
  CONSTRAINT `fk_customer` FOREIGN KEY (`cust_id`) REFERENCES `customers` (`cust_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `cust_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Customer name',
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact` varchar(25) DEFAULT NULL COMMENT 'Name of contact person at customer site',
  PRIMARY KEY (`cust_id`),
  UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genre`
--

DROP TABLE IF EXISTS `genre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genre` (
  `genre_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `genre` varchar(80) NOT NULL,
  PRIMARY KEY (`genre_id`),
  UNIQUE KEY `uk_genre` (`genre`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `playlist`
--

DROP TABLE IF EXISTS `playlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playlist` (
  `playlist_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ad_id` int(11) unsigned NOT NULL,
  `duplicate_genre` int(1) NOT NULL DEFAULT '0',
  `to_play` int(1) NOT NULL DEFAULT '0',
  `over_played` int(1) NOT NULL DEFAULT '0',
  `spread` int(1) NOT NULL DEFAULT '0' COMMENT 'Spread ads out evenly through the core hours, 1 = don''t use as it is has been played recently',
  `random` double DEFAULT NULL,
  PRIMARY KEY (`playlist_id`),
  UNIQUE KEY `uk_ad_id` (`ad_id`),
  CONSTRAINT `playlist_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`ad_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `setting_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`setting_id`),
  KEY `k_setting` (`setting`(191))
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-12-18 18:33:06
