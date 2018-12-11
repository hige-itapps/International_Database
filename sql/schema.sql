CREATE DATABASE  IF NOT EXISTS `international` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `international`;
-- MySQL dump 10.13  Distrib 5.7.19, for Win64 (x86_64)
--
-- Host: 141.218.158.65    Database: international
-- ------------------------------------------------------
-- Server version	5.5.5-10.2.8-MariaDB

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
-- Table structure for table `administrators`
--

DROP TABLE IF EXISTS `administrators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `administrators` (
  `broncoNetID` varchar(20) COLLATE utf8_bin NOT NULL,
  `name` varchar(100) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`broncoNetID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(2) NOT NULL DEFAULT '',
  `country_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `country_experience`
--

DROP TABLE IF EXISTS `country_experience`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `country_experience` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `experience` varchar(40) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `emails`
--

DROP TABLE IF EXISTS `emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address` varchar(254) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `issues`
--

DROP TABLE IF EXISTS `issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `issues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `issue` varchar(50) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `language_proficiencies`
--

DROP TABLE IF EXISTS `language_proficiencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `language_proficiencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proficiency_level` varchar(25) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(34) NOT NULL,
  `iso` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=192 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `region` varchar(20) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login_email` varchar(254) CHARACTER SET latin1 NOT NULL,
  `firstname` varchar(50) CHARACTER SET latin1 NOT NULL,
  `lastname` varchar(50) CHARACTER SET latin1 NOT NULL,
  `alternate_email` varchar(254) CHARACTER SET latin1 DEFAULT NULL,
  `affiliations` varchar(300) CHARACTER SET latin1 NOT NULL,
  `phone` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
  `issues_expertise_other` varchar(300) CHARACTER SET latin1 DEFAULT NULL,
  `regions_expertise_other` varchar(300) CHARACTER SET latin1 DEFAULT NULL,
  `countries_expertise_other` varchar(300) CHARACTER SET latin1 DEFAULT NULL,
  `social_link` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `approved` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE_LOGIN_EMAIL` (`login_email`,`approved`),
  UNIQUE KEY `UNIQUE_ALTERNATE_EMAIL` (`alternate_email`,`approved`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_codes`
--

DROP TABLE IF EXISTS `users_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(254) NOT NULL,
  `code` binary(32) NOT NULL,
  `expiration_timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_country_experience`
--

DROP TABLE IF EXISTS `users_country_experience`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_country_experience` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `experience_id` int(11) DEFAULT NULL,
  `other_experience` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_users_country_experience_user_id` (`user_id`),
  KEY `fk_users_country_experience_country_id` (`country_id`),
  KEY `fk_users_country_experience_experience_id` (`experience_id`),
  CONSTRAINT `fk_users_country_experience_country_id` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `fk_users_country_experience_experience_id` FOREIGN KEY (`experience_id`) REFERENCES `country_experience` (`id`),
  CONSTRAINT `fk_users_country_experience_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER `international`.`users_country_experience_BEFORE_INSERT` BEFORE INSERT ON `users_country_experience` FOR EACH ROW
BEGIN
	IF (NEW.experience_id IS NULL AND NEW.other_experience IS NULL) THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = '\'experience_id\' and \'other_experience\' cannot both be null';
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER `international`.`users_country_experience_BEFORE_UPDATE` BEFORE UPDATE ON `users_country_experience` FOR EACH ROW
BEGIN
	IF (NEW.experience_id IS NULL AND NEW.other_experience IS NULL) THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = '\'experience_id\' and \'other_experience\' cannot both be null';
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `users_country_expertise`
--

DROP TABLE IF EXISTS `users_country_expertise`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_country_expertise` (
  `user_id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`country_id`),
  KEY `fk_users_country_expertise_country_id` (`country_id`),
  CONSTRAINT `fk_users_country_expertise_country_id` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `fk_users_country_expertise_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_issues`
--

DROP TABLE IF EXISTS `users_issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_issues` (
  `user_id` int(11) NOT NULL,
  `issue_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`issue_id`),
  KEY `fk_users_issues_issue_id` (`issue_id`),
  CONSTRAINT `fk_users_issues_issue_id` FOREIGN KEY (`issue_id`) REFERENCES `issues` (`id`),
  CONSTRAINT `fk_users_issues_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_languages`
--

DROP TABLE IF EXISTS `users_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_languages` (
  `user_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `proficiency_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`language_id`),
  KEY `fk_users_languages_proficiency_id` (`proficiency_id`),
  KEY `fk_users_languages_language_id` (`language_id`),
  CONSTRAINT `fk_users_languages_language_id` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `fk_users_languages_proficiency_id` FOREIGN KEY (`proficiency_id`) REFERENCES `language_proficiencies` (`id`),
  CONSTRAINT `fk_users_languages_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_regions`
--

DROP TABLE IF EXISTS `users_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_regions` (
  `user_id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`region_id`),
  KEY `fk_users_regions_region_id` (`region_id`),
  CONSTRAINT `fk_users_regions_region_id` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  CONSTRAINT `fk_users_regions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `variables`
--

DROP TABLE IF EXISTS `variables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `variables` (
  `name` varchar(30) COLLATE utf8_bin NOT NULL,
  `value` text COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'international'
--

--
-- Dumping routines for database 'international'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-12-10 10:08:36
