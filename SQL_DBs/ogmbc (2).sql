-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: second_mysql
-- Generation Time: Feb 28, 2026 at 04:39 AM
-- Server version: 8.0.45
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ogmbc`
--

-- --------------------------------------------------------

--
-- Table structure for table `annual_performance`
--

CREATE TABLE `annual_performance` (
  `performance_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `year` int NOT NULL,
  `total_points` int NOT NULL,
  `base_percentage` decimal(5,2) NOT NULL,
  `cdp_uplift` decimal(5,2) DEFAULT '0.00',
  `loyalty_uplift` decimal(5,2) DEFAULT '0.00',
  `behavior_uplift` decimal(5,2) DEFAULT '0.00',
  `total_uplift` decimal(5,2) DEFAULT '0.00',
  `final_percentage` decimal(5,2) NOT NULL,
  `recommended_band` varchar(50) DEFAULT NULL,
  `status` enum('DRAFT','PENDING_APPROVAL','APPROVED','LOCKED') DEFAULT 'DRAFT',
  `approved_by` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `log_id` int NOT NULL,
  `user_id` int NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int NOT NULL,
  `old_data` json DEFAULT NULL,
  `new_data` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `account_id` int NOT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `account_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `account_number` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `iban_number` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `swift_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank_country` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `bank_address` text COLLATE utf8mb4_general_ci,
  `currency` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'USD',
  `is_active` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_accounts`
--

INSERT INTO `bank_accounts` (`account_id`, `bank_name`, `account_name`, `account_number`, `iban_number`, `swift_code`, `bank_country`, `bank_address`, `currency`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'New Bank', 'Bank Name', '123456789', 'AE1234567', '4444', 'UAE', 'New', 'GBP', 0, '2025-12-02 10:14:20', '2025-12-02 10:14:33');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `cat_id` int NOT NULL,
  `cat_title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `cat_price` decimal(10,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`cat_id`, `cat_title`, `cat_price`) VALUES
(1, 'Business Setup', 10000.00),
(2, 'Accounting & Taxation', 4000.00);

-- --------------------------------------------------------

--
-- Table structure for table `cdp_records`
--

CREATE TABLE `cdp_records` (
  `cdp_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `cdp_type` enum('CERTIFICATE','COURSE','LOYALTY','BEHAVIOR') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `document_file` varchar(255) NOT NULL,
  `uplift_percentage` decimal(5,2) DEFAULT NULL,
  `effective_date` date NOT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  `approved_by` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `trade_license_no` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `emirate_zone` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `business_activity` text COLLATE utf8mb4_general_ci,
  `address` text COLLATE utf8mb4_general_ci,
  `contact_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `contact_designation` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_mobile` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `client_password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `service_id` int DEFAULT NULL,
  `service_description` text COLLATE utf8mb4_general_ci,
  `expected_start_date` date DEFAULT NULL,
  `payment_currency` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'AED',
  `payment_term` enum('Monthly','Quarterly','Bi-yearly','One-time') COLLATE utf8mb4_general_ci DEFAULT 'Monthly',
  `service_total_fee` decimal(10,2) DEFAULT '0.00',
  `lead_source` enum('referral','website','digital_marketing','event') COLLATE utf8mb4_general_ci DEFAULT 'website',
  `client_status` enum('New Lead','Contacted','Qualified','Proposal Drafted','Under Manager Review','Rejected by Manager','Approved by Manager','Under CEO Review','Rejected by CEO','Final Proposal Ready','Proposal Sent to Client','Awaiting Client Action','Signed – Move to Finance') COLLATE utf8mb4_general_ci DEFAULT 'New Lead',
  `assigned_sales_id` int DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `company_name`, `trade_license_no`, `country`, `emirate_zone`, `business_activity`, `address`, `contact_name`, `contact_designation`, `contact_mobile`, `contact_email`, `client_password`, `service_id`, `service_description`, `expected_start_date`, `payment_currency`, `payment_term`, `service_total_fee`, `lead_source`, `client_status`, `assigned_sales_id`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'First Client', '12345678000', 'United Arab Emirates', 'Dubai', 'Gym Service', 'JLT, Dubai.', 'Mr. Heg', 'Accountant', '123456789', 'heg@email.comm', NULL, 2, 'He need some services. And more.', '2025-12-03', 'AED', 'Quarterly', 4000.00, 'digital_marketing', 'Proposal Drafted', 3, 3, '2025-11-30 17:21:50', '2025-12-02 10:38:45'),
(2, 'ABCD LLC', '12345678', 'France', '', 'Gym Service', 'Hello', 'Otaksi Clients', 'Accountant', '09090909', 'otaksiclients@gmail.com', NULL, 2, 'Helloooooooooo', '2026-02-28', 'CNY', 'Quarterly', 233.00, 'referral', 'New Lead', NULL, NULL, '2026-02-27 13:10:01', '2026-02-27 13:10:01'),
(3, 'Theta-X', '12345678', 'Bahrain', 'Riffa', 'Gym Service', 'ertyui765', 'Otaksi Clients', 'Accountant', '09090909', 'otaksiclients@gmail.com', NULL, 1, 'trewtyujhg', '2026-02-27', 'AED', 'Monthly', 344.00, 'referral', 'Proposal Drafted', NULL, NULL, '2026-02-27 13:12:34', '2026-02-27 17:54:16'),
(4, 'Theta-X', '99999999', 'United Arab Emirates', 'Abu Dhabi', 'Gym Service', 'tueffg', 'Joe', 'Manager', '09090909', 'otaksiclients@gmail.com', NULL, 1, 'good', '2026-02-27', 'AED', 'Monthly', 455.00, 'digital_marketing', 'Approved by Manager', NULL, NULL, '2026-02-27 13:17:57', '2026-02-27 15:44:44'),
(5, 'Zawaj Mubarak', '12345678', 'India', 'Maharashtra', 'Gym Service', 'Hwllooooo', 'Vurat', 'Former', '09090909', 'otaksiclients@gmail.com', NULL, 2, 'Hellooo', '2026-02-28', 'AED', 'Monthly', 3444.00, 'referral', 'New Lead', NULL, NULL, '2026-02-27 19:23:56', '2026-02-27 19:23:56'),
(6, 'First Client Edited', '12345678', 'Russia', 'Saint Petersburg', 'Gym', 'Hjjejje', 'Otaksi Clients', 'Manager', '09090909', 'otaksiclients@gmail.com', '$2y$10$YU8Beh7R4sNH4m9RyuH0x.b8iXw9.R1HY0gTxrTGQtLNKUU.BLEve', 1, 'Tyyrruur', '2026-02-27', 'AED', 'Monthly', 3333.00, 'event', 'New Lead', NULL, NULL, '2026-02-27 19:37:55', '2026-02-27 19:37:55'),
(7, 'Madaki', '12345678', 'Russia', 'Moscow', 'Gym Service', 'Hello', 'Abdull', 'Accountant', '09090909', 'otaksiclients@gmail.com', '$2y$10$YLHPUA/SFJVuhutBobV13.BRpLWwtswsKlhnEWOplxIg.T.8dfXZ.', 1, 'Hekko', '2026-02-28', 'AED', 'Monthly', 555.00, 'digital_marketing', 'New Lead', NULL, NULL, '2026-02-27 19:47:11', '2026-02-27 19:47:11'),
(8, 'Madaki01', '12345678', 'Russia', 'Moscow', 'Gym Service شىي ةخقث', 'Hello', 'Abdull', 'Accountant', '09090909', 'otaksiclients@gmail.com', '$2y$10$4Tmar8F9fdIiLgzCw/CaDuSjjswIx60Udtd1OWM3YfLz18zHSc.SC', 1, 'Hekko', '2026-02-28', 'AED', 'Monthly', 555.00, 'digital_marketing', 'New Lead', NULL, NULL, '2026-02-27 19:47:21', '2026-02-28 00:10:04');

-- --------------------------------------------------------

--
-- Table structure for table `client_documents`
--

CREATE TABLE `client_documents` (
  `doc_id` int NOT NULL,
  `client_id` int NOT NULL,
  `document_title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `document_type` enum('trade_license','bank_statement','signed_proposal','signed_proforma','other') COLLATE utf8mb4_general_ci DEFAULT 'other',
  `file_path` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `uploaded_by` int DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_documents`
--

INSERT INTO `client_documents` (`doc_id`, `client_id`, `document_title`, `document_type`, `file_path`, `uploaded_by`, `uploaded_at`) VALUES
(12, 1, 'DoB', 'trade_license', 'uploads/client_documents/doc_1_1764542402_7866_test.docx', 2, '2025-11-30 22:40:02'),
(18, 1, 'New Dox', 'bank_statement', 'uploads/client_documents/doc_1_1764542837_9018_OGM-Letter-Head.pdf', 2, '2025-11-30 22:47:17'),
(19, 1, 'Helloo', 'signed_proforma', 'uploads/client_documents/doc_1_1764671630_2004_OGM-Letter-Head.pdf', 2, '2025-12-02 10:33:50'),
(20, 1, 'Helloo', 'signed_proforma', 'uploads/client_documents/doc_1_1764671638_4719_OGM-Letter-Head.pdf', 2, '2025-12-02 10:33:58'),
(21, 5, 'DoB', 'bank_statement', 'uploads/client_documents/doc_5_1772220275_1872_Screenshot_2025-10-25_004112.png', 9, '2026-02-27 19:24:35'),
(22, 5, 'DoB', 'bank_statement', 'uploads/client_documents/doc_5_1772220282_8938_Screenshot_2025-10-25_004112.png', 9, '2026-02-27 19:24:42'),
(23, 4, 'New Dox', 'signed_proposal', 'uploads/client_documents/doc_4_1772220964_2006_Screenshot_2025-11-02_221650.png', 9, '2026-02-27 19:36:04');

-- --------------------------------------------------------

--
-- Table structure for table `client_feedback`
--

CREATE TABLE `client_feedback` (
  `feedback_id` int NOT NULL,
  `client_id` int NOT NULL,
  `engagement_id` int DEFAULT NULL,
  `feedback_date` date NOT NULL,
  `feedback_text` text,
  `is_positive` tinyint(1) DEFAULT '1',
  `points_awarded` int DEFAULT '50',
  `evidence_file` varchar(255) DEFAULT NULL,
  `is_validated` tinyint(1) DEFAULT '0',
  `validated_by` int DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_notes`
--

CREATE TABLE `client_notes` (
  `note_id` int NOT NULL,
  `client_id` int NOT NULL,
  `user_id` int NOT NULL,
  `note_type` enum('internal','rejection_reason','follow_up','status_change') COLLATE utf8mb4_general_ci DEFAULT 'internal',
  `note_content` text COLLATE utf8mb4_general_ci NOT NULL,
  `visibility_roles` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'all',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deadline_change_requests`
--

CREATE TABLE `deadline_change_requests` (
  `request_id` int NOT NULL,
  `engagement_id` int NOT NULL,
  `requested_by` int NOT NULL,
  `requested_date` date NOT NULL,
  `reason_code` enum('workload','client_delay','technical','other') NOT NULL,
  `reason_notes` text,
  `status` enum('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `deadline_change_requests`
--

INSERT INTO `deadline_change_requests` (`request_id`, `engagement_id`, `requested_by`, `requested_date`, `reason_code`, `reason_notes`, `status`, `reviewed_by`, `reviewed_at`, `review_notes`, `created_at`) VALUES
(1, 1, 9, '2026-04-02', 'client_delay', 'Please change', 'PENDING', NULL, NULL, NULL, '2026-02-28 04:22:29');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int NOT NULL,
  `dept_name` varchar(100) NOT NULL,
  `dept_code` varchar(10) NOT NULL,
  `manager` varchar(100) DEFAULT NULL,
  `budget` decimal(15,2) NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `dept_name`, `dept_code`, `manager`, `budget`, `location`, `description`, `created_at`, `updated_at`) VALUES
(3, 'Operations', 'OPS', '2', 30000.00, 'OGMBC', 'Our operations department', '2026-02-27 23:17:33', '2026-02-27 23:17:33'),
(4, 'Sales', 'SLS', '1', 6000.00, 'OGMBC', 'Our sales department', '2026-02-28 00:23:03', '2026-02-28 00:23:03');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `log_id` int NOT NULL,
  `client_id` int NOT NULL,
  `proposal_id` int DEFAULT NULL,
  `email_type` enum('proposal_sent','reminder','follow_up') COLLATE utf8mb4_general_ci NOT NULL,
  `recipient_email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `subject` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sent_by` int DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int NOT NULL,
  `user_id` int NOT NULL,
  `user_email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `user_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'employee',
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `user_image` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `field_of_study` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `qualification` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `highest_graduation` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `year_of_graduation` year DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT '0.00',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `user_id`, `user_email`, `user_type`, `password`, `first_name`, `last_name`, `user_image`, `field_of_study`, `department_id`, `qualification`, `highest_graduation`, `year_of_graduation`, `salary`, `created_at`) VALUES
(1, 2, 'abc@email.com', 'employee', '123456', 'New1', 'Joiner', 'profile_2_1757270810.jpg', 'IT', NULL, 'Engineer', 'MSc', '2021', 0.00, '2025-09-07 15:27:08'),
(2, 2, 'Woon@email.com', 'employee', '123456', 'Xeee', 'User', 'profile_1757347189_8877.png', 'Medical', 3, 'MSc', 'MSc', '2000', 1000.00, '2025-09-08 16:59:49'),
(5, 4, 'otaksiclients@gmail.com', 'employee', '123456', 'Otaksi', 'Clients', '', 'Medical', 3, 'Engineer', 'MSc', '2023', 5000.00, '2026-02-27 23:50:50');

-- --------------------------------------------------------

--
-- Table structure for table `engagements`
--

CREATE TABLE `engagements` (
  `engagement_id` int NOT NULL,
  `client_id` int NOT NULL,
  `service_id` int NOT NULL,
  `rule_version_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `assigned_to` int NOT NULL,
  `assigned_by` int NOT NULL,
  `reviewer_id` int DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `start_date` date NOT NULL,
  `original_deadline` date NOT NULL,
  `approved_deadline` date DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `status` enum('ASSIGNED','IN_PROGRESS','AWAITING_REVIEW','SUBMITTED','CLOSED','REJECTED') DEFAULT 'ASSIGNED',
  `evidence_required` tinyint(1) DEFAULT '1',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `submitted_by` int DEFAULT NULL,
  `delay_days` int DEFAULT '0',
  `points_awarded` int DEFAULT NULL,
  `calculation_explanation` json DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `engagements`
--

INSERT INTO `engagements` (`engagement_id`, `client_id`, `service_id`, `rule_version_id`, `title`, `description`, `assigned_to`, `assigned_by`, `reviewer_id`, `assigned_at`, `start_date`, `original_deadline`, `approved_deadline`, `completion_date`, `status`, `evidence_required`, `submitted_at`, `submitted_by`, `delay_days`, `points_awarded`, `calculation_explanation`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, 'Financial Audit', 'He need finance audit', 4, 9, 2, '2026-02-28 04:19:56', '2026-02-28', '2026-03-30', NULL, NULL, 'ASSIGNED', 1, NULL, NULL, 0, NULL, NULL, 9, '2026-02-28 04:19:56', '2026-02-28 04:19:56');

-- --------------------------------------------------------

--
-- Table structure for table `engagement_status_history`
--

CREATE TABLE `engagement_status_history` (
  `history_id` int NOT NULL,
  `engagement_id` int NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_by` int NOT NULL,
  `changed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `engagement_status_history`
--

INSERT INTO `engagement_status_history` (`history_id`, `engagement_id`, `old_status`, `new_status`, `changed_by`, `changed_at`, `notes`) VALUES
(1, 1, NULL, 'ASSIGNED', 9, '2026-02-28 04:19:56', 'Engagement created');

-- --------------------------------------------------------

--
-- Table structure for table `enquiries`
--

CREATE TABLE `enquiries` (
  `enquiry_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `contact` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `service` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `sub_service` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) DEFAULT '0',
  `status` enum('new','contacted','in_progress','completed') COLLATE utf8mb4_general_ci DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enquiries`
--

INSERT INTO `enquiries` (`enquiry_id`, `name`, `email`, `contact`, `service`, `sub_service`, `message`, `submitted_at`, `is_read`, `status`) VALUES
(2, 'Abdull', 'abdaullahbalam@gmail.com', '+123456789', 'Accounting &amp; Taxation', 'Sub Service', 'Some message here.', '2025-12-28 12:19:57', 0, 'new'),
(3, 'Abdull', 'abdaullahbalam@gmail.com', '+123456789', 'Accounting &amp; Taxation', 'Sub Service', 'Hello some description.', '2025-12-28 12:20:23', 0, 'new'),
(4, 'Abdull1', 'abdaullahbalam@gmail.com', '+123456789', 'Business Setup', 'Sub Service', 'Hello some description.', '2025-12-28 13:05:27', 0, 'new'),
(5, 'Abdull2', 'abdaullahbalam@gmail.com', '+123456789', 'Accounting &amp; Taxation', 'Sub Service1233', 'Here is some description.', '2025-12-28 13:27:04', 0, 'new'),
(6, 'Abdull2', 'abdaullahbalam@gmail.com', '+123456789', 'Accounting &amp; Taxation', 'Sub Service1233', 'Here is some description.', '2025-12-28 13:28:20', 0, 'new'),
(7, 'Abdull3', 'abdaullahbalam@gmail.com', '+123456789', 'Business Setup', 'Sub Service', 'Hello some description.1234', '2025-12-28 13:40:02', 0, 'new'),
(8, 'Abdull4', 'madakisoft@gmail.com', '+123456789', 'Accounting &amp; Taxation', 'New Service', 'Hello how are you?', '2025-12-28 14:16:15', 0, 'new'),
(9, 'Abdull', 'abdaullahbalam@gmail.com', '+123456789', 'Accounting &amp; Taxation', 'Live Test', 'Live Website Email Sending Test', '2025-12-28 14:34:09', 0, 'new'),
(10, 'Abdull112', 'abdaullahbalam@gmail.com', '+123456789', 'Business Setup', 'Another Random Test', 'Live Website Email Sending Test From Random Page.', '2025-12-28 14:35:32', 0, 'new'),
(11, 'Abdull55', 'abdaullahbalam@gmail.com', '+123456789', 'Accounting &amp; Taxation', 'Another Random Test22', 'Just testing this stuff.', '2025-12-28 14:43:26', 0, 'new'),
(12, 'New Comer', 'madakisoft@gmail.com', '80808080', 'Accounting &amp; Taxation', 'Taxation services', 'Hello this is new live test.', '2026-01-02 18:19:00', 0, 'new'),
(13, 'Odai', 'tom@ohmbc.ae', '0509860136', 'Accounting &amp; Taxation', 'Hdhd', 'Hi', '2026-01-09 05:15:10', 0, 'new');

-- --------------------------------------------------------

--
-- Table structure for table `evidence`
--

CREATE TABLE `evidence` (
  `evidence_id` int NOT NULL,
  `engagement_id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `uploaded_by` int NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_validated` tinyint(1) DEFAULT '0',
  `validated_by` int DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  `validation_notes` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `industry` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consent_given` tinyint(1) DEFAULT '0',
  `ratios_calculated` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of calculated ratios',
  `report_generated` datetime DEFAULT NULL COMMENT 'When the report was generated',
  `first_interaction` datetime NOT NULL,
  `last_interaction` datetime NOT NULL,
  `status` enum('new','contacted','qualified','converted','unresponsive') COLLATE utf8mb4_unicode_ci DEFAULT 'new',
  `source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'financial_ratio_calculator',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `full_name`, `email`, `phone`, `company_name`, `industry`, `consent_given`, `ratios_calculated`, `report_generated`, `first_interaction`, `last_interaction`, `status`, `source`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Doe', 'test@example.com', '', 'First Client', 'other', 1, '[\"operating_margin\"]', NULL, '2026-01-10 16:59:32', '2026-01-10 18:17:42', '', 'financial_ratio_calculator', NULL, '2026-01-10 15:59:32', '2026-01-10 17:17:42'),
(2, 'Test Userr', 'test@eexample.com', '', 'Otaksi Connect', 'other', 1, '[\"current_ratio\"]', NULL, '2026-01-10 17:25:42', '2026-01-10 17:25:42', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 16:25:42', '2026-01-10 16:25:42'),
(3, 'Test User2', 'test@ex3ample.com', '', 'Theta-X', 'other', 1, '[\"current_ratio\"]', NULL, '2026-01-10 17:31:16', '2026-01-10 17:31:16', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 16:31:16', '2026-01-10 16:31:16'),
(4, 'Teste User', 'test@examplre.com', '', 'First Client', 'other', 1, '[\"cash_ratio\"]', NULL, '2026-01-10 17:33:04', '2026-01-10 17:33:04', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 16:33:04', '2026-01-10 16:33:04'),
(5, 'Hi', 'test@exreample.com', '', 'First Client', 'transportation', 1, '[\"interest_coverage\"]', NULL, '2026-01-10 17:33:59', '2026-01-10 17:33:59', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 16:33:59', '2026-01-10 16:33:59'),
(6, 'New', 'test@e4xample.com', '', 'First Client', 'transportation', 1, '[\"asset_turnover\",\"dpo\"]', NULL, '2026-01-10 17:36:02', '2026-01-10 17:36:02', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 16:36:02', '2026-01-10 16:36:02'),
(7, 'Josh', 'test@exrample.com', '', 'First Client', 'other', 1, '[\"current_ratio\"]', NULL, '2026-01-10 17:39:03', '2026-01-10 17:39:03', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 16:39:03', '2026-01-10 16:39:03'),
(8, 'Last Test', 'abdaullahbalam@gmail.com', '+971345264456', 'Otaksi Connect', 'transportation', 1, '[\"quick_ratio\"]', NULL, '2026-01-10 17:44:00', '2026-01-10 19:02:21', '', 'financial_ratio_calculator', NULL, '2026-01-10 16:44:00', '2026-01-10 18:02:21'),
(9, 'San', 'test@example.come', '', 'First Client', 'other', 1, '[\"quick_ratio\"]', NULL, '2026-01-10 18:02:43', '2026-01-10 18:02:43', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 17:02:43', '2026-01-10 17:02:43'),
(10, 'Doe', 'test@exatmple.com', '', 'First Client', 'other', 1, '[\"quick_ratio\"]', NULL, '2026-01-10 18:21:45', '2026-01-10 18:21:45', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 17:21:45', '2026-01-10 17:21:45'),
(11, 'John', 'test@exampwle.com', '', 'First Client', 'agriculture', 1, '[\"interest_coverage\"]', NULL, '2026-01-10 18:26:31', '2026-01-10 18:26:31', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 17:26:31', '2026-01-10 17:26:31'),
(12, 'Putin', 'test@exeample.com', '+971501234567', 'Test Company', 'technology', 1, '[\"quick_ratio\",\"operating_cash_flow_ratio\"]', NULL, '2026-01-10 18:45:38', '2026-01-10 18:45:38', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 17:45:38', '2026-01-10 17:45:38'),
(13, 'Last Test', 'abm@gmail.com', '+971345264456', 'First Client', 'hospitality', 1, '[\"quick_ratio\"]', NULL, '2026-01-10 18:48:21', '2026-01-10 19:03:42', '', 'financial_ratio_calculator', NULL, '2026-01-10 17:48:21', '2026-01-10 18:03:42'),
(14, 'Test User2', 'jose95@example.com', '+971501234567', 'Otaksi Connect', 'agriculture', 1, '[\"net_margin\"]', NULL, '2026-01-11 00:25:04', '2026-01-11 00:25:04', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 23:25:04', '2026-01-10 23:25:04');

-- --------------------------------------------------------

--
-- Table structure for table `monthly_point_summary`
--

CREATE TABLE `monthly_point_summary` (
  `summary_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `year` int NOT NULL,
  `month` int NOT NULL,
  `total_points` int DEFAULT '0',
  `cashable_points` int DEFAULT '0',
  `is_closed` tinyint(1) DEFAULT '0',
  `closed_by` int DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `override_notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `points_ledger`
--

CREATE TABLE `points_ledger` (
  `ledger_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `source_type` enum('ENGAGEMENT','SALES_TARGET','CLIENT_FEEDBACK','MANUAL_ADJUSTMENT','CDP') NOT NULL,
  `source_id` int DEFAULT NULL,
  `points` int NOT NULL,
  `points_type` enum('EARNED','DEDUCTED','ADJUSTMENT') DEFAULT 'EARNED',
  `description` varchar(255) DEFAULT NULL,
  `calculation_data` json DEFAULT NULL,
  `requires_approval` tinyint(1) DEFAULT '0',
  `approved_by` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int NOT NULL,
  `post_title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `post_slug` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `post_content` text COLLATE utf8mb4_general_ci NOT NULL,
  `post_excerpt` text COLLATE utf8mb4_general_ci,
  `post_author` int NOT NULL,
  `post_status` enum('published','draft') COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `post_image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `meta_keywords` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `post_title`, `post_slug`, `post_content`, `post_excerpt`, `post_author`, `post_status`, `post_image`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`) VALUES
(14, 'What Are the Advantages of Company Formation in Dubai’s Mainland vs. Free Zones?', 'what-are-the-advantages-of-company-formation-in-dubai-s-mainland-vs-free-zones', 'A global hub for business, Dubai offers immense opportunities for investors and entrepreneurs. If you are considering Company Formation in Dubai, one crucial decision is whether to set up on the mainland or in one of the numerous accessible areas. Each choice has distinct advantages and benefits, catering to various requirements and objectives for business. This blog will discuss the advantages of starting a company on the mainland of Dubai vs free zones. It will provide helpful information for anyone considering establishing a business in the UAE.\r\n\r\nThe advantages of forming a company in Dubai\'s Mainland\r\n1. The ability to access the Local Market\r\nOne of the significant advantages of starting an enterprise in the Dubai mainland is the unrestricted access to the market in Dubai. The business can be operated freely across Dubai and throughout the UAE, allowing for more significant market penetration opportunities and the ability to interact directly with customers and local clients. This primarily benefits retailers, hospitality, and companies based on a regional client base.\r\n\r\n2. Flexible Business Activity\r\nCompanies from mainland countries located in Dubai benefit from many actions. In contrast to free zones, which typically limit the kinds of commercial activities that may be conducted, companies on the mainland can participate in a wide variety of professional, commercial, and industrial tasks without any restrictions. This versatility is great for businesses looking to diversify or expand into new markets.\r\n\r\n3. The ability to bid on government Contracts\r\nBusinesses from mainland countries can take part in contracts and tenders. This is a huge benefit for companies looking to get lucrative government contracts that are usually large and long-term. Contracts with the government can create steady revenue streams and boost a company\'s image and trustworthiness.\r\n\r\n4. There are no restrictions on office location\r\nCompanies located in mainland countries aren\'t restricted to specific geographic regions or specific structures. The office can be set up at any site anywhere in Dubai and enjoy optimal operational configuration and control freedom. This flexibility can assist businesses in locating the most efficient or economical locations.\r\n\r\nBenefits of forming a company in Dubai\'s free Zones\r\n1. Full Foreign Ownership\r\nOne of the most significant advantages of starting a business in the free zones of Dubai is the foreign ownership limit. In contrast to mainland businesses that need a local sponsor or partners who hold a majority stake, foreign investors can completely own free zone enterprises. The ideal situation is for business owners who wish to control their operations completely.\r\n\r\n2. Tax Benefits, Incentives and Tax Credits\r\nFree zones within Dubai have a range of tax benefits, including complete exclusion from personal and corporate tax on income and taxes on exports and imports. They can significantly ease the financial burden for companies and allow them to invest more significant amounts of their profits in development and growth. Furthermore, several areas that are free offer the promise of tax-free income for a period of as long as 50 years.\r\n\r\n3. A Simple Setup Procedure\r\nBusiness Setup in UAE free zones is usually easy and quick. The authorities of free zones provide complete support and assistance, which includes help regarding visas, licenses, and various other requirements for regulatory compliance. This makes the set-up procedure more accessible and more straightforward. This is beneficial to entrepreneurs who want to begin their business in a short time.\r\n\r\n4. Specialized Zones to Support Various Industries\r\nThe Dubai free zones are usually specifically designed for media, technology, health logistics, and healthcare sectors. Establishing a business in a specific free zone could provide companies access to specialized infrastructure, facilities, and networking opportunities. The environment could encourage expansion, innovation and cooperation between firms in the same industry.\r\nConclusion\r\nThe mainland and the accessible zones provide distinct benefits when it comes to company formation in Dubai, depending on your business\'s needs and needs. A company formation on the mainland is an excellent option for enterprises with unlimited access to local markets, operating flexibility, and opportunities to obtain government contracts. In contrast, these zones can provide advantages, including full foreign ownership tax-free zones, a more straightforward set-up process, and a specific industry environment.\r\n\r\nKnowing these benefits can assist entrepreneurs in making educated decisions regarding their company\'s set-up in the UAE. We at O.G.M. Consultants specialise in offering individualized advice and assistance to companies forming within Dubai, regardless of whether it is situated on the mainland or in free zones. Contact us today for more about our services and how we could help you create an effective business in Dubai.', 'Advantages and disadvantages of Mainland company formation in Dubai Vs. Free Zone', 4, 'published', 'post_1767279663_3154.png', 'What Are the Advantages of Company Formation in Dubai’s Mainland vs. Free Zones?', 'Dubai Company setup Mainland Vs. Free Zone', 'Dubai company formation mainland Vs. Free Zone, business setup in Dubai', '2026-01-01 15:01:03', '2026-01-01 15:02:41'),
(15, 'How Can Bookkeeping Services Support Your Business Growth?', 'how-can-bookkeeping-services-support-your-business-growth', 'In the frantic business world in Dubai, keeping current and accurate financial records is crucial to the sustainability and growth of your business. Bookkeeping services in Dubai provide businesses with the knowledge and the equipment needed to manage their business\'s finances effectively. This blog examines how professional bookkeeping services will help and expand your business.\r\nEnsuring Accurate Financial Records\r\nOne of the primary advantages of using bookkeeping services in Dubai is the security of accurate and current financial reports. Professional bookkeepers keep precise accounts of all monetary transactions and adequately record each expenditure, revenue, and investment. Accuracy is vital to making informed business decisions, preparing tax deadlines, and avoiding any financial discrepancies that could result in legal issues.\r\nFacilitating Financial Processes\r\nBookkeeping outsourcing can dramatically simplify your financial procedures. Bookkeepers with expertise use the latest software and techniques to handle your accounts payable and accounts receivable pay, payroll, and various finance-related jobs. It saves you time and lowers the chances of making mistakes, allowing you to focus on the core activities of your business. The streamlined financial management improves productivity, resulting in more efficient resource allocation, ultimately helping to boost business growth.\r\nLatest financial Insights and Analysis\r\nBookkeeping services offered in Dubai can provide invaluable financial insight and analyses. Professional bookkeepers create thorough financial statements that give an in-depth view of the economic condition of your company. They include profits and loss statements, balance sheets, and cash flow statements. The business owners can identify patterns, assess performance, and make informed decisions by studying the documents. Accessing accurate financial information allows you to make better decisions, plan your future, and take advantage of growth opportunities.\r\nImproving Control of Cash Flow\r\nA well-organized cash flow system is vital to every company\'s success and long-term viability. Bookkeeping solutions help you track cash flow by meticulously monitoring the flow of money. It helps you to have a clear picture of your cash balance on any date. Effective cash flow management can avoid liquidity crises, plan for future expenses, and ensure you have enough funds to fund growth projects. An efficient cash flow is the foundation of an expanding business. Professional bookkeeping will ensure that it\'s maintained.\r\nHelping Compliance with the law Tax Preparation\r\nUnderstanding the complicated tax regulations to comply with tax regulations in Dubai is a challenge for companies. Bookkeeping solutions can help your company ensure compliance with tax laws and rules in the local area. Professional bookkeepers track the deductions for all expenses, create exact financial records and ensure you submit your tax return. The risk is reduced by audits and penalties, which provide assurance and allow you to concentrate on expanding your company. Simple tax compliance with professional bookkeeping will also boost tax benefits, contributing to more financial security.\r\nFacilitating the process of Business Planning and Budgeting\r\nBookkeeping is a crucial function in the business planning process and budgeting. In keeping up-to-date accounting records and producing precise financial information, they help companies create accurate budgets and economic strategies. They are crucial in setting growth goals, managing costs, and efficiently allocating resources. By having a financial plan, firms can make intelligent choices, limit spending, and ensure they\'re going in the right direction to achieve their growth goals.\r\nConclusion\r\nIn the dynamic and competitive business climate, expert bookkeeping services can be invaluable in helping businesses grow. They provide precise financial records, streamline procedures for financial management, give crucial insight into financials, improve cash flow control, facilitate tax compliance, aid in business planning and allow time to focus on essential tasks. Expert bookkeeping solutions in Dubai firms can create an enduring financial foundation to make well-informed decisions and concentrate on strategies to drive success and growth.\r\nAt O.G.M. Consultants, we specialize in providing comprehensive bookkeeping service in Dubai tailored to meet the unique needs of your business. Our experienced team is dedicated to helping you manage your finances efficiently and achieve your growth objectives. Contact us today to learn more about how our bookkeeping services can support your business growth', 'Bookkeeping in Dubai, Accounting report', 4, 'published', 'post_1767280128_5014.png', 'How Can Bookkeeping Services Support Your Business Growth?', 'Bookkeeping', 'Bookkeeping services', '2026-01-01 15:08:48', '2026-01-01 15:08:48'),
(16, 'Why Should You Consider Tax Consulting Services in UAE for Your Business?', 'why-should-you-consider-tax-consulting-services-in-uae-for-your-business', 'The existing competition in the market makes it necessary for businesses to have a clear-cut position in tax planning and compliance. Dealing with taxes in Dubai may be complicated, given the recent introduction of VAT alongside other tax laws that continue to evolve. This is why most businesses outsource tax consultants in Dubai, UAE, wherever they need help. In this blog, I will provide reasons why I think that using tax consulting services in the UAE is a reasonable and beneficial decision for your business.\r\n\r\nSpecialization in UAE Taxation\r\nOne of the reasons many clients engage local tax consultants in Dubai, UAE, is their local expertise. Running a business in the UAE is likely to be a taxing affair as it is a jurisdiction with a great deal of tax regulation. Therefore, new business owners find it difficult to keep up with the new business regulations. Corporate taxes, VAT tariffs, and excise atomization are some of the codes a tax consultant understands, thus verifying that your business is legally confirmed. This expertise assists you in evading mistakes that cost the firm substantial sums in penalties and audits, hence making room for operational smoothness.\r\nEffective Tax Management Services\r\nNo two corporations are alike, and for this reason, taxation differs from one organization to another. An approach based on indiscriminate taxation normally brings about leanness and wastage. Tax consultancy in the UAE can be a platform through which it is possible to obtain tax planning tailored to your needs and the requirements of your business. These services make it easier for tax liabilities to be optimized whilst only paying what is required and by fully exploiting the available deductions, exemptions, and incentives within the system. This customized tax planning can significantly improve the company\'s financial performance and long-term sustainability.\r\nVAT Compliance Support\r\nIt has been impossible for businesses to keep up with changes regarding tax compliance after the introduction of Value Added Tax to the UAE. Not only is it time-consuming, but VAT returns, keeping track of tax invoices, and VAT payments can also get pretty complicated. Tax consultants in Dubai, UAE, specialize in VAT compliance, ensuring your business keeps records in the right order, files on time, and meets all VAT obligations. With their help, you can avoid penalties for late or wrong VAT submissions.\r\nYou can save your time and resources by using them\r\nManaging tax requirements in-house is a very easy way to waste precious time and resources, mainly for small and medium enterprises. Tax consulting services in UAE can let you focus on the core business of your activities while letting experts take care of the complexity and compliance issues concerning taxes. In this fashion, your tax process is efficient and allows your team to work on activities that would directly add to the growth and profitability of your company.\r\nRisk Management and Auditing Support\r\nProfessional tax consultant services in Dubai, UAE, can be of good value if one is faced with a tax audit or dispute with the tax department. They may be able to guide you through the process of an audit by making sure your documentation is properly and fully prepared. This also helps in terms of risk as it can highlight potential tax problems before the situation becomes essential and problematic. In this case, such practice minimizes unexpected liabilities while maintaining all business operations in line all the time.\r\nConclusion\r\nEngaging in tax consulting services in the UAE is an investment that can bring so much into your business. From expert opinions regarding local tax law to tailored tax strategies to help with VAT compliance as well as risks on audit, tax consultants offer a wholesome package that can save you time and reduce liabilities while improving the financial health of your enterprise. Hence, with the help of professional tax consultants, your business shall be well prepared to familiarize itself with the intricacies of the UAE tax system and find more ways to grow.', '', 4, 'published', 'post_1767280582_5728.png', 'Why Should You Consider Tax Consulting Services in UAE for Your Business?', 'Does your business have approved tax agency?', 'VAT and CT services in Dubai', '2026-01-01 15:16:22', '2026-01-01 15:16:22');

-- --------------------------------------------------------

--
-- Table structure for table `proforma_invoices`
--

CREATE TABLE `proforma_invoices` (
  `invoice_id` int NOT NULL,
  `client_id` int NOT NULL,
  `invoice_ref` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `proposal_id` int DEFAULT NULL,
  `version` int DEFAULT '1',
  `invoice_content` longtext COLLATE utf8mb4_general_ci,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_breakdown` text COLLATE utf8mb4_general_ci,
  `validity_period` date NOT NULL,
  `prepared_by` int DEFAULT NULL,
  `prepared_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `file_path` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proforma_invoices`
--

INSERT INTO `proforma_invoices` (`invoice_id`, `client_id`, `invoice_ref`, `proposal_id`, `version`, `invoice_content`, `total_amount`, `payment_breakdown`, `validity_period`, `prepared_by`, `prepared_at`, `file_path`) VALUES
(1, 1, 'PROF-20251201-0001-V1', 3, 1, '<!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proforma Invoice: PROF-20251201-0001-V1</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\r\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\r\n            .section { margin-bottom: 25px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\r\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\r\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFORMA INVOICE</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Invoice Reference: PROF-20251201-0001-V1</h3>\r\n            <p><strong>Date Issued:</strong> December 1, 2025 | <strong>Valid Until:</strong> December 31, 2025</p>\r\n        </div>\r\n        \r\n        <div class=\'company-info\'>\r\n            <h4>From:</h4>\r\n            <p><strong>OGM Business Consultancy</strong></p>\r\n            <p>Business Bay, Dubai</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: info@ogmbusiness.com</p>\r\n            <p>Phone: +971 4 123 4567</p>\r\n            <p>VAT: TRN 123456789012345</p>\r\n        </div>\r\n        \r\n        <div class=\'client-info\'>\r\n            <h4>Bill To:</h4>\r\n            <p><strong>First Client</strong></p>\r\n            <p>Attn: Mr. Heg</p>\r\n            <p>Accountant</p>\r\n            <p>JLT, Dubai.</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: heg@email.comm</p>\r\n            <p>Phone: 123456789</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Service Description</h4>\r\n            <p><strong>Accounting &amp; Taxation</strong> - He need some services. And more.</p>\r\n        </div>\r\n        \r\n        <table>\r\n            <thead>\r\n                <tr>\r\n                    <th>Description</th>\r\n                    <th>Quantity</th>\r\n                    <th>Unit Price (AED)</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr>\r\n            </thead>\r\n            <tbody>\r\n                <tr>\r\n                    <td>Accounting &amp; Taxation Service</td>\r\n                    <td>1</td>\r\n                    <td>4,000.00</td>\r\n                    <td>4,000.00</td>\r\n                </tr>\r\n                <tr class=\'total-row\'>\r\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\r\n                    <td><strong>4,000.00</strong></td>\r\n                </tr>\r\n            </tbody>\r\n        </table>\r\n        \r\n        <div class=\'payment-terms\'>\r\n            <h4>Payment Terms: Bi-yearly</h4>\r\n            <table>\r\n                <tr>\r\n                    <th>Installment</th>\r\n                    <th>Due</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr><tr>\r\n                    <td>1</td>\r\n                    <td>Half 1</td>\r\n                    <td>2,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Half 2</td>\r\n                    <td>2,000.00</td>\r\n                  </tr></table>\r\n        </div>\r\n        \r\n        <div class=\'bank-details\'>\r\n            <h4>Bank Transfer Details</h4>\r\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\r\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\r\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\r\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\r\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\r\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Notes</h4>\r\n            <ol>\r\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\r\n                <li>Prices are valid for 30 days from the date of issue</li>\r\n                <li>Payment should be made in full before service commencement</li>\r\n                <li>All bank charges are to be borne by the client</li>\r\n                <li>Services will commence upon receipt of payment</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div style=\'float: left; width: 45%;\'>\r\n                <p><strong>Prepared by:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p> </p>\r\n                <p>Sales Consultant</p>\r\n                <p>OGM Business Consultancy</p>\r\n            </div>\r\n            \r\n            <div style=\'float: right; width: 45%; text-align: center;\'>\r\n                <p><strong>For OGM Business Consultancy:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p>Authorized Signature</p>\r\n                <p>Date: December 1, 2025</p>\r\n            </div>\r\n            <div style=\'clear: both;\'></div>\r\n        </div>\r\n    </body>\r\n    </html>', 4000.00, '[{\"installment\":1,\"amount\":\"2,000.00\",\"due_description\":\"Half 1\"},{\"installment\":2,\"amount\":\"2,000.00\",\"due_description\":\"Half 2\"}]', '0000-00-00', 2, '2025-12-01 15:03:21', 'uploads/proformas/proforma_PROF-20251201-0001-V1.html'),
(2, 1, 'PROF-20251201-0001-V2', 3, 2, '<!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proforma Invoice: PROF-20251201-0001-V2</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\r\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\r\n            .section { margin-bottom: 25px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\r\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\r\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFORMA INVOICE</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Invoice Reference: PROF-20251201-0001-V2</h3>\r\n            <p><strong>Date Issued:</strong> December 1, 2025 | <strong>Valid Until:</strong> December 31, 2025</p>\r\n        </div>\r\n        \r\n        <div class=\'company-info\'>\r\n            <h4>From:</h4>\r\n            <p><strong>OGM Business Consultancy</strong></p>\r\n            <p>Business Bay, Dubai</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: info@ogmbusiness.com</p>\r\n            <p>Phone: +971 4 123 4567</p>\r\n            <p>VAT: TRN 123456789012345</p>\r\n        </div>\r\n        \r\n        <div class=\'client-info\'>\r\n            <h4>Bill To:</h4>\r\n            <p><strong>First Client</strong></p>\r\n            <p>Attn: Mr. Heg</p>\r\n            <p>Accountant</p>\r\n            <p>JLT, Dubai.</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: heg@email.comm</p>\r\n            <p>Phone: 123456789</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Service Description</h4>\r\n            <p><strong>Accounting &amp; Taxation</strong> - He need some services. And more.</p>\r\n        </div>\r\n        \r\n        <table>\r\n            <thead>\r\n                <tr>\r\n                    <th>Description</th>\r\n                    <th>Quantity</th>\r\n                    <th>Unit Price (AED)</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr>\r\n            </thead>\r\n            <tbody>\r\n                <tr>\r\n                    <td>Accounting &amp; Taxation Service</td>\r\n                    <td>1</td>\r\n                    <td>4,000.00</td>\r\n                    <td>4,000.00</td>\r\n                </tr>\r\n                <tr class=\'total-row\'>\r\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\r\n                    <td><strong>4,000.00</strong></td>\r\n                </tr>\r\n            </tbody>\r\n        </table>\r\n        \r\n        <div class=\'payment-terms\'>\r\n            <h4>Payment Terms: Bi-yearly</h4>\r\n            <table>\r\n                <tr>\r\n                    <th>Installment</th>\r\n                    <th>Due</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr><tr>\r\n                    <td>1</td>\r\n                    <td>Half 1</td>\r\n                    <td>2,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Half 2</td>\r\n                    <td>2,000.00</td>\r\n                  </tr></table>\r\n        </div>\r\n        \r\n        <div class=\'bank-details\'>\r\n            <h4>Bank Transfer Details</h4>\r\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\r\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\r\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\r\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\r\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\r\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Notes</h4>\r\n            <ol>\r\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\r\n                <li>Prices are valid for 30 days from the date of issue</li>\r\n                <li>Payment should be made in full before service commencement</li>\r\n                <li>All bank charges are to be borne by the client</li>\r\n                <li>Services will commence upon receipt of payment</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div style=\'float: left; width: 45%;\'>\r\n                <p><strong>Prepared by:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p> </p>\r\n                <p>Sales Consultant</p>\r\n                <p>OGM Business Consultancy</p>\r\n            </div>\r\n            \r\n            <div style=\'float: right; width: 45%; text-align: center;\'>\r\n                <p><strong>For OGM Business Consultancy:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p>Authorized Signature</p>\r\n                <p>Date: December 1, 2025</p>\r\n            </div>\r\n            <div style=\'clear: both;\'></div>\r\n        </div>\r\n    </body>\r\n    </html>', 4000.00, '[{\"installment\":1,\"amount\":\"2,000.00\",\"due_description\":\"Half 1\"},{\"installment\":2,\"amount\":\"2,000.00\",\"due_description\":\"Half 2\"}]', '0000-00-00', 2, '2025-12-01 15:04:57', 'uploads/proformas/proforma_PROF-20251201-0001-V2.html'),
(3, 1, 'PROF-20251201-0001-V3', 3, 3, '<!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proforma Invoice: PROF-20251201-0001-V3</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\r\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\r\n            .section { margin-bottom: 25px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\r\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\r\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFORMA INVOICE</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Invoice Reference: PROF-20251201-0001-V3</h3>\r\n            <p><strong>Date Issued:</strong> December 1, 2025 | <strong>Valid Until:</strong> December 31, 2025</p>\r\n        </div>\r\n        \r\n        <div class=\'company-info\'>\r\n            <h4>From:</h4>\r\n            <p><strong>OGM Business Consultancy</strong></p>\r\n            <p>Business Bay, Dubai</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: info@ogmbusiness.com</p>\r\n            <p>Phone: +971 4 123 4567</p>\r\n            <p>VAT: TRN 123456789012345</p>\r\n        </div>\r\n        \r\n        <div class=\'client-info\'>\r\n            <h4>Bill To:</h4>\r\n            <p><strong>First Client</strong></p>\r\n            <p>Attn: Mr. Heg</p>\r\n            <p>Accountant</p>\r\n            <p>JLT, Dubai.</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: heg@email.comm</p>\r\n            <p>Phone: 123456789</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Service Description</h4>\r\n            <p><strong>Accounting &amp; Taxation</strong> - He need some services. And more.</p>\r\n        </div>\r\n        \r\n        <table>\r\n            <thead>\r\n                <tr>\r\n                    <th>Description</th>\r\n                    <th>Quantity</th>\r\n                    <th>Unit Price (AED)</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr>\r\n            </thead>\r\n            <tbody>\r\n                <tr>\r\n                    <td>Accounting &amp; Taxation Service</td>\r\n                    <td>1</td>\r\n                    <td>4,000.00</td>\r\n                    <td>4,000.00</td>\r\n                </tr>\r\n                <tr class=\'total-row\'>\r\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\r\n                    <td><strong>4,000.00</strong></td>\r\n                </tr>\r\n            </tbody>\r\n        </table>\r\n        \r\n        <div class=\'payment-terms\'>\r\n            <h4>Payment Terms: Bi-yearly</h4>\r\n            <table>\r\n                <tr>\r\n                    <th>Installment</th>\r\n                    <th>Due</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr><tr>\r\n                    <td>1</td>\r\n                    <td>Half 1</td>\r\n                    <td>2,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Half 2</td>\r\n                    <td>2,000.00</td>\r\n                  </tr></table>\r\n        </div>\r\n        \r\n        <div class=\'bank-details\'>\r\n            <h4>Bank Transfer Details</h4>\r\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\r\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\r\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\r\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\r\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\r\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Notes</h4>\r\n            <ol>\r\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\r\n                <li>Prices are valid for 30 days from the date of issue</li>\r\n                <li>Payment should be made in full before service commencement</li>\r\n                <li>All bank charges are to be borne by the client</li>\r\n                <li>Services will commence upon receipt of payment</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div style=\'float: left; width: 45%;\'>\r\n                <p><strong>Prepared by:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p> </p>\r\n                <p>Sales Consultant</p>\r\n                <p>OGM Business Consultancy</p>\r\n            </div>\r\n            \r\n            <div style=\'float: right; width: 45%; text-align: center;\'>\r\n                <p><strong>For OGM Business Consultancy:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p>Authorized Signature</p>\r\n                <p>Date: December 1, 2025</p>\r\n            </div>\r\n            <div style=\'clear: both;\'></div>\r\n        </div>\r\n    </body>\r\n    </html>', 4000.00, '[{\"installment\":1,\"amount\":\"2,000.00\",\"due_description\":\"Half 1\"},{\"installment\":2,\"amount\":\"2,000.00\",\"due_description\":\"Half 2\"}]', '0000-00-00', 2, '2025-12-01 15:05:20', 'uploads/proformas/proforma_PROF-20251201-0001-V3.html'),
(4, 1, 'PROF-20251202-0001-V4', 6, 4, '<!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proforma Invoice: PROF-20251202-0001-V4</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\r\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\r\n            .section { margin-bottom: 25px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\r\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\r\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFORMA INVOICE</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Invoice Reference: PROF-20251202-0001-V4</h3>\r\n            <p><strong>Date Issued:</strong> December 2, 2025 | <strong>Valid Until:</strong> January 1, 2026</p>\r\n        </div>\r\n        \r\n        <div class=\'company-info\'>\r\n            <h4>From:</h4>\r\n            <p><strong>OGM Business Consultancy</strong></p>\r\n            <p>Business Bay, Dubai</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: info@ogmbusiness.com</p>\r\n            <p>Phone: +971 4 123 4567</p>\r\n            <p>VAT: TRN 123456789012345</p>\r\n        </div>\r\n        \r\n        <div class=\'client-info\'>\r\n            <h4>Bill To:</h4>\r\n            <p><strong>First Client</strong></p>\r\n            <p>Attn: Mr. Heg</p>\r\n            <p>Accountant</p>\r\n            <p>JLT, Dubai.</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: heg@email.comm</p>\r\n            <p>Phone: 123456789</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Service Description</h4>\r\n            <p><strong>Accounting &amp; Taxation</strong> - He need some services. And more.</p>\r\n        </div>\r\n        \r\n        <table>\r\n            <thead>\r\n                <tr>\r\n                    <th>Description</th>\r\n                    <th>Quantity</th>\r\n                    <th>Unit Price (AED)</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr>\r\n            </thead>\r\n            <tbody>\r\n                <tr>\r\n                    <td>Accounting &amp; Taxation Service</td>\r\n                    <td>1</td>\r\n                    <td>4,000.00</td>\r\n                    <td>4,000.00</td>\r\n                </tr>\r\n                <tr class=\'total-row\'>\r\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\r\n                    <td><strong>4,000.00</strong></td>\r\n                </tr>\r\n            </tbody>\r\n        </table>\r\n        \r\n        <div class=\'payment-terms\'>\r\n            <h4>Payment Terms: Quarterly</h4>\r\n            <table>\r\n                <tr>\r\n                    <th>Installment</th>\r\n                    <th>Due</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr><tr>\r\n                    <td>1</td>\r\n                    <td>Quarter 1</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Quarter 2</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>3</td>\r\n                    <td>Quarter 3</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>4</td>\r\n                    <td>Quarter 4</td>\r\n                    <td>1,000.00</td>\r\n                  </tr></table>\r\n        </div>\r\n        \r\n        <div class=\'bank-details\'>\r\n            <h4>Bank Transfer Details</h4>\r\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\r\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\r\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\r\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\r\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\r\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Notes</h4>\r\n            <ol>\r\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\r\n                <li>Prices are valid for 30 days from the date of issue</li>\r\n                <li>Payment should be made in full before service commencement</li>\r\n                <li>All bank charges are to be borne by the client</li>\r\n                <li>Services will commence upon receipt of payment</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div style=\'float: left; width: 45%;\'>\r\n                <p><strong>Prepared by:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p> </p>\r\n                <p>Sales Consultant</p>\r\n                <p>OGM Business Consultancy</p>\r\n            </div>\r\n            \r\n            <div style=\'float: right; width: 45%; text-align: center;\'>\r\n                <p><strong>For OGM Business Consultancy:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p>Authorized Signature</p>\r\n                <p>Date: December 2, 2025</p>\r\n            </div>\r\n            <div style=\'clear: both;\'></div>\r\n        </div>\r\n    </body>\r\n    </html>', 4000.00, '[{\"installment\":1,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 1\"},{\"installment\":2,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 2\"},{\"installment\":3,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 3\"},{\"installment\":4,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 4\"}]', '0000-00-00', 2, '2025-12-02 11:00:18', 'uploads/proformas/proforma_PROF-20251202-0001-V4.html'),
(7, 3, 'PROF-20260227-0003-V1', NULL, 1, '<!DOCTYPE html>\n    <html>\n    <head>\n        <meta charset=\'UTF-8\'>\n        <title>Proforma Invoice: PROF-20260227-0003-V1</title>\n        <style>\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\n            .section { margin-bottom: 25px; }\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\n            th { background-color: #f2f2f2; font-weight: bold; }\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\n            .company-logo { text-align: center; margin-bottom: 20px; }\n        </style>\n    </head>\n    <body>\n        <div class=\'company-logo\'>\n            <h1>OGM BUSINESS CONSULTANCY</h1>\n            <h2>PROFORMA INVOICE</h2>\n        </div>\n        \n        <div class=\'header\'>\n            <h3>Invoice Reference: PROF-20260227-0003-V1</h3>\n            <p><strong>Date Issued:</strong> February 27, 2026 | <strong>Valid Until:</strong> March 29, 2026</p>\n        </div>\n        \n        <div class=\'company-info\'>\n            <h4>From:</h4>\n            <p><strong>OGM Business Consultancy</strong></p>\n            <p>Business Bay, Dubai</p>\n            <p>United Arab Emirates</p>\n            <p>Email: info@ogmbusiness.com</p>\n            <p>Phone: +971 4 123 4567</p>\n            <p>VAT: TRN 123456789012345</p>\n        </div>\n        \n        <div class=\'client-info\'>\n            <h4>Bill To:</h4>\n            <p><strong>Theta-X</strong></p>\n            <p>Attn: Otaksi Clients</p>\n            <p>Accountant</p>\n            <p>ertyui765</p>\n            <p>Bahrain</p>\n            <p>Email: otaksiclients@gmail.com</p>\n            <p>Phone: 09090909</p>\n        </div>\n        \n        <div class=\'section\'>\n            <h4>Service Description</h4>\n            <p><strong>Business Setup</strong> - trewtyujhg</p>\n        </div>\n        \n        <table>\n            <thead>\n                <tr>\n                    <th>Description</th>\n                    <th>Quantity</th>\n                    <th>Unit Price (AED)</th>\n                    <th>Amount (AED)</th>\n                </tr>\n            </thead>\n            <tbody>\n                <tr>\n                    <td>Business Setup Service</td>\n                    <td>1</td>\n                    <td>344.00</td>\n                    <td>344.00</td>\n                </tr>\n                <tr class=\'total-row\'>\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\n                    <td><strong>344.00</strong></td>\n                </tr>\n            </tbody>\n        </table>\n        \n        <div class=\'payment-terms\'>\n            <h4>Payment Terms: Monthly</h4>\n            <table>\n                <tr>\n                    <th>Installment</th>\n                    <th>Due</th>\n                    <th>Amount (AED)</th>\n                </tr><tr>\n                    <td>1</td>\n                    <td>Month 1</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>2</td>\n                    <td>Month 2</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>3</td>\n                    <td>Month 3</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>4</td>\n                    <td>Month 4</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>5</td>\n                    <td>Month 5</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>6</td>\n                    <td>Month 6</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>7</td>\n                    <td>Month 7</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>8</td>\n                    <td>Month 8</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>9</td>\n                    <td>Month 9</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>10</td>\n                    <td>Month 10</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>11</td>\n                    <td>Month 11</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>12</td>\n                    <td>Month 12</td>\n                    <td>28.67</td>\n                  </tr></table>\n        </div>\n        \n        <div class=\'bank-details\'>\n            <h4>Bank Transfer Details</h4>\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\n        </div>\n        \n        <div class=\'section\'>\n            <h4>Notes</h4>\n            <ol>\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\n                <li>Prices are valid for 30 days from the date of issue</li>\n                <li>Payment should be made in full before service commencement</li>\n                <li>All bank charges are to be borne by the client</li>\n                <li>Services will commence upon receipt of payment</li>\n            </ol>\n        </div>\n        \n        <div class=\'footer\'>\n            <div style=\'float: left; width: 45%;\'>\n                <p><strong>Prepared by:</strong></p>\n                <br><br>\n                <p>_________________________</p>\n                <p> </p>\n                <p>Sales Consultant</p>\n                <p>OGM Business Consultancy</p>\n            </div>\n            \n            <div style=\'float: right; width: 45%; text-align: center;\'>\n                <p><strong>For OGM Business Consultancy:</strong></p>\n                <br><br>\n                <p>_________________________</p>\n                <p>Authorized Signature</p>\n                <p>Date: February 27, 2026</p>\n            </div>\n            <div style=\'clear: both;\'></div>\n        </div>\n    </body>\n    </html>', 344.00, '[{\"installment\":1,\"amount\":\"28.67\",\"due_description\":\"Month 1\"},{\"installment\":2,\"amount\":\"28.67\",\"due_description\":\"Month 2\"},{\"installment\":3,\"amount\":\"28.67\",\"due_description\":\"Month 3\"},{\"installment\":4,\"amount\":\"28.67\",\"due_description\":\"Month 4\"},{\"installment\":5,\"amount\":\"28.67\",\"due_description\":\"Month 5\"},{\"installment\":6,\"amount\":\"28.67\",\"due_description\":\"Month 6\"},{\"installment\":7,\"amount\":\"28.67\",\"due_description\":\"Month 7\"},{\"installment\":8,\"amount\":\"28.67\",\"due_description\":\"Month 8\"},{\"installment\":9,\"amount\":\"28.67\",\"due_description\":\"Month 9\"},{\"installment\":10,\"amount\":\"28.67\",\"due_description\":\"Month 10\"},{\"installment\":11,\"amount\":\"28.67\",\"due_description\":\"Month 11\"},{\"installment\":12,\"amount\":\"28.67\",\"due_description\":\"Month 12\"}]', '2026-03-29', 9, '2026-02-27 15:40:27', 'uploads/proformas/proforma_PROF-20260227-0003-V1.html'),
(8, 3, 'PROF-20260227-0003-V2', NULL, 2, '<!DOCTYPE html>\n    <html>\n    <head>\n        <meta charset=\'UTF-8\'>\n        <title>Proforma Invoice: PROF-20260227-0003-V2</title>\n        <style>\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\n            .section { margin-bottom: 25px; }\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\n            th { background-color: #f2f2f2; font-weight: bold; }\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\n            .company-logo { text-align: center; margin-bottom: 20px; }\n        </style>\n    </head>\n    <body>\n        <div class=\'company-logo\'>\n            <h1>OGM BUSINESS CONSULTANCY</h1>\n            <h2>PROFORMA INVOICE</h2>\n        </div>\n        \n        <div class=\'header\'>\n            <h3>Invoice Reference: PROF-20260227-0003-V2</h3>\n            <p><strong>Date Issued:</strong> February 27, 2026 | <strong>Valid Until:</strong> March 29, 2026</p>\n        </div>\n        \n        <div class=\'company-info\'>\n            <h4>From:</h4>\n            <p><strong>OGM Business Consultancy</strong></p>\n            <p>Business Bay, Dubai</p>\n            <p>United Arab Emirates</p>\n            <p>Email: info@ogmbusiness.com</p>\n            <p>Phone: +971 4 123 4567</p>\n            <p>VAT: TRN 123456789012345</p>\n        </div>\n        \n        <div class=\'client-info\'>\n            <h4>Bill To:</h4>\n            <p><strong>Theta-X</strong></p>\n            <p>Attn: Otaksi Clients</p>\n            <p>Accountant</p>\n            <p>ertyui765</p>\n            <p>Bahrain</p>\n            <p>Email: otaksiclients@gmail.com</p>\n            <p>Phone: 09090909</p>\n        </div>\n        \n        <div class=\'section\'>\n            <h4>Service Description</h4>\n            <p><strong>Business Setup</strong> - trewtyujhg</p>\n        </div>\n        \n        <table>\n            <thead>\n                <tr>\n                    <th>Description</th>\n                    <th>Quantity</th>\n                    <th>Unit Price (AED)</th>\n                    <th>Amount (AED)</th>\n                </tr>\n            </thead>\n            <tbody>\n                <tr>\n                    <td>Business Setup Service</td>\n                    <td>1</td>\n                    <td>344.00</td>\n                    <td>344.00</td>\n                </tr>\n                <tr class=\'total-row\'>\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\n                    <td><strong>344.00</strong></td>\n                </tr>\n            </tbody>\n        </table>\n        \n        <div class=\'payment-terms\'>\n            <h4>Payment Terms: Monthly</h4>\n            <table>\n                <tr>\n                    <th>Installment</th>\n                    <th>Due</th>\n                    <th>Amount (AED)</th>\n                </tr><tr>\n                    <td>1</td>\n                    <td>Month 1</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>2</td>\n                    <td>Month 2</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>3</td>\n                    <td>Month 3</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>4</td>\n                    <td>Month 4</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>5</td>\n                    <td>Month 5</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>6</td>\n                    <td>Month 6</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>7</td>\n                    <td>Month 7</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>8</td>\n                    <td>Month 8</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>9</td>\n                    <td>Month 9</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>10</td>\n                    <td>Month 10</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>11</td>\n                    <td>Month 11</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>12</td>\n                    <td>Month 12</td>\n                    <td>28.67</td>\n                  </tr></table>\n        </div>\n        \n        <div class=\'bank-details\'>\n            <h4>Bank Transfer Details</h4>\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\n        </div>\n        \n        <div class=\'section\'>\n            <h4>Notes</h4>\n            <ol>\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\n                <li>Prices are valid for 30 days from the date of issue</li>\n                <li>Payment should be made in full before service commencement</li>\n                <li>All bank charges are to be borne by the client</li>\n                <li>Services will commence upon receipt of payment</li>\n            </ol>\n        </div>\n        \n        <div class=\'footer\'>\n            <div style=\'float: left; width: 45%;\'>\n                <p><strong>Prepared by:</strong></p>\n                <br><br>\n                <p>_________________________</p>\n                <p> </p>\n                <p>Sales Consultant</p>\n                <p>OGM Business Consultancy</p>\n            </div>\n            \n            <div style=\'float: right; width: 45%; text-align: center;\'>\n                <p><strong>For OGM Business Consultancy:</strong></p>\n                <br><br>\n                <p>_________________________</p>\n                <p>Authorized Signature</p>\n                <p>Date: February 27, 2026</p>\n            </div>\n            <div style=\'clear: both;\'></div>\n        </div>\n    </body>\n    </html>', 344.00, '[{\"installment\":1,\"amount\":\"28.67\",\"due_description\":\"Month 1\"},{\"installment\":2,\"amount\":\"28.67\",\"due_description\":\"Month 2\"},{\"installment\":3,\"amount\":\"28.67\",\"due_description\":\"Month 3\"},{\"installment\":4,\"amount\":\"28.67\",\"due_description\":\"Month 4\"},{\"installment\":5,\"amount\":\"28.67\",\"due_description\":\"Month 5\"},{\"installment\":6,\"amount\":\"28.67\",\"due_description\":\"Month 6\"},{\"installment\":7,\"amount\":\"28.67\",\"due_description\":\"Month 7\"},{\"installment\":8,\"amount\":\"28.67\",\"due_description\":\"Month 8\"},{\"installment\":9,\"amount\":\"28.67\",\"due_description\":\"Month 9\"},{\"installment\":10,\"amount\":\"28.67\",\"due_description\":\"Month 10\"},{\"installment\":11,\"amount\":\"28.67\",\"due_description\":\"Month 11\"},{\"installment\":12,\"amount\":\"28.67\",\"due_description\":\"Month 12\"}]', '2026-03-29', 9, '2026-02-27 15:40:48', 'uploads/proformas/proforma_PROF-20260227-0003-V2.html'),
(9, 2, 'PROF-20260227-0002-V1', NULL, 1, '<!DOCTYPE html>\n    <html>\n    <head>\n        <meta charset=\'UTF-8\'>\n        <title>Proforma Invoice: PROF-20260227-0002-V1</title>\n        <style>\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\n            .section { margin-bottom: 25px; }\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\n            th { background-color: #f2f2f2; font-weight: bold; }\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\n            .company-logo { text-align: center; margin-bottom: 20px; }\n        </style>\n    </head>\n    <body>\n        <div class=\'company-logo\'>\n            <h1>OGM BUSINESS CONSULTANCY</h1>\n            <h2>PROFORMA INVOICE</h2>\n        </div>\n        \n        <div class=\'header\'>\n            <h3>Invoice Reference: PROF-20260227-0002-V1</h3>\n            <p><strong>Date Issued:</strong> February 27, 2026 | <strong>Valid Until:</strong> March 29, 2026</p>\n        </div>\n        \n        <div class=\'company-info\'>\n            <h4>From:</h4>\n            <p><strong>OGM Business Consultancy</strong></p>\n            <p>Business Bay, Dubai</p>\n            <p>United Arab Emirates</p>\n            <p>Email: info@ogmbusiness.com</p>\n            <p>Phone: +971 4 123 4567</p>\n            <p>VAT: TRN 123456789012345</p>\n        </div>\n        \n        <div class=\'client-info\'>\n            <h4>Bill To:</h4>\n            <p><strong>ABCD LLC</strong></p>\n            <p>Attn: Otaksi Clients</p>\n            <p>Accountant</p>\n            <p>Hello</p>\n            <p>France</p>\n            <p>Email: otaksiclients@gmail.com</p>\n            <p>Phone: 09090909</p>\n        </div>\n        \n        <div class=\'section\'>\n            <h4>Service Description</h4>\n            <p><strong>Accounting &amp; Taxation</strong> - Helloooooooooo</p>\n        </div>\n        \n        <table>\n            <thead>\n                <tr>\n                    <th>Description</th>\n                    <th>Quantity</th>\n                    <th>Unit Price (CNY)</th>\n                    <th>Amount (CNY)</th>\n                </tr>\n            </thead>\n            <tbody>\n                <tr>\n                    <td>Accounting &amp; Taxation Service</td>\n                    <td>1</td>\n                    <td>233.00</td>\n                    <td>233.00</td>\n                </tr>\n                <tr class=\'total-row\'>\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\n                    <td><strong>233.00</strong></td>\n                </tr>\n            </tbody>\n        </table>\n        \n        <div class=\'payment-terms\'>\n            <h4>Payment Terms: Quarterly</h4>\n            <table>\n                <tr>\n                    <th>Installment</th>\n                    <th>Due</th>\n                    <th>Amount (CNY)</th>\n                </tr><tr>\n                    <td>1</td>\n                    <td>Quarter 1</td>\n                    <td>58.25</td>\n                  </tr><tr>\n                    <td>2</td>\n                    <td>Quarter 2</td>\n                    <td>58.25</td>\n                  </tr><tr>\n                    <td>3</td>\n                    <td>Quarter 3</td>\n                    <td>58.25</td>\n                  </tr><tr>\n                    <td>4</td>\n                    <td>Quarter 4</td>\n                    <td>58.25</td>\n                  </tr></table>\n        </div>\n        \n        <div class=\'bank-details\'>\n            <h4>Bank Transfer Details</h4>\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\n        </div>\n        \n        <div class=\'section\'>\n            <h4>Notes</h4>\n            <ol>\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\n                <li>Prices are valid for 30 days from the date of issue</li>\n                <li>Payment should be made in full before service commencement</li>\n                <li>All bank charges are to be borne by the client</li>\n                <li>Services will commence upon receipt of payment</li>\n            </ol>\n        </div>\n        \n        <div class=\'footer\'>\n            <div style=\'float: left; width: 45%;\'>\n                <p><strong>Prepared by:</strong></p>\n                <br><br>\n                <p>_________________________</p>\n                <p> </p>\n                <p>Sales Consultant</p>\n                <p>OGM Business Consultancy</p>\n            </div>\n            \n            <div style=\'float: right; width: 45%; text-align: center;\'>\n                <p><strong>For OGM Business Consultancy:</strong></p>\n                <br><br>\n                <p>_________________________</p>\n                <p>Authorized Signature</p>\n                <p>Date: February 27, 2026</p>\n            </div>\n            <div style=\'clear: both;\'></div>\n        </div>\n    </body>\n    </html>', 233.00, '[{\"installment\":1,\"amount\":\"58.25\",\"due_description\":\"Quarter 1\"},{\"installment\":2,\"amount\":\"58.25\",\"due_description\":\"Quarter 2\"},{\"installment\":3,\"amount\":\"58.25\",\"due_description\":\"Quarter 3\"},{\"installment\":4,\"amount\":\"58.25\",\"due_description\":\"Quarter 4\"}]', '2026-03-29', 9, '2026-02-27 15:41:54', 'uploads/proformas/proforma_PROF-20260227-0002-V1.html');
INSERT INTO `proforma_invoices` (`invoice_id`, `client_id`, `invoice_ref`, `proposal_id`, `version`, `invoice_content`, `total_amount`, `payment_breakdown`, `validity_period`, `prepared_by`, `prepared_at`, `file_path`) VALUES
(10, 4, 'PROF-20260227-0004-V1', 8, 1, '<!DOCTYPE html>\n    <html>\n    <head>\n        <meta charset=\'UTF-8\'>\n        <title>Proforma Invoice: PROF-20260227-0004-V1</title>\n        <style>\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\n            .section { margin-bottom: 25px; }\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\n            th { background-color: #f2f2f2; font-weight: bold; }\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\n            .company-logo { text-align: center; margin-bottom: 20px; }\n        </style>\n    </head>\n    <body>\n        <div class=\'company-logo\'>\n            <h1>OGM BUSINESS CONSULTANCY</h1>\n            <h2>PROFORMA INVOICE</h2>\n        </div>\n        \n        <div class=\'header\'>\n            <h3>Invoice Reference: PROF-20260227-0004-V1</h3>\n            <p><strong>Date Issued:</strong> February 27, 2026 | <strong>Valid Until:</strong> March 29, 2026</p>\n        </div>\n        \n        <div class=\'company-info\'>\n            <h4>From:</h4>\n            <p><strong>OGM Business Consultancy</strong></p>\n            <p>Business Bay, Dubai</p>\n            <p>United Arab Emirates</p>\n            <p>Email: info@ogmbusiness.com</p>\n            <p>Phone: +971 4 123 4567</p>\n            <p>VAT: TRN 123456789012345</p>\n        </div>\n        \n        <div class=\'client-info\'>\n            <h4>Bill To:</h4>\n            <p><strong>Theta-X</strong></p>\n            <p>Attn: Joe</p>\n            <p>Manager</p>\n            <p>tueffg</p>\n            <p>United Arab Emirates</p>\n            <p>Email: otaksiclients@gmail.com</p>\n            <p>Phone: 09090909</p>\n        </div>\n        \n        <div class=\'section\'>\n            <h4>Service Description</h4>\n            <p><strong>Business Setup</strong> - good</p>\n        </div>\n        \n        <table>\n            <thead>\n                <tr>\n                    <th>Description</th>\n                    <th>Quantity</th>\n                    <th>Unit Price (AED)</th>\n                    <th>Amount (AED)</th>\n                </tr>\n            </thead>\n            <tbody>\n                <tr>\n                    <td>Business Setup Service</td>\n                    <td>1</td>\n                    <td>455.00</td>\n                    <td>455.00</td>\n                </tr>\n                <tr class=\'total-row\'>\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\n                    <td><strong>455.00</strong></td>\n                </tr>\n            </tbody>\n        </table>\n        \n        <div class=\'payment-terms\'>\n            <h4>Payment Terms: Monthly</h4>\n            <table>\n                <tr>\n                    <th>Installment</th>\n                    <th>Due</th>\n                    <th>Amount (AED)</th>\n                </tr><tr>\n                    <td>1</td>\n                    <td>Month 1</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>2</td>\n                    <td>Month 2</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>3</td>\n                    <td>Month 3</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>4</td>\n                    <td>Month 4</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>5</td>\n                    <td>Month 5</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>6</td>\n                    <td>Month 6</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>7</td>\n                    <td>Month 7</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>8</td>\n                    <td>Month 8</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>9</td>\n                    <td>Month 9</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>10</td>\n                    <td>Month 10</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>11</td>\n                    <td>Month 11</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>12</td>\n                    <td>Month 12</td>\n                    <td>37.92</td>\n                  </tr></table>\n        </div>\n        \n        <div class=\'bank-details\'>\n            <h4>Bank Transfer Details</h4>\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\n        </div>\n        \n        <div class=\'section\'>\n            <h4>Notes</h4>\n            <ol>\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\n                <li>Prices are valid for 30 days from the date of issue</li>\n                <li>Payment should be made in full before service commencement</li>\n                <li>All bank charges are to be borne by the client</li>\n                <li>Services will commence upon receipt of payment</li>\n            </ol>\n        </div>\n        \n        <div class=\'footer\'>\n            <div style=\'float: left; width: 45%;\'>\n                <p><strong>Prepared by:</strong></p>\n                <br><br>\n                <p>_________________________</p>\n                <p> </p>\n                <p>Sales Consultant</p>\n                <p>OGM Business Consultancy</p>\n            </div>\n            \n            <div style=\'float: right; width: 45%; text-align: center;\'>\n                <p><strong>For OGM Business Consultancy:</strong></p>\n                <br><br>\n                <p>_________________________</p>\n                <p>Authorized Signature</p>\n                <p>Date: February 27, 2026</p>\n            </div>\n            <div style=\'clear: both;\'></div>\n        </div>\n    </body>\n    </html>', 455.00, '[{\"installment\":1,\"amount\":\"37.92\",\"due_description\":\"Month 1\"},{\"installment\":2,\"amount\":\"37.92\",\"due_description\":\"Month 2\"},{\"installment\":3,\"amount\":\"37.92\",\"due_description\":\"Month 3\"},{\"installment\":4,\"amount\":\"37.92\",\"due_description\":\"Month 4\"},{\"installment\":5,\"amount\":\"37.92\",\"due_description\":\"Month 5\"},{\"installment\":6,\"amount\":\"37.92\",\"due_description\":\"Month 6\"},{\"installment\":7,\"amount\":\"37.92\",\"due_description\":\"Month 7\"},{\"installment\":8,\"amount\":\"37.92\",\"due_description\":\"Month 8\"},{\"installment\":9,\"amount\":\"37.92\",\"due_description\":\"Month 9\"},{\"installment\":10,\"amount\":\"37.92\",\"due_description\":\"Month 10\"},{\"installment\":11,\"amount\":\"37.92\",\"due_description\":\"Month 11\"},{\"installment\":12,\"amount\":\"37.92\",\"due_description\":\"Month 12\"}]', '2026-03-29', 9, '2026-02-27 17:53:47', 'uploads/proformas/proforma_PROF-20260227-0004-V1.html'),
(11, 4, 'PROF-20260227-0004-V2', 8, 2, '<!DOCTYPE html>\n    <html>\n    <head>\n        <meta charset=\'UTF-8\'>\n        <title>Proforma Invoice: PROF-20260227-0004-V2</title>\n        <style>\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\n            .section { margin-bottom: 25px; }\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\n            th { background-color: #f2f2f2; font-weight: bold; }\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\n            .company-logo { text-align: center; margin-bottom: 20px; }\n        </style>\n    </head>\n    <body>\n        <div class=\'company-logo\'>\n            <h1>OGM BUSINESS CONSULTANCY</h1>\n            <h2>PROFORMA INVOICE</h2>\n        </div>\n        \n        <div class=\'header\'>\n            <h3>Invoice Reference: PROF-20260227-0004-V2</h3>\n            <p><strong>Date Issued:</strong> February 27, 2026 | <strong>Valid Until:</strong> March 29, 2026</p>\n        </div>\n        \n        <div class=\'company-info\'>\n            <h4>From:</h4>\n            <p><strong>OGM Business Consultancy</strong></p>\n            <p>Business Bay, Dubai</p>\n            <p>United Arab Emirates</p>\n            <p>Email: info@ogmbusiness.com</p>\n            <p>Phone: +971 4 123 4567</p>\n            <p>VAT: TRN 123456789012345</p>\n        </div>\n        \n        <div class=\'client-info\'>\n            <h4>Bill To:</h4>\n            <p><strong>Theta-X</strong></p>\n            <p>Attn: Joe</p>\n            <p>Manager</p>\n            <p>tueffg</p>\n            <p>United Arab Emirates</p>\n            <p>Email: otaksiclients@gmail.com</p>\n            <p>Phone: 09090909</p>\n        </div>\n        \n        <div class=\'section\'>\n            <h4>Service Description</h4>\n            <p><strong>Business Setup</strong> - good</p>\n        </div>\n        \n        <table>\n            <thead>\n                <tr>\n                    <th>Description</th>\n                    <th>Quantity</th>\n                    <th>Unit Price (AED)</th>\n                    <th>Amount (AED)</th>\n                </tr>\n            </thead>\n            <tbody>\n                <tr>\n                    <td>Business Setup Service</td>\n                    <td>1</td>\n                    <td>455.00</td>\n                    <td>455.00</td>\n                </tr>\n                <tr class=\'total-row\'>\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\n                    <td><strong>455.00</strong></td>\n                </tr>\n            </tbody>\n        </table>\n        \n        <div class=\'payment-terms\'>\n            <h4>Payment Terms: Monthly</h4>\n            <table>\n                <tr>\n                    <th>Installment</th>\n                    <th>Due</th>\n                    <th>Amount (AED)</th>\n                </tr><tr>\n                    <td>1</td>\n                    <td>Month 1</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>2</td>\n                    <td>Month 2</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>3</td>\n                    <td>Month 3</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>4</td>\n                    <td>Month 4</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>5</td>\n                    <td>Month 5</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>6</td>\n                    <td>Month 6</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>7</td>\n                    <td>Month 7</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>8</td>\n                    <td>Month 8</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>9</td>\n                    <td>Month 9</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>10</td>\n                    <td>Month 10</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>11</td>\n                    <td>Month 11</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>12</td>\n                    <td>Month 12</td>\n                    <td>37.92</td>\n                  </tr></table>\n        </div>\n        \n        <div class=\'bank-details\'>\n            <h4>Bank Transfer Details</h4>\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\n        </div>\n        \n        <div class=\'section\'>\n            <h4>Notes</h4>\n            <ol>\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\n                <li>Prices are valid for 30 days from the date of issue</li>\n                <li>Payment should be made in full before service commencement</li>\n                <li>All bank charges are to be borne by the client</li>\n                <li>Services will commence upon receipt of payment</li>\n            </ol>\n        </div>\n        \n        <div class=\'footer\'>\n            <div style=\'float: left; width: 45%;\'>\n                <p><strong>Prepared by:</strong></p>\n                <br><br>\n                <p>_________________________</p>\n                <p> </p>\n                <p>Sales Consultant</p>\n                <p>OGM Business Consultancy</p>\n            </div>\n            \n            <div style=\'float: right; width: 45%; text-align: center;\'>\n                <p><strong>For OGM Business Consultancy:</strong></p>\n                <br><br>\n                <p>_________________________</p>\n                <p>Authorized Signature</p>\n                <p>Date: February 27, 2026</p>\n            </div>\n            <div style=\'clear: both;\'></div>\n        </div>\n    </body>\n    </html>', 455.00, '[{\"installment\":1,\"amount\":\"37.92\",\"due_description\":\"Month 1\"},{\"installment\":2,\"amount\":\"37.92\",\"due_description\":\"Month 2\"},{\"installment\":3,\"amount\":\"37.92\",\"due_description\":\"Month 3\"},{\"installment\":4,\"amount\":\"37.92\",\"due_description\":\"Month 4\"},{\"installment\":5,\"amount\":\"37.92\",\"due_description\":\"Month 5\"},{\"installment\":6,\"amount\":\"37.92\",\"due_description\":\"Month 6\"},{\"installment\":7,\"amount\":\"37.92\",\"due_description\":\"Month 7\"},{\"installment\":8,\"amount\":\"37.92\",\"due_description\":\"Month 8\"},{\"installment\":9,\"amount\":\"37.92\",\"due_description\":\"Month 9\"},{\"installment\":10,\"amount\":\"37.92\",\"due_description\":\"Month 10\"},{\"installment\":11,\"amount\":\"37.92\",\"due_description\":\"Month 11\"},{\"installment\":12,\"amount\":\"37.92\",\"due_description\":\"Month 12\"}]', '2026-03-29', 9, '2026-02-27 18:13:51', 'uploads/proformas/proforma_PROF-20260227-0004-V2.html'),
(12, 5, 'PROF-20260227-0005-V1', NULL, 1, '<!DOCTYPE html>\n    <html>\n    <head>\n        <meta charset=\'UTF-8\'>\n        <title>Proforma Invoice: PROF-20260227-0005-V1</title>\n        <style>\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\n            .section { margin-bottom: 25px; }\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\n            th { background-color: #f2f2f2; font-weight: bold; }\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\n            .company-logo { text-align: center; margin-bottom: 20px; }\n        </style>\n    </head>\n    <body>\n        <div class=\'company-logo\'>\n            <h1>OGM BUSINESS CONSULTANCY</h1>\n            <h2>PROFORMA INVOICE</h2>\n        </div>\n        \n        <div class=\'header\'>\n            <h3>Invoice Reference: PROF-20260227-0005-V1</h3>\n            <p><strong>Date Issued:</strong> February 27, 2026 | <strong>Valid Until:</strong> March 29, 2026</p>\n        </div>\n        \n        <div class=\'company-info\'>\n            <h4>From:</h4>\n            <p><strong>OGM Business Consultancy</strong></p>\n            <p>Business Bay, Dubai</p>\n            <p>United Arab Emirates</p>\n            <p>Email: info@ogmbusiness.com</p>\n            <p>Phone: +971 4 123 4567</p>\n            <p>VAT: TRN 123456789012345</p>\n        </div>\n        \n        <div class=\'client-info\'>\n            <h4>Bill To:</h4>\n            <p><strong>Zawaj Mubarak</strong></p>\n            <p>Attn: Vurat</p>\n            <p>Former</p>\n            <p>Hwllooooo</p>\n            <p>India</p>\n            <p>Email: otaksiclients@gmail.com</p>\n            <p>Phone: 09090909</p>\n        </div>\n        \n        <div class=\'section\'>\n            <h4>Service Description</h4>\n            <p><strong>Accounting &amp; Taxation</strong> - Hellooo</p>\n        </div>\n        \n        <table>\n            <thead>\n                <tr>\n                    <th>Description</th>\n                    <th>Quantity</th>\n                    <th>Unit Price (AED)</th>\n                    <th>Amount (AED)</th>\n                </tr>\n            </thead>\n            <tbody>\n                <tr>\n                    <td>Accounting &amp; Taxation Service</td>\n                    <td>1</td>\n                    <td>3,444.00</td>\n                    <td>3,444.00</td>\n                </tr>\n                <tr class=\'total-row\'>\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\n                    <td><strong>3,444.00</strong></td>\n                </tr>\n            </tbody>\n        </table>\n        \n        <div class=\'payment-terms\'>\n            <h4>Payment Terms: Monthly</h4>\n            <table>\n                <tr>\n                    <th>Installment</th>\n                    <th>Due</th>\n                    <th>Amount (AED)</th>\n                </tr><tr>\n                    <td>1</td>\n                    <td>Month 1</td>\n                    <td>287.00</td>\n                  </tr><tr>\n                    <td>2</td>\n                    <td>Month 2</td>\n                    <td>287.00</td>\n                  </tr><tr>\n                    <td>3</td>\n                    <td>Month 3</td>\n                    <td>287.00</td>\n                  </tr><tr>\n                    <td>4</td>\n                    <td>Month 4</td>\n                    <td>287.00</td>\n                  </tr><tr>\n                    <td>5</td>\n                    <td>Month 5</td>\n                    <td>287.00</td>\n                  </tr><tr>\n                    <td>6</td>\n                    <td>Month 6</td>\n                    <td>287.00</td>\n                  </tr><tr>\n                    <td>7</td>\n                    <td>Month 7</td>\n                    <td>287.00</td>\n                  </tr><tr>\n                    <td>8</td>\n                    <td>Month 8</td>\n                    <td>287.00</td>\n                  </tr><tr>\n                    <td>9</td>\n                    <td>Month 9</td>\n                    <td>287.00</td>\n                  </tr><tr>\n                    <td>10</td>\n                    <td>Month 10</td>\n                    <td>287.00</td>\n                  </tr><tr>\n                    <td>11</td>\n                    <td>Month 11</td>\n                    <td>287.00</td>\n                  </tr><tr>\n                    <td>12</td>\n                    <td>Month 12</td>\n                    <td>287.00</td>\n                  </tr></table>\n        </div>\n        \n        <div class=\'bank-details\'>\n            <h4>Bank Transfer Details</h4>\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\n        </div>\n        \n        <div class=\'section\'>\n            <h4>Notes</h4>\n            <ol>\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\n                <li>Prices are valid for 30 days from the date of issue</li>\n                <li>Payment should be made in full before service commencement</li>\n                <li>All bank charges are to be borne by the client</li>\n                <li>Services will commence upon receipt of payment</li>\n            </ol>\n        </div>\n        \n        <div class=\'footer\'>\n            <div style=\'float: left; width: 45%;\'>\n                <p><strong>Prepared by:</strong></p>\n                <br><br>\n                <p>_________________________</p>\n                <p> </p>\n                <p>Sales Consultant</p>\n                <p>OGM Business Consultancy</p>\n            </div>\n            \n            <div style=\'float: right; width: 45%; text-align: center;\'>\n                <p><strong>For OGM Business Consultancy:</strong></p>\n                <br><br>\n                <p>_________________________</p>\n                <p>Authorized Signature</p>\n                <p>Date: February 27, 2026</p>\n            </div>\n            <div style=\'clear: both;\'></div>\n        </div>\n    </body>\n    </html>', 3444.00, '[{\"installment\":1,\"amount\":\"287.00\",\"due_description\":\"Month 1\"},{\"installment\":2,\"amount\":\"287.00\",\"due_description\":\"Month 2\"},{\"installment\":3,\"amount\":\"287.00\",\"due_description\":\"Month 3\"},{\"installment\":4,\"amount\":\"287.00\",\"due_description\":\"Month 4\"},{\"installment\":5,\"amount\":\"287.00\",\"due_description\":\"Month 5\"},{\"installment\":6,\"amount\":\"287.00\",\"due_description\":\"Month 6\"},{\"installment\":7,\"amount\":\"287.00\",\"due_description\":\"Month 7\"},{\"installment\":8,\"amount\":\"287.00\",\"due_description\":\"Month 8\"},{\"installment\":9,\"amount\":\"287.00\",\"due_description\":\"Month 9\"},{\"installment\":10,\"amount\":\"287.00\",\"due_description\":\"Month 10\"},{\"installment\":11,\"amount\":\"287.00\",\"due_description\":\"Month 11\"},{\"installment\":12,\"amount\":\"287.00\",\"due_description\":\"Month 12\"}]', '2026-03-29', 9, '2026-02-27 19:24:12', 'uploads/proformas/proforma_PROF-20260227-0005-V1.html');

-- --------------------------------------------------------

--
-- Table structure for table `proforma_reviews`
--

CREATE TABLE `proforma_reviews` (
  `review_id` int NOT NULL,
  `proforma_id` int NOT NULL,
  `client_id` int NOT NULL,
  `reviewed_by` int NOT NULL,
  `reviewer_role` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `review_notes` text COLLATE utf8mb4_general_ci,
  `checklist_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `signature_data` text COLLATE utf8mb4_general_ci,
  `company_stamp` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_general_ci,
  `rejection_action` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `review_result` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `reviewed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `proposals`
--

CREATE TABLE `proposals` (
  `proposal_id` int NOT NULL,
  `client_id` int NOT NULL,
  `proposal_ref` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `version` int DEFAULT '1',
  `proposal_content` longtext COLLATE utf8mb4_general_ci,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_breakdown` text COLLATE utf8mb4_general_ci,
  `prepared_by` int DEFAULT NULL,
  `prepared_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `manager_approved` tinyint(1) DEFAULT '0',
  `manager_approved_by` int DEFAULT NULL,
  `manager_approved_at` timestamp NULL DEFAULT NULL,
  `manager_signature` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ceo_approved` tinyint(1) DEFAULT '0',
  `ceo_approved_by` int DEFAULT NULL,
  `ceo_approved_at` timestamp NULL DEFAULT NULL,
  `ceo_signature` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company_stamp` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('draft','under_manager_review','under_ceo_review','approved','rejected') COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `file_path` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`proposal_id`, `client_id`, `proposal_ref`, `version`, `proposal_content`, `total_amount`, `payment_breakdown`, `prepared_by`, `prepared_at`, `manager_approved`, `manager_approved_by`, `manager_approved_at`, `manager_signature`, `ceo_approved`, `ceo_approved_by`, `ceo_approved_at`, `ceo_signature`, `company_stamp`, `status`, `file_path`) VALUES
(1, 1, 'PROP-20251201-0001-V1', 1, '\r\n    <!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proposal: PROP-20251201-0001-V1</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .section { margin-bottom: 30px; }\r\n            .section-title { background: #f8f9fa; padding: 12px; font-weight: bold; border-left: 4px solid #007bff; margin-bottom: 15px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 15px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total { font-weight: bold; font-size: 1.2em; background-color: #f8f9fa; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; }\r\n            .signature-box { margin-top: 50px; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFESSIONAL SERVICE PROPOSAL</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Proposal Reference: PROP-20251201-0001-V1</h3>\r\n            <p><strong>Date:</strong> December 1, 2025 | <strong>Valid Until:</strong> December 31, 2025</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Client Information</div>\r\n            <table>\r\n                <tr>\r\n                    <td width=\'30%\'><strong>Company Name:</strong></td>\r\n                    <td>First Client</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Contact Person:</strong></td>\r\n                    <td>Mr. Heg</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Email Address:</strong></td>\r\n                    <td>heg@email.comm</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Phone Number:</strong></td>\r\n                    <td>123456789</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Address:</strong></td>\r\n                    <td>JLT, Dubai., United Arab Emirates</td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Service Details</div>\r\n            <table>\r\n                <tr>\r\n                    <td width=\'30%\'><strong>Service Type:</strong></td>\r\n                    <td>Accounting &amp; Taxation</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Service Description:</strong></td>\r\n                    <td>He need some services. And more.</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Expected Start Date:</strong></td>\r\n                    <td>December 3, 2025</td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Financial Proposal</div>\r\n            <table>\r\n                <thead>\r\n                    <tr>\r\n                        <th>Description</th>\r\n                        <th>Amount (AED)</th>\r\n                    </tr>\r\n                </thead>\r\n                <tbody>\r\n                    <tr>\r\n                        <td>Accounting &amp; Taxation Service</td>\r\n                        <td>350,000.00</td>\r\n                    </tr>\r\n                    <tr class=\'total\'>\r\n                        <td><strong>Total Amount</strong></td>\r\n                        <td><strong>350,000.00</strong></td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Payment Schedule</div>\r\n            <p><strong>Payment Term:</strong> Bi-yearly</p>\r\n            <table>\r\n                <thead>\r\n                    <tr>\r\n                        <th>Installment</th>\r\n                        <th>Due</th>\r\n                        <th>Amount (AED)</th>\r\n                    </tr>\r\n                </thead>\r\n                <tbody><tr>\r\n                    <td>1</td>\r\n                    <td>Half 1</td>\r\n                    <td>175,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Half 2</td>\r\n                    <td>175,000.00</td>\r\n                  </tr></tbody>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Terms & Conditions</div>\r\n            <ol>\r\n                <li>This proposal is valid for 30 days from the date of issue.</li>\r\n                <li>Services will commence upon receipt of the signed proposal and initial payment.</li>\r\n                <li>All payments are to be made in AED.</li>\r\n                <li>Any additional services requested will be billed separately.</li>\r\n                <li>Either party may terminate this agreement with 30 days written notice.</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div class=\'signature-box\'>\r\n                <div style=\'float: left; width: 45%;\'>\r\n                    <p><strong>For OGM Business Consultancy:</strong></p>\r\n                    <br><br><br>\r\n                    <p>_________________________</p>\r\n                    <p>Authorized Signature</p>\r\n                    <p>Name:  </p>\r\n                    <p>Position: Sales Consultant</p>\r\n                    <p>Date: December 1, 2025</p>\r\n                </div>\r\n                \r\n                <div style=\'float: right; width: 45%;\'>\r\n                    <p><strong>Accepted by Client:</strong></p>\r\n                    <br><br><br>\r\n                    <p>_________________________</p>\r\n                    <p>Authorized Signature</p>\r\n                    <p>Name: ___________________</p>\r\n                    <p>Position: ________________</p>\r\n                    <p>Date: ___________________</p>\r\n                </div>\r\n                <div style=\'clear: both;\'></div>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 350000.00, '[{\"installment\":1,\"amount\":\"175,000.00\",\"due_description\":\"Half 1\"},{\"installment\":2,\"amount\":\"175,000.00\",\"due_description\":\"Half 2\"}]', 2, '2025-12-01 13:53:09', 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'draft', '../uploads/proposals/proposal_PROP-20251201-0001-V1.html'),
(2, 1, 'PROP-20251201-0001-V2', 2, '\r\n    <!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proposal: PROP-20251201-0001-V2</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .section { margin-bottom: 30px; }\r\n            .section-title { background: #f8f9fa; padding: 12px; font-weight: bold; border-left: 4px solid #007bff; margin-bottom: 15px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 15px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total { font-weight: bold; font-size: 1.2em; background-color: #f8f9fa; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; }\r\n            .signature-box { margin-top: 50px; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFESSIONAL SERVICE PROPOSAL</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Proposal Reference: PROP-20251201-0001-V2</h3>\r\n            <p><strong>Date:</strong> December 1, 2025 | <strong>Valid Until:</strong> December 31, 2025</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Client Information</div>\r\n            <table>\r\n                <tr>\r\n                    <td width=\'30%\'><strong>Company Name:</strong></td>\r\n                    <td>First Client</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Contact Person:</strong></td>\r\n                    <td>Mr. Heg</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Email Address:</strong></td>\r\n                    <td>heg@email.comm</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Phone Number:</strong></td>\r\n                    <td>123456789</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Address:</strong></td>\r\n                    <td>JLT, Dubai., United Arab Emirates</td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Service Details</div>\r\n            <table>\r\n                <tr>\r\n                    <td width=\'30%\'><strong>Service Type:</strong></td>\r\n                    <td>Accounting &amp; Taxation</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Service Description:</strong></td>\r\n                    <td>He need some services. And more.</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Expected Start Date:</strong></td>\r\n                    <td>December 3, 2025</td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Financial Proposal</div>\r\n            <table>\r\n                <thead>\r\n                    <tr>\r\n                        <th>Description</th>\r\n                        <th>Amount (AED)</th>\r\n                    </tr>\r\n                </thead>\r\n                <tbody>\r\n                    <tr>\r\n                        <td>Accounting &amp; Taxation Service</td>\r\n                        <td>350,000.00</td>\r\n                    </tr>\r\n                    <tr class=\'total\'>\r\n                        <td><strong>Total Amount</strong></td>\r\n                        <td><strong>350,000.00</strong></td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Payment Schedule</div>\r\n            <p><strong>Payment Term:</strong> Bi-yearly</p>\r\n            <table>\r\n                <thead>\r\n                    <tr>\r\n                        <th>Installment</th>\r\n                        <th>Due</th>\r\n                        <th>Amount (AED)</th>\r\n                    </tr>\r\n                </thead>\r\n                <tbody><tr>\r\n                    <td>1</td>\r\n                    <td>Half 1</td>\r\n                    <td>175,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Half 2</td>\r\n                    <td>175,000.00</td>\r\n                  </tr></tbody>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Terms & Conditions</div>\r\n            <ol>\r\n                <li>This proposal is valid for 30 days from the date of issue.</li>\r\n                <li>Services will commence upon receipt of the signed proposal and initial payment.</li>\r\n                <li>All payments are to be made in AED.</li>\r\n                <li>Any additional services requested will be billed separately.</li>\r\n                <li>Either party may terminate this agreement with 30 days written notice.</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div class=\'signature-box\'>\r\n                <div style=\'float: left; width: 45%;\'>\r\n                    <p><strong>For OGM Business Consultancy:</strong></p>\r\n                    <br><br><br>\r\n                    <p>_________________________</p>\r\n                    <p>Authorized Signature</p>\r\n                    <p>Name:  </p>\r\n                    <p>Position: Sales Consultant</p>\r\n                    <p>Date: December 1, 2025</p>\r\n                </div>\r\n                \r\n                <div style=\'float: right; width: 45%;\'>\r\n                    <p><strong>Accepted by Client:</strong></p>\r\n                    <br><br><br>\r\n                    <p>_________________________</p>\r\n                    <p>Authorized Signature</p>\r\n                    <p>Name: ___________________</p>\r\n                    <p>Position: ________________</p>\r\n                    <p>Date: ___________________</p>\r\n                </div>\r\n                <div style=\'clear: both;\'></div>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 350000.00, '[{\"installment\":1,\"amount\":\"175,000.00\",\"due_description\":\"Half 1\"},{\"installment\":2,\"amount\":\"175,000.00\",\"due_description\":\"Half 2\"}]', 2, '2025-12-01 13:54:28', 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'draft', '../uploads/proposals/proposal_PROP-20251201-0001-V2.html'),
(3, 1, 'PROP-20251201-0001-V3', 3, '\r\n    <!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proposal: PROP-20251201-0001-V3</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .section { margin-bottom: 30px; }\r\n            .section-title { background: #f8f9fa; padding: 12px; font-weight: bold; border-left: 4px solid #007bff; margin-bottom: 15px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 15px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total { font-weight: bold; font-size: 1.2em; background-color: #f8f9fa; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; }\r\n            .signature-box { margin-top: 50px; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFESSIONAL SERVICE PROPOSAL</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Proposal Reference: PROP-20251201-0001-V3</h3>\r\n            <p><strong>Date:</strong> December 1, 2025 | <strong>Valid Until:</strong> December 31, 2025</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Client Information</div>\r\n            <table>\r\n                <tr>\r\n                    <td width=\'30%\'><strong>Company Name:</strong></td>\r\n                    <td>First Client</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Contact Person:</strong></td>\r\n                    <td>Mr. Heg</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Email Address:</strong></td>\r\n                    <td>heg@email.comm</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Phone Number:</strong></td>\r\n                    <td>123456789</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Address:</strong></td>\r\n                    <td>JLT, Dubai., United Arab Emirates</td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Service Details</div>\r\n            <table>\r\n                <tr>\r\n                    <td width=\'30%\'><strong>Service Type:</strong></td>\r\n                    <td>Accounting &amp; Taxation</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Service Description:</strong></td>\r\n                    <td>He need some services. And more.</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Expected Start Date:</strong></td>\r\n                    <td>December 3, 2025</td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Financial Proposal</div>\r\n            <table>\r\n                <thead>\r\n                    <tr>\r\n                        <th>Description</th>\r\n                        <th>Amount (AED)</th>\r\n                    </tr>\r\n                </thead>\r\n                <tbody>\r\n                    <tr>\r\n                        <td>Accounting &amp; Taxation Service</td>\r\n                        <td>4,000.00</td>\r\n                    </tr>\r\n                    <tr class=\'total\'>\r\n                        <td><strong>Total Amount</strong></td>\r\n                        <td><strong>4,000.00</strong></td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Payment Schedule</div>\r\n            <p><strong>Payment Term:</strong> Bi-yearly</p>\r\n            <table>\r\n                <thead>\r\n                    <tr>\r\n                        <th>Installment</th>\r\n                        <th>Due</th>\r\n                        <th>Amount (AED)</th>\r\n                    </tr>\r\n                </thead>\r\n                <tbody><tr>\r\n                    <td>1</td>\r\n                    <td>Half 1</td>\r\n                    <td>2,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Half 2</td>\r\n                    <td>2,000.00</td>\r\n                  </tr></tbody>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Terms & Conditions</div>\r\n            <ol>\r\n                <li>This proposal is valid for 30 days from the date of issue.</li>\r\n                <li>Services will commence upon receipt of the signed proposal and initial payment.</li>\r\n                <li>All payments are to be made in AED.</li>\r\n                <li>Any additional services requested will be billed separately.</li>\r\n                <li>Either party may terminate this agreement with 30 days written notice.</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div class=\'signature-box\'>\r\n                <div style=\'float: left; width: 45%;\'>\r\n                    <p><strong>For OGM Business Consultancy:</strong></p>\r\n                    <br><br><br>\r\n                    <p>_________________________</p>\r\n                    <p>Authorized Signature</p>\r\n                    <p>Name:  </p>\r\n                    <p>Position: Sales Consultant</p>\r\n                    <p>Date: December 1, 2025</p>\r\n                </div>\r\n                \r\n                <div style=\'float: right; width: 45%;\'>\r\n                    <p><strong>Accepted by Client:</strong></p>\r\n                    <br><br><br>\r\n                    <p>_________________________</p>\r\n                    <p>Authorized Signature</p>\r\n                    <p>Name: ___________________</p>\r\n                    <p>Position: ________________</p>\r\n                    <p>Date: ___________________</p>\r\n                </div>\r\n                <div style=\'clear: both;\'></div>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 4000.00, '[{\"installment\":1,\"amount\":\"2,000.00\",\"due_description\":\"Half 1\"},{\"installment\":2,\"amount\":\"2,000.00\",\"due_description\":\"Half 2\"}]', 2, '2025-12-01 14:09:53', 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'draft', '../uploads/proposals/proposal_PROP-20251201-0001-V3.html'),
(4, 1, 'PROP-20251202-0001-V4', 4, '\r\n    <!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proposal: PROP-20251202-0001-V4</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .section { margin-bottom: 30px; }\r\n            .section-title { background: #f8f9fa; padding: 12px; font-weight: bold; border-left: 4px solid #007bff; margin-bottom: 15px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 15px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total { font-weight: bold; font-size: 1.2em; background-color: #f8f9fa; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; }\r\n            .signature-box { margin-top: 50px; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFESSIONAL SERVICE PROPOSAL</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Proposal Reference: PROP-20251202-0001-V4</h3>\r\n            <p><strong>Date:</strong> December 2, 2025 | <strong>Valid Until:</strong> January 1, 2026</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Client Information</div>\r\n            <table>\r\n                <tr>\r\n                    <td width=\'30%\'><strong>Company Name:</strong></td>\r\n                    <td>First Client</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Contact Person:</strong></td>\r\n                    <td>Mr. Heg</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Email Address:</strong></td>\r\n                    <td>heg@email.comm</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Phone Number:</strong></td>\r\n                    <td>123456789</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Address:</strong></td>\r\n                    <td>JLT, Dubai., United Arab Emirates</td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Service Details</div>\r\n            <table>\r\n                <tr>\r\n                    <td width=\'30%\'><strong>Service Type:</strong></td>\r\n                    <td>Accounting &amp; Taxation</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Service Description:</strong></td>\r\n                    <td>He need some services. And more.</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Expected Start Date:</strong></td>\r\n                    <td>December 3, 2025</td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Financial Proposal</div>\r\n            <table>\r\n                <thead>\r\n                    <tr>\r\n                        <th>Description</th>\r\n                        <th>Amount (AED)</th>\r\n                    </tr>\r\n                </thead>\r\n                <tbody>\r\n                    <tr>\r\n                        <td>Accounting &amp; Taxation Service</td>\r\n                        <td>4,000.00</td>\r\n                    </tr>\r\n                    <tr class=\'total\'>\r\n                        <td><strong>Total Amount</strong></td>\r\n                        <td><strong>4,000.00</strong></td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Payment Schedule</div>\r\n            <p><strong>Payment Term:</strong> Quarterly</p>\r\n            <table>\r\n                <thead>\r\n                    <tr>\r\n                        <th>Installment</th>\r\n                        <th>Due</th>\r\n                        <th>Amount (AED)</th>\r\n                    </tr>\r\n                </thead>\r\n                <tbody><tr>\r\n                    <td>1</td>\r\n                    <td>Quarter 1</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Quarter 2</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>3</td>\r\n                    <td>Quarter 3</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>4</td>\r\n                    <td>Quarter 4</td>\r\n                    <td>1,000.00</td>\r\n                  </tr></tbody>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Terms & Conditions</div>\r\n            <ol>\r\n                <li>This proposal is valid for 30 days from the date of issue.</li>\r\n                <li>Services will commence upon receipt of the signed proposal and initial payment.</li>\r\n                <li>All payments are to be made in AED.</li>\r\n                <li>Any additional services requested will be billed separately.</li>\r\n                <li>Either party may terminate this agreement with 30 days written notice.</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div class=\'signature-box\'>\r\n                <div style=\'float: left; width: 45%;\'>\r\n                    <p><strong>For OGM Business Consultancy:</strong></p>\r\n                    <br><br><br>\r\n                    <p>_________________________</p>\r\n                    <p>Authorized Signature</p>\r\n                    <p>Name:  </p>\r\n                    <p>Position: Sales Consultant</p>\r\n                    <p>Date: December 2, 2025</p>\r\n                </div>\r\n                \r\n                <div style=\'float: right; width: 45%;\'>\r\n                    <p><strong>Accepted by Client:</strong></p>\r\n                    <br><br><br>\r\n                    <p>_________________________</p>\r\n                    <p>Authorized Signature</p>\r\n                    <p>Name: ___________________</p>\r\n                    <p>Position: ________________</p>\r\n                    <p>Date: ___________________</p>\r\n                </div>\r\n                <div style=\'clear: both;\'></div>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 4000.00, '[{\"installment\":1,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 1\"},{\"installment\":2,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 2\"},{\"installment\":3,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 3\"},{\"installment\":4,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 4\"}]', 2, '2025-12-02 01:01:21', 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'draft', '../uploads/proposals/proposal_PROP-20251202-0001-V4.html'),
(5, 1, 'PROP-20251202-0001-V5', 5, '\r\n    <!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proposal: PROP-20251202-0001-V5</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .section { margin-bottom: 30px; }\r\n            .section-title { background: #f8f9fa; padding: 12px; font-weight: bold; border-left: 4px solid #007bff; margin-bottom: 15px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 15px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total { font-weight: bold; font-size: 1.2em; background-color: #f8f9fa; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; }\r\n            .signature-box { margin-top: 50px; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFESSIONAL SERVICE PROPOSAL</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Proposal Reference: PROP-20251202-0001-V5</h3>\r\n            <p><strong>Date:</strong> December 2, 2025 | <strong>Valid Until:</strong> January 1, 2026</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Client Information</div>\r\n            <table>\r\n                <tr>\r\n                    <td width=\'30%\'><strong>Company Name:</strong></td>\r\n                    <td>First Client</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Contact Person:</strong></td>\r\n                    <td>Mr. Heg</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Email Address:</strong></td>\r\n                    <td>heg@email.comm</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Phone Number:</strong></td>\r\n                    <td>123456789</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Address:</strong></td>\r\n                    <td>JLT, Dubai., United Arab Emirates</td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Service Details</div>\r\n            <table>\r\n                <tr>\r\n                    <td width=\'30%\'><strong>Service Type:</strong></td>\r\n                    <td>Accounting &amp; Taxation</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Service Description:</strong></td>\r\n                    <td>He need some services. And more.</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Expected Start Date:</strong></td>\r\n                    <td>December 3, 2025</td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Financial Proposal</div>\r\n            <table>\r\n                <thead>\r\n                    <tr>\r\n                        <th>Description</th>\r\n                        <th>Amount (AED)</th>\r\n                    </tr>\r\n                </thead>\r\n                <tbody>\r\n                    <tr>\r\n                        <td>Accounting &amp; Taxation Service</td>\r\n                        <td>4,000.00</td>\r\n                    </tr>\r\n                    <tr class=\'total\'>\r\n                        <td><strong>Total Amount</strong></td>\r\n                        <td><strong>4,000.00</strong></td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Payment Schedule</div>\r\n            <p><strong>Payment Term:</strong> Quarterly</p>\r\n            <table>\r\n                <thead>\r\n                    <tr>\r\n                        <th>Installment</th>\r\n                        <th>Due</th>\r\n                        <th>Amount (AED)</th>\r\n                    </tr>\r\n                </thead>\r\n                <tbody><tr>\r\n                    <td>1</td>\r\n                    <td>Quarter 1</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Quarter 2</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>3</td>\r\n                    <td>Quarter 3</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>4</td>\r\n                    <td>Quarter 4</td>\r\n                    <td>1,000.00</td>\r\n                  </tr></tbody>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Terms & Conditions</div>\r\n            <ol>\r\n                <li>This proposal is valid for 30 days from the date of issue.</li>\r\n                <li>Services will commence upon receipt of the signed proposal and initial payment.</li>\r\n                <li>All payments are to be made in AED.</li>\r\n                <li>Any additional services requested will be billed separately.</li>\r\n                <li>Either party may terminate this agreement with 30 days written notice.</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div class=\'signature-box\'>\r\n                <div style=\'float: left; width: 45%;\'>\r\n                    <p><strong>For OGM Business Consultancy:</strong></p>\r\n                    <br><br><br>\r\n                    <p>_________________________</p>\r\n                    <p>Authorized Signature</p>\r\n                    <p>Name:  </p>\r\n                    <p>Position: Sales Consultant</p>\r\n                    <p>Date: December 2, 2025</p>\r\n                </div>\r\n                \r\n                <div style=\'float: right; width: 45%;\'>\r\n                    <p><strong>Accepted by Client:</strong></p>\r\n                    <br><br><br>\r\n                    <p>_________________________</p>\r\n                    <p>Authorized Signature</p>\r\n                    <p>Name: ___________________</p>\r\n                    <p>Position: ________________</p>\r\n                    <p>Date: ___________________</p>\r\n                </div>\r\n                <div style=\'clear: both;\'></div>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 4000.00, '[{\"installment\":1,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 1\"},{\"installment\":2,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 2\"},{\"installment\":3,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 3\"},{\"installment\":4,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 4\"}]', 2, '2025-12-02 02:09:53', 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'draft', '../uploads/proposals/proposal_PROP-20251202-0001-V5.html'),
(6, 1, 'PROP-20251202-0001-V6', 6, '\r\n    <!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proposal: PROP-20251202-0001-V6</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .section { margin-bottom: 30px; }\r\n            .section-title { background: #f8f9fa; padding: 12px; font-weight: bold; border-left: 4px solid #007bff; margin-bottom: 15px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 15px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total { font-weight: bold; font-size: 1.2em; background-color: #f8f9fa; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; }\r\n            .signature-box { margin-top: 50px; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFESSIONAL SERVICE PROPOSAL</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Proposal Reference: PROP-20251202-0001-V6</h3>\r\n            <p><strong>Date:</strong> December 2, 2025 | <strong>Valid Until:</strong> January 1, 2026</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Client Information</div>\r\n            <table>\r\n                <tr>\r\n                    <td width=\'30%\'><strong>Company Name:</strong></td>\r\n                    <td>First Client</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Contact Person:</strong></td>\r\n                    <td>Mr. Heg</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Email Address:</strong></td>\r\n                    <td>heg@email.comm</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Phone Number:</strong></td>\r\n                    <td>123456789</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Address:</strong></td>\r\n                    <td>JLT, Dubai., United Arab Emirates</td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Service Details</div>\r\n            <table>\r\n                <tr>\r\n                    <td width=\'30%\'><strong>Service Type:</strong></td>\r\n                    <td>Accounting &amp; Taxation</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Service Description:</strong></td>\r\n                    <td>He need some services. And more.</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Expected Start Date:</strong></td>\r\n                    <td>December 3, 2025</td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Financial Proposal</div>\r\n            <table>\r\n                <thead>\r\n                    <tr>\r\n                        <th>Description</th>\r\n                        <th>Amount (AED)</th>\r\n                    </tr>\r\n                </thead>\r\n                <tbody>\r\n                    <tr>\r\n                        <td>Accounting &amp; Taxation Service</td>\r\n                        <td>4,000.00</td>\r\n                    </tr>\r\n                    <tr class=\'total\'>\r\n                        <td><strong>Total Amount</strong></td>\r\n                        <td><strong>4,000.00</strong></td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Payment Schedule</div>\r\n            <p><strong>Payment Term:</strong> Quarterly</p>\r\n            <table>\r\n                <thead>\r\n                    <tr>\r\n                        <th>Installment</th>\r\n                        <th>Due</th>\r\n                        <th>Amount (AED)</th>\r\n                    </tr>\r\n                </thead>\r\n                <tbody><tr>\r\n                    <td>1</td>\r\n                    <td>Quarter 1</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Quarter 2</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>3</td>\r\n                    <td>Quarter 3</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>4</td>\r\n                    <td>Quarter 4</td>\r\n                    <td>1,000.00</td>\r\n                  </tr></tbody>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Terms & Conditions</div>\r\n            <ol>\r\n                <li>This proposal is valid for 30 days from the date of issue.</li>\r\n                <li>Services will commence upon receipt of the signed proposal and initial payment.</li>\r\n                <li>All payments are to be made in AED.</li>\r\n                <li>Any additional services requested will be billed separately.</li>\r\n                <li>Either party may terminate this agreement with 30 days written notice.</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div class=\'signature-box\'>\r\n                <div style=\'float: left; width: 45%;\'>\r\n                    <p><strong>For OGM Business Consultancy:</strong></p>\r\n                    <br><br><br>\r\n                    <p>_________________________</p>\r\n                    <p>Authorized Signature</p>\r\n                    <p>Name:  </p>\r\n                    <p>Position: Sales Consultant</p>\r\n                    <p>Date: December 2, 2025</p>\r\n                </div>\r\n                \r\n                <div style=\'float: right; width: 45%;\'>\r\n                    <p><strong>Accepted by Client:</strong></p>\r\n                    <br><br><br>\r\n                    <p>_________________________</p>\r\n                    <p>Authorized Signature</p>\r\n                    <p>Name: ___________________</p>\r\n                    <p>Position: ________________</p>\r\n                    <p>Date: ___________________</p>\r\n                </div>\r\n                <div style=\'clear: both;\'></div>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 4000.00, '[{\"installment\":1,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 1\"},{\"installment\":2,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 2\"},{\"installment\":3,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 3\"},{\"installment\":4,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 4\"}]', 2, '2025-12-02 10:38:45', 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'draft', '../uploads/proposals/proposal_PROP-20251202-0001-V6.html');
INSERT INTO `proposals` (`proposal_id`, `client_id`, `proposal_ref`, `version`, `proposal_content`, `total_amount`, `payment_breakdown`, `prepared_by`, `prepared_at`, `manager_approved`, `manager_approved_by`, `manager_approved_at`, `manager_signature`, `ceo_approved`, `ceo_approved_by`, `ceo_approved_at`, `ceo_signature`, `company_stamp`, `status`, `file_path`) VALUES
(7, 4, 'PROP-20260227-0004-V1', 1, '\n    <!DOCTYPE html>\n    <html>\n    <head>\n        <meta charset=\'UTF-8\'>\n        <title>Proposal: PROP-20260227-0004-V1</title>\n        <style>\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\n            .section { margin-bottom: 30px; }\n            .section-title { background: #f8f9fa; padding: 12px; font-weight: bold; border-left: 4px solid #007bff; margin-bottom: 15px; }\n            table { width: 100%; border-collapse: collapse; margin: 15px 0; }\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\n            th { background-color: #f2f2f2; font-weight: bold; }\n            .total { font-weight: bold; font-size: 1.2em; background-color: #f8f9fa; }\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; }\n            .signature-box { margin-top: 50px; }\n            .company-logo { text-align: center; margin-bottom: 20px; }\n        </style>\n    </head>\n    <body>\n        <div class=\'company-logo\'>\n            <h1>OGM BUSINESS CONSULTANCY</h1>\n            <h2>PROFESSIONAL SERVICE PROPOSAL</h2>\n        </div>\n        \n        <div class=\'header\'>\n            <h3>Proposal Reference: PROP-20260227-0004-V1</h3>\n            <p><strong>Date:</strong> February 27, 2026 | <strong>Valid Until:</strong> March 29, 2026</p>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Client Information</div>\n            <table>\n                <tr>\n                    <td width=\'30%\'><strong>Company Name:</strong></td>\n                    <td>Theta-X</td>\n                </tr>\n                <tr>\n                    <td><strong>Contact Person:</strong></td>\n                    <td>Joe</td>\n                </tr>\n                <tr>\n                    <td><strong>Email Address:</strong></td>\n                    <td>otaksiclients@gmail.com</td>\n                </tr>\n                <tr>\n                    <td><strong>Phone Number:</strong></td>\n                    <td>09090909</td>\n                </tr>\n                <tr>\n                    <td><strong>Address:</strong></td>\n                    <td>tueffg, United Arab Emirates</td>\n                </tr>\n            </table>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Service Details</div>\n            <table>\n                <tr>\n                    <td width=\'30%\'><strong>Service Type:</strong></td>\n                    <td>Business Setup</td>\n                </tr>\n                <tr>\n                    <td><strong>Service Description:</strong></td>\n                    <td>good</td>\n                </tr>\n                <tr>\n                    <td><strong>Expected Start Date:</strong></td>\n                    <td>February 27, 2026</td>\n                </tr>\n            </table>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Financial Proposal</div>\n            <table>\n                <thead>\n                    <tr>\n                        <th>Description</th>\n                        <th>Amount (AED)</th>\n                    </tr>\n                </thead>\n                <tbody>\n                    <tr>\n                        <td>Business Setup Service</td>\n                        <td>455.00</td>\n                    </tr>\n                    <tr class=\'total\'>\n                        <td><strong>Total Amount</strong></td>\n                        <td><strong>455.00</strong></td>\n                    </tr>\n                </tbody>\n            </table>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Payment Schedule</div>\n            <p><strong>Payment Term:</strong> Monthly</p>\n            <table>\n                <thead>\n                    <tr>\n                        <th>Installment</th>\n                        <th>Due</th>\n                        <th>Amount (AED)</th>\n                    </tr>\n                </thead>\n                <tbody><tr>\n                    <td>1</td>\n                    <td>Month 1</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>2</td>\n                    <td>Month 2</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>3</td>\n                    <td>Month 3</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>4</td>\n                    <td>Month 4</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>5</td>\n                    <td>Month 5</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>6</td>\n                    <td>Month 6</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>7</td>\n                    <td>Month 7</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>8</td>\n                    <td>Month 8</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>9</td>\n                    <td>Month 9</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>10</td>\n                    <td>Month 10</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>11</td>\n                    <td>Month 11</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>12</td>\n                    <td>Month 12</td>\n                    <td>37.92</td>\n                  </tr></tbody>\n            </table>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Terms & Conditions</div>\n            <ol>\n                <li>This proposal is valid for 30 days from the date of issue.</li>\n                <li>Services will commence upon receipt of the signed proposal and initial payment.</li>\n                <li>All payments are to be made in AED.</li>\n                <li>Any additional services requested will be billed separately.</li>\n                <li>Either party may terminate this agreement with 30 days written notice.</li>\n            </ol>\n        </div>\n        \n        <div class=\'footer\'>\n            <div class=\'signature-box\'>\n                <div style=\'float: left; width: 45%;\'>\n                    <p><strong>For OGM Business Consultancy:</strong></p>\n                    <br><br><br>\n                    <p>_________________________</p>\n                    <p>Authorized Signature</p>\n                    <p>Name:  </p>\n                    <p>Position: Sales Consultant</p>\n                    <p>Date: February 27, 2026</p>\n                </div>\n                \n                <div style=\'float: right; width: 45%;\'>\n                    <p><strong>Accepted by Client:</strong></p>\n                    <br><br><br>\n                    <p>_________________________</p>\n                    <p>Authorized Signature</p>\n                    <p>Name: ___________________</p>\n                    <p>Position: ________________</p>\n                    <p>Date: ___________________</p>\n                </div>\n                <div style=\'clear: both;\'></div>\n            </div>\n        </div>\n    </body>\n    </html>', 455.00, '[{\"installment\":1,\"amount\":\"37.92\",\"due_description\":\"Month 1\"},{\"installment\":2,\"amount\":\"37.92\",\"due_description\":\"Month 2\"},{\"installment\":3,\"amount\":\"37.92\",\"due_description\":\"Month 3\"},{\"installment\":4,\"amount\":\"37.92\",\"due_description\":\"Month 4\"},{\"installment\":5,\"amount\":\"37.92\",\"due_description\":\"Month 5\"},{\"installment\":6,\"amount\":\"37.92\",\"due_description\":\"Month 6\"},{\"installment\":7,\"amount\":\"37.92\",\"due_description\":\"Month 7\"},{\"installment\":8,\"amount\":\"37.92\",\"due_description\":\"Month 8\"},{\"installment\":9,\"amount\":\"37.92\",\"due_description\":\"Month 9\"},{\"installment\":10,\"amount\":\"37.92\",\"due_description\":\"Month 10\"},{\"installment\":11,\"amount\":\"37.92\",\"due_description\":\"Month 11\"},{\"installment\":12,\"amount\":\"37.92\",\"due_description\":\"Month 12\"}]', 9, '2026-02-27 13:39:19', 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'draft', '../uploads/proposals/proposal_PROP-20260227-0004-V1.html'),
(8, 4, 'PROP-20260227-0004-V2', 2, '\n    <!DOCTYPE html>\n    <html>\n    <head>\n        <meta charset=\'UTF-8\'>\n        <title>Proposal: PROP-20260227-0004-V2</title>\n        <style>\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\n            .section { margin-bottom: 30px; }\n            .section-title { background: #f8f9fa; padding: 12px; font-weight: bold; border-left: 4px solid #007bff; margin-bottom: 15px; }\n            table { width: 100%; border-collapse: collapse; margin: 15px 0; }\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\n            th { background-color: #f2f2f2; font-weight: bold; }\n            .total { font-weight: bold; font-size: 1.2em; background-color: #f8f9fa; }\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; }\n            .signature-box { margin-top: 50px; }\n            .company-logo { text-align: center; margin-bottom: 20px; }\n        </style>\n    </head>\n    <body>\n        <div class=\'company-logo\'>\n            <h1>OGM BUSINESS CONSULTANCY</h1>\n            <h2>PROFESSIONAL SERVICE PROPOSAL</h2>\n        </div>\n        \n        <div class=\'header\'>\n            <h3>Proposal Reference: PROP-20260227-0004-V2</h3>\n            <p><strong>Date:</strong> February 27, 2026 | <strong>Valid Until:</strong> March 29, 2026</p>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Client Information</div>\n            <table>\n                <tr>\n                    <td width=\'30%\'><strong>Company Name:</strong></td>\n                    <td>Theta-X</td>\n                </tr>\n                <tr>\n                    <td><strong>Contact Person:</strong></td>\n                    <td>Joe</td>\n                </tr>\n                <tr>\n                    <td><strong>Email Address:</strong></td>\n                    <td>otaksiclients@gmail.com</td>\n                </tr>\n                <tr>\n                    <td><strong>Phone Number:</strong></td>\n                    <td>09090909</td>\n                </tr>\n                <tr>\n                    <td><strong>Address:</strong></td>\n                    <td>tueffg, United Arab Emirates</td>\n                </tr>\n            </table>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Service Details</div>\n            <table>\n                <tr>\n                    <td width=\'30%\'><strong>Service Type:</strong></td>\n                    <td>Business Setup</td>\n                </tr>\n                <tr>\n                    <td><strong>Service Description:</strong></td>\n                    <td>good</td>\n                </tr>\n                <tr>\n                    <td><strong>Expected Start Date:</strong></td>\n                    <td>February 27, 2026</td>\n                </tr>\n            </table>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Financial Proposal</div>\n            <table>\n                <thead>\n                    <tr>\n                        <th>Description</th>\n                        <th>Amount (AED)</th>\n                    </tr>\n                </thead>\n                <tbody>\n                    <tr>\n                        <td>Business Setup Service</td>\n                        <td>455.00</td>\n                    </tr>\n                    <tr class=\'total\'>\n                        <td><strong>Total Amount</strong></td>\n                        <td><strong>455.00</strong></td>\n                    </tr>\n                </tbody>\n            </table>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Payment Schedule</div>\n            <p><strong>Payment Term:</strong> Monthly</p>\n            <table>\n                <thead>\n                    <tr>\n                        <th>Installment</th>\n                        <th>Due</th>\n                        <th>Amount (AED)</th>\n                    </tr>\n                </thead>\n                <tbody><tr>\n                    <td>1</td>\n                    <td>Month 1</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>2</td>\n                    <td>Month 2</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>3</td>\n                    <td>Month 3</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>4</td>\n                    <td>Month 4</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>5</td>\n                    <td>Month 5</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>6</td>\n                    <td>Month 6</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>7</td>\n                    <td>Month 7</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>8</td>\n                    <td>Month 8</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>9</td>\n                    <td>Month 9</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>10</td>\n                    <td>Month 10</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>11</td>\n                    <td>Month 11</td>\n                    <td>37.92</td>\n                  </tr><tr>\n                    <td>12</td>\n                    <td>Month 12</td>\n                    <td>37.92</td>\n                  </tr></tbody>\n            </table>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Terms & Conditions</div>\n            <ol>\n                <li>This proposal is valid for 30 days from the date of issue.</li>\n                <li>Services will commence upon receipt of the signed proposal and initial payment.</li>\n                <li>All payments are to be made in AED.</li>\n                <li>Any additional services requested will be billed separately.</li>\n                <li>Either party may terminate this agreement with 30 days written notice.</li>\n            </ol>\n        </div>\n        \n        <div class=\'footer\'>\n            <div class=\'signature-box\'>\n                <div style=\'float: left; width: 45%;\'>\n                    <p><strong>For OGM Business Consultancy:</strong></p>\n                    <br><br><br>\n                    <p>_________________________</p>\n                    <p>Authorized Signature</p>\n                    <p>Name:  </p>\n                    <p>Position: Sales Consultant</p>\n                    <p>Date: February 27, 2026</p>\n                </div>\n                \n                <div style=\'float: right; width: 45%;\'>\n                    <p><strong>Accepted by Client:</strong></p>\n                    <br><br><br>\n                    <p>_________________________</p>\n                    <p>Authorized Signature</p>\n                    <p>Name: ___________________</p>\n                    <p>Position: ________________</p>\n                    <p>Date: ___________________</p>\n                </div>\n                <div style=\'clear: both;\'></div>\n            </div>\n        </div>\n    </body>\n    </html>', 455.00, '[{\"installment\":1,\"amount\":\"37.92\",\"due_description\":\"Month 1\"},{\"installment\":2,\"amount\":\"37.92\",\"due_description\":\"Month 2\"},{\"installment\":3,\"amount\":\"37.92\",\"due_description\":\"Month 3\"},{\"installment\":4,\"amount\":\"37.92\",\"due_description\":\"Month 4\"},{\"installment\":5,\"amount\":\"37.92\",\"due_description\":\"Month 5\"},{\"installment\":6,\"amount\":\"37.92\",\"due_description\":\"Month 6\"},{\"installment\":7,\"amount\":\"37.92\",\"due_description\":\"Month 7\"},{\"installment\":8,\"amount\":\"37.92\",\"due_description\":\"Month 8\"},{\"installment\":9,\"amount\":\"37.92\",\"due_description\":\"Month 9\"},{\"installment\":10,\"amount\":\"37.92\",\"due_description\":\"Month 10\"},{\"installment\":11,\"amount\":\"37.92\",\"due_description\":\"Month 11\"},{\"installment\":12,\"amount\":\"37.92\",\"due_description\":\"Month 12\"}]', 9, '2026-02-27 14:30:58', 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'draft', '../uploads/proposals/proposal_PROP-20260227-0004-V2.html'),
(9, 3, 'PROP-20260227-0003-V1', 1, '\n    <!DOCTYPE html>\n    <html>\n    <head>\n        <meta charset=\'UTF-8\'>\n        <title>Proposal: PROP-20260227-0003-V1</title>\n        <style>\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\n            .section { margin-bottom: 30px; }\n            .section-title { background: #f8f9fa; padding: 12px; font-weight: bold; border-left: 4px solid #007bff; margin-bottom: 15px; }\n            table { width: 100%; border-collapse: collapse; margin: 15px 0; }\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\n            th { background-color: #f2f2f2; font-weight: bold; }\n            .total { font-weight: bold; font-size: 1.2em; background-color: #f8f9fa; }\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; }\n            .signature-box { margin-top: 50px; }\n            .company-logo { text-align: center; margin-bottom: 20px; }\n        </style>\n    </head>\n    <body>\n        <div class=\'company-logo\'>\n            <h1>OGM BUSINESS CONSULTANCY</h1>\n            <h2>PROFESSIONAL SERVICE PROPOSAL</h2>\n        </div>\n        \n        <div class=\'header\'>\n            <h3>Proposal Reference: PROP-20260227-0003-V1</h3>\n            <p><strong>Date:</strong> February 27, 2026 | <strong>Valid Until:</strong> March 29, 2026</p>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Client Information</div>\n            <table>\n                <tr>\n                    <td width=\'30%\'><strong>Company Name:</strong></td>\n                    <td>Theta-X</td>\n                </tr>\n                <tr>\n                    <td><strong>Contact Person:</strong></td>\n                    <td>Otaksi Clients</td>\n                </tr>\n                <tr>\n                    <td><strong>Email Address:</strong></td>\n                    <td>otaksiclients@gmail.com</td>\n                </tr>\n                <tr>\n                    <td><strong>Phone Number:</strong></td>\n                    <td>09090909</td>\n                </tr>\n                <tr>\n                    <td><strong>Address:</strong></td>\n                    <td>ertyui765, Bahrain</td>\n                </tr>\n            </table>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Service Details</div>\n            <table>\n                <tr>\n                    <td width=\'30%\'><strong>Service Type:</strong></td>\n                    <td>Business Setup</td>\n                </tr>\n                <tr>\n                    <td><strong>Service Description:</strong></td>\n                    <td>trewtyujhg</td>\n                </tr>\n                <tr>\n                    <td><strong>Expected Start Date:</strong></td>\n                    <td>February 27, 2026</td>\n                </tr>\n            </table>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Financial Proposal</div>\n            <table>\n                <thead>\n                    <tr>\n                        <th>Description</th>\n                        <th>Amount (AED)</th>\n                    </tr>\n                </thead>\n                <tbody>\n                    <tr>\n                        <td>Business Setup Service</td>\n                        <td>344.00</td>\n                    </tr>\n                    <tr class=\'total\'>\n                        <td><strong>Total Amount</strong></td>\n                        <td><strong>344.00</strong></td>\n                    </tr>\n                </tbody>\n            </table>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Payment Schedule</div>\n            <p><strong>Payment Term:</strong> Monthly</p>\n            <table>\n                <thead>\n                    <tr>\n                        <th>Installment</th>\n                        <th>Due</th>\n                        <th>Amount (AED)</th>\n                    </tr>\n                </thead>\n                <tbody><tr>\n                    <td>1</td>\n                    <td>Month 1</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>2</td>\n                    <td>Month 2</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>3</td>\n                    <td>Month 3</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>4</td>\n                    <td>Month 4</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>5</td>\n                    <td>Month 5</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>6</td>\n                    <td>Month 6</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>7</td>\n                    <td>Month 7</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>8</td>\n                    <td>Month 8</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>9</td>\n                    <td>Month 9</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>10</td>\n                    <td>Month 10</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>11</td>\n                    <td>Month 11</td>\n                    <td>28.67</td>\n                  </tr><tr>\n                    <td>12</td>\n                    <td>Month 12</td>\n                    <td>28.67</td>\n                  </tr></tbody>\n            </table>\n        </div>\n        \n        <div class=\'section\'>\n            <div class=\'section-title\'>Terms & Conditions</div>\n            <ol>\n                <li>This proposal is valid for 30 days from the date of issue.</li>\n                <li>Services will commence upon receipt of the signed proposal and initial payment.</li>\n                <li>All payments are to be made in AED.</li>\n                <li>Any additional services requested will be billed separately.</li>\n                <li>Either party may terminate this agreement with 30 days written notice.</li>\n            </ol>\n        </div>\n        \n        <div class=\'footer\'>\n            <div class=\'signature-box\'>\n                <div style=\'float: left; width: 45%;\'>\n                    <p><strong>For OGM Business Consultancy:</strong></p>\n                    <br><br><br>\n                    <p>_________________________</p>\n                    <p>Authorized Signature</p>\n                    <p>Name:  </p>\n                    <p>Position: Sales Consultant</p>\n                    <p>Date: February 27, 2026</p>\n                </div>\n                \n                <div style=\'float: right; width: 45%;\'>\n                    <p><strong>Accepted by Client:</strong></p>\n                    <br><br><br>\n                    <p>_________________________</p>\n                    <p>Authorized Signature</p>\n                    <p>Name: ___________________</p>\n                    <p>Position: ________________</p>\n                    <p>Date: ___________________</p>\n                </div>\n                <div style=\'clear: both;\'></div>\n            </div>\n        </div>\n    </body>\n    </html>', 344.00, '[{\"installment\":1,\"amount\":\"28.67\",\"due_description\":\"Month 1\"},{\"installment\":2,\"amount\":\"28.67\",\"due_description\":\"Month 2\"},{\"installment\":3,\"amount\":\"28.67\",\"due_description\":\"Month 3\"},{\"installment\":4,\"amount\":\"28.67\",\"due_description\":\"Month 4\"},{\"installment\":5,\"amount\":\"28.67\",\"due_description\":\"Month 5\"},{\"installment\":6,\"amount\":\"28.67\",\"due_description\":\"Month 6\"},{\"installment\":7,\"amount\":\"28.67\",\"due_description\":\"Month 7\"},{\"installment\":8,\"amount\":\"28.67\",\"due_description\":\"Month 8\"},{\"installment\":9,\"amount\":\"28.67\",\"due_description\":\"Month 9\"},{\"installment\":10,\"amount\":\"28.67\",\"due_description\":\"Month 10\"},{\"installment\":11,\"amount\":\"28.67\",\"due_description\":\"Month 11\"},{\"installment\":12,\"amount\":\"28.67\",\"due_description\":\"Month 12\"}]', 9, '2026-02-27 17:54:16', 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'draft', '../uploads/proposals/proposal_PROP-20260227-0003-V1.html');

-- --------------------------------------------------------

--
-- Table structure for table `proposal_reviews`
--

CREATE TABLE `proposal_reviews` (
  `review_id` int NOT NULL,
  `proposal_id` int NOT NULL,
  `client_id` int NOT NULL,
  `reviewed_by` int NOT NULL,
  `reviewer_role` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `review_notes` text COLLATE utf8mb4_general_ci,
  `checklist_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `signature_data` text COLLATE utf8mb4_general_ci,
  `company_stamp` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_general_ci,
  `rejection_action` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `review_result` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `reviewed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `proposal_reviews`
--

INSERT INTO `proposal_reviews` (`review_id`, `proposal_id`, `client_id`, `reviewed_by`, `reviewer_role`, `review_notes`, `checklist_items`, `signature_data`, `company_stamp`, `rejection_reason`, `rejection_action`, `review_result`, `reviewed_at`) VALUES
(1, 5, 1, 2, 'admin', 'Nothing more, all points verified.', '[\"company_name_correct\",\"contact_details_correct\",\"address_complete\",\"country_specified\",\"service_type_correct\",\"description_clear\",\"scope_defined\",\"start_date_realistic\",\"amount_correct\",\"currency_correct\",\"payment_term_appropriate\",\"breakdown_correct\",\"terms_complete\",\"validity_period\",\"company_info_complete\",\"references_correct\"]', '', '', NULL, NULL, 'approved', '2025-12-02 04:05:12'),
(2, 5, 1, 2, 'admin', 'Nothing more, all points verified.', '[\"company_name_correct\",\"contact_details_correct\",\"address_complete\",\"country_specified\",\"service_type_correct\",\"description_clear\",\"scope_defined\",\"start_date_realistic\",\"amount_correct\",\"currency_correct\",\"payment_term_appropriate\",\"breakdown_correct\",\"terms_complete\",\"validity_period\",\"company_info_complete\",\"references_correct\"]', '', '', NULL, NULL, 'approved', '2025-12-02 04:09:26'),
(3, 5, 1, 2, 'admin', 'Nothing more, all good.', '[\"company_name_correct\",\"contact_details_correct\",\"address_complete\",\"country_specified\",\"service_type_correct\",\"description_clear\",\"scope_defined\",\"start_date_realistic\",\"amount_correct\",\"currency_correct\",\"payment_term_appropriate\",\"breakdown_correct\",\"terms_complete\",\"validity_period\",\"company_info_complete\",\"references_correct\"]', '', '', NULL, NULL, 'approved', '2025-12-02 04:12:40'),
(4, 5, 1, 2, 'admin', 'Not done.', '[\"company_name_correct\",\"contact_details_correct\",\"address_complete\",\"country_specified\",\"service_type_correct\",\"description_clear\",\"scope_defined\",\"start_date_realistic\",\"amount_correct\",\"currency_correct\",\"payment_term_appropriate\",\"breakdown_correct\",\"terms_complete\",\"validity_period\",\"company_info_complete\",\"references_correct\"]', '', '', NULL, NULL, 'approved', '2025-12-02 04:17:56');

-- --------------------------------------------------------

--
-- Table structure for table `quarterly_payouts`
--

CREATE TABLE `quarterly_payouts` (
  `payout_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `year` int NOT NULL,
  `quarter` int NOT NULL,
  `total_cashable_points` int NOT NULL,
  `cash_amount` decimal(10,2) NOT NULL,
  `status` enum('PENDING','APPROVED','PAID','REJECTED') DEFAULT 'PENDING',
  `approved_by` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `payroll_reference` varchar(100) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_increment_bands`
--

CREATE TABLE `salary_increment_bands` (
  `band_id` int NOT NULL,
  `department_type` enum('OPERATIONS','SALES') NOT NULL,
  `min_performance` decimal(5,2) NOT NULL,
  `max_performance` decimal(5,2) NOT NULL,
  `increment_percentage` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `salary_increment_bands`
--

INSERT INTO `salary_increment_bands` (`band_id`, `department_type`, `min_performance`, `max_performance`, `increment_percentage`, `is_active`, `created_at`) VALUES
(1, 'OPERATIONS', 65.00, 74.00, 20.00, 1, '2026-02-28 03:35:44'),
(2, 'OPERATIONS', 75.00, 85.00, 30.00, 1, '2026-02-28 03:35:44'),
(3, 'OPERATIONS', 86.00, 100.00, 35.00, 1, '2026-02-28 03:35:44'),
(4, 'SALES', 65.00, 80.00, 25.00, 1, '2026-02-28 03:35:44'),
(5, 'SALES', 81.00, 90.00, 30.00, 1, '2026-02-28 03:35:44'),
(6, 'SALES', 91.00, 100.00, 35.00, 1, '2026-02-28 03:35:44');

-- --------------------------------------------------------

--
-- Table structure for table `sales_targets`
--

CREATE TABLE `sales_targets` (
  `target_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `year` int NOT NULL,
  `month` int NOT NULL,
  `target_value` decimal(15,2) NOT NULL,
  `actual_value` decimal(15,2) DEFAULT NULL,
  `attainment_percentage` decimal(5,2) DEFAULT NULL,
  `points_awarded` int DEFAULT NULL,
  `evidence_file` varchar(255) DEFAULT NULL,
  `validated_by` int DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  `validation_notes` text,
  `status` enum('PENDING','SUBMITTED','VALIDATED','REJECTED') DEFAULT 'PENDING',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_target_bands`
--

CREATE TABLE `sales_target_bands` (
  `band_id` int NOT NULL,
  `min_attainment` decimal(5,2) NOT NULL,
  `max_attainment` decimal(5,2) NOT NULL,
  `points_awarded` int NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sales_target_bands`
--

INSERT INTO `sales_target_bands` (`band_id`, `min_attainment`, `max_attainment`, `points_awarded`, `is_active`, `created_at`) VALUES
(1, 100.00, 999.00, 1000, 1, '2026-02-28 03:34:50'),
(2, 75.00, 99.99, 750, 1, '2026-02-28 03:34:50'),
(3, 50.00, 74.99, 500, 1, '2026-02-28 03:34:50'),
(4, 0.00, 49.99, 250, 1, '2026-02-28 03:34:50');

-- --------------------------------------------------------

--
-- Table structure for table `service_point_rules`
--

CREATE TABLE `service_point_rules` (
  `rule_id` int NOT NULL,
  `service_id` int NOT NULL,
  `rule_version` int NOT NULL,
  `base_points` int NOT NULL,
  `penalty_type` enum('linear','threshold','fixed') NOT NULL,
  `penalty_value` int DEFAULT NULL,
  `penalty_unit` enum('day','5days','10days') NOT NULL,
  `threshold_days` int DEFAULT NULL,
  `threshold_award` int DEFAULT NULL,
  `floor_points` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `effective_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `service_point_rules`
--

INSERT INTO `service_point_rules` (`rule_id`, `service_id`, `rule_version`, `base_points`, `penalty_type`, `penalty_value`, `penalty_unit`, `threshold_days`, `threshold_award`, `floor_points`, `is_active`, `created_by`, `effective_date`, `created_at`) VALUES
(1, 1, 1, 100, 'linear', 25, '10days', NULL, NULL, 0, 1, 9, '2026-02-28', '2026-02-28 04:04:55');

-- --------------------------------------------------------

--
-- Table structure for table `service_types`
--

CREATE TABLE `service_types` (
  `service_id` int NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `service_category` enum('bookkeeping','audit','tax','registration','setup','other') NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `service_types`
--

INSERT INTO `service_types` (`service_id`, `service_name`, `service_category`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Vat Return Filing', 'bookkeeping', 1, 9, '2026-02-28 04:03:38', '2026-02-28 04:04:07');

-- --------------------------------------------------------

--
-- Table structure for table `signatures`
--

CREATE TABLE `signatures` (
  `signature_id` int NOT NULL,
  `user_id` int NOT NULL,
  `signature_data` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `signature_type` enum('digital','upload') COLLATE utf8mb4_general_ci DEFAULT 'digital',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stamps`
--

CREATE TABLE `stamps` (
  `stamp_id` int NOT NULL,
  `stamp_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `stamp_data` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `uploaded_by` int DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `user_image` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `user_role` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'subscriber',
  `role_id` int DEFAULT NULL,
  `type_id` int DEFAULT NULL,
  `user_status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `user_email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `user_image`, `user_role`, `role_id`, `type_id`, `user_status`, `username`, `user_email`, `password`, `created_at`) VALUES
(2, 'New1', 'Joiner', 'profile_2_1757270810.jpg', 'admin', NULL, NULL, 'active', 'New', 'abc@email.com', '$2y$10$umjaCOlAIDTqyZ2UlvviMuB8BVh0z.0ckCTVCCaQHvMqgQxQgBp8q', '2025-09-07 15:27:08'),
(3, '', '', '', 'subscriber', NULL, NULL, 'active', 'Yo', 'xyz@email.com', '$2y$10$YbRTATseEXc4h6DEywJpg.HaGbq4SXMaPK.S6YG8HV0RjW05waUzG', '2025-09-07 22:21:41'),
(4, 'Tom', 'Odai', '', 'admin', NULL, 1, 'active', 'Tom', 'aaaa.tom20@gmail.com', '$2y$10$TzxTERCQAAYKuQG7enxJbupBqeiunI35.46aiE4Fkxw/OI15l849O', '2025-12-18 04:28:14'),
(7, 'Otaksi', 'Clients', '', 'subscriber', 4, 1, 'active', 'Joiner', 'zzz@email.com', '$2y$10$ObCMY5b2Q174a7zyVL1x1OvRM0AdouCOi9UdNtlwbraRXJhjuXYPC', '2025-12-18 18:04:17'),
(9, 'Habibi', 'Habibi', 'default.jpg', 'admin', 1, NULL, 'active', 'Habibi', 'otaksiclients@gmail.com', '$2y$10$RQTTbSzuBe3WYKOzbr4RUuKzUGzOHYuN/d3s0RYDgO1Xl1DOubv86', '2026-02-15 15:01:40'),
(10, 'Assalam', 'Alaikum', '', 'subscriber', 1, 1, 'active', 'Assalam', 'hello@email.com', '$2y$10$8Ure2TIlnKAP/YNgSrzJhuMNA6KYJLcOdcbgS5UYPqj7ypJ1CUXdW', '2026-02-27 22:46:11');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `after_user_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.user_type = 'employee' THEN
        INSERT INTO employees (user_id, user_email, password, created_at)
        VALUES (NEW.user_id, NEW.user_email, NEW.password, NEW.created_at);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_user_update` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    IF NEW.user_type = 'employee' AND OLD.user_type != 'employee' THEN
        -- User type changed to employee, insert basic info into employees table
        INSERT INTO employees (user_id, user_email, password, created_at)
        VALUES (NEW.user_id, NEW.user_email, NEW.password, NEW.created_at);
    ELSEIF NEW.user_type != 'employee' AND OLD.user_type = 'employee' THEN
        -- User type changed from employee, remove from employees table
        DELETE FROM employees WHERE user_id = NEW.user_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `role_id` int NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `role_description` text,
  `role_level` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`role_id`, `role_name`, `role_description`, `role_level`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Full system access with all permissions', 100, '2026-02-27 20:59:01', '2026-02-27 21:34:11'),
(2, 'manager', 'Can manage users and view reports', 75, '2026-02-27 20:59:01', '2026-02-27 20:59:01'),
(3, 'supervisor', 'Can supervise daily operations', 50, '2026-02-27 20:59:01', '2026-02-27 20:59:01'),
(4, 'editor', 'Can edit content but not manage users', 30, '2026-02-27 20:59:01', '2026-02-27 20:59:01'),
(7, 'viewer', '', 10, '2026-02-27 21:56:39', '2026-02-27 21:56:39');

-- --------------------------------------------------------

--
-- Table structure for table `user_types`
--

CREATE TABLE `user_types` (
  `type_id` int NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `type_description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_types`
--

INSERT INTO `user_types` (`type_id`, `type_name`, `type_description`, `created_at`, `updated_at`) VALUES
(1, 'employee', 'Internal staff members', '2026-02-27 20:59:01', '2026-02-27 22:49:37'),
(2, 'client', 'Client users with limited access', '2026-02-27 20:59:01', '2026-02-27 21:54:00'),
(3, 'partner', 'Partner users with special access', '2026-02-27 20:59:01', '2026-02-27 20:59:01'),
(4, 'vendor', 'Vendor users', '2026-02-27 20:59:01', '2026-02-27 20:59:01'),
(5, 'guest', 'Guest users with minimal access', '2026-02-27 20:59:01', '2026-02-27 20:59:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `annual_performance`
--
ALTER TABLE `annual_performance`
  ADD PRIMARY KEY (`performance_id`),
  ADD UNIQUE KEY `unique_employee_year` (`employee_id`,`year`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_user_date` (`user_id`,`created_at`);

--
-- Indexes for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indexes for table `cdp_records`
--
ALTER TABLE `cdp_records`
  ADD PRIMARY KEY (`cdp_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `assigned_sales_id` (`assigned_sales_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `client_documents`
--
ALTER TABLE `client_documents`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `client_feedback`
--
ALTER TABLE `client_feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `engagement_id` (`engagement_id`),
  ADD KEY `validated_by` (`validated_by`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `client_notes`
--
ALTER TABLE `client_notes`
  ADD PRIMARY KEY (`note_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `deadline_change_requests`
--
ALTER TABLE `deadline_change_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `engagement_id` (`engagement_id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dept_code` (`dept_code`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `proposal_id` (`proposal_id`),
  ADD KEY `sent_by` (`sent_by`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `user_email` (`user_email`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_employee_department` (`department_id`);

--
-- Indexes for table `engagements`
--
ALTER TABLE `engagements`
  ADD PRIMARY KEY (`engagement_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `rule_version_id` (`rule_version_id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `submitted_by` (`submitted_by`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_assigned` (`assigned_to`),
  ADD KEY `idx_dates` (`start_date`,`original_deadline`,`approved_deadline`);

--
-- Indexes for table `engagement_status_history`
--
ALTER TABLE `engagement_status_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `engagement_id` (`engagement_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD PRIMARY KEY (`enquiry_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_submitted_at` (`submitted_at`);

--
-- Indexes for table `evidence`
--
ALTER TABLE `evidence`
  ADD PRIMARY KEY (`evidence_id`),
  ADD KEY `engagement_id` (`engagement_id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `validated_by` (`validated_by`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_industry` (`industry`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_full_name` (`full_name`);

--
-- Indexes for table `monthly_point_summary`
--
ALTER TABLE `monthly_point_summary`
  ADD PRIMARY KEY (`summary_id`),
  ADD UNIQUE KEY `unique_employee_month` (`employee_id`,`year`,`month`),
  ADD KEY `closed_by` (`closed_by`);

--
-- Indexes for table `points_ledger`
--
ALTER TABLE `points_ledger`
  ADD PRIMARY KEY (`ledger_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_employee_date` (`employee_id`,`created_at`),
  ADD KEY `idx_source` (`source_type`,`source_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD UNIQUE KEY `post_slug` (`post_slug`),
  ADD KEY `post_author` (`post_author`);

--
-- Indexes for table `proforma_invoices`
--
ALTER TABLE `proforma_invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `invoice_ref` (`invoice_ref`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `proposal_id` (`proposal_id`),
  ADD KEY `prepared_by` (`prepared_by`);

--
-- Indexes for table `proforma_reviews`
--
ALTER TABLE `proforma_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `proforma_id` (`proforma_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `proposals`
--
ALTER TABLE `proposals`
  ADD PRIMARY KEY (`proposal_id`),
  ADD UNIQUE KEY `proposal_ref` (`proposal_ref`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `prepared_by` (`prepared_by`),
  ADD KEY `manager_approved_by` (`manager_approved_by`),
  ADD KEY `ceo_approved_by` (`ceo_approved_by`);

--
-- Indexes for table `proposal_reviews`
--
ALTER TABLE `proposal_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `proposal_id` (`proposal_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `quarterly_payouts`
--
ALTER TABLE `quarterly_payouts`
  ADD PRIMARY KEY (`payout_id`),
  ADD UNIQUE KEY `unique_employee_quarter` (`employee_id`,`year`,`quarter`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `salary_increment_bands`
--
ALTER TABLE `salary_increment_bands`
  ADD PRIMARY KEY (`band_id`);

--
-- Indexes for table `sales_targets`
--
ALTER TABLE `sales_targets`
  ADD PRIMARY KEY (`target_id`),
  ADD UNIQUE KEY `unique_employee_target` (`employee_id`,`year`,`month`),
  ADD KEY `validated_by` (`validated_by`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `sales_target_bands`
--
ALTER TABLE `sales_target_bands`
  ADD PRIMARY KEY (`band_id`);

--
-- Indexes for table `service_point_rules`
--
ALTER TABLE `service_point_rules`
  ADD PRIMARY KEY (`rule_id`),
  ADD UNIQUE KEY `unique_service_version` (`service_id`,`rule_version`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `service_types`
--
ALTER TABLE `service_types`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `signatures`
--
ALTER TABLE `signatures`
  ADD PRIMARY KEY (`signature_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `stamps`
--
ALTER TABLE `stamps`
  ADD PRIMARY KEY (`stamp_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`user_email`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `user_types`
--
ALTER TABLE `user_types`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `annual_performance`
--
ALTER TABLE `annual_performance`
  MODIFY `performance_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `account_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `cat_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `cdp_records`
--
ALTER TABLE `cdp_records`
  MODIFY `cdp_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `client_documents`
--
ALTER TABLE `client_documents`
  MODIFY `doc_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `client_feedback`
--
ALTER TABLE `client_feedback`
  MODIFY `feedback_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_notes`
--
ALTER TABLE `client_notes`
  MODIFY `note_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deadline_change_requests`
--
ALTER TABLE `deadline_change_requests`
  MODIFY `request_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `engagements`
--
ALTER TABLE `engagements`
  MODIFY `engagement_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `engagement_status_history`
--
ALTER TABLE `engagement_status_history`
  MODIFY `history_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `enquiry_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `evidence`
--
ALTER TABLE `evidence`
  MODIFY `evidence_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `monthly_point_summary`
--
ALTER TABLE `monthly_point_summary`
  MODIFY `summary_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `points_ledger`
--
ALTER TABLE `points_ledger`
  MODIFY `ledger_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `proforma_invoices`
--
ALTER TABLE `proforma_invoices`
  MODIFY `invoice_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `proforma_reviews`
--
ALTER TABLE `proforma_reviews`
  MODIFY `review_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `proposal_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `proposal_reviews`
--
ALTER TABLE `proposal_reviews`
  MODIFY `review_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quarterly_payouts`
--
ALTER TABLE `quarterly_payouts`
  MODIFY `payout_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_increment_bands`
--
ALTER TABLE `salary_increment_bands`
  MODIFY `band_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sales_targets`
--
ALTER TABLE `sales_targets`
  MODIFY `target_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_target_bands`
--
ALTER TABLE `sales_target_bands`
  MODIFY `band_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `service_point_rules`
--
ALTER TABLE `service_point_rules`
  MODIFY `rule_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `service_types`
--
ALTER TABLE `service_types`
  MODIFY `service_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `signatures`
--
ALTER TABLE `signatures`
  MODIFY `signature_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stamps`
--
ALTER TABLE `stamps`
  MODIFY `stamp_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `role_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_types`
--
ALTER TABLE `user_types`
  MODIFY `type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `annual_performance`
--
ALTER TABLE `annual_performance`
  ADD CONSTRAINT `annual_performance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `annual_performance_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `cdp_records`
--
ALTER TABLE `cdp_records`
  ADD CONSTRAINT `cdp_records_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cdp_records_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `categories` (`cat_id`),
  ADD CONSTRAINT `clients_ibfk_2` FOREIGN KEY (`assigned_sales_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `clients_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `client_documents`
--
ALTER TABLE `client_documents`
  ADD CONSTRAINT `client_documents_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `client_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `client_feedback`
--
ALTER TABLE `client_feedback`
  ADD CONSTRAINT `client_feedback_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `client_feedback_ibfk_2` FOREIGN KEY (`engagement_id`) REFERENCES `engagements` (`engagement_id`),
  ADD CONSTRAINT `client_feedback_ibfk_3` FOREIGN KEY (`validated_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `client_feedback_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `client_notes`
--
ALTER TABLE `client_notes`
  ADD CONSTRAINT `client_notes_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `client_notes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `deadline_change_requests`
--
ALTER TABLE `deadline_change_requests`
  ADD CONSTRAINT `deadline_change_requests_ibfk_1` FOREIGN KEY (`engagement_id`) REFERENCES `engagements` (`engagement_id`),
  ADD CONSTRAINT `deadline_change_requests_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `deadline_change_requests_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `email_logs_ibfk_2` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`proposal_id`),
  ADD CONSTRAINT `email_logs_ibfk_3` FOREIGN KEY (`sent_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_employee_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `engagements`
--
ALTER TABLE `engagements`
  ADD CONSTRAINT `engagements_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `engagements_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `service_types` (`service_id`),
  ADD CONSTRAINT `engagements_ibfk_3` FOREIGN KEY (`rule_version_id`) REFERENCES `service_point_rules` (`rule_id`),
  ADD CONSTRAINT `engagements_ibfk_4` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `engagements_ibfk_5` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `engagements_ibfk_6` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `engagements_ibfk_7` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `engagements_ibfk_8` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `engagement_status_history`
--
ALTER TABLE `engagement_status_history`
  ADD CONSTRAINT `engagement_status_history_ibfk_1` FOREIGN KEY (`engagement_id`) REFERENCES `engagements` (`engagement_id`),
  ADD CONSTRAINT `engagement_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `evidence`
--
ALTER TABLE `evidence`
  ADD CONSTRAINT `evidence_ibfk_1` FOREIGN KEY (`engagement_id`) REFERENCES `engagements` (`engagement_id`),
  ADD CONSTRAINT `evidence_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `evidence_ibfk_3` FOREIGN KEY (`validated_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `monthly_point_summary`
--
ALTER TABLE `monthly_point_summary`
  ADD CONSTRAINT `monthly_point_summary_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `monthly_point_summary_ibfk_2` FOREIGN KEY (`closed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `points_ledger`
--
ALTER TABLE `points_ledger`
  ADD CONSTRAINT `points_ledger_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `points_ledger_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `points_ledger_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`post_author`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `proforma_invoices`
--
ALTER TABLE `proforma_invoices`
  ADD CONSTRAINT `proforma_invoices_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `proforma_invoices_ibfk_2` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`proposal_id`),
  ADD CONSTRAINT `proforma_invoices_ibfk_3` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `proforma_reviews`
--
ALTER TABLE `proforma_reviews`
  ADD CONSTRAINT `proforma_reviews_ibfk_1` FOREIGN KEY (`proforma_id`) REFERENCES `proforma_invoices` (`invoice_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proforma_reviews_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proforma_reviews_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `proposals`
--
ALTER TABLE `proposals`
  ADD CONSTRAINT `proposals_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `proposals_ibfk_2` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `proposals_ibfk_3` FOREIGN KEY (`manager_approved_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `proposals_ibfk_4` FOREIGN KEY (`ceo_approved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `proposal_reviews`
--
ALTER TABLE `proposal_reviews`
  ADD CONSTRAINT `proposal_reviews_ibfk_1` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`proposal_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proposal_reviews_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proposal_reviews_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `quarterly_payouts`
--
ALTER TABLE `quarterly_payouts`
  ADD CONSTRAINT `quarterly_payouts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `quarterly_payouts_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `sales_targets`
--
ALTER TABLE `sales_targets`
  ADD CONSTRAINT `sales_targets_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sales_targets_ibfk_2` FOREIGN KEY (`validated_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `sales_targets_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `service_point_rules`
--
ALTER TABLE `service_point_rules`
  ADD CONSTRAINT `service_point_rules_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `service_types` (`service_id`),
  ADD CONSTRAINT `service_point_rules_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `service_types`
--
ALTER TABLE `service_types`
  ADD CONSTRAINT `service_types_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `signatures`
--
ALTER TABLE `signatures`
  ADD CONSTRAINT `signatures_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `stamps`
--
ALTER TABLE `stamps`
  ADD CONSTRAINT `stamps_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `user_roles` (`role_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `user_types` (`type_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
