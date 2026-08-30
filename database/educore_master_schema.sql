-- MySQL dump 10.13  Distrib 9.1.0, for Win64 (x86_64)
--
-- Host: localhost    Database: school_admission_portal
-- ------------------------------------------------------
-- Server version	9.1.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `admin_id` int unsigned DEFAULT NULL,
  `action` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_logs_admin` (`admin_id`),
  KEY `idx_logs_created` (`created_at`),
  KEY `idx_activity_logs_school` (`school_id`),
  CONSTRAINT `fk_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,1,'login','Admin logged in','::1','2026-06-03 23:57:57'),(2,1,1,'login','Admin logged in','::1','2026-06-03 23:59:22'),(3,1,1,'application_enrolled','ADM-2026-00001','::1','2026-06-04 00:46:43'),(4,1,1,'application_enrolled','ADM-2026-00001','::1','2026-06-04 00:46:51'),(5,1,1,'application_approved','ADM-2026-00001','::1','2026-06-04 00:47:45'),(6,1,1,'login','Admin logged in','::1','2026-06-04 10:07:43'),(7,1,1,'application_enrolled','ADM-2026-00001','::1','2026-06-04 12:07:35'),(8,1,1,'application_approved','ADM-2026-00001','::1','2026-06-04 12:08:07'),(9,1,1,'application_enrolled','ADM-2026-00001','::1','2026-06-04 12:10:10'),(10,1,1,'application_approved','ADM-2026-00001','::1','2026-06-04 12:16:07'),(11,1,1,'application_terminated','ADM-2026-00001','::1','2026-06-04 12:17:00'),(12,1,1,'application_approved','ADM-2026-00001','::1','2026-06-04 12:17:10'),(13,1,1,'login','Admin logged in','::1','2026-06-17 16:04:46'),(14,1,1,'login','Admin logged in','::1','2026-06-17 16:16:00'),(15,1,1,'application_enrolled','ADM-2026-00001','::1','2026-06-17 16:28:18'),(16,1,1,'application_approved','ADM-2026-00001','::1','2026-06-17 16:45:54'),(17,1,1,'application_enrolled','ADM-2026-00001','::1','2026-06-17 16:57:21'),(18,1,1,'login','Admin logged in','::1','2026-06-17 20:53:40'),(19,1,1,'payment_approved','Approved manual payment for ref: PAY202606040045243502','::1','2026-06-17 22:38:41'),(20,1,1,'student_enrolled','Auto enrolled student: SCH/2026/0001 (Username: SCH20260001)','::1','2026-06-17 22:54:36'),(21,1,1,'student_enrolled','Auto enrolled student: SCH/2026/0002 (Username: SCH20260002)','::1','2026-06-17 22:55:12'),(22,1,1,'student_enrolled','Auto enrolled student: SCH/2026/0002 (Username: SCH20260002)','::1','2026-06-17 22:56:00'),(23,1,1,'student_enrolled','Auto enrolled student: SCH/2026/0002 (Username: SCH20260002)','::1','2026-06-17 22:57:42'),(24,1,1,'application_approved','ADM-2026-00001','::1','2026-06-17 23:00:21'),(25,1,1,'student_enrolled','Auto enrolled student: SCH/2026/0002 (Username: SCH20260002)','::1','2026-06-17 23:14:35'),(26,1,1,'student_enrolled','Auto enrolled student: SCH/2026/0002 (Username: SCH20260002)','::1','2026-06-17 23:14:51'),(27,1,1,'login','Admin logged in','::1','2026-06-18 00:04:39'),(28,1,1,'login','Admin logged in','::1','2026-06-29 21:19:43'),(29,1,1,'login','Admin logged in','::1','2026-06-29 21:22:53');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for key-value app configurations
--
DROP TABLE IF EXISTS `app_configs`;
CREATE TABLE `app_configs` (
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('super_admin','admin','admission_officer','accountant','principal','staff') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_admins_school` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,1,'Portal Administrator','admin@school.test','$2y$10$WV4O4S0SKhteP8SVnsMt/OUbA6xKEJF2Wc1lypIrUYk6KeyATSUaW','super_admin','2026-06-03 22:56:07');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admission_letters`
--

DROP TABLE IF EXISTS `admission_letters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admission_letters` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `admission_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `generated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admission_number` (`admission_number`),
  KEY `fk_letters_applicant` (`applicant_id`),
  KEY `idx_admission_letters_school` (`school_id`),
  CONSTRAINT `fk_letters_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admission_letters`
--

LOCK TABLES `admission_letters` WRITE;
/*!40000 ALTER TABLE `admission_letters` DISABLE KEYS */;
INSERT INTO `admission_letters` VALUES (1,1,1,'SCH/2026/00001','2026-06-17 16:28:18');
/*!40000 ALTER TABLE `admission_letters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admissions`
--

DROP TABLE IF EXISTS `admissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `admission_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admitted_class_id` int unsigned DEFAULT NULL,
  `status` enum('offered','accepted','enrolled','withdrawn') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'offered',
  `offered_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enrolled_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admission_number` (`admission_number`),
  KEY `fk_admissions_applicant` (`applicant_id`),
  KEY `fk_admissions_class` (`admitted_class_id`),
  KEY `idx_admissions_school` (`school_id`),
  CONSTRAINT `fk_admissions_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_admissions_class` FOREIGN KEY (`admitted_class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admissions`
--

LOCK TABLES `admissions` WRITE;
/*!40000 ALTER TABLE `admissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `admissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `audience` enum('all','class','parents','staff') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `class_id` int unsigned DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `published_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ann_class` (`class_id`),
  KEY `fk_ann_admin` (`created_by`),
  KEY `idx_announcements_school` (`school_id`),
  CONSTRAINT `fk_ann_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ann_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_keys` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `api_key` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','revoked') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `fk_apik_school` (`school_id`),
  CONSTRAINT `fk_apik_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_keys`
--

LOCK TABLES `api_keys` WRITE;
/*!40000 ALTER TABLE `api_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `applicants`
--

DROP TABLE IF EXISTS `applicants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applicants` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `application_number` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admission_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `student_username` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('Male','Female') COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date NOT NULL,
  `state_of_origin` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `local_government` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `religion` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `home_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_phone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `father_name` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_name` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_name` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_occupation` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class_id` int unsigned DEFAULT NULL,
  `previous_school` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_class` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `blood_group` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `allergies` text COLLATE utf8mb4_unicode_ci,
  `special_needs` text COLLATE utf8mb4_unicode_ci,
  `emergency_name` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_relationship` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_certificate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_result` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `testimonial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recommendation_letter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Pending','Submitted','Under Review','Awaiting Exam','Exam Completed','Interview Scheduled','Approved','Rejected','Enrolled','Terminated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Submitted',
  `admission_status` enum('Pending','Offered','Rejected','Terminated','Enrolled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `enrollment_status` enum('Pending','In Progress','Completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `enrolled_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `qr_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_data` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `student_status` enum('Active','Suspended','Graduated','Transferred','Withdrawn') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `student_login_created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_number` (`application_number`),
  UNIQUE KEY `admission_number` (`admission_number`),
  UNIQUE KEY `student_username` (`student_username`),
  KEY `fk_applicants_class` (`class_id`),
  KEY `idx_app_number` (`application_number`),
  KEY `idx_app_status` (`status`),
  KEY `idx_app_created` (`created_at`),
  KEY `idx_app_parent_phone` (`parent_phone`),
  KEY `idx_app_email` (`parent_email`),
  KEY `idx_applicants_school` (`school_id`),
  CONSTRAINT `fk_applicants_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applicants`
--

LOCK TABLES `applicants` WRITE;
/*!40000 ALTER TABLE `applicants` DISABLE KEYS */;
INSERT INTO `applicants` VALUES (1,1,'ADM-2026-00001','SCH/2026/0002','SCH20260002','eyitayo','Micheal','Azzan','Male','2020-01-01','Oyo','lagelu','Nigerian','chires','akobo\r\nOLORUDA','','07081306993','azzanmic@gmail.com','azzan','fisayou','','douctor',2,'tgjdd','primy 6','a','xggaxjg','xguax','aixaixi','agjxagx','07081306993','passports/a1629dfae7101adb5ec443c1.webp','birth_certificates/4710272d3b24462f3c73315c.jpg','results/5139651ad8a0d1347531ed54.png','testimonials/d078a9be9adee608e92def17.jpg','recommendations/d656cbc403d651f76e72b081.jpg','Enrolled','Offered','Completed','2026-06-17 23:14:37','2026-06-04 00:45:24','2026-06-17 23:14:37','qrcodes/std_1.png','ATTENDANCE-STD-1-a9643025','Active','2026-06-17 23:14:37');
/*!40000 ALTER TABLE `applicants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `class_id` int unsigned NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `status` enum('Present','Absent','Late','Excused') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Present',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alert_sent` tinyint(1) NOT NULL DEFAULT '0',
  `marked_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_attendance` (`applicant_id`,`date`),
  KEY `fk_att_admin` (`marked_by`),
  KEY `idx_att_date` (`date`),
  KEY `idx_att_class_date` (`class_id`,`date`),
  KEY `idx_attendance_school` (`school_id`),
  CONSTRAINT `fk_att_admin` FOREIGN KEY (`marked_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_att_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_att_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_devices`
--

DROP TABLE IF EXISTS `attendance_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_devices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `device_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_model` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `android_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_uuid` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` enum('POS','Tablet','Gate') COLLATE utf8mb4_unicode_ci DEFAULT 'POS',
  `app_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '1.0.0',
  `signal_strength` int DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `battery_level` int DEFAULT NULL,
  `activation_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_token` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firmware_version` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','blocked') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `last_seen` datetime DEFAULT NULL,
  `last_scan_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_token` (`device_token`),
  UNIQUE KEY `serial_number` (`serial_number`),
  UNIQUE KEY `activation_code` (`activation_code`),
  KEY `fk_dev_school` (`school_id`),
  CONSTRAINT `fk_dev_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_devices`
--

LOCK TABLES `attendance_devices` WRITE;
/*!40000 ALTER TABLE `attendance_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `user_id` int unsigned DEFAULT NULL,
  `action` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` int unsigned DEFAULT NULL,
  `ip_address` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_audit_user` (`user_id`),
  KEY `idx_audit_created` (`created_at`),
  KEY `idx_audit_logs_school` (`school_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`school_id`,`name`),
  KEY `idx_classes_sort` (`sort_order`),
  KEY `idx_classes_school` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,1,'Primary 1',10,'2026-06-03 22:56:07'),(2,1,'Primary 2',20,'2026-06-03 22:56:07'),(3,1,'Primary 3',30,'2026-06-03 22:56:07'),(4,1,'JSS 1',100,'2026-06-03 22:56:07'),(5,1,'JSS 2',110,'2026-06-03 22:56:07'),(6,1,'SS 1',200,'2026-06-03 22:56:07'),(7,1,'SS 2',210,'2026-06-03 22:56:07');
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_percent` int NOT NULL DEFAULT '0',
  `max_uses` int NOT NULL DEFAULT '100',
  `used_count` int NOT NULL DEFAULT '0',
  `expires_at` date NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES (1,'SSTLAUNCH50',50,100,0,'2027-12-31','2026-06-29 23:15:22'),(2,'FREE10',10,500,0,'2027-12-31','2026-06-29 23:15:22');
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `document_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_documents_applicant` (`applicant_id`),
  KEY `idx_documents_type` (`document_type`),
  KEY `idx_documents_school` (`school_id`),
  CONSTRAINT `fk_documents_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` VALUES (1,1,1,'Passport Photograph','passports/a1629dfae7101adb5ec443c1.webp','2026-06-04 00:45:24',NULL),(2,1,1,'Birth Certificate','birth_certificates/4710272d3b24462f3c73315c.jpg','2026-06-04 00:45:24',NULL),(3,1,1,'Previous School Result','results/5139651ad8a0d1347531ed54.png','2026-06-04 00:45:24',NULL),(4,1,1,'Testimonial','testimonials/d078a9be9adee608e92def17.jpg','2026-06-04 00:45:24',NULL),(5,1,1,'Recommendation Letter','recommendations/d656cbc403d651f76e72b081.jpg','2026-06-04 00:45:24',NULL);
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_logs`
--

DROP TABLE IF EXISTS `email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned DEFAULT NULL,
  `email_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('sent','failed','bounced') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sent',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `sent_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email_logs_applicant` (`applicant_id`),
  KEY `idx_email_logs_type` (`email_type`),
  KEY `idx_email_logs_school` (`school_id`),
  CONSTRAINT `fk_email_logs_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_logs`
--

LOCK TABLES `email_logs` WRITE;
/*!40000 ALTER TABLE `email_logs` DISABLE KEYS */;
INSERT INTO `email_logs` VALUES (1,1,1,'payment_receipt','azzanmic@gmail.com','Payment Receipt - PAY202606040045243502','sent',NULL,'2026-06-17 22:38:41'),(2,1,1,'welcome_credentials','azzanmic@gmail.com','Enrollment Completed - SCH/2026/0001','sent',NULL,'2026-06-17 22:54:36'),(3,1,1,'welcome_credentials','azzanmic@gmail.com','Enrollment Completed - SCH/2026/0002','sent',NULL,'2026-06-17 22:55:12'),(4,1,1,'welcome_credentials','azzanmic@gmail.com','Enrollment Completed - SCH/2026/0002','sent',NULL,'2026-06-17 22:56:00'),(5,1,1,'welcome_credentials','azzanmic@gmail.com','Enrollment Completed - SCH/2026/0002','sent',NULL,'2026-06-17 22:57:42'),(6,1,1,'application_status_approved','azzanmic@gmail.com','Admission Application Approved - ADM-2026-00001','sent',NULL,'2026-06-17 23:00:42'),(7,1,1,'welcome_credentials','azzanmic@gmail.com','Enrollment Completed - SCH/2026/0002','sent',NULL,'2026-06-17 23:14:35'),(8,1,1,'welcome_credentials','azzanmic@gmail.com','Enrollment Completed - SCH/2026/0002','sent',NULL,'2026-06-17 23:14:51');
/*!40000 ALTER TABLE `email_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_questions`
--

DROP TABLE IF EXISTS `exam_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_questions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `subject_id` int unsigned NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_a` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_b` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_c` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_d` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correct_option` enum('A','B','C','D') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_questions_subject` (`subject_id`),
  KEY `idx_exam_questions_school` (`school_id`),
  CONSTRAINT `fk_questions_subject` FOREIGN KEY (`subject_id`) REFERENCES `exam_subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_questions`
--

LOCK TABLES `exam_questions` WRITE;
/*!40000 ALTER TABLE `exam_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `exam_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_results`
--

DROP TABLE IF EXISTS `exam_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_results` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `subject_id` int unsigned NOT NULL,
  `score` int NOT NULL DEFAULT '0',
  `total_questions` int NOT NULL DEFAULT '0',
  `submitted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_results_applicant` (`applicant_id`),
  KEY `fk_results_subject` (`subject_id`),
  KEY `idx_results_score` (`score`),
  KEY `idx_exam_results_school` (`school_id`),
  CONSTRAINT `fk_results_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_results_subject` FOREIGN KEY (`subject_id`) REFERENCES `exam_subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_results`
--

LOCK TABLES `exam_results` WRITE;
/*!40000 ALTER TABLE `exam_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `exam_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_subjects`
--

DROP TABLE IF EXISTS `exam_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_subjects` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_minutes` int NOT NULL DEFAULT '30',
  `pass_mark` int NOT NULL DEFAULT '50',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_exam_subjects_school` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_subjects`
--

LOCK TABLES `exam_subjects` WRITE;
/*!40000 ALTER TABLE `exam_subjects` DISABLE KEYS */;
INSERT INTO `exam_subjects` VALUES (1,1,'Mathematics',30,50),(2,1,'English Language',30,50),(3,1,'General Knowledge',20,50);
/*!40000 ALTER TABLE `exam_subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fee_structures`
--

DROP TABLE IF EXISTS `fee_structures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fee_structures` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `class_id` int unsigned DEFAULT NULL COMMENT 'NULL = applies to all classes',
  `term` enum('First','Second','Third','Annual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'First',
  `academic_year` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fee_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `is_optional` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_fs_class` (`class_id`),
  KEY `idx_fs_year_term` (`academic_year`,`term`),
  KEY `idx_fee_structures_school` (`school_id`),
  CONSTRAINT `fk_fs_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fee_structures`
--

LOCK TABLES `fee_structures` WRITE;
/*!40000 ALTER TABLE `fee_structures` DISABLE KEYS */;
/*!40000 ALTER TABLE `fee_structures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interviews`
--

DROP TABLE IF EXISTS `interviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interviews` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `score` int DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_interviews_applicant` (`applicant_id`),
  KEY `idx_interviews_schedule` (`scheduled_at`),
  KEY `idx_interviews_school` (`school_id`),
  CONSTRAINT `fk_interviews_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interviews`
--

LOCK TABLES `interviews` WRITE;
/*!40000 ALTER TABLE `interviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `interviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_items`
--

DROP TABLE IF EXISTS `inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `item_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('Books','Uniform','Furniture','Electronics','Sports','Stationery','Other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Other',
  `quantity` int unsigned NOT NULL DEFAULT '0',
  `unit` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `reorder_level` int unsigned DEFAULT NULL,
  `supplier` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_restocked` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inv_category` (`category`),
  KEY `idx_inventory_items_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_items`
--

LOCK TABLES `inventory_items` WRITE;
/*!40000 ALTER TABLE `inventory_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_transactions`
--

DROP TABLE IF EXISTS `inventory_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_transactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `item_id` int unsigned NOT NULL,
  `transaction_type` enum('restock','issued','damaged','disposed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_it_item` (`item_id`),
  KEY `fk_it_admin` (`recorded_by`),
  KEY `idx_inventory_transactions_school` (`school_id`),
  CONSTRAINT `fk_it_admin` FOREIGN KEY (`recorded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_it_item` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_transactions`
--

LOCK TABLES `inventory_transactions` WRITE;
/*!40000 ALTER TABLE `inventory_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `library_books`
--

DROP TABLE IF EXISTS `library_books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `library_books` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `isbn` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publisher` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year_published` int DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_copies` int unsigned NOT NULL DEFAULT '1',
  `available_copies` int unsigned NOT NULL DEFAULT '1',
  `location` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lib_title` (`title`(60)),
  KEY `idx_library_books_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `library_books`
--

LOCK TABLES `library_books` WRITE;
/*!40000 ALTER TABLE `library_books` DISABLE KEYS */;
/*!40000 ALTER TABLE `library_books` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `library_borrowings`
--

DROP TABLE IF EXISTS `library_borrowings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `library_borrowings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `book_id` int unsigned NOT NULL,
  `borrower_type` enum('student','staff') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student',
  `applicant_id` int unsigned DEFAULT NULL,
  `staff_id` int unsigned DEFAULT NULL,
  `borrowed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `due_date` date NOT NULL,
  `returned_at` datetime DEFAULT NULL,
  `fine_amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `fine_paid` tinyint(1) NOT NULL DEFAULT '0',
  `issued_by` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_lb_book` (`book_id`),
  KEY `fk_lb_applicant` (`applicant_id`),
  KEY `fk_lb_staff` (`staff_id`),
  KEY `idx_lb_due` (`due_date`),
  KEY `idx_lb_returned` (`returned_at`),
  KEY `idx_library_borrowings_school` (`school_id`),
  CONSTRAINT `fk_lb_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_lb_book` FOREIGN KEY (`book_id`) REFERENCES `library_books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lb_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `library_borrowings`
--

LOCK TABLES `library_borrowings` WRITE;
/*!40000 ALTER TABLE `library_borrowings` DISABLE KEYS */;
/*!40000 ALTER TABLE `library_borrowings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marketplace_transactions`
--

DROP TABLE IF EXISTS `marketplace_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketplace_transactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `item_type` enum('sms','storage','device','branding','support') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `cost` decimal(12,2) NOT NULL,
  `payment_status` enum('pending','paid','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `transaction_ref` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_mkt_school` (`school_id`),
  CONSTRAINT `fk_mkt_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marketplace_transactions`
--

LOCK TABLES `marketplace_transactions` WRITE;
/*!40000 ALTER TABLE `marketplace_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `marketplace_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `user_type` enum('student','parent','staff') COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int unsigned NOT NULL COMMENT 'Refers to target login accounts table id (student_accounts.id, parent_accounts.id, staff_accounts.id)',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_school` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,1,'student',1,'Welcome to Student Portal!','Welcome to your portal. Please configure your profile and change your temporary password.',0,'2026-06-17 22:54:21'),(2,1,'parent',1,'Welcome to Parent Portal!','Welcome to the parent portal. You can now monitor your child\'s results, fees, and attendance.',0,'2026-06-17 22:54:21'),(3,1,'student',1,'Welcome to Student Portal!','Welcome to your portal. Please configure your profile and change your temporary password.',0,'2026-06-17 22:54:36'),(4,1,'parent',1,'Welcome to Parent Portal!','Welcome to the parent portal. You can now monitor your child\'s results, fees, and attendance.',0,'2026-06-17 22:54:36'),(5,1,'student',1,'Welcome to Student Portal!','Welcome to your portal. Please configure your profile and change your temporary password.',0,'2026-06-17 22:55:20'),(6,1,'parent',1,'Welcome to Parent Portal!','Welcome to the parent portal. You can now monitor your child\'s results, fees, and attendance.',0,'2026-06-17 22:55:20'),(7,1,'student',1,'Welcome to Student Portal!','Welcome to your portal. Please configure your profile and change your temporary password.',0,'2026-06-17 22:56:34'),(8,1,'parent',1,'Welcome to Parent Portal!','Welcome to the parent portal. You can now monitor your child\'s results, fees, and attendance.',0,'2026-06-17 22:56:34'),(9,1,'student',1,'Welcome to Student Portal!','Welcome to your portal. Please configure your profile and change your temporary password.',0,'2026-06-17 23:14:13'),(10,1,'parent',1,'Welcome to Parent Portal!','Welcome to the parent portal. You can now monitor your child\'s results, fees, and attendance.',0,'2026-06-17 23:14:13'),(11,1,'student',1,'Welcome to Student Portal!','Welcome to your portal. Please configure your profile and change your temporary password.',0,'2026-06-17 23:14:37'),(12,1,'parent',1,'Welcome to Parent Portal!','Welcome to the parent portal. You can now monitor your child\'s results, fees, and attendance.',0,'2026-06-17 23:14:37');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parent_accounts`
--

DROP TABLE IF EXISTS `parent_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parent_accounts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '1',
  `reset_token` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `applicant_id` (`applicant_id`),
  KEY `idx_pa_email` (`email`),
  KEY `idx_pa_phone` (`phone`),
  KEY `idx_parent_accounts_school` (`school_id`),
  CONSTRAINT `fk_pa_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parent_accounts`
--

LOCK TABLES `parent_accounts` WRITE;
/*!40000 ALTER TABLE `parent_accounts` DISABLE KEYS */;
INSERT INTO `parent_accounts` VALUES (1,1,1,'07081306993','azzanmic@gmail.com','$2y$10$GKnOESB5EgpHeZdeMaW7bOgCs/s7eJzz3TkXf0PfFJfWFNIdj6Uri',0,NULL,NULL,'2026-06-30 00:43:52','2026-06-17 16:28:18');
/*!40000 ALTER TABLE `parent_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parents`
--

DROP TABLE IF EXISTS `parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `father_name` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_name` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_name` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `occupation` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_parents_applicant` (`applicant_id`),
  KEY `idx_parent_email` (`email`),
  KEY `idx_parent_phone` (`phone`),
  KEY `idx_parents_school` (`school_id`),
  CONSTRAINT `fk_parents_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parents`
--

LOCK TABLES `parents` WRITE;
/*!40000 ALTER TABLE `parents` DISABLE KEYS */;
/*!40000 ALTER TABLE `parents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `transaction_reference` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_status` enum('Pending','Paid','Failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `gateway` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_response` longtext COLLATE utf8mb4_unicode_ci,
  `fee_type` enum('admission_fee','acceptance_fee','enrollment_fee') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admission_fee',
  `payment_date` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `approved_by` int unsigned DEFAULT NULL,
  `approval_notes` text COLLATE utf8mb4_unicode_ci,
  `approved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_reference` (`transaction_reference`),
  KEY `fk_payments_applicant` (`applicant_id`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `fk_payments_admin` (`approved_by`),
  KEY `idx_payment_reference` (`transaction_reference`),
  KEY `idx_payments_school` (`school_id`),
  CONSTRAINT `fk_payments_admin` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_payments_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,1,'PAY202606040045243502',5000.00,'Paid','manual_bank',NULL,'admission_fee','2026-06-17 22:38:15','2026-06-04 00:45:24','2026-06-17 22:38:15',1,'','2026-06-17 22:38:15');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'applications','Manage applications'),(2,'payments','Manage payments'),(3,'reports','View reports'),(4,'settings','Manage white-label settings'),(5,'exams','Manage CBT entrance exams'),(6,'interviews','Manage interviews'),(7,'letters','Generate admission letters');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promotion_history`
--

DROP TABLE IF EXISTS `promotion_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promotion_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `from_class_id` int unsigned DEFAULT NULL,
  `to_class_id` int unsigned DEFAULT NULL,
  `academic_year` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` enum('Promoted','Repeated','Transferred','Graduated','Withdrawn') COLLATE utf8mb4_unicode_ci NOT NULL,
  `transfer_to_school` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `promoted_by` int unsigned DEFAULT NULL,
  `promoted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ph_applicant` (`applicant_id`),
  KEY `fk_ph_from` (`from_class_id`),
  KEY `fk_ph_to` (`to_class_id`),
  KEY `fk_ph_admin` (`promoted_by`),
  KEY `idx_promotion_history_school` (`school_id`),
  CONSTRAINT `fk_ph_admin` FOREIGN KEY (`promoted_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ph_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ph_from` FOREIGN KEY (`from_class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ph_to` FOREIGN KEY (`to_class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotion_history`
--

LOCK TABLES `promotion_history` WRITE;
/*!40000 ALTER TABLE `promotion_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `promotion_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` int unsigned NOT NULL,
  `permission_id` int unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_role_permissions_permission` (`permission_id`),
  CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','Full access to all system features'),(2,'admission_officer','Manage applications, exams, interviews, and admission decisions'),(3,'accountant','Manage payments, receipts, and revenue reports'),(4,'principal','View applications and reports'),(5,'staff','Limited application access');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_licenses`
--

DROP TABLE IF EXISTS `school_licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_licenses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `license_key` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan` enum('trial','basic','professional','enterprise') COLLATE utf8mb4_unicode_ci DEFAULT 'trial',
  `activated_at` datetime DEFAULT NULL,
  `expires_at` date NOT NULL,
  `grace_days` int DEFAULT '7',
  `is_active` tinyint(1) DEFAULT '1',
  `last_verified` datetime DEFAULT NULL,
  `features` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_key` (`license_key`),
  KEY `fk_lic_school` (`school_id`),
  CONSTRAINT `fk_lic_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_licenses`
--

LOCK TABLES `school_licenses` WRITE;
/*!40000 ALTER TABLE `school_licenses` DISABLE KEYS */;
INSERT INTO `school_licenses` VALUES (1,1,'SKY-LIC-BLUEFIELD-ACTIVE-KEY','enterprise','2026-06-29 22:09:46','2027-06-29',7,1,'2026-06-29 22:09:46',NULL);
/*!40000 ALTER TABLE `school_licenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_referrals`
--

DROP TABLE IF EXISTS `school_referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_referrals` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `referrer_school_id` int unsigned NOT NULL,
  `referred_school_id` int unsigned NOT NULL,
  `reward_status` enum('pending','credited') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referred_school_id` (`referred_school_id`),
  KEY `fk_ref_referrer` (`referrer_school_id`),
  CONSTRAINT `fk_ref_referred` FOREIGN KEY (`referred_school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ref_referrer` FOREIGN KEY (`referrer_school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_referrals`
--

LOCK TABLES `school_referrals` WRITE;
/*!40000 ALTER TABLE `school_referrals` DISABLE KEYS */;
/*!40000 ALTER TABLE `school_referrals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_settings`
--

DROP TABLE IF EXISTS `school_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `setting_key` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_school_setting` (`school_id`,`setting_key`),
  KEY `idx_settings_school` (`school_id`)
) ENGINE=InnoDB AUTO_INCREMENT=305 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_settings`
--

LOCK TABLES `school_settings` WRITE;
/*!40000 ALTER TABLE `school_settings` DISABLE KEYS */;
INSERT INTO `school_settings` VALUES (1,1,'school_name','Bluefield International School'),(2,1,'school_logo','branding/53fec8192ac39fe58f5d8f4f.jpg'),(3,1,'school_address','No. 1 Excellence Avenue, Lagos, Nigeria'),(4,1,'school_phone','+234 800 000 0000'),(5,1,'school_email','info@bluefieldschool.test'),(6,1,'principal_name','Mrs. Adeola Johnson'),(7,1,'admission_fee','5000'),(8,1,'school_motto','Excellence, Character, and Innovation'),(9,1,'school_website','https://school.example.com'),(10,1,'primary_color','#0b3d91'),(11,1,'secondary_color','#f4b942'),(12,1,'sidebar_color','#061a40'),(13,1,'button_color','#0b3d91'),(14,1,'dashboard_color','#1056c2'),(15,1,'acceptance_fee','25000'),(16,1,'enrollment_fee','50000'),(17,1,'students_admitted','1,250'),(18,1,'success_rate','96%'),(19,1,'graduates','800+'),(20,1,'available_spaces','120'),(41,1,'active_payment_gateway','paystack'),(42,1,'paystack_public_key',''),(43,1,'paystack_secret_key',''),(44,1,'monnify_api_key',''),(45,1,'monnify_secret_key',''),(46,1,'monnify_contract_code',''),(48,1,'academic_year','2024/2025'),(49,1,'current_term','First'),(50,1,'grading_system','nigerian'),(51,1,'grade_a1_min','75'),(52,1,'grade_a1_label','A1'),(53,1,'grade_b2_min','70'),(54,1,'grade_b2_label','B2'),(55,1,'grade_b3_min','65'),(56,1,'grade_b3_label','B3'),(57,1,'grade_c4_min','60'),(58,1,'grade_c4_label','C4'),(59,1,'grade_c5_min','55'),(60,1,'grade_c5_label','C5'),(61,1,'grade_c6_min','50'),(62,1,'grade_c6_label','C6'),(63,1,'grade_d7_min','45'),(64,1,'grade_d7_label','D7'),(65,1,'grade_e8_min','40'),(66,1,'grade_e8_label','E8'),(67,1,'grade_f9_min','0'),(68,1,'grade_f9_label','F9'),(69,1,'ca1_max','10'),(70,1,'ca2_max','10'),(71,1,'assignment_max','10'),(72,1,'mid_term_max','10'),(73,1,'exam_max','60'),(74,1,'parent_portal_enabled','1'),(75,1,'sms_gateway','stub'),(76,1,'termii_api_key',''),(77,1,'termii_sender_id','School'),(78,1,'school_daily_open_time','07:30'),(79,1,'school_daily_close_time','14:30'),(80,1,'library_fine_per_day','50'),(81,1,'transport_fee_label','Transport Levy'),(116,1,'admission_letter_title','Offer of Admission'),(117,1,'admission_letter_body','We are pleased to inform you that you have been offered admission into {class_name} at {school_name}.'),(118,1,'admission_letter_instruction','Please report to the school office with original copies of your submitted documents.'),(119,1,'admission_letter_closing','Congratulations, and welcome to our academic community.'),(120,1,'admission_letter_signature_title','Principal'),(155,1,'payment_environment','test'),(156,1,'smtp_host','mail.skysaveings.com.ng'),(157,1,'smtp_port','465'),(158,1,'smtp_secure','smtps'),(159,1,'smtp_username','support@skysaveings.com.ng'),(160,1,'smtp_password','@skysaveings.com.ng'),(161,1,'smtp_from_email','support@skysaveings.com.ng'),(162,1,'smtp_from_name','School Admission Portal'),(214,1,'attendance_open_time','07:00'),(215,1,'attendance_ontime_until','07:30'),(216,1,'attendance_late_from','07:31'),(217,1,'attendance_close_time','09:00'),(218,1,'school_close_time','14:30'),(219,1,'attendance_sms_enabled','1'),(220,1,'checkin_sms_enabled','1'),(221,1,'absent_sms_enabled','1'),(222,1,'auto_absent_enabled','1'),(223,1,'auto_absent_last_run',''),(224,1,'sms_api_key',''),(225,1,'sms_sender_id','EduCore');
/*!40000 ALTER TABLE `school_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_subscriptions`
--

DROP TABLE IF EXISTS `school_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_subscriptions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `invoice_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan` enum('trial','basic','professional','enterprise') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'NGN',
  `payment_method` enum('bank_transfer','paystack','flutterwave','cash','invoice') COLLATE utf8mb4_unicode_ci DEFAULT 'bank_transfer',
  `transaction_ref` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `status` enum('pending','paid','failed','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `fk_sub_school` (`school_id`),
  CONSTRAINT `fk_sub_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_subscriptions`
--

LOCK TABLES `school_subscriptions` WRITE;
/*!40000 ALTER TABLE `school_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `school_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `school_update_log`
--

DROP TABLE IF EXISTS `school_update_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_update_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `from_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','success','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `log` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_upl_school` (`school_id`),
  CONSTRAINT `fk_upl_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `school_update_log`
--

LOCK TABLES `school_update_log` WRITE;
/*!40000 ALTER TABLE `school_update_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `school_update_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schools` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `principal_name` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `state` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT 'Nigeria',
  `status` enum('active','suspended','inactive','trial') COLLATE utf8mb4_unicode_ci DEFAULT 'trial',
  `sms_balance` int unsigned NOT NULL DEFAULT '100',
  `referral_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referred_by_id` int unsigned DEFAULT NULL,
  `api_key` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installation_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `php_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mysql_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hosting_provider` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ssl_status` tinyint(1) DEFAULT '0',
  `educore_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installed_at` datetime DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `registered_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_code` (`school_code`),
  UNIQUE KEY `referral_code` (`referral_code`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `fk_sch_referrer` (`referred_by_id`),
  CONSTRAINT `fk_sch_referrer` FOREIGN KEY (`referred_by_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schools`
--

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` VALUES (1,'SKY-0001','Bluefield International School',NULL,NULL,'Mrs. Adeola Johnson',NULL,NULL,NULL,NULL,'Nigeria','active',100,'REF-BLUEFIELD-99',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'2026-06-29 22:09:46');
/*!40000 ALTER TABLE `schools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_logs`
--

DROP TABLE IF EXISTS `sms_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `recipient_phone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('sent','failed','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `gateway` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sms_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general' COMMENT 'checkin | absent | bulk | test | general',
  `attendance_id` int unsigned DEFAULT NULL COMMENT 'Reference to attendance row',
  `gateway_response` text COLLATE utf8mb4_unicode_ci,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sms_status` (`status`),
  KEY `idx_sms_phone` (`recipient_phone`),
  KEY `idx_sms_type` (`sms_type`),
  KEY `idx_sms_att` (`attendance_id`),
  KEY `idx_sms_logs_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_logs`
--

LOCK TABLES `sms_logs` WRITE;
/*!40000 ALTER TABLE `sms_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `sms_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_recharge_logs`
--

DROP TABLE IF EXISTS `sms_recharge_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_recharge_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL,
  `amount_credits` int unsigned NOT NULL,
  `cost` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'invoice',
  `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recharged_by` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_sms_rec_school` (`school_id`),
  CONSTRAINT `fk_sms_rec_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_recharge_logs`
--

LOCK TABLES `sms_recharge_logs` WRITE;
/*!40000 ALTER TABLE `sms_recharge_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `sms_recharge_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `staff_id` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Male',
  `date_of_birth` date DEFAULT NULL,
  `qualification` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('Teacher','Form Teacher','Head of Department','Vice Principal','Principal','Bursar','Librarian','Driver','Security','Cleaner','Other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Teacher',
  `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_joined` date DEFAULT NULL,
  `salary` decimal(12,2) DEFAULT NULL,
  `bank_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Active','On Leave','Resigned','Terminated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_id` (`staff_id`),
  KEY `idx_staff_role` (`role`),
  KEY `idx_staff_status` (`status`),
  KEY `idx_staff_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_accounts`
--

DROP TABLE IF EXISTS `staff_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_accounts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `staff_id` int unsigned NOT NULL,
  `username` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_id` (`staff_id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_staff_accounts_school` (`school_id`),
  CONSTRAINT `staff_accounts_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_accounts`
--

LOCK TABLES `staff_accounts` WRITE;
/*!40000 ALTER TABLE `staff_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_attendance`
--

DROP TABLE IF EXISTS `staff_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_attendance` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `staff_id` int unsigned NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','Late','On Leave','Holiday') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Present',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_staff_att` (`staff_id`,`date`),
  KEY `idx_staff_attendance_school` (`school_id`),
  CONSTRAINT `fk_sa_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_attendance`
--

LOCK TABLES `staff_attendance` WRITE;
/*!40000 ALTER TABLE `staff_attendance` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_class_assignments`
--

DROP TABLE IF EXISTS `staff_class_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_class_assignments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `staff_id` int unsigned NOT NULL,
  `class_id` int unsigned NOT NULL,
  `subject_id` int unsigned DEFAULT NULL,
  `academic_year` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_form_teacher` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sca` (`staff_id`,`class_id`,`subject_id`,`academic_year`),
  KEY `fk_sca_class` (`class_id`),
  KEY `fk_sca_subject` (`subject_id`),
  KEY `idx_staff_class_assignments_school` (`school_id`),
  CONSTRAINT `fk_sca_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sca_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sca_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_class_assignments`
--

LOCK TABLES `staff_class_assignments` WRITE;
/*!40000 ALTER TABLE `staff_class_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_class_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_accounts`
--

DROP TABLE IF EXISTS `student_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_accounts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `username` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `applicant_id` (`applicant_id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_student_accounts_school` (`school_id`),
  CONSTRAINT `student_accounts_ibfk_1` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_accounts`
--

LOCK TABLES `student_accounts` WRITE;
/*!40000 ALTER TABLE `student_accounts` DISABLE KEYS */;
INSERT INTO `student_accounts` VALUES (1,1,1,'SCH20260002','$2y$10$N8PXqIMwc2CdasDmyrrDQufhjvunGPnH4rp0K5uF7QaEsYmHrHOh2',0,'2026-06-17 22:58:28','2026-06-17 22:54:21');
/*!40000 ALTER TABLE `student_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_fee_payments`
--

DROP TABLE IF EXISTS `student_fee_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_fee_payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `fee_structure_id` int unsigned NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL,
  `balance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_reference` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` enum('Pending','Partial','Paid','Failed','Manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `payment_method` enum('paystack','cash','bank_transfer','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paystack',
  `payment_date` datetime DEFAULT NULL,
  `receipt_number` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recorded_by` int unsigned DEFAULT NULL COMMENT 'admin id for manual payments',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_reference` (`payment_reference`),
  KEY `fk_sfp_applicant` (`applicant_id`),
  KEY `fk_sfp_fee` (`fee_structure_id`),
  KEY `fk_sfp_admin` (`recorded_by`),
  KEY `idx_sfp_status` (`payment_status`),
  KEY `idx_student_fee_payments_school` (`school_id`),
  CONSTRAINT `fk_sfp_admin` FOREIGN KEY (`recorded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sfp_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sfp_fee` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_fee_payments`
--

LOCK TABLES `student_fee_payments` WRITE;
/*!40000 ALTER TABLE `student_fee_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_fee_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_results`
--

DROP TABLE IF EXISTS `student_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_results` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `subject_id` int unsigned NOT NULL,
  `class_id` int unsigned NOT NULL,
  `term` enum('First','Second','Third') COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ca1` decimal(5,2) DEFAULT NULL COMMENT 'Test / CA 1 (max 10)',
  `ca2` decimal(5,2) DEFAULT NULL COMMENT 'Test / CA 2 (max 10)',
  `assignment` decimal(5,2) DEFAULT NULL COMMENT 'Assignment / Project (max 10)',
  `mid_term` decimal(5,2) DEFAULT NULL COMMENT 'Mid-term test (max 10)',
  `exam` decimal(5,2) DEFAULT NULL COMMENT 'Exam score (max 60)',
  `total` decimal(5,2) DEFAULT NULL COMMENT 'Calculated total /100',
  `grade` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remark` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int unsigned DEFAULT NULL,
  `class_size` int unsigned DEFAULT NULL,
  `teacher_remark` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_result` (`applicant_id`,`subject_id`,`term`,`academic_year`),
  KEY `fk_sr_subject` (`subject_id`),
  KEY `fk_sr_class` (`class_id`),
  KEY `idx_sr_year_term` (`academic_year`,`term`),
  KEY `idx_student_results_school` (`school_id`),
  CONSTRAINT `fk_sr_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sr_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sr_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_results`
--

LOCK TABLES `student_results` WRITE;
/*!40000 ALTER TABLE `student_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_transport`
--

DROP TABLE IF EXISTS `student_transport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_transport` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `bus_id` int unsigned DEFAULT NULL,
  `route_id` int unsigned DEFAULT NULL,
  `pickup_point` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `academic_year` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `applicant_id` (`applicant_id`),
  KEY `fk_st_bus` (`bus_id`),
  KEY `fk_st_route` (`route_id`),
  KEY `idx_student_transport_school` (`school_id`),
  CONSTRAINT `fk_st_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_st_bus` FOREIGN KEY (`bus_id`) REFERENCES `transport_buses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_st_route` FOREIGN KEY (`route_id`) REFERENCES `transport_routes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_transport`
--

LOCK TABLES `student_transport` WRITE;
/*!40000 ALTER TABLE `student_transport` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_transport` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subjects` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `class_id` int unsigned DEFAULT NULL COMMENT 'NULL = all classes (general subject)',
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `teacher_id` int unsigned DEFAULT NULL COMMENT 'staff member assigned',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_subj_class` (`class_id`),
  KEY `idx_subjects_school` (`school_id`),
  CONSTRAINT `fk_subj_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,1,NULL,'English Language','ENG',NULL,1,'2026-06-17 16:23:09'),(2,1,NULL,'Mathematics','MTH',NULL,1,'2026-06-17 16:23:09'),(3,1,NULL,'Basic Science','BSC',NULL,1,'2026-06-17 16:23:09'),(4,1,NULL,'Social Studies','SST',NULL,1,'2026-06-17 16:23:09'),(5,1,NULL,'Civic Education','CIV',NULL,1,'2026-06-17 16:23:09'),(6,1,NULL,'Christian Religious Studies','CRS',NULL,1,'2026-06-17 16:23:09'),(7,1,NULL,'Islamic Religious Studies','IRS',NULL,1,'2026-06-17 16:23:09'),(8,1,NULL,'Physical & Health Education','PHE',NULL,1,'2026-06-17 16:23:09'),(9,1,NULL,'Agricultural Science','AGR',NULL,1,'2026-06-17 16:23:09'),(10,1,NULL,'Computer Studies','CMP',NULL,1,'2026-06-17 16:23:09'),(11,1,NULL,'Fine Arts','ART',NULL,1,'2026-06-17 16:23:09'),(12,1,NULL,'Music','MUS',NULL,1,'2026-06-17 16:23:09'),(13,1,NULL,'Home Economics','HEC',NULL,1,'2026-06-17 16:23:09'),(14,1,NULL,'Technical Drawing','TDR',NULL,1,'2026-06-17 16:23:09'),(15,1,NULL,'French','FRN',NULL,1,'2026-06-17 16:23:09'),(16,1,NULL,'Yoruba','YOR',NULL,1,'2026-06-17 16:23:09'),(17,1,NULL,'Hausa','HAU',NULL,1,'2026-06-17 16:23:09'),(18,1,NULL,'Igbo','IGB',NULL,1,'2026-06-17 16:23:09'),(19,1,NULL,'Biology','BIO',NULL,1,'2026-06-17 16:23:09'),(20,1,NULL,'Chemistry','CHM',NULL,1,'2026-06-17 16:23:09'),(21,1,NULL,'Physics','PHY',NULL,1,'2026-06-17 16:23:09'),(22,1,NULL,'Further Mathematics','FMT',NULL,1,'2026-06-17 16:23:09'),(23,1,NULL,'Economics','ECO',NULL,1,'2026-06-17 16:23:09'),(24,1,NULL,'Geography','GEO',NULL,1,'2026-06-17 16:23:09'),(25,1,NULL,'Government','GOV',NULL,1,'2026-06-17 16:23:09'),(26,1,NULL,'Literature in English','LIT',NULL,1,'2026-06-17 16:23:09'),(27,1,NULL,'Commerce','COM',NULL,1,'2026-06-17 16:23:09'),(28,1,NULL,'Accounting','ACC',NULL,1,'2026-06-17 16:23:09'),(29,1,NULL,'English Language','ENG',NULL,1,'2026-06-17 16:23:41'),(30,1,NULL,'Mathematics','MTH',NULL,1,'2026-06-17 16:23:41'),(31,1,NULL,'Basic Science','BSC',NULL,1,'2026-06-17 16:23:41'),(32,1,NULL,'Social Studies','SST',NULL,1,'2026-06-17 16:23:41'),(33,1,NULL,'Civic Education','CIV',NULL,1,'2026-06-17 16:23:41'),(34,1,NULL,'Christian Religious Studies','CRS',NULL,1,'2026-06-17 16:23:41'),(35,1,NULL,'Islamic Religious Studies','IRS',NULL,1,'2026-06-17 16:23:41'),(36,1,NULL,'Physical & Health Education','PHE',NULL,1,'2026-06-17 16:23:41'),(37,1,NULL,'Agricultural Science','AGR',NULL,1,'2026-06-17 16:23:41'),(38,1,NULL,'Computer Studies','CMP',NULL,1,'2026-06-17 16:23:41'),(39,1,NULL,'Fine Arts','ART',NULL,1,'2026-06-17 16:23:41'),(40,1,NULL,'Music','MUS',NULL,1,'2026-06-17 16:23:41'),(41,1,NULL,'Home Economics','HEC',NULL,1,'2026-06-17 16:23:41'),(42,1,NULL,'Technical Drawing','TDR',NULL,1,'2026-06-17 16:23:41'),(43,1,NULL,'French','FRN',NULL,1,'2026-06-17 16:23:41'),(44,1,NULL,'Yoruba','YOR',NULL,1,'2026-06-17 16:23:41'),(45,1,NULL,'Hausa','HAU',NULL,1,'2026-06-17 16:23:41'),(46,1,NULL,'Igbo','IGB',NULL,1,'2026-06-17 16:23:41'),(47,1,NULL,'Biology','BIO',NULL,1,'2026-06-17 16:23:41'),(48,1,NULL,'Chemistry','CHM',NULL,1,'2026-06-17 16:23:41'),(49,1,NULL,'Physics','PHY',NULL,1,'2026-06-17 16:23:41'),(50,1,NULL,'Further Mathematics','FMT',NULL,1,'2026-06-17 16:23:41'),(51,1,NULL,'Economics','ECO',NULL,1,'2026-06-17 16:23:41'),(52,1,NULL,'Geography','GEO',NULL,1,'2026-06-17 16:23:41'),(53,1,NULL,'Government','GOV',NULL,1,'2026-06-17 16:23:41'),(54,1,NULL,'Literature in English','LIT',NULL,1,'2026-06-17 16:23:41'),(55,1,NULL,'Commerce','COM',NULL,1,'2026-06-17 16:23:41'),(56,1,NULL,'Accounting','ACC',NULL,1,'2026-06-17 16:23:41'),(57,1,NULL,'English Language','ENG',NULL,1,'2026-06-17 17:00:22'),(58,1,NULL,'Mathematics','MTH',NULL,1,'2026-06-17 17:00:22'),(59,1,NULL,'Basic Science','BSC',NULL,1,'2026-06-17 17:00:22'),(60,1,NULL,'Social Studies','SST',NULL,1,'2026-06-17 17:00:22'),(61,1,NULL,'Civic Education','CIV',NULL,1,'2026-06-17 17:00:22'),(62,1,NULL,'Christian Religious Studies','CRS',NULL,1,'2026-06-17 17:00:22'),(63,1,NULL,'Islamic Religious Studies','IRS',NULL,1,'2026-06-17 17:00:22'),(64,1,NULL,'Physical & Health Education','PHE',NULL,1,'2026-06-17 17:00:22'),(65,1,NULL,'Agricultural Science','AGR',NULL,1,'2026-06-17 17:00:22'),(66,1,NULL,'Computer Studies','CMP',NULL,1,'2026-06-17 17:00:22'),(67,1,NULL,'Fine Arts','ART',NULL,1,'2026-06-17 17:00:22'),(68,1,NULL,'Music','MUS',NULL,1,'2026-06-17 17:00:22'),(69,1,NULL,'Home Economics','HEC',NULL,1,'2026-06-17 17:00:22'),(70,1,NULL,'Technical Drawing','TDR',NULL,1,'2026-06-17 17:00:22'),(71,1,NULL,'French','FRN',NULL,1,'2026-06-17 17:00:22'),(72,1,NULL,'Yoruba','YOR',NULL,1,'2026-06-17 17:00:22'),(73,1,NULL,'Hausa','HAU',NULL,1,'2026-06-17 17:00:22'),(74,1,NULL,'Igbo','IGB',NULL,1,'2026-06-17 17:00:22'),(75,1,NULL,'Biology','BIO',NULL,1,'2026-06-17 17:00:22'),(76,1,NULL,'Chemistry','CHM',NULL,1,'2026-06-17 17:00:22'),(77,1,NULL,'Physics','PHY',NULL,1,'2026-06-17 17:00:22'),(78,1,NULL,'Further Mathematics','FMT',NULL,1,'2026-06-17 17:00:22'),(79,1,NULL,'Economics','ECO',NULL,1,'2026-06-17 17:00:22'),(80,1,NULL,'Geography','GEO',NULL,1,'2026-06-17 17:00:22'),(81,1,NULL,'Government','GOV',NULL,1,'2026-06-17 17:00:22'),(82,1,NULL,'Literature in English','LIT',NULL,1,'2026-06-17 17:00:22'),(83,1,NULL,'Commerce','COM',NULL,1,'2026-06-17 17:00:22'),(84,1,NULL,'Accounting','ACC',NULL,1,'2026-06-17 17:00:22'),(85,1,NULL,'English Language','ENG',NULL,1,'2026-06-17 22:02:42'),(86,1,NULL,'Mathematics','MTH',NULL,1,'2026-06-17 22:02:42'),(87,1,NULL,'Basic Science','BSC',NULL,1,'2026-06-17 22:02:42'),(88,1,NULL,'Social Studies','SST',NULL,1,'2026-06-17 22:02:42'),(89,1,NULL,'Civic Education','CIV',NULL,1,'2026-06-17 22:02:42'),(90,1,NULL,'Christian Religious Studies','CRS',NULL,1,'2026-06-17 22:02:42'),(91,1,NULL,'Islamic Religious Studies','IRS',NULL,1,'2026-06-17 22:02:42'),(92,1,NULL,'Physical & Health Education','PHE',NULL,1,'2026-06-17 22:02:42'),(93,1,NULL,'Agricultural Science','AGR',NULL,1,'2026-06-17 22:02:42'),(94,1,NULL,'Computer Studies','CMP',NULL,1,'2026-06-17 22:02:42'),(95,1,NULL,'Fine Arts','ART',NULL,1,'2026-06-17 22:02:42'),(96,1,NULL,'Music','MUS',NULL,1,'2026-06-17 22:02:42'),(97,1,NULL,'Home Economics','HEC',NULL,1,'2026-06-17 22:02:42'),(98,1,NULL,'Technical Drawing','TDR',NULL,1,'2026-06-17 22:02:42'),(99,1,NULL,'French','FRN',NULL,1,'2026-06-17 22:02:42'),(100,1,NULL,'Yoruba','YOR',NULL,1,'2026-06-17 22:02:42'),(101,1,NULL,'Hausa','HAU',NULL,1,'2026-06-17 22:02:42'),(102,1,NULL,'Igbo','IGB',NULL,1,'2026-06-17 22:02:42'),(103,1,NULL,'Biology','BIO',NULL,1,'2026-06-17 22:02:42'),(104,1,NULL,'Chemistry','CHM',NULL,1,'2026-06-17 22:02:42'),(105,1,NULL,'Physics','PHY',NULL,1,'2026-06-17 22:02:42'),(106,1,NULL,'Further Mathematics','FMT',NULL,1,'2026-06-17 22:02:42'),(107,1,NULL,'Economics','ECO',NULL,1,'2026-06-17 22:02:42'),(108,1,NULL,'Geography','GEO',NULL,1,'2026-06-17 22:02:42'),(109,1,NULL,'Government','GOV',NULL,1,'2026-06-17 22:02:42'),(110,1,NULL,'Literature in English','LIT',NULL,1,'2026-06-17 22:02:42'),(111,1,NULL,'Commerce','COM',NULL,1,'2026-06-17 22:02:42'),(112,1,NULL,'Accounting','ACC',NULL,1,'2026-06-17 22:02:42'),(113,1,NULL,'English Language','ENG',NULL,1,'2026-06-17 22:51:03'),(114,1,NULL,'Mathematics','MTH',NULL,1,'2026-06-17 22:51:03'),(115,1,NULL,'Basic Science','BSC',NULL,1,'2026-06-17 22:51:03'),(116,1,NULL,'Social Studies','SST',NULL,1,'2026-06-17 22:51:03'),(117,1,NULL,'Civic Education','CIV',NULL,1,'2026-06-17 22:51:03'),(118,1,NULL,'Christian Religious Studies','CRS',NULL,1,'2026-06-17 22:51:03'),(119,1,NULL,'Islamic Religious Studies','IRS',NULL,1,'2026-06-17 22:51:03'),(120,1,NULL,'Physical & Health Education','PHE',NULL,1,'2026-06-17 22:51:03'),(121,1,NULL,'Agricultural Science','AGR',NULL,1,'2026-06-17 22:51:03'),(122,1,NULL,'Computer Studies','CMP',NULL,1,'2026-06-17 22:51:03'),(123,1,NULL,'Fine Arts','ART',NULL,1,'2026-06-17 22:51:03'),(124,1,NULL,'Music','MUS',NULL,1,'2026-06-17 22:51:03'),(125,1,NULL,'Home Economics','HEC',NULL,1,'2026-06-17 22:51:03'),(126,1,NULL,'Technical Drawing','TDR',NULL,1,'2026-06-17 22:51:03'),(127,1,NULL,'French','FRN',NULL,1,'2026-06-17 22:51:03'),(128,1,NULL,'Yoruba','YOR',NULL,1,'2026-06-17 22:51:03'),(129,1,NULL,'Hausa','HAU',NULL,1,'2026-06-17 22:51:03'),(130,1,NULL,'Igbo','IGB',NULL,1,'2026-06-17 22:51:03'),(131,1,NULL,'Biology','BIO',NULL,1,'2026-06-17 22:51:03'),(132,1,NULL,'Chemistry','CHM',NULL,1,'2026-06-17 22:51:03'),(133,1,NULL,'Physics','PHY',NULL,1,'2026-06-17 22:51:03'),(134,1,NULL,'Further Mathematics','FMT',NULL,1,'2026-06-17 22:51:03'),(135,1,NULL,'Economics','ECO',NULL,1,'2026-06-17 22:51:03'),(136,1,NULL,'Geography','GEO',NULL,1,'2026-06-17 22:51:03'),(137,1,NULL,'Government','GOV',NULL,1,'2026-06-17 22:51:03'),(138,1,NULL,'Literature in English','LIT',NULL,1,'2026-06-17 22:51:03'),(139,1,NULL,'Commerce','COM',NULL,1,'2026-06-17 22:51:03'),(140,1,NULL,'Accounting','ACC',NULL,1,'2026-06-17 22:51:03'),(141,1,NULL,'English Language','ENG',NULL,1,'2026-06-29 22:29:14'),(142,1,NULL,'Mathematics','MTH',NULL,1,'2026-06-29 22:29:14'),(143,1,NULL,'Basic Science','BSC',NULL,1,'2026-06-29 22:29:14'),(144,1,NULL,'Social Studies','SST',NULL,1,'2026-06-29 22:29:14'),(145,1,NULL,'Civic Education','CIV',NULL,1,'2026-06-29 22:29:14'),(146,1,NULL,'Christian Religious Studies','CRS',NULL,1,'2026-06-29 22:29:14'),(147,1,NULL,'Islamic Religious Studies','IRS',NULL,1,'2026-06-29 22:29:14'),(148,1,NULL,'Physical & Health Education','PHE',NULL,1,'2026-06-29 22:29:14'),(149,1,NULL,'Agricultural Science','AGR',NULL,1,'2026-06-29 22:29:14'),(150,1,NULL,'Computer Studies','CMP',NULL,1,'2026-06-29 22:29:14'),(151,1,NULL,'Fine Arts','ART',NULL,1,'2026-06-29 22:29:14'),(152,1,NULL,'Music','MUS',NULL,1,'2026-06-29 22:29:14'),(153,1,NULL,'Home Economics','HEC',NULL,1,'2026-06-29 22:29:14'),(154,1,NULL,'Technical Drawing','TDR',NULL,1,'2026-06-29 22:29:14'),(155,1,NULL,'French','FRN',NULL,1,'2026-06-29 22:29:14'),(156,1,NULL,'Yoruba','YOR',NULL,1,'2026-06-29 22:29:14'),(157,1,NULL,'Hausa','HAU',NULL,1,'2026-06-29 22:29:14'),(158,1,NULL,'Igbo','IGB',NULL,1,'2026-06-29 22:29:14'),(159,1,NULL,'Biology','BIO',NULL,1,'2026-06-29 22:29:14'),(160,1,NULL,'Chemistry','CHM',NULL,1,'2026-06-29 22:29:14'),(161,1,NULL,'Physics','PHY',NULL,1,'2026-06-29 22:29:14'),(162,1,NULL,'Further Mathematics','FMT',NULL,1,'2026-06-29 22:29:14'),(163,1,NULL,'Economics','ECO',NULL,1,'2026-06-29 22:29:14'),(164,1,NULL,'Geography','GEO',NULL,1,'2026-06-29 22:29:14'),(165,1,NULL,'Government','GOV',NULL,1,'2026-06-29 22:29:14'),(166,1,NULL,'Literature in English','LIT',NULL,1,'2026-06-29 22:29:14'),(167,1,NULL,'Commerce','COM',NULL,1,'2026-06-29 22:29:14'),(168,1,NULL,'Accounting','ACC',NULL,1,'2026-06-29 22:29:14');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `super_announcements`
--

DROP TABLE IF EXISTS `super_announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `super_announcements` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` enum('all','specific','trial','expired') COLLATE utf8mb4_unicode_ci DEFAULT 'all',
  `target_school_ids` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `super_announcements`
--

LOCK TABLES `super_announcements` WRITE;
/*!40000 ALTER TABLE `super_announcements` DISABLE KEYS */;
/*!40000 ALTER TABLE `super_announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `super_audit_logs`
--

DROP TABLE IF EXISTS `super_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `super_audit_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int unsigned DEFAULT NULL,
  `school_id` int unsigned DEFAULT NULL,
  `action` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` int unsigned DEFAULT NULL,
  `old_value` text COLLATE utf8mb4_unicode_ci,
  `new_value` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_aud_admin` (`admin_id`),
  KEY `fk_aud_school` (`school_id`),
  CONSTRAINT `fk_aud_admin` FOREIGN KEY (`admin_id`) REFERENCES `superadmins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_aud_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `super_audit_logs`
--

LOCK TABLES `super_audit_logs` WRITE;
/*!40000 ALTER TABLE `super_audit_logs` DISABLE KEYS */;
INSERT INTO `super_audit_logs` VALUES (1,1,NULL,'login','superadmins',1,NULL,'Logged in to SST Hub','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-29 22:30:35'),(2,1,NULL,'login','superadmins',1,NULL,'Logged in to SST Hub','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0','2026-06-30 00:32:29');
/*!40000 ALTER TABLE `super_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `superadmins`
--

DROP TABLE IF EXISTS `superadmins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `superadmins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('owner','manager','support') COLLATE utf8mb4_unicode_ci DEFAULT 'support',
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `superadmins`
--

LOCK TABLES `superadmins` WRITE;
/*!40000 ALTER TABLE `superadmins` DISABLE KEYS */;
INSERT INTO `superadmins` VALUES (1,'SkySavingTech Admin','superadmin@skysavingtech.com','$2y$10$2vcKgYBrgFUIePHS0uoL8.Por9TLg/jStVsz5oWhGw5EFXR1nI83C','owner',1,'2026-06-30 00:32:29','2026-06-29 22:09:46');
/*!40000 ALTER TABLE `superadmins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_ticket_replies`
--

DROP TABLE IF EXISTS `support_ticket_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_ticket_replies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int unsigned NOT NULL,
  `sender_type` enum('school','superadmin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_id` int unsigned NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachments` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_reply_ticket` (`ticket_id`),
  CONSTRAINT `fk_reply_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_ticket_replies`
--

LOCK TABLES `support_ticket_replies` WRITE;
/*!40000 ALTER TABLE `support_ticket_replies` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_ticket_replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_tickets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ticket_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` int unsigned NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('billing','technical','feature','bug','other') COLLATE utf8mb4_unicode_ci DEFAULT 'technical',
  `priority` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `status` enum('open','in_progress','waiting','resolved','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `assigned_to` int unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_number` (`ticket_number`),
  KEY `fk_ticket_school` (`school_id`),
  KEY `fk_ticket_assignee` (`assigned_to`),
  CONSTRAINT `fk_ticket_assignee` FOREIGN KEY (`assigned_to`) REFERENCES `superadmins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ticket_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_tickets`
--

LOCK TABLES `support_tickets` WRITE;
/*!40000 ALTER TABLE `support_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'paystack_public_key','pk_test_placeholder','2026-06-30 00:50:03'),(2,'paystack_secret_key','sk_test_placeholder','2026-06-30 00:50:03'),(3,'monnify_api_key','api_test_placeholder','2026-06-30 00:50:03'),(4,'monnify_contract_code','contract_placeholder','2026-06-30 00:50:03'),(5,'flutterwave_public_key','flwpk_test_placeholder','2026-06-30 00:50:03'),(6,'flutterwave_secret_key','flwsk_test_placeholder','2026-06-30 00:50:03'),(7,'gateway_mode','test','2026-06-30 00:50:03'),(8,'tax_rate_percent','7.5','2026-06-30 00:50:03'),(9,'sms_rate_ngn','4.50','2026-06-30 00:50:03');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_updates`
--

DROP TABLE IF EXISTS `system_updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_updates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `release_notes` text COLLATE utf8mb4_unicode_ci,
  `download_url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checksum` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_package_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sql_migration_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apk_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '2.0.0',
  `is_published` tinyint(1) DEFAULT '0',
  `released_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_updates`
--

LOCK TABLES `system_updates` WRITE;
/*!40000 ALTER TABLE `system_updates` DISABLE KEYS */;
INSERT INTO `system_updates` VALUES (1,'2.0.0','Initial release of EduCore SaaS Platform',NULL,NULL,NULL,NULL,NULL,'2.0.0',1,'2026-06-29 22:09:46','2026-06-29 22:09:46'),(2,'2.0.0','Initial release of EduCore SaaS Platform',NULL,NULL,NULL,NULL,NULL,'2.0.0',1,'2026-06-29 22:28:31','2026-06-29 22:28:31');
/*!40000 ALTER TABLE `system_updates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `term_remarks`
--

DROP TABLE IF EXISTS `term_remarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `term_remarks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `applicant_id` int unsigned NOT NULL,
  `class_id` int unsigned NOT NULL,
  `term` enum('First','Second','Third') COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_score` decimal(6,2) DEFAULT NULL,
  `average` decimal(5,2) DEFAULT NULL,
  `position` int unsigned DEFAULT NULL,
  `class_size` int unsigned DEFAULT NULL,
  `times_present` int unsigned DEFAULT '0',
  `times_absent` int unsigned DEFAULT '0',
  `class_teacher_remark` text COLLATE utf8mb4_unicode_ci,
  `principal_remark` text COLLATE utf8mb4_unicode_ci,
  `next_term_begins` date DEFAULT NULL,
  `promoted` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_term_remark` (`applicant_id`,`term`,`academic_year`),
  KEY `fk_tr_class` (`class_id`),
  KEY `idx_term_remarks_school` (`school_id`),
  CONSTRAINT `fk_tr_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tr_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `term_remarks`
--

LOCK TABLES `term_remarks` WRITE;
/*!40000 ALTER TABLE `term_remarks` DISABLE KEYS */;
/*!40000 ALTER TABLE `term_remarks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timetables`
--

DROP TABLE IF EXISTS `timetables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timetables` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `class_id` int unsigned NOT NULL,
  `day_of_week` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_id` int unsigned NOT NULL,
  `teacher_id` int unsigned DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `idx_timetables_school` (`school_id`),
  CONSTRAINT `timetables_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timetables_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timetables_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timetables`
--

LOCK TABLES `timetables` WRITE;
/*!40000 ALTER TABLE `timetables` DISABLE KEYS */;
/*!40000 ALTER TABLE `timetables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transport_buses`
--

DROP TABLE IF EXISTS `transport_buses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transport_buses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `bus_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plate_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int unsigned NOT NULL DEFAULT '30',
  `driver_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `driver_phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route_id` int unsigned DEFAULT NULL,
  `status` enum('Active','Maintenance','Retired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate_number` (`plate_number`),
  KEY `fk_tb_route` (`route_id`),
  KEY `idx_transport_buses_school` (`school_id`),
  CONSTRAINT `fk_tb_route` FOREIGN KEY (`route_id`) REFERENCES `transport_routes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transport_buses`
--

LOCK TABLES `transport_buses` WRITE;
/*!40000 ALTER TABLE `transport_buses` DISABLE KEYS */;
/*!40000 ALTER TABLE `transport_buses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transport_routes`
--

DROP TABLE IF EXISTS `transport_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transport_routes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int unsigned NOT NULL DEFAULT '1',
  `route_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pickup_points` text COLLATE utf8mb4_unicode_ci,
  `fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_transport_routes_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transport_routes`
--

LOCK TABLES `transport_routes` WRITE;
/*!40000 ALTER TABLE `transport_routes` DISABLE KEYS */;
/*!40000 ALTER TABLE `transport_routes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-30  0:50:12
