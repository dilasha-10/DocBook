/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.22-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: docbook_test
-- ------------------------------------------------------
-- Server version	10.6.22-MariaDB-0ubuntu0.22.04.1

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
-- Table structure for table `appointment_comments`
--

DROP TABLE IF EXISTS `appointment_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointment_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `appointment_comments_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointment_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment_comments`
--

LOCK TABLES `appointment_comments` WRITE;
/*!40000 ALTER TABLE `appointment_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `appointment_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `slot_count` int(11) DEFAULT 1,
  `reference_number` varchar(20) DEFAULT NULL,
  `status` enum('Pending','Confirmed','Cancelled','Completed','Rescheduled') DEFAULT 'Pending',
  `visit_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_number` (`reference_number`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`),
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (1,1,1,'2026-03-28','10:00:00','10:30:00',1,'DBK-2025-0001','Confirmed','Regular check-up','2026-03-26 12:39:13'),(2,1,2,'2026-04-02','14:30:00','15:15:00',1,'DBK-2025-0002','Cancelled','Chest pain follow-up','2026-03-26 12:39:13'),(3,1,3,'2026-04-10','11:00:00','11:30:00',1,'DBK-2025-0003','Rescheduled','Skin rash consultation','2026-03-26 12:39:13'),(4,1,5,'2026-03-16','09:00:00','09:30:00',1,'DBK-2025-0004','Completed','Child vaccination review','2026-03-26 12:39:13'),(5,1,1,'2026-02-09','10:00:00','10:30:00',1,'DBK-2025-0005','Completed','Flu symptoms','2026-03-26 12:39:13'),(6,1,4,'2026-01-25','11:00:00','11:45:00',1,'DBK-2025-0006','Cancelled','Migraine assessment','2026-03-26 12:39:13'),(7,1,7,'2026-03-30','16:00:00','17:00:00',1,'DBK-2026-1411','Rescheduled',NULL,'2026-03-29 11:20:10'),(8,1,5,'2026-03-30','10:00:00','10:30:00',1,'DBK-2026-6069','Rescheduled',NULL,'2026-03-29 11:20:43'),(9,1,5,'2026-03-31','08:00:00','08:30:00',1,'DBK-2026-3387','Pending',NULL,'2026-03-29 11:23:03'),(10,1,7,'2026-03-30','15:00:00','16:00:00',1,'DBK-2026-0525','Cancelled',NULL,'2026-03-29 11:23:50'),(11,1,1,'2026-03-30','11:00:00','11:30:00',1,'DBK-2026-5121','Cancelled',NULL,'2026-03-29 11:29:21'),(12,1,2,'2026-03-30','10:45:00','11:30:00',1,'DBK-2026-5306','Rescheduled',NULL,'2026-03-29 11:34:16'),(13,1,1,'2026-03-31','15:30:00','16:00:00',1,'DBK-2026-7413','Pending',NULL,'2026-03-29 11:36:40'),(14,1,2,'2026-04-01','13:00:00','13:45:00',1,'DBK-2026-7153','Cancelled',NULL,'2026-03-29 12:59:08'),(15,1,5,'2026-04-03','09:00:00','09:30:00',1,'DBK-2026-8215','Cancelled',NULL,'2026-03-30 11:02:13'),(16,1,2,'2026-04-03','12:15:00','13:00:00',1,'DBK-2026-6825','Rescheduled',NULL,'2026-03-30 11:05:50'),(17,1,7,'2026-04-02','17:00:00','18:00:00',1,'DBK-2026-9843','Cancelled',NULL,'2026-03-30 14:38:59'),(18,1,2,'2026-04-06','15:00:00','15:45:00',1,'DBK-2026-3909','Pending',NULL,'2026-04-01 14:20:55'),(19,1,3,'2026-04-02','14:00:00','14:30:00',1,'DBK-2026-8277','Rescheduled','Skin rash consultation','2026-04-01 14:21:03'),(20,1,7,'2026-04-02','17:00:00','18:00:00',1,'DBK-2026-8508','Cancelled',NULL,'2026-04-01 14:22:43'),(21,1,3,'2026-04-07','16:00:00','16:30:00',1,'DBK-2026-4287','Cancelled','Skin rash consultation','2026-04-01 14:43:24'),(22,1,4,'2026-04-06','13:15:00','14:00:00',1,'DBK-2026-3019','Pending',NULL,'2026-04-05 01:50:31'),(23,1,5,'2026-04-07','14:00:00','14:30:00',1,'DBK-2026-1608','Pending',NULL,'2026-04-06 13:36:52'),(24,10,7,'2026-04-09','17:00:00','18:00:00',1,'DBK-2026-4790','Pending',NULL,'2026-04-08 01:48:57'),(25,11,7,'2026-04-09','15:00:00','16:00:00',1,'DBK-2026-8523','Pending',NULL,'2026-04-08 15:08:02');
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `avg_slot_minutes` int(11) DEFAULT 30,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'General Physician','general-physician','fa-stethoscope','2026-03-26 12:39:13',30),(2,'Cardiologist','cardiologist','fa-heart-pulse','2026-03-26 12:39:13',45),(3,'Dermatologist','dermatologist','fa-hand-dots','2026-03-26 12:39:13',30),(4,'Neurologist','neurologist','fa-brain','2026-03-26 12:39:13',45),(5,'Pediatrician','pediatrician','fa-baby','2026-03-26 12:39:13',30),(6,'Orthopedic','orthopedic','fa-bone','2026-03-26 12:39:13',45),(7,'Psychiatrist','psychiatrist','fa-head-side-brain','2026-03-26 12:39:13',60),(8,'Ophthalmologist','ophthalmologist','fa-eye','2026-03-26 12:39:13',30);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctor_availability`
--

DROP TABLE IF EXISTS `doctor_availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctor_availability` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `break_minutes` int(11) DEFAULT 5,
  PRIMARY KEY (`id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `doctor_availability_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctor_availability`
--

LOCK TABLES `doctor_availability` WRITE;
/*!40000 ALTER TABLE `doctor_availability` DISABLE KEYS */;
INSERT INTO `doctor_availability` VALUES (1,1,'Monday','09:00:00','17:00:00',30),(2,1,'Tuesday','09:00:00','17:00:00',30),(3,1,'Wednesday','09:00:00','17:00:00',30),(4,1,'Thursday','09:00:00','17:00:00',30),(5,1,'Friday','09:00:00','14:00:00',0),(6,2,'Monday','10:00:00','16:00:00',30),(7,2,'Wednesday','10:00:00','16:00:00',30),(8,2,'Friday','10:00:00','14:00:00',0),(9,3,'Tuesday','09:00:00','17:00:00',30),(10,3,'Thursday','09:00:00','17:00:00',30),(11,4,'Monday','11:00:00','18:00:00',30),(12,4,'Wednesday','11:00:00','18:00:00',30),(13,4,'Friday','11:00:00','15:00:00',0),(14,5,'Monday','08:00:00','16:00:00',30),(15,5,'Tuesday','08:00:00','16:00:00',30),(16,5,'Wednesday','08:00:00','16:00:00',30),(17,5,'Thursday','08:00:00','16:00:00',30),(18,5,'Friday','08:00:00','13:00:00',0),(19,6,'Tuesday','09:00:00','17:00:00',30),(20,6,'Thursday','09:00:00','17:00:00',30),(21,7,'Monday','14:00:00','20:00:00',30),(22,7,'Thursday','14:00:00','20:00:00',30),(23,8,'Monday','09:00:00','16:00:00',30),(24,8,'Wednesday','09:00:00','16:00:00',30),(25,8,'Friday','09:00:00','13:00:00',0);
/*!40000 ALTER TABLE `doctor_availability` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctor_slots`
--

DROP TABLE IF EXISTS `doctor_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctor_slots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('available','booked','break') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_doctor_date` (`doctor_id`,`date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `doctor_slots_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctor_slots`
--

LOCK TABLES `doctor_slots` WRITE;
/*!40000 ALTER TABLE `doctor_slots` DISABLE KEYS */;
/*!40000 ALTER TABLE `doctor_slots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctors`
--

DROP TABLE IF EXISTS `doctors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `specialty` varchar(100) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `bio` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `doctors_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctors`
--

LOCK TABLES `doctors` WRITE;
/*!40000 ALTER TABLE `doctors` DISABLE KEYS */;
INSERT INTO `doctors` VALUES (1,2,1,'General Physician',8,'Dr. Sarah Lim is an experienced general practitioner with 8 years of clinical practice, specialising in preventive care, chronic disease management, and routine health check-ups.',NULL),(2,3,2,'Cardiologist',15,'Dr. James Okoro is a board-certified cardiologist with 15 years of expertise in diagnosing and treating heart disease, arrhythmias, and hypertension.',NULL),(3,4,3,'Dermatologist',6,'Dr. Priya Nair is a dermatologist with 6 years of experience treating acne, eczema, psoriasis, and providing cosmetic skin care consultations.',NULL),(4,5,4,'Neurologist',12,'Dr. Ahmed Hassan is a neurologist with 12 years of experience managing complex neurological conditions including migraines, epilepsy, and stroke rehabilitation.',NULL),(5,6,5,'Pediatrician',10,'Dr. Emily Chen is a dedicated paediatrician with 10 years of experience providing compassionate care for children from birth through adolescence.',NULL),(6,7,6,'Orthopedic Surgeon',18,'Dr. Mark Rivera is an orthopaedic surgeon with 18 years of expertise in bone and joint disorders, sports injuries, and minimally invasive surgical procedures.',NULL),(7,8,7,'Psychiatrist',9,'Dr. Aisha Kamara is a psychiatrist with 9 years of clinical experience in treating anxiety, depression, PTSD, and providing evidence-based therapy.',NULL),(8,9,8,'Ophthalmologist',7,'Dr. Tom Nguyen is an ophthalmologist with 7 years of practice in comprehensive eye care, cataract surgery, and refractive vision correction.',NULL);
/*!40000 ALTER TABLE `doctors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_role` enum('patient','doctor') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `sender_id` (`sender_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,24,10,'patient','hi',0,'2026-04-08 01:54:14'),(2,24,10,'patient','hello',0,'2026-04-08 01:56:25'),(3,24,10,'patient','<s>lol',0,'2026-04-08 02:04:43'),(4,24,10,'patient','<b>base</b>',0,'2026-04-08 02:04:51'),(5,24,10,'patient','<script>alert()</script>',0,'2026-04-08 02:05:48'),(6,25,11,'patient','hi doc',0,'2026-04-08 15:08:10'),(7,25,11,'patient','<s>wow',0,'2026-04-08 15:08:16');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('patient','doctor','admin') NOT NULL DEFAULT 'patient',
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'John Patient','john@example.com','98002300421','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','patient',NULL,'2026-03-26 12:39:13','2026-03-27 05:38:01'),(2,'Sarah Lim','sarah@docbook.com','9800000002','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','doctor',NULL,'2026-03-26 12:39:13','2026-03-26 12:39:13'),(3,'James Okoro','james@docbook.com','9800000003','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','doctor',NULL,'2026-03-26 12:39:13','2026-03-26 12:39:13'),(4,'Priya Nair','priya@docbook.com','9800000004','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','doctor',NULL,'2026-03-26 12:39:13','2026-03-26 12:39:13'),(5,'Ahmed Hassan','ahmed@docbook.com','9800000005','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','doctor',NULL,'2026-03-26 12:39:13','2026-03-26 12:39:13'),(6,'Emily Chen','emily@docbook.com','9800000006','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','doctor',NULL,'2026-03-26 12:39:13','2026-03-26 12:39:13'),(7,'Mark Rivera','mark@docbook.com','9800000007','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','doctor',NULL,'2026-03-26 12:39:13','2026-03-26 12:39:13'),(8,'Aisha Kamara','aisha@docbook.com','9800000008','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','doctor',NULL,'2026-03-26 12:39:13','2026-03-26 12:39:13'),(9,'Tom Nguyen','tom@docbook.com','9800000009','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','doctor',NULL,'2026-03-26 12:39:13','2026-03-26 12:39:13'),(10,'Pranjal','pranjal@gmail.com','','$2y$10$jkb39o40UopeedQwuF.WBe2xUEQjQlK4F64o8WcmDK/RVRA4DcFS6','patient',NULL,'2026-04-08 01:48:40','2026-04-08 01:48:40'),(11,'tester batman','testerbatman@gmail.com','','$2y$10$Z687T0m2lh2Orm0C6eLEZOvLNK543nbmU6a6qmVDssW4SSUS7JIgG','patient',NULL,'2026-04-08 15:05:25','2026-04-08 15:05:25');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-09 17:10:13
