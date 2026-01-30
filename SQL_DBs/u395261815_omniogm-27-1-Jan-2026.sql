-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 27, 2026 at 05:07 PM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u395261815_omniogm`
--

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `account_id` int(11) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_number` varchar(100) NOT NULL,
  `iban_number` varchar(100) DEFAULT NULL,
  `swift_code` varchar(50) DEFAULT NULL,
  `bank_country` varchar(100) NOT NULL,
  `bank_address` text DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `is_active` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
  `cat_id` int(11) NOT NULL,
  `cat_title` varchar(255) NOT NULL,
  `cat_price` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`cat_id`, `cat_title`, `cat_price`) VALUES
(1, 'Business Setup', 10000.00),
(2, 'Accounting & Taxation', 4000.00);

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `trade_license_no` varchar(100) DEFAULT NULL,
  `country` varchar(100) NOT NULL,
  `emirate_zone` varchar(100) DEFAULT NULL,
  `business_activity` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_name` varchar(255) NOT NULL,
  `contact_designation` varchar(100) DEFAULT NULL,
  `contact_mobile` varchar(20) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `service_description` text DEFAULT NULL,
  `expected_start_date` date DEFAULT NULL,
  `payment_currency` varchar(10) DEFAULT 'AED',
  `payment_term` enum('Monthly','Quarterly','Bi-yearly','One-time') DEFAULT 'Monthly',
  `service_total_fee` decimal(10,2) DEFAULT 0.00,
  `lead_source` enum('referral','website','digital_marketing','event') DEFAULT 'website',
  `client_status` enum('New Lead','Contacted','Qualified','Proposal Drafted','Under Manager Review','Rejected by Manager','Approved by Manager','Under CEO Review','Rejected by CEO','Final Proposal Ready','Proposal Sent to Client','Awaiting Client Action','Signed – Move to Finance') DEFAULT 'New Lead',
  `assigned_sales_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `company_name`, `trade_license_no`, `country`, `emirate_zone`, `business_activity`, `address`, `contact_name`, `contact_designation`, `contact_mobile`, `contact_email`, `service_id`, `service_description`, `expected_start_date`, `payment_currency`, `payment_term`, `service_total_fee`, `lead_source`, `client_status`, `assigned_sales_id`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'First Client', '12345678000', 'United Arab Emirates', 'Dubai', 'Gym Service', 'JLT, Dubai.', 'Mr. Heg', 'Accountant', '123456789', 'heg@email.comm', 2, 'He need some services. And more.', '2025-12-03', 'AED', 'Quarterly', 4000.00, 'digital_marketing', 'Proposal Drafted', 3, 3, '2025-11-30 17:21:50', '2025-12-02 10:38:45');

-- --------------------------------------------------------

--
-- Table structure for table `client_documents`
--

CREATE TABLE `client_documents` (
  `doc_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `document_title` varchar(255) NOT NULL,
  `document_type` enum('trade_license','bank_statement','signed_proposal','signed_proforma','other') DEFAULT 'other',
  `file_path` varchar(500) NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_documents`
--

INSERT INTO `client_documents` (`doc_id`, `client_id`, `document_title`, `document_type`, `file_path`, `uploaded_by`, `uploaded_at`) VALUES
(12, 1, 'DoB', 'trade_license', 'uploads/client_documents/doc_1_1764542402_7866_test.docx', 2, '2025-11-30 22:40:02'),
(18, 1, 'New Dox', 'bank_statement', 'uploads/client_documents/doc_1_1764542837_9018_OGM-Letter-Head.pdf', 2, '2025-11-30 22:47:17'),
(19, 1, 'Helloo', 'signed_proforma', 'uploads/client_documents/doc_1_1764671630_2004_OGM-Letter-Head.pdf', 2, '2025-12-02 10:33:50'),
(20, 1, 'Helloo', 'signed_proforma', 'uploads/client_documents/doc_1_1764671638_4719_OGM-Letter-Head.pdf', 2, '2025-12-02 10:33:58');

-- --------------------------------------------------------

--
-- Table structure for table `client_notes`
--

CREATE TABLE `client_notes` (
  `note_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note_type` enum('internal','rejection_reason','follow_up','status_change') DEFAULT 'internal',
  `note_content` text NOT NULL,
  `visibility_roles` varchar(255) DEFAULT 'all',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `log_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `proposal_id` int(11) DEFAULT NULL,
  `email_type` enum('proposal_sent','reminder','follow_up') NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `sent_by` int(11) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `user_image` varchar(50) NOT NULL,
  `field_of_study` varchar(100) DEFAULT NULL,
  `qualification` varchar(100) DEFAULT NULL,
  `highest_graduation` varchar(100) DEFAULT NULL,
  `year_of_graduation` year(4) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `user_id`, `user_email`, `password`, `first_name`, `last_name`, `user_image`, `field_of_study`, `qualification`, `highest_graduation`, `year_of_graduation`, `created_at`) VALUES
(1, 2, 'abc@email.com', '123456', 'New1', 'Joiner', 'profile_2_1757270810.jpg', 'IT', 'Engineer', 'MSc', '2021', '2025-09-07 15:27:08'),
(2, 2, 'Woon@email.com', '123456', 'Xeee', 'User', 'profile_1757347189_8877.png', 'Medical', 'MSc', 'MSc', '2000', '2025-09-08 16:59:49');

-- --------------------------------------------------------

--
-- Table structure for table `enquiries`
--

CREATE TABLE `enquiries` (
  `enquiry_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `service` varchar(100) NOT NULL,
  `sub_service` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `status` enum('new','contacted','in_progress','completed') DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enquiries`
--

INSERT INTO `enquiries` (`enquiry_id`, `name`, `email`, `contact`, `service`, `sub_service`, `message`, `submitted_at`, `is_read`, `status`) VALUES
(13, 'Odai', 'tom@ohmbc.ae', '0509860136', 'Accounting &amp; Taxation', 'Hdhd', 'Hi', '2026-01-09 05:15:10', 0, 'new'),
(14, 'Last Test', 'abdaullahbalam@gmail.com', '+123456789', 'Accounting &amp; Taxation', 'Sub Service', 'Hello last test before SMTP switch.', '2026-01-13 21:47:35', 0, 'new'),
(15, 'Last Test', 'abdaullahbalam@gmail.com', '+123456789', 'Accounting &amp; Taxation', 'Sub Service', 'Hello last test before SMTP switch.', '2026-01-13 21:49:36', 0, 'new'),
(16, 'New Test', 'abdaullahbalam@gmail.com', '+123456789', 'Business Setup', 'New Service', 'After switching SMTP settings.', '2026-01-13 21:55:45', 0, 'new'),
(17, 'Tester', 'abdaullahbalam@gmail.com', '+9715647663', 'Accounting &amp; Taxation', 'Another Random Test22', 'After SMMTP switch test.', '2026-01-13 22:04:22', 0, 'new'),
(18, 'test', 'jaw@test.com', '03170276969', 'Accounting &amp; Taxation', 'test', 'test', '2026-01-15 13:53:35', 0, 'new'),
(19, 'JAW TEST', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'sdad', '2026-01-15 14:17:49', 0, 'new'),
(20, 'JAW TEST', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'sdad', '2026-01-15 14:18:15', 0, 'new'),
(21, 'JAW TEST', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'sdad', '2026-01-15 14:18:42', 0, 'new'),
(22, 'compliance compliance', 'compliance@compliance.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'compliance', '2026-01-15 14:21:18', 0, 'new'),
(23, 'compliance compliance', 'compliance@compliance.com', 'compliance', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'compliance', '2026-01-15 14:21:39', 0, 'new'),
(24, 'compliance', 'compliance@compliance.com', '03170276969', 'Accounting &amp; Taxation', 'compliance', 'compliance', '2026-01-15 14:22:16', 0, 'new'),
(25, 'Muhammad Shoaib', 'shoaibzain849@gmail.comdsad', '03170276969', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'sadad', '2026-01-15 14:46:30', 0, 'new'),
(26, 'Muhammad Shoaib', 'shoaibzain849@gmail.comdsad', '03170276969', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'sadad', '2026-01-15 14:46:36', 0, 'new'),
(27, 'Muhammad Shoaib', 'shoaibzain849@gmail.comdsad', '03170276969', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'sadad', '2026-01-15 14:46:40', 0, 'new'),
(28, 'JAW', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'ss', '2026-01-15 14:48:30', 0, 'new'),
(29, 'JAW', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'ss', '2026-01-15 14:48:36', 0, 'new'),
(30, 'JAW', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'ss', '2026-01-15 14:48:42', 0, 'new'),
(31, 'hammad', 'asntalha@gmail.com', '10313227269', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'fdfddfdf', '2026-01-16 09:26:13', 0, 'new'),
(32, 'hammad', 'asntalha@gmail.com', '10313227269', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'fdfddfdf', '2026-01-16 09:26:18', 0, 'new'),
(33, 'hammad', 'asntalha@gmail.com', '10313227269', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'fdfddfdf', '2026-01-16 09:26:24', 0, 'new'),
(34, 'test', 'tets@gmail.com', '+97112345678', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'Test', '2026-01-16 11:15:32', 0, 'new'),
(35, 'test', 'tets@gmail.com', '+97112345678', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'Test', '2026-01-16 11:15:38', 0, 'new'),
(36, 'test', 'tets@gmail.com', '+97112345678', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'Test', '2026-01-16 11:15:44', 0, 'new'),
(37, 'test', 'tets@gmail.com', '+97112345678', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'Test', '2026-01-16 11:16:10', 0, 'new'),
(38, 'test', 'tets@gmail.com', '+97112345678', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'Test', '2026-01-16 11:16:16', 0, 'new'),
(39, 'test', 'tets@gmail.com', '+97112345678', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'Test', '2026-01-16 11:16:22', 0, 'new'),
(40, 'Test', 'mufasa.seo@gmail.com', '+97112345678', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'Test', '2026-01-16 11:20:58', 0, 'new'),
(41, 'Test', 'mufasa.seo@gmail.com', '+97112345678', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'Test', '2026-01-16 11:21:04', 0, 'new'),
(42, 'Test', 'mufasa.seo@gmail.com', '+97112345678', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'Test', '2026-01-16 11:21:10', 0, 'new'),
(43, 'Jetha', 'mufasa.seo@gmail.com', '+97112345678', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Contact Form', 'Test', '2026-01-16 13:14:53', 0, 'new'),
(44, 'Jetha', 'mufasa.seo@gmail.com', '+97112345678', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Contact Form', 'Test', '2026-01-16 13:14:58', 0, 'new'),
(45, 'Jetha', 'mufasa.seo@gmail.com', '+97112345678', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Contact Form', 'Test', '2026-01-16 13:15:03', 0, 'new'),
(46, 'JAW', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'asdas', '2026-01-19 07:39:57', 0, 'new'),
(47, 'JAW', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'asdas', '2026-01-19 07:40:03', 0, 'new'),
(48, 'JAW', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'asdas', '2026-01-19 07:40:08', 0, 'new'),
(49, 'Technical Support', 'jaw@test.com', '03170276969', 'Accounting &amp; Taxation', 'test', 'Test', '2026-01-19 07:41:03', 0, 'new'),
(50, 'JAW', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'aa', '2026-01-19 09:09:54', 0, 'new'),
(51, 'JAW', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'aa', '2026-01-19 09:09:58', 0, 'new'),
(52, 'JAW', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'aa', '2026-01-19 09:10:04', 0, 'new'),
(53, 'compliance', 'compliance@compliance.com', '03323582835', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'compliance Test 2:10 pm', '2026-01-19 09:10:59', 0, 'new'),
(54, 'compliance', 'compliance@compliance.com', '03323582835', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'compliance Test 2:10 pm', '2026-01-19 09:11:05', 0, 'new'),
(55, 'compliance', 'compliance@compliance.com', '03323582835', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'compliance Test 2:10 pm', '2026-01-19 09:11:09', 0, 'new'),
(56, 'compliance', 'compliance@compliance.com', '03323582835', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'compliance Test 2:10 pm', '2026-01-19 09:13:44', 0, 'new'),
(57, 'compliance', 'compliance@compliance.com', '03323582835', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'compliance Test 2:10 pm', '2026-01-19 09:13:50', 0, 'new'),
(58, 'compliance', 'compliance@compliance.com', '03323582835', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'compliance Test 2:10 pm', '2026-01-19 09:13:54', 0, 'new'),
(59, 'compliance compliance', 'compliance@compliance.com', '03323582835', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'compliance Test 2:10 pm', '2026-01-19 09:14:45', 0, 'new'),
(60, 'JAW TEST', 'compliance@gmail.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'compliancecompliancecompliancecompliance 20:15 pm', '2026-01-19 09:15:10', 0, 'new'),
(61, 'Technical Support ogmbc', 'sarfaraz@TechnicalSupportogmbc.pk', '9712265141', 'Accounting &amp; Taxation', 'compliancesarfaraz@TechnicalSupportogmbc.pk', 'sarfaraz@TechnicalSupportogmbc.pk', '2026-01-19 09:15:56', 0, 'new'),
(62, 'JAW TEST', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'asdad', '2026-01-19 09:52:05', 0, 'new'),
(63, 'Home', 'Home@gmail.com', '9712265141', 'Accounting &amp; Taxation', 'Home', 'Home', '2026-01-19 09:53:22', 0, 'new'),
(64, 'JAW TEST', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'asdad', '2026-01-19 09:53:38', 0, 'new'),
(65, 'JAW TEST', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'asdad', '2026-01-19 09:54:55', 0, 'new'),
(66, 'JAW TEST', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'sda', '2026-01-19 09:58:30', 0, 'new'),
(67, 'JAW TEST', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'asda', '2026-01-19 11:20:29', 0, 'new'),
(68, 'Muhammad Sarfaraz', 'sarfaraz@test.pk', '03323582835', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'asdad', '2026-01-19 11:21:22', 0, 'new'),
(69, 'Abdul Gul', 'hameed@leopardscontracting.com', '03352897504', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'asdad', '2026-01-19 11:21:36', 0, 'new'),
(70, 'testing testing', 'testing@gmail.com', 'testing', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', '.......................', '2026-01-20 12:49:54', 0, 'new'),
(71, 'testing testing', 'testing@gmil.com', 'testing', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Contact Form', '...................', '2026-01-20 12:50:43', 0, 'new'),
(72, 'JAW TEST', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 's', '2026-01-21 06:42:30', 0, 'new'),
(73, 'JAW TEST', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 's', '2026-01-21 06:44:12', 0, 'new'),
(74, 'JAW TEST', 'jaw@test.com', '9712265141', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 's', '2026-01-21 06:44:52', 0, 'new'),
(75, 'test test', 'test@test.com', 'test', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'test', '2026-01-21 09:55:42', 0, 'new'),
(76, 'test test', 'test@test.com', 'test', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'test', '2026-01-21 09:55:59', 0, 'new'),
(77, 'Rubadah Sanaa', 'rubadahsanaa@gmail.com', '2692281425', 'Accounting &amp; Taxation', 'Tax', 'Tax', '2026-01-21 11:22:20', 0, 'new'),
(78, 'Lisa', 'lisa.99seosolutionworld@gmail.com', '', 'Accounting &amp; Taxation', '', 'Hi http://ogmbc.ae,<br />\r\n<br />\r\nJust had a look at your site – it’s well-designed, but not performing well in search engines.<br />\r\n<br />\r\nWould you be interested in improving your SEO and getting more traffic?<br />\r\n<br />\r\nI can send over a detailed proposal with affordable packages.<br />\r\n<br />\r\nThank You,<br />\r\nLisa', '2026-01-22 06:09:31', 0, 'new'),
(79, 'test test', 'test@gmail.com', 'test', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'test', '2026-01-23 12:56:06', 0, 'new'),
(80, 'deepak en', 'deepakenen@gmail.com', '524213425', 'Tax Compliance Health Check', 'Tax Compliance Health Check - Banner', 'training for tax filling', '2026-01-26 08:59:31', 0, 'new');

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `industry` varchar(50) DEFAULT NULL,
  `consent_given` tinyint(1) DEFAULT 0,
  `ratios_calculated` text DEFAULT NULL COMMENT 'JSON array of calculated ratios',
  `report_generated` datetime DEFAULT NULL COMMENT 'When the report was generated',
  `first_interaction` datetime NOT NULL,
  `last_interaction` datetime NOT NULL,
  `status` enum('new','contacted','qualified','converted','unresponsive') DEFAULT 'new',
  `source` varchar(50) DEFAULT 'financial_ratio_calculator',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `full_name`, `email`, `phone`, `company_name`, `industry`, `consent_given`, `ratios_calculated`, `report_generated`, `first_interaction`, `last_interaction`, `status`, `source`, `notes`, `created_at`, `updated_at`) VALUES
(9, 'San', 'test@example.come', '', 'First Client', 'other', 1, '[\"quick_ratio\"]', NULL, '2026-01-10 18:02:43', '2026-01-10 18:02:43', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 17:02:43', '2026-01-10 17:02:43'),
(10, 'Doe', 'test@exatmple.com', '', 'First Client', 'other', 1, '[\"quick_ratio\"]', NULL, '2026-01-10 18:21:45', '2026-01-10 18:21:45', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 17:21:45', '2026-01-10 17:21:45'),
(11, 'John', 'test@exampwle.com', '', 'First Client', 'agriculture', 1, '[\"interest_coverage\"]', NULL, '2026-01-10 18:26:31', '2026-01-10 18:26:31', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 17:26:31', '2026-01-10 17:26:31'),
(12, 'Putin', 'test@exeample.com', '+971501234567', 'Test Company', 'technology', 1, '[\"quick_ratio\",\"operating_cash_flow_ratio\"]', NULL, '2026-01-10 18:45:38', '2026-01-10 18:45:38', 'new', 'financial_ratio_calculator', NULL, '2026-01-10 17:45:38', '2026-01-10 17:45:38'),
(13, 'Last Test', 'abm@gmail.com', '+971345264456', 'First Client', 'hospitality', 1, '[\"quick_ratio\"]', NULL, '2026-01-10 18:48:21', '2026-01-10 19:03:42', '', 'financial_ratio_calculator', NULL, '2026-01-10 17:48:21', '2026-01-10 18:03:42'),
(15, 'Dani', 'dani@gmai.com', '+971345264456', 'First Client Edited', 'transportation', 1, '[\"payables_turnover\",\"dpo\"]', NULL, '2026-01-11 01:45:39', '2026-01-11 01:45:39', 'new', 'financial_ratio_calculator', NULL, '2026-01-11 01:45:39', '2026-01-11 01:45:39'),
(16, 'Odai Tom ', 'aaaa.odai@gmail.com', '+971509860136', 'Trust Books Accounting ', 'finance', 1, '[\"current_ratio\",\"quick_ratio\",\"cash_ratio\",\"operating_cash_flow_ratio\"]', NULL, '2026-01-11 05:11:12', '2026-01-11 05:11:12', 'new', 'financial_ratio_calculator', NULL, '2026-01-11 05:11:12', '2026-01-11 05:11:12'),
(17, 'Odai ', 'aaaa.tom20@gmail.com', '+44509860136', 'Odeemstore', 'education', 1, '[\"current_ratio\",\"quick_ratio\",\"cash_ratio\",\"operating_cash_flow_ratio\"]', NULL, '2026-01-11 05:13:49', '2026-01-11 05:13:49', 'new', 'financial_ratio_calculator', NULL, '2026-01-11 05:13:49', '2026-01-11 05:13:49');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `post_title` varchar(255) NOT NULL,
  `post_slug` varchar(255) NOT NULL,
  `post_content` longtext DEFAULT NULL,
  `post_excerpt` text DEFAULT NULL,
  `post_author` int(11) NOT NULL,
  `post_status` enum('published','draft') DEFAULT 'draft',
  `post_image` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `post_title`, `post_slug`, `post_content`, `post_excerpt`, `post_author`, `post_status`, `post_image`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`) VALUES
(14, 'What Are the Advantages of Company Formation in Dubai’s Mainland vs. Free Zones?', 'what-are-the-advantages-of-company-formation-in-dubai-s-mainland-vs-free-zones', 'A global hub for business, Dubai offers immense opportunities for investors and entrepreneurs. If you are considering Company Formation in Dubai, one crucial decision is whether to set up on the mainland or in one of the numerous accessible areas. Each choice has distinct advantages and benefits, catering to various requirements and objectives for business. This blog will discuss the advantages of starting a company on the mainland of Dubai vs free zones. It will provide helpful information for anyone considering establishing a business in the UAE.\r\n\r\nThe advantages of forming a company in Dubai\'s Mainland\r\n1. The ability to access the Local Market\r\nOne of the significant advantages of starting an enterprise in the Dubai mainland is the unrestricted access to the market in Dubai. The business can be operated freely across Dubai and throughout the UAE, allowing for more significant market penetration opportunities and the ability to interact directly with customers and local clients. This primarily benefits retailers, hospitality, and companies based on a regional client base.\r\n\r\n2. Flexible Business Activity\r\nCompanies from mainland countries located in Dubai benefit from many actions. In contrast to free zones, which typically limit the kinds of commercial activities that may be conducted, companies on the mainland can participate in a wide variety of professional, commercial, and industrial tasks without any restrictions. This versatility is great for businesses looking to diversify or expand into new markets.\r\n\r\n3. The ability to bid on government Contracts\r\nBusinesses from mainland countries can take part in contracts and tenders. This is a huge benefit for companies looking to get lucrative government contracts that are usually large and long-term. Contracts with the government can create steady revenue streams and boost a company\'s image and trustworthiness.\r\n\r\n4. There are no restrictions on office location\r\nCompanies located in mainland countries aren\'t restricted to specific geographic regions or specific structures. The office can be set up at any site anywhere in Dubai and enjoy optimal operational configuration and control freedom. This flexibility can assist businesses in locating the most efficient or economical locations.\r\n\r\nBenefits of forming a company in Dubai\'s free Zones\r\n1. Full Foreign Ownership\r\nOne of the most significant advantages of starting a business in the free zones of Dubai is the foreign ownership limit. In contrast to mainland businesses that need a local sponsor or partners who hold a majority stake, foreign investors can completely own free zone enterprises. The ideal situation is for business owners who wish to control their operations completely.\r\n\r\n2. Tax Benefits, Incentives and Tax Credits\r\nFree zones within Dubai have a range of tax benefits, including complete exclusion from personal and corporate tax on income and taxes on exports and imports. They can significantly ease the financial burden for companies and allow them to invest more significant amounts of their profits in development and growth. Furthermore, several areas that are free offer the promise of tax-free income for a period of as long as 50 years.\r\n\r\n3. A Simple Setup Procedure\r\nBusiness Setup in UAE free zones is usually easy and quick. The authorities of free zones provide complete support and assistance, which includes help regarding visas, licenses, and various other requirements for regulatory compliance. This makes the set-up procedure more accessible and more straightforward. This is beneficial to entrepreneurs who want to begin their business in a short time.\r\n\r\n4. Specialized Zones to Support Various Industries\r\nThe Dubai free zones are usually specifically designed for media, technology, health logistics, and healthcare sectors. Establishing a business in a specific free zone could provide companies access to specialized infrastructure, facilities, and networking opportunities. The environment could encourage expansion, innovation and cooperation between firms in the same industry.\r\nConclusion\r\nThe mainland and the accessible zones provide distinct benefits when it comes to company formation in Dubai, depending on your business\'s needs and needs. A company formation on the mainland is an excellent option for enterprises with unlimited access to local markets, operating flexibility, and opportunities to obtain government contracts. In contrast, these zones can provide advantages, including full foreign ownership tax-free zones, a more straightforward set-up process, and a specific industry environment.\r\n\r\nKnowing these benefits can assist entrepreneurs in making educated decisions regarding their company\'s set-up in the UAE. We at O.G.M. Consultants specialise in offering individualized advice and assistance to companies forming within Dubai, regardless of whether it is situated on the mainland or in free zones. Contact us today for more about our services and how we could help you create an effective business in Dubai.', 'Advantages and disadvantages of Mainland company formation in Dubai Vs. Free Zone', 4, 'published', 'post_1767279663_3154.png', 'What Are the Advantages of Company Formation in Dubai’s Mainland vs. Free Zones?', 'Dubai Company setup Mainland Vs. Free Zone', 'Dubai company formation mainland Vs. Free Zone, business setup in Dubai', '2026-01-01 15:01:03', '2026-01-01 15:02:41'),
(15, 'How Can Bookkeeping Services Support Your Business Growth?', 'how-can-bookkeeping-services-support-your-business-growth', 'In the frantic business world in Dubai, keeping current and accurate financial records is crucial to the sustainability and growth of your business. Bookkeeping services in Dubai provide businesses with the knowledge and the equipment needed to manage their business\'s finances effectively. This blog examines how professional bookkeeping services will help and expand your business.\r\nEnsuring Accurate Financial Records\r\nOne of the primary advantages of using bookkeeping services in Dubai is the security of accurate and current financial reports. Professional bookkeepers keep precise accounts of all monetary transactions and adequately record each expenditure, revenue, and investment. Accuracy is vital to making informed business decisions, preparing tax deadlines, and avoiding any financial discrepancies that could result in legal issues.\r\nFacilitating Financial Processes\r\nBookkeeping outsourcing can dramatically simplify your financial procedures. Bookkeepers with expertise use the latest software and techniques to handle your accounts payable and accounts receivable pay, payroll, and various finance-related jobs. It saves you time and lowers the chances of making mistakes, allowing you to focus on the core activities of your business. The streamlined financial management improves productivity, resulting in more efficient resource allocation, ultimately helping to boost business growth.\r\nLatest financial Insights and Analysis\r\nBookkeeping services offered in Dubai can provide invaluable financial insight and analyses. Professional bookkeepers create thorough financial statements that give an in-depth view of the economic condition of your company. They include profits and loss statements, balance sheets, and cash flow statements. The business owners can identify patterns, assess performance, and make informed decisions by studying the documents. Accessing accurate financial information allows you to make better decisions, plan your future, and take advantage of growth opportunities.\r\nImproving Control of Cash Flow\r\nA well-organized cash flow system is vital to every company\'s success and long-term viability. Bookkeeping solutions help you track cash flow by meticulously monitoring the flow of money. It helps you to have a clear picture of your cash balance on any date. Effective cash flow management can avoid liquidity crises, plan for future expenses, and ensure you have enough funds to fund growth projects. An efficient cash flow is the foundation of an expanding business. Professional bookkeeping will ensure that it\'s maintained.\r\nHelping Compliance with the law Tax Preparation\r\nUnderstanding the complicated tax regulations to comply with tax regulations in Dubai is a challenge for companies. Bookkeeping solutions can help your company ensure compliance with tax laws and rules in the local area. Professional bookkeepers track the deductions for all expenses, create exact financial records and ensure you submit your tax return. The risk is reduced by audits and penalties, which provide assurance and allow you to concentrate on expanding your company. Simple tax compliance with professional bookkeeping will also boost tax benefits, contributing to more financial security.\r\nFacilitating the process of Business Planning and Budgeting\r\nBookkeeping is a crucial function in the business planning process and budgeting. In keeping up-to-date accounting records and producing precise financial information, they help companies create accurate budgets and economic strategies. They are crucial in setting growth goals, managing costs, and efficiently allocating resources. By having a financial plan, firms can make intelligent choices, limit spending, and ensure they\'re going in the right direction to achieve their growth goals.\r\nConclusion\r\nIn the dynamic and competitive business climate, expert bookkeeping services can be invaluable in helping businesses grow. They provide precise financial records, streamline procedures for financial management, give crucial insight into financials, improve cash flow control, facilitate tax compliance, aid in business planning and allow time to focus on essential tasks. Expert bookkeeping solutions in Dubai firms can create an enduring financial foundation to make well-informed decisions and concentrate on strategies to drive success and growth.\r\nAt O.G.M. Consultants, we specialize in providing comprehensive bookkeeping service in Dubai tailored to meet the unique needs of your business. Our experienced team is dedicated to helping you manage your finances efficiently and achieve your growth objectives. Contact us today to learn more about how our bookkeeping services can support your business growth', 'Bookkeeping in Dubai, Accounting report', 4, 'published', 'post_1767280128_5014.png', 'How Can Bookkeeping Services Support Your Business Growth?', 'Bookkeeping', 'Bookkeeping services', '2026-01-01 15:08:48', '2026-01-01 15:08:48'),
(16, 'Why Should You Consider Tax Consulting Services in UAE for Your Business?', 'why-should-you-consider-tax-consulting-services-in-uae-for-your-business', '<p style=\"text-align: justify;\"><strong>The existing<em> competition</em></strong> in the market makes it necessary for businesses to have a clear-cut position in tax planning and compliance. Dealing with taxes in Dubai may be complicated, given the recent introduction of VAT alongside other tax laws that continue to evolve. This is why most businesses outsource tax consultants in Dubai, UAE, wherever they need help. In this blog, I will provide reasons why I think that using tax consulting services in the UAE is a reasonable and beneficial decision for your business. Specialization in UAE Taxation One of the reasons many clients engage local tax consultants in Dubai, UAE, is their local expertise. Running a business in the UAE is likely to be a taxing affair as it is a jurisdiction with a great deal of tax regulation. Therefore, new business owners find it difficult to keep up with the new business regulations. Corporate taxes, VAT tariffs, and excise atomization are some of the codes a tax consultant understands, thus verifying that your business is legally confirmed. This expertise assists you in evading mistakes that cost the firm substantial sums in penalties and audits, hence making room for operational smoothness. Effective Tax Management Services No two corporations are alike, and for this reason, taxation differs from one organization to another. An approach based on indiscriminate taxation normally brings about leanness and wastage. Tax consultancy in the UAE can be a platform through which it is possible to obtain tax planning tailored to your needs and the requirements of your business. These services make it easier for tax liabilities to be optimized whilst only paying what is required and by fully exploiting the available deductions, exemptions, and incentives within the system. This customized tax planning can significantly improve the company\'s financial performance and long-term sustainability. VAT Compliance Support It has been impossible for businesses to keep up with changes regarding tax compliance after the introduction of Value Added Tax to the UAE. Not only is it time-consuming, but VAT returns, keeping track of tax invoices, and VAT payments can also get pretty complicated. Tax consultants in Dubai, UAE, specialize in VAT compliance, ensuring your business keeps records in the right order, files on time, and meets all VAT obligations. With their help, you can avoid penalties for late or wrong VAT submissions. You can save your time and resources by using them Managing tax requirements in-house is a very easy way to waste precious time and resources, mainly for small and medium enterprises. Tax consulting services in UAE can let you focus on the core business of your activities while letting experts take care of the complexity and compliance issues concerning taxes. In this fashion, your tax process is efficient and allows your team to work on activities that would directly add to the growth and profitability of your company. Risk Management and Auditing Support Professional tax consultant services in Dubai, UAE, can be of good value if one is faced with a tax audit or dispute with the tax department. They may be able to guide you through the process of an audit by making sure your documentation is properly and fully prepared. This also helps in terms of risk as it can highlight potential tax problems before the situation becomes essential and problematic. In this case, such practice minimizes unexpected liabilities while maintaining all business operations in line all the time. Conclusion Engaging in tax consulting services in the UAE is an investment that can bring so much into your business. From expert opinions regarding local tax law to tailored tax strategies to help with VAT compliance as well as risks on audit, tax consultants offer a wholesome package that can save you time and reduce liabilities while improving the financial health of your enterprise. Hence, with the help of professional tax consultants, your business shall be well prepared to familiarize itself with the intricacies of the UAE tax system and find more ways to grow.</p>', '', 4, 'published', 'post_1767280582_5728.png', 'Why Should You Consider Tax Consulting Services in UAE for Your Business?', 'Does your business have approved tax agency?', 'VAT and CT services in Dubai', '2026-01-01 15:16:22', '2026-01-27 17:04:42');

-- --------------------------------------------------------

--
-- Table structure for table `proforma_invoices`
--

CREATE TABLE `proforma_invoices` (
  `invoice_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `invoice_ref` varchar(50) NOT NULL,
  `proposal_id` int(11) DEFAULT NULL,
  `version` int(11) DEFAULT 1,
  `invoice_content` longtext DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_breakdown` text DEFAULT NULL,
  `validity_period` date DEFAULT NULL,
  `prepared_by` int(11) DEFAULT NULL,
  `prepared_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_path` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proforma_invoices`
--

INSERT INTO `proforma_invoices` (`invoice_id`, `client_id`, `invoice_ref`, `proposal_id`, `version`, `invoice_content`, `total_amount`, `payment_breakdown`, `validity_period`, `prepared_by`, `prepared_at`, `file_path`) VALUES
(1, 1, 'PROF-20251201-0001-V1', 3, 1, '<!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proforma Invoice: PROF-20251201-0001-V1</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\r\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\r\n            .section { margin-bottom: 25px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\r\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\r\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFORMA INVOICE</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Invoice Reference: PROF-20251201-0001-V1</h3>\r\n            <p><strong>Date Issued:</strong> December 1, 2025 | <strong>Valid Until:</strong> December 31, 2025</p>\r\n        </div>\r\n        \r\n        <div class=\'company-info\'>\r\n            <h4>From:</h4>\r\n            <p><strong>OGM Business Consultancy</strong></p>\r\n            <p>Business Bay, Dubai</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: info@ogmbusiness.com</p>\r\n            <p>Phone: +971 4 123 4567</p>\r\n            <p>VAT: TRN 123456789012345</p>\r\n        </div>\r\n        \r\n        <div class=\'client-info\'>\r\n            <h4>Bill To:</h4>\r\n            <p><strong>First Client</strong></p>\r\n            <p>Attn: Mr. Heg</p>\r\n            <p>Accountant</p>\r\n            <p>JLT, Dubai.</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: heg@email.comm</p>\r\n            <p>Phone: 123456789</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Service Description</h4>\r\n            <p><strong>Accounting &amp; Taxation</strong> - He need some services. And more.</p>\r\n        </div>\r\n        \r\n        <table>\r\n            <thead>\r\n                <tr>\r\n                    <th>Description</th>\r\n                    <th>Quantity</th>\r\n                    <th>Unit Price (AED)</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr>\r\n            </thead>\r\n            <tbody>\r\n                <tr>\r\n                    <td>Accounting &amp; Taxation Service</td>\r\n                    <td>1</td>\r\n                    <td>4,000.00</td>\r\n                    <td>4,000.00</td>\r\n                </tr>\r\n                <tr class=\'total-row\'>\r\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\r\n                    <td><strong>4,000.00</strong></td>\r\n                </tr>\r\n            </tbody>\r\n        </table>\r\n        \r\n        <div class=\'payment-terms\'>\r\n            <h4>Payment Terms: Bi-yearly</h4>\r\n            <table>\r\n                <tr>\r\n                    <th>Installment</th>\r\n                    <th>Due</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr><tr>\r\n                    <td>1</td>\r\n                    <td>Half 1</td>\r\n                    <td>2,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Half 2</td>\r\n                    <td>2,000.00</td>\r\n                  </tr></table>\r\n        </div>\r\n        \r\n        <div class=\'bank-details\'>\r\n            <h4>Bank Transfer Details</h4>\r\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\r\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\r\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\r\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\r\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\r\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Notes</h4>\r\n            <ol>\r\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\r\n                <li>Prices are valid for 30 days from the date of issue</li>\r\n                <li>Payment should be made in full before service commencement</li>\r\n                <li>All bank charges are to be borne by the client</li>\r\n                <li>Services will commence upon receipt of payment</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div style=\'float: left; width: 45%;\'>\r\n                <p><strong>Prepared by:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p> </p>\r\n                <p>Sales Consultant</p>\r\n                <p>OGM Business Consultancy</p>\r\n            </div>\r\n            \r\n            <div style=\'float: right; width: 45%; text-align: center;\'>\r\n                <p><strong>For OGM Business Consultancy:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p>Authorized Signature</p>\r\n                <p>Date: December 1, 2025</p>\r\n            </div>\r\n            <div style=\'clear: both;\'></div>\r\n        </div>\r\n    </body>\r\n    </html>', 4000.00, '[{\"installment\":1,\"amount\":\"2,000.00\",\"due_description\":\"Half 1\"},{\"installment\":2,\"amount\":\"2,000.00\",\"due_description\":\"Half 2\"}]', '0000-00-00', 2, '2025-12-01 15:03:21', 'uploads/proformas/proforma_PROF-20251201-0001-V1.html'),
(2, 1, 'PROF-20251201-0001-V2', 3, 2, '<!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proforma Invoice: PROF-20251201-0001-V2</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\r\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\r\n            .section { margin-bottom: 25px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\r\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\r\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFORMA INVOICE</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Invoice Reference: PROF-20251201-0001-V2</h3>\r\n            <p><strong>Date Issued:</strong> December 1, 2025 | <strong>Valid Until:</strong> December 31, 2025</p>\r\n        </div>\r\n        \r\n        <div class=\'company-info\'>\r\n            <h4>From:</h4>\r\n            <p><strong>OGM Business Consultancy</strong></p>\r\n            <p>Business Bay, Dubai</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: info@ogmbusiness.com</p>\r\n            <p>Phone: +971 4 123 4567</p>\r\n            <p>VAT: TRN 123456789012345</p>\r\n        </div>\r\n        \r\n        <div class=\'client-info\'>\r\n            <h4>Bill To:</h4>\r\n            <p><strong>First Client</strong></p>\r\n            <p>Attn: Mr. Heg</p>\r\n            <p>Accountant</p>\r\n            <p>JLT, Dubai.</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: heg@email.comm</p>\r\n            <p>Phone: 123456789</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Service Description</h4>\r\n            <p><strong>Accounting &amp; Taxation</strong> - He need some services. And more.</p>\r\n        </div>\r\n        \r\n        <table>\r\n            <thead>\r\n                <tr>\r\n                    <th>Description</th>\r\n                    <th>Quantity</th>\r\n                    <th>Unit Price (AED)</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr>\r\n            </thead>\r\n            <tbody>\r\n                <tr>\r\n                    <td>Accounting &amp; Taxation Service</td>\r\n                    <td>1</td>\r\n                    <td>4,000.00</td>\r\n                    <td>4,000.00</td>\r\n                </tr>\r\n                <tr class=\'total-row\'>\r\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\r\n                    <td><strong>4,000.00</strong></td>\r\n                </tr>\r\n            </tbody>\r\n        </table>\r\n        \r\n        <div class=\'payment-terms\'>\r\n            <h4>Payment Terms: Bi-yearly</h4>\r\n            <table>\r\n                <tr>\r\n                    <th>Installment</th>\r\n                    <th>Due</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr><tr>\r\n                    <td>1</td>\r\n                    <td>Half 1</td>\r\n                    <td>2,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Half 2</td>\r\n                    <td>2,000.00</td>\r\n                  </tr></table>\r\n        </div>\r\n        \r\n        <div class=\'bank-details\'>\r\n            <h4>Bank Transfer Details</h4>\r\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\r\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\r\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\r\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\r\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\r\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Notes</h4>\r\n            <ol>\r\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\r\n                <li>Prices are valid for 30 days from the date of issue</li>\r\n                <li>Payment should be made in full before service commencement</li>\r\n                <li>All bank charges are to be borne by the client</li>\r\n                <li>Services will commence upon receipt of payment</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div style=\'float: left; width: 45%;\'>\r\n                <p><strong>Prepared by:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p> </p>\r\n                <p>Sales Consultant</p>\r\n                <p>OGM Business Consultancy</p>\r\n            </div>\r\n            \r\n            <div style=\'float: right; width: 45%; text-align: center;\'>\r\n                <p><strong>For OGM Business Consultancy:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p>Authorized Signature</p>\r\n                <p>Date: December 1, 2025</p>\r\n            </div>\r\n            <div style=\'clear: both;\'></div>\r\n        </div>\r\n    </body>\r\n    </html>', 4000.00, '[{\"installment\":1,\"amount\":\"2,000.00\",\"due_description\":\"Half 1\"},{\"installment\":2,\"amount\":\"2,000.00\",\"due_description\":\"Half 2\"}]', '0000-00-00', 2, '2025-12-01 15:04:57', 'uploads/proformas/proforma_PROF-20251201-0001-V2.html'),
(3, 1, 'PROF-20251201-0001-V3', 3, 3, '<!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proforma Invoice: PROF-20251201-0001-V3</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\r\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\r\n            .section { margin-bottom: 25px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\r\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\r\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFORMA INVOICE</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Invoice Reference: PROF-20251201-0001-V3</h3>\r\n            <p><strong>Date Issued:</strong> December 1, 2025 | <strong>Valid Until:</strong> December 31, 2025</p>\r\n        </div>\r\n        \r\n        <div class=\'company-info\'>\r\n            <h4>From:</h4>\r\n            <p><strong>OGM Business Consultancy</strong></p>\r\n            <p>Business Bay, Dubai</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: info@ogmbusiness.com</p>\r\n            <p>Phone: +971 4 123 4567</p>\r\n            <p>VAT: TRN 123456789012345</p>\r\n        </div>\r\n        \r\n        <div class=\'client-info\'>\r\n            <h4>Bill To:</h4>\r\n            <p><strong>First Client</strong></p>\r\n            <p>Attn: Mr. Heg</p>\r\n            <p>Accountant</p>\r\n            <p>JLT, Dubai.</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: heg@email.comm</p>\r\n            <p>Phone: 123456789</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Service Description</h4>\r\n            <p><strong>Accounting &amp; Taxation</strong> - He need some services. And more.</p>\r\n        </div>\r\n        \r\n        <table>\r\n            <thead>\r\n                <tr>\r\n                    <th>Description</th>\r\n                    <th>Quantity</th>\r\n                    <th>Unit Price (AED)</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr>\r\n            </thead>\r\n            <tbody>\r\n                <tr>\r\n                    <td>Accounting &amp; Taxation Service</td>\r\n                    <td>1</td>\r\n                    <td>4,000.00</td>\r\n                    <td>4,000.00</td>\r\n                </tr>\r\n                <tr class=\'total-row\'>\r\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\r\n                    <td><strong>4,000.00</strong></td>\r\n                </tr>\r\n            </tbody>\r\n        </table>\r\n        \r\n        <div class=\'payment-terms\'>\r\n            <h4>Payment Terms: Bi-yearly</h4>\r\n            <table>\r\n                <tr>\r\n                    <th>Installment</th>\r\n                    <th>Due</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr><tr>\r\n                    <td>1</td>\r\n                    <td>Half 1</td>\r\n                    <td>2,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Half 2</td>\r\n                    <td>2,000.00</td>\r\n                  </tr></table>\r\n        </div>\r\n        \r\n        <div class=\'bank-details\'>\r\n            <h4>Bank Transfer Details</h4>\r\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\r\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\r\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\r\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\r\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\r\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Notes</h4>\r\n            <ol>\r\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\r\n                <li>Prices are valid for 30 days from the date of issue</li>\r\n                <li>Payment should be made in full before service commencement</li>\r\n                <li>All bank charges are to be borne by the client</li>\r\n                <li>Services will commence upon receipt of payment</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div style=\'float: left; width: 45%;\'>\r\n                <p><strong>Prepared by:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p> </p>\r\n                <p>Sales Consultant</p>\r\n                <p>OGM Business Consultancy</p>\r\n            </div>\r\n            \r\n            <div style=\'float: right; width: 45%; text-align: center;\'>\r\n                <p><strong>For OGM Business Consultancy:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p>Authorized Signature</p>\r\n                <p>Date: December 1, 2025</p>\r\n            </div>\r\n            <div style=\'clear: both;\'></div>\r\n        </div>\r\n    </body>\r\n    </html>', 4000.00, '[{\"installment\":1,\"amount\":\"2,000.00\",\"due_description\":\"Half 1\"},{\"installment\":2,\"amount\":\"2,000.00\",\"due_description\":\"Half 2\"}]', '0000-00-00', 2, '2025-12-01 15:05:20', 'uploads/proformas/proforma_PROF-20251201-0001-V3.html'),
(4, 1, 'PROF-20251202-0001-V4', 6, 4, '<!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proforma Invoice: PROF-20251202-0001-V4</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\r\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\r\n            .section { margin-bottom: 25px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\r\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\r\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFORMA INVOICE</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Invoice Reference: PROF-20251202-0001-V4</h3>\r\n            <p><strong>Date Issued:</strong> December 2, 2025 | <strong>Valid Until:</strong> January 1, 2026</p>\r\n        </div>\r\n        \r\n        <div class=\'company-info\'>\r\n            <h4>From:</h4>\r\n            <p><strong>OGM Business Consultancy</strong></p>\r\n            <p>Business Bay, Dubai</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: info@ogmbusiness.com</p>\r\n            <p>Phone: +971 4 123 4567</p>\r\n            <p>VAT: TRN 123456789012345</p>\r\n        </div>\r\n        \r\n        <div class=\'client-info\'>\r\n            <h4>Bill To:</h4>\r\n            <p><strong>First Client</strong></p>\r\n            <p>Attn: Mr. Heg</p>\r\n            <p>Accountant</p>\r\n            <p>JLT, Dubai.</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: heg@email.comm</p>\r\n            <p>Phone: 123456789</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Service Description</h4>\r\n            <p><strong>Accounting &amp; Taxation</strong> - He need some services. And more.</p>\r\n        </div>\r\n        \r\n        <table>\r\n            <thead>\r\n                <tr>\r\n                    <th>Description</th>\r\n                    <th>Quantity</th>\r\n                    <th>Unit Price (AED)</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr>\r\n            </thead>\r\n            <tbody>\r\n                <tr>\r\n                    <td>Accounting &amp; Taxation Service</td>\r\n                    <td>1</td>\r\n                    <td>4,000.00</td>\r\n                    <td>4,000.00</td>\r\n                </tr>\r\n                <tr class=\'total-row\'>\r\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\r\n                    <td><strong>4,000.00</strong></td>\r\n                </tr>\r\n            </tbody>\r\n        </table>\r\n        \r\n        <div class=\'payment-terms\'>\r\n            <h4>Payment Terms: Quarterly</h4>\r\n            <table>\r\n                <tr>\r\n                    <th>Installment</th>\r\n                    <th>Due</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr><tr>\r\n                    <td>1</td>\r\n                    <td>Quarter 1</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Quarter 2</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>3</td>\r\n                    <td>Quarter 3</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>4</td>\r\n                    <td>Quarter 4</td>\r\n                    <td>1,000.00</td>\r\n                  </tr></table>\r\n        </div>\r\n        \r\n        <div class=\'bank-details\'>\r\n            <h4>Bank Transfer Details</h4>\r\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\r\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\r\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\r\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\r\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\r\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Notes</h4>\r\n            <ol>\r\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\r\n                <li>Prices are valid for 30 days from the date of issue</li>\r\n                <li>Payment should be made in full before service commencement</li>\r\n                <li>All bank charges are to be borne by the client</li>\r\n                <li>Services will commence upon receipt of payment</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div style=\'float: left; width: 45%;\'>\r\n                <p><strong>Prepared by:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p> </p>\r\n                <p>Sales Consultant</p>\r\n                <p>OGM Business Consultancy</p>\r\n            </div>\r\n            \r\n            <div style=\'float: right; width: 45%; text-align: center;\'>\r\n                <p><strong>For OGM Business Consultancy:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p>Authorized Signature</p>\r\n                <p>Date: December 2, 2025</p>\r\n            </div>\r\n            <div style=\'clear: both;\'></div>\r\n        </div>\r\n    </body>\r\n    </html>', 4000.00, '[{\"installment\":1,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 1\"},{\"installment\":2,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 2\"},{\"installment\":3,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 3\"},{\"installment\":4,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 4\"}]', '0000-00-00', 2, '2025-12-02 11:00:18', 'uploads/proformas/proforma_PROF-20251202-0001-V4.html');

-- --------------------------------------------------------

--
-- Table structure for table `proforma_reviews`
--

CREATE TABLE `proforma_reviews` (
  `review_id` int(11) NOT NULL,
  `proforma_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `reviewed_by` int(11) NOT NULL,
  `reviewer_role` varchar(50) NOT NULL,
  `review_notes` text DEFAULT NULL,
  `checklist_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`checklist_items`)),
  `signature_data` text DEFAULT NULL,
  `company_stamp` varchar(100) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `rejection_action` varchar(50) DEFAULT NULL,
  `review_result` varchar(20) NOT NULL,
  `reviewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposals`
--

CREATE TABLE `proposals` (
  `proposal_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `proposal_ref` varchar(50) NOT NULL,
  `version` int(11) DEFAULT 1,
  `proposal_content` longtext DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_breakdown` text DEFAULT NULL,
  `prepared_by` int(11) DEFAULT NULL,
  `prepared_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `manager_approved` tinyint(1) DEFAULT 0,
  `manager_approved_by` int(11) DEFAULT NULL,
  `manager_approved_at` timestamp NULL DEFAULT NULL,
  `manager_signature` varchar(255) DEFAULT NULL,
  `ceo_approved` tinyint(1) DEFAULT 0,
  `ceo_approved_by` int(11) DEFAULT NULL,
  `ceo_approved_at` timestamp NULL DEFAULT NULL,
  `ceo_signature` varchar(255) DEFAULT NULL,
  `company_stamp` varchar(255) DEFAULT NULL,
  `status` enum('draft','under_manager_review','under_ceo_review','approved','rejected') DEFAULT 'draft',
  `file_path` varchar(500) DEFAULT NULL
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

-- --------------------------------------------------------

--
-- Table structure for table `proposal_reviews`
--

CREATE TABLE `proposal_reviews` (
  `review_id` int(11) NOT NULL,
  `proposal_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `reviewed_by` int(11) NOT NULL,
  `reviewer_role` varchar(50) NOT NULL,
  `review_notes` text DEFAULT NULL,
  `checklist_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`checklist_items`)),
  `signature_data` text DEFAULT NULL,
  `company_stamp` varchar(100) DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `rejection_action` varchar(50) DEFAULT NULL,
  `review_result` varchar(20) NOT NULL,
  `reviewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `signatures`
--

CREATE TABLE `signatures` (
  `signature_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `signature_data` longtext NOT NULL,
  `signature_type` enum('digital','upload') DEFAULT 'digital',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stamps`
--

CREATE TABLE `stamps` (
  `stamp_id` int(11) NOT NULL,
  `stamp_name` varchar(255) NOT NULL,
  `stamp_data` longtext NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `user_image` varchar(50) NOT NULL,
  `user_role` varchar(20) NOT NULL DEFAULT 'subscriber',
  `user_type` varchar(20) NOT NULL DEFAULT 'client',
  `user_status` varchar(20) NOT NULL DEFAULT 'active',
  `username` varchar(50) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `user_image`, `user_role`, `user_type`, `user_status`, `username`, `user_email`, `password`, `created_at`) VALUES
(2, 'New1', 'Joiner', 'profile_2_1757270810.jpg', 'admin', 'employee', 'active', 'New', 'abc@email.com', '$2y$10$umjaCOlAIDTqyZ2UlvviMuB8BVh0z.0ckCTVCCaQHvMqgQxQgBp8q', '2025-09-07 15:27:08'),
(3, '', '', '', 'subscriber', 'client', 'active', 'Yo', 'xyz@email.com', '$2y$10$YbRTATseEXc4h6DEywJpg.HaGbq4SXMaPK.S6YG8HV0RjW05waUzG', '2025-09-07 22:21:41'),
(4, '', '', '', 'admin', 'client', 'active', 'Tom', 'aaaa.tom20@gmail.com', '$2y$10$TzxTERCQAAYKuQG7enxJbupBqeiunI35.46aiE4Fkxw/OI15l849O', '2025-12-18 04:28:14'),
(7, '', '', '', 'subscriber', 'client', 'active', 'Joiner', 'zzz@email.com', '$2y$10$ObCMY5b2Q174a7zyVL1x1OvRM0AdouCOi9UdNtlwbraRXJhjuXYPC', '2025-12-18 18:04:17'),
(8, 'Abdullah', 'Madaki', 'profile_8_1766121611.jpeg', 'super_admin', 'client', 'active', 'ABM', 'abm@email.com', '$2y$10$eqxeERNtMesBTla36XLQK.xEqprVJfZzdKE65gK/Ad2GyH0WW3ize', '2025-12-18 21:59:49');

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

--
-- Indexes for dumped tables
--

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
-- Indexes for table `client_notes`
--
ALTER TABLE `client_notes`
  ADD PRIMARY KEY (`note_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `user_id` (`user_id`);

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
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD PRIMARY KEY (`enquiry_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_submitted_at` (`submitted_at`);

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
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `client_documents`
--
ALTER TABLE `client_documents`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `client_notes`
--
ALTER TABLE `client_notes`
  MODIFY `note_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `enquiry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `proforma_invoices`
--
ALTER TABLE `proforma_invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `proforma_reviews`
--
ALTER TABLE `proforma_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `proposal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `proposal_reviews`
--
ALTER TABLE `proposal_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `signatures`
--
ALTER TABLE `signatures`
  MODIFY `signature_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stamps`
--
ALTER TABLE `stamps`
  MODIFY `stamp_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

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
-- Constraints for table `client_notes`
--
ALTER TABLE `client_notes`
  ADD CONSTRAINT `client_notes_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `client_notes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

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
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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
-- Constraints for table `signatures`
--
ALTER TABLE `signatures`
  ADD CONSTRAINT `signatures_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `stamps`
--
ALTER TABLE `stamps`
  ADD CONSTRAINT `stamps_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
