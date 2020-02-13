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
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` VALUES (1,'AF','Afghanistan'),(2,'AL','Albania'),(3,'DZ','Algeria'),(4,'DS','American Samoa'),(5,'AD','Andorra'),(6,'AO','Angola'),(7,'AI','Anguilla'),(8,'AQ','Antarctica'),(9,'AG','Antigua and Barbuda'),(10,'AR','Argentina'),(11,'AM','Armenia'),(12,'AW','Aruba'),(13,'AU','Australia'),(14,'AT','Austria'),(15,'AZ','Azerbaijan'),(16,'BS','Bahamas'),(17,'BH','Bahrain'),(18,'BD','Bangladesh'),(19,'BB','Barbados'),(20,'BY','Belarus'),(21,'BE','Belgium'),(22,'BZ','Belize'),(23,'BJ','Benin'),(24,'BM','Bermuda'),(25,'BT','Bhutan'),(26,'BO','Bolivia'),(27,'BA','Bosnia and Herzegovina'),(28,'BW','Botswana'),(29,'BV','Bouvet Island'),(30,'BR','Brazil'),(31,'IO','British Indian Ocean Territory'),(32,'BN','Brunei Darussalam'),(33,'BG','Bulgaria'),(34,'BF','Burkina Faso'),(35,'BI','Burundi'),(36,'KH','Cambodia'),(37,'CM','Cameroon'),(38,'CA','Canada'),(39,'CV','Cape Verde'),(40,'KY','Cayman Islands'),(41,'CF','Central African Republic'),(42,'TD','Chad'),(43,'CL','Chile'),(44,'CN','China'),(45,'CX','Christmas Island'),(46,'CC','Cocos (Keeling) Islands'),(47,'CO','Colombia'),(48,'KM','Comoros'),(49,'CG','Congo'),(50,'CK','Cook Islands'),(51,'CR','Costa Rica'),(52,'HR','Croatia (Hrvatska)'),(53,'CU','Cuba'),(54,'CY','Cyprus'),(55,'CZ','Czech Republic'),(56,'DK','Denmark'),(57,'DJ','Djibouti'),(58,'DM','Dominica'),(59,'DO','Dominican Republic'),(60,'TP','East Timor'),(61,'EC','Ecuador'),(62,'EG','Egypt'),(63,'SV','El Salvador'),(64,'GQ','Equatorial Guinea'),(65,'ER','Eritrea'),(66,'EE','Estonia'),(67,'ET','Ethiopia'),(68,'FK','Falkland Islands (Malvinas)'),(69,'FO','Faroe Islands'),(70,'FJ','Fiji'),(71,'FI','Finland'),(72,'FR','France'),(73,'FX','France, Metropolitan'),(74,'GF','French Guiana'),(75,'PF','French Polynesia'),(76,'TF','French Southern Territories'),(77,'GA','Gabon'),(78,'GM','Gambia'),(79,'GE','Georgia'),(80,'DE','Germany'),(81,'GH','Ghana'),(82,'GI','Gibraltar'),(83,'GK','Guernsey'),(84,'GR','Greece'),(85,'GL','Greenland'),(86,'GD','Grenada'),(87,'GP','Guadeloupe'),(88,'GU','Guam'),(89,'GT','Guatemala'),(90,'GN','Guinea'),(91,'GW','Guinea-Bissau'),(92,'GY','Guyana'),(93,'HT','Haiti'),(94,'HM','Heard and Mc Donald Islands'),(95,'HN','Honduras'),(96,'HK','Hong Kong'),(97,'HU','Hungary'),(98,'IS','Iceland'),(99,'IN','India'),(100,'IM','Isle of Man'),(101,'ID','Indonesia'),(102,'IR','Iran (Islamic Republic of)'),(103,'IQ','Iraq'),(104,'IE','Ireland'),(105,'IL','Israel'),(106,'IT','Italy'),(107,'CI','Ivory Coast'),(108,'JE','Jersey'),(109,'JM','Jamaica'),(110,'JP','Japan'),(111,'JO','Jordan'),(112,'KZ','Kazakhstan'),(113,'KE','Kenya'),(114,'KI','Kiribati'),(115,'KP','Korea, Democratic People\'s Republic of'),(116,'KR','Korea, Republic of'),(117,'XK','Kosovo'),(118,'KW','Kuwait'),(119,'KG','Kyrgyzstan'),(120,'LA','Lao People\'s Democratic Republic'),(121,'LV','Latvia'),(122,'LB','Lebanon'),(123,'LS','Lesotho'),(124,'LR','Liberia'),(125,'LY','Libyan Arab Jamahiriya'),(126,'LI','Liechtenstein'),(127,'LT','Lithuania'),(128,'LU','Luxembourg'),(129,'MO','Macau'),(130,'MK','Macedonia'),(131,'MG','Madagascar'),(132,'MW','Malawi'),(133,'MY','Malaysia'),(134,'MV','Maldives'),(135,'ML','Mali'),(136,'MT','Malta'),(137,'MH','Marshall Islands'),(138,'MQ','Martinique'),(139,'MR','Mauritania'),(140,'MU','Mauritius'),(141,'TY','Mayotte'),(142,'MX','Mexico'),(143,'FM','Micronesia, Federated States of'),(144,'MD','Moldova, Republic of'),(145,'MC','Monaco'),(146,'MN','Mongolia'),(147,'ME','Montenegro'),(148,'MS','Montserrat'),(149,'MA','Morocco'),(150,'MZ','Mozambique'),(151,'MM','Myanmar'),(152,'NA','Namibia'),(153,'NR','Nauru'),(154,'NP','Nepal'),(155,'NL','Netherlands'),(156,'AN','Netherlands Antilles'),(157,'NC','New Caledonia'),(158,'NZ','New Zealand'),(159,'NI','Nicaragua'),(160,'NE','Niger'),(161,'NG','Nigeria'),(162,'NU','Niue'),(163,'NF','Norfolk Island'),(164,'MP','Northern Mariana Islands'),(165,'NO','Norway'),(166,'OM','Oman'),(167,'PK','Pakistan'),(168,'PW','Palau'),(169,'PS','Palestine'),(170,'PA','Panama'),(171,'PG','Papua New Guinea'),(172,'PY','Paraguay'),(173,'PE','Peru'),(174,'PH','Philippines'),(175,'PN','Pitcairn'),(176,'PL','Poland'),(177,'PT','Portugal'),(178,'PR','Puerto Rico'),(179,'QA','Qatar'),(180,'RE','Reunion'),(181,'RO','Romania'),(182,'RU','Russian Federation'),(183,'RW','Rwanda'),(184,'KN','Saint Kitts and Nevis'),(185,'LC','Saint Lucia'),(186,'VC','Saint Vincent and the Grenadines'),(187,'WS','Samoa'),(188,'SM','San Marino'),(189,'ST','Sao Tome and Principe'),(190,'SA','Saudi Arabia'),(191,'SN','Senegal'),(192,'RS','Serbia'),(193,'SC','Seychelles'),(194,'SL','Sierra Leone'),(195,'SG','Singapore'),(196,'SK','Slovakia'),(197,'SI','Slovenia'),(198,'SB','Solomon Islands'),(199,'SO','Somalia'),(200,'ZA','South Africa'),(201,'GS','South Georgia South Sandwich Islands'),(202,'SS','South Sudan'),(203,'ES','Spain'),(204,'LK','Sri Lanka'),(205,'SH','St. Helena'),(206,'PM','St. Pierre and Miquelon'),(207,'SD','Sudan'),(208,'SR','Suriname'),(209,'SJ','Svalbard and Jan Mayen Islands'),(210,'SZ','Swaziland'),(211,'SE','Sweden'),(212,'CH','Switzerland'),(213,'SY','Syrian Arab Republic'),(214,'TW','Taiwan'),(215,'TJ','Tajikistan'),(216,'TZ','Tanzania, United Republic of'),(217,'TH','Thailand'),(218,'TG','Togo'),(219,'TK','Tokelau'),(220,'TO','Tonga'),(221,'TT','Trinidad and Tobago'),(222,'TN','Tunisia'),(223,'TR','Turkey'),(224,'TM','Turkmenistan'),(225,'TC','Turks and Caicos Islands'),(226,'TV','Tuvalu'),(227,'UG','Uganda'),(228,'UA','Ukraine'),(229,'AE','United Arab Emirates'),(230,'GB','United Kingdom'),(231,'US','United States'),(232,'UM','United States minor outlying islands'),(233,'UY','Uruguay'),(234,'UZ','Uzbekistan'),(235,'VU','Vanuatu'),(236,'VA','Vatican City State'),(237,'VE','Venezuela'),(238,'VN','Vietnam'),(239,'VG','Virgin Islands (British)'),(240,'VI','Virgin Islands (U.S.)'),(241,'WF','Wallis and Futuna Islands'),(242,'EH','Western Sahara'),(243,'YE','Yemen'),(244,'ZR','Zaire'),(245,'ZM','Zambia'),(246,'ZW','Zimbabwe');
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `country_experience`
--

LOCK TABLES `country_experience` WRITE;
/*!40000 ALTER TABLE `country_experience` DISABLE KEYS */;
INSERT INTO `country_experience` VALUES (1,'Degree Earned'),(2,'Academic Research'),(3,'Study Abroad'),(4,'Teaching'),(5,'Policy or Advocacy Experience'),(6,'Practitioner Experience/Service'),(7,'Home Country');
/*!40000 ALTER TABLE `country_experience` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `issues`
--

LOCK TABLES `issues` WRITE;
/*!40000 ALTER TABLE `issues` DISABLE KEYS */;
INSERT INTO `issues` VALUES (1,'International Politics/Diplomacy'),(2,'Domestic Policy (Related to Foreign Affairs)'),(3,'Development/Humanitarian'),(4,'Earth, Environment, Climate Science'),(5,'Human Rights'),(6,'Military/Security'),(7,'Economics'),(8,'Social/Cultural'),(9,'Health Science'),(10,'Legal/Rule of Law/Governance'),(11,'Fine Arts'),(12,'Food Security'),(13,'Education'),(14,'Peace/War'),(15,'Model UN'),(16,'Accounting'),(17,'Business'),(18,'Business Analytics'),(19,'Business/Computer Information Systems'),(20,'Consulting'),(21,'Economics'),(22,'Entrepreneurship'),(23,'Finance'),(24,'Human Resource Management'),(25,'Integrated Supply'),(26,'Leadership'),(27,'Linguistics'),(28,'Management'),(29,'Marketing');
/*!40000 ALTER TABLE `issues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `language_proficiencies`
--

LOCK TABLES `language_proficiencies` WRITE;
/*!40000 ALTER TABLE `language_proficiencies` DISABLE KEYS */;
INSERT INTO `language_proficiencies` VALUES (1,'basic understanding'),(2,'moderate understanding'),(3,'advanced understanding'),(4,'native language');
/*!40000 ALTER TABLE `language_proficiencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'Abkhazian','ab'),(2,'Afar','aa'),(3,'Afrikaans','af'),(4,'Akan','ak'),(5,'Albanian','sq'),(6,'Amharic','am'),(7,'Arabic','ar'),(8,'Aragonese','an'),(9,'Armenian','hy'),(10,'Assamese','as'),(11,'Avaric','av'),(12,'Avestan','ae'),(13,'Aymara','ay'),(14,'Azerbaijani','az'),(15,'Bambara','bm'),(16,'Bashkir','ba'),(17,'Basque','eu'),(18,'Belarusian','be'),(19,'Bengali (Bangla)','bn'),(20,'Bihari','bh'),(21,'Bislama','bi'),(22,'Bosnian','bs'),(23,'Breton','br'),(24,'Bulgarian','bg'),(25,'Burmese','my'),(26,'Catalan','ca'),(27,'Chamorro','ch'),(28,'Chechen','ce'),(29,'Chichewa, Chewa, Nyanja','ny'),(30,'Chinese','zh'),(31,'Chinese (Simplified)','zh-Hans'),(32,'Chinese (Traditional)','zh-Hant'),(33,'Chuvash','cv'),(34,'Cornish','kw'),(35,'Corsican','co'),(36,'Cree','cr'),(37,'Croatian','hr'),(38,'Czech','cs'),(39,'Danish','da'),(40,'Divehi, Dhivehi, Maldivian','dv'),(41,'Dutch','nl'),(42,'Dzongkha','dz'),(43,'English','en'),(44,'Esperanto','eo'),(45,'Estonian','et'),(46,'Ewe','ee'),(47,'Faroese','fo'),(48,'Fijian','fj'),(49,'Finnish','fi'),(50,'French','fr'),(51,'Fula, Fulah, Pulaar, Pular','ff'),(52,'Galician','gl'),(53,'Gaelic (Scottish)','gd'),(54,'Gaelic (Manx)','gv'),(55,'Georgian','ka'),(56,'German','de'),(57,'Greek','el'),(58,'Greenlandic','kl'),(59,'Guarani','gn'),(60,'Gujarati','gu'),(61,'Haitian Creole','ht'),(62,'Hausa','ha'),(63,'Hebrew','he'),(64,'Herero','hz'),(65,'Hindi','hi'),(66,'Hiri Motu','ho'),(67,'Hungarian','hu'),(68,'Icelandic','is'),(69,'Ido','io'),(70,'Igbo','ig'),(71,'Indonesian','id, in'),(72,'Interlingua','ia'),(73,'Interlingue','ie'),(74,'Inuktitut','iu'),(75,'Inupiak','ik'),(76,'Irish','ga'),(77,'Italian','it'),(78,'Japanese','ja'),(79,'Javanese','jv'),(80,'Kalaallisut, Greenlandic','kl'),(81,'Kannada','kn'),(82,'Kanuri','kr'),(83,'Kashmiri','ks'),(84,'Kazakh','kk'),(85,'Khmer','km'),(86,'Kikuyu','ki'),(87,'Kinyarwanda (Rwanda)','rw'),(88,'Kirundi','rn'),(89,'Kyrgyz','ky'),(90,'Komi','kv'),(91,'Kongo','kg'),(92,'Korean','ko'),(93,'Kurdish','ku'),(94,'Kwanyama','kj'),(95,'Lao','lo'),(96,'Latin','la'),(97,'Latvian (Lettish)','lv'),(98,'Limburgish ( Limburger)','li'),(99,'Lingala','ln'),(100,'Lithuanian','lt'),(101,'Luga-Katanga','lu'),(102,'Luganda, Ganda','lg'),(103,'Luxembourgish','lb'),(104,'Manx','gv'),(105,'Macedonian','mk'),(106,'Malagasy','mg'),(107,'Malay','ms'),(108,'Malayalam','ml'),(109,'Maltese','mt'),(110,'Maori','mi'),(111,'Marathi','mr'),(112,'Marshallese','mh'),(113,'Moldavian','mo'),(114,'Mongolian','mn'),(115,'Nauru','na'),(116,'Navajo','nv'),(117,'Ndonga','ng'),(118,'Northern Ndebele','nd'),(119,'Nepali','ne'),(120,'Norwegian','no'),(121,'Norwegian bokmål','nb'),(122,'Norwegian nynorsk','nn'),(123,'Nuosu','ii'),(124,'Occitan','oc'),(125,'Ojibwe','oj'),(126,'Old Church Slavonic, Old Bulgarian','cu'),(127,'Oriya','or'),(128,'Oromo (Afaan Oromo)','om'),(129,'Ossetian','os'),(130,'Pāli','pi'),(131,'Pashto, Pushto','ps'),(132,'Persian (Farsi)','fa'),(133,'Polish','pl'),(134,'Portuguese','pt'),(135,'Punjabi (Eastern)','pa'),(136,'Quechua','qu'),(137,'Romansh','rm'),(138,'Romanian','ro'),(139,'Russian','ru'),(140,'Sami','se'),(141,'Samoan','sm'),(142,'Sango','sg'),(143,'Sanskrit','sa'),(144,'Serbian','sr'),(145,'Serbo-Croatian','sh'),(146,'Sesotho','st'),(147,'Setswana','tn'),(148,'Shona','sn'),(149,'Sichuan Yi','ii'),(150,'Sindhi','sd'),(151,'Sinhalese','si'),(152,'Siswati','ss'),(153,'Slovak','sk'),(154,'Slovenian','sl'),(155,'Somali','so'),(156,'Southern Ndebele','nr'),(157,'Spanish','es'),(158,'Sundanese','su'),(159,'Swahili (Kiswahili)','sw'),(160,'Swati','ss'),(161,'Swedish','sv'),(162,'Tagalog','tl'),(163,'Tahitian','ty'),(164,'Tajik','tg'),(165,'Tamil','ta'),(166,'Tatar','tt'),(167,'Telugu','te'),(168,'Thai','th'),(169,'Tibetan','bo'),(170,'Tigrinya','ti'),(171,'Tonga','to'),(172,'Tsonga','ts'),(173,'Turkish','tr'),(174,'Turkmen','tk'),(175,'Twi','tw'),(176,'Uyghur','ug'),(177,'Ukrainian','uk'),(178,'Urdu','ur'),(179,'Uzbek','uz'),(180,'Venda','ve'),(181,'Vietnamese','vi'),(182,'Volapük','vo'),(183,'Wallon','wa'),(184,'Welsh','cy'),(185,'Wolof','wo'),(186,'Western Frisian','fy'),(187,'Xhosa','xh'),(188,'Yiddish','yi, ji'),(189,'Yoruba','yo'),(190,'Zhuang, Chuang','za'),(191,'Zulu','zu'),(192,'Taiwanese','zh');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `regions`
--

LOCK TABLES `regions` WRITE;
/*!40000 ALTER TABLE `regions` DISABLE KEYS */;
INSERT INTO `regions` VALUES (1,'East Africa'),(2,'South Africa'),(3,'West Africa'),(4,'North Africa'),(5,'Central Africa'),(6,'South America'),(7,'North America'),(8,'Central America'),(9,'East Asia'),(10,'Southeast Asia'),(11,'South Asia'),(12,'West Asia'),(13,'Caribbean'),(14,'East Europe'),(15,'North Europe'),(16,'South Europe'),(17,'West Europe'),(18,'Oceania');
/*!40000 ALTER TABLE `regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `variables`
--

LOCK TABLES `variables` WRITE;
/*!40000 ALTER TABLE `variables` DISABLE KEYS */;
INSERT INTO `variables` VALUES ('ReminderEmailsLastSent',NULL),('SiteWarning',NULL),('DatabaseLastBackedUp',NULL);
/*!40000 ALTER TABLE `variables` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-12-10 10:27:37
