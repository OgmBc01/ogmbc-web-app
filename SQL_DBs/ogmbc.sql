-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 05:38 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
(1, 'New Bank11', 'Me Bank Account', '44444', '44444767', '4444', 'UAE', 'hhhbbb', 'GBP', 0, '2025-11-23 17:24:41', '2025-12-01 10:56:38');

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
(1, 'Business Setup', 0.00),
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
(1, 'First Client', '12345678000', 'United Arab Emirates', 'Dubai', 'Gym Service', 'JLT, Dubai.', 'Mr. Heg', 'Accountant', '123456789', 'heg@email.comm', 2, 'He need some services. And more.', '2025-12-03', 'AED', 'Quarterly', 4000.00, 'digital_marketing', 'Proposal Drafted', 3, 3, '2025-11-30 17:21:50', '2025-12-02 04:18:10');

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
(18, 1, 'New Dox', 'bank_statement', 'uploads/client_documents/doc_1_1764542837_9018_OGM-Letter-Head.pdf', 2, '2025-11-30 22:47:17');

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
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `post_title` varchar(255) NOT NULL,
  `post_slug` varchar(255) NOT NULL,
  `post_content` text NOT NULL,
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
(5, 'First Post', 'first-post', 'Free, high quality, open source icon library with over 2,000 icons. Include them anyway you like—SVGs, SVG sprite, or web fonts. Use them with or without Bootstrap in any project.', 'Use them with or without Bootstrap in any project.', 2, 'published', 'post_1757516437_6889.png', 'Hi', 'Hi', 'first, post, here', '2025-09-10 16:00:37', '2025-09-10 16:00:37'),
(6, 'Second Post', 'second-post', 'Free, high quality, open source icon library with over 1300 icons. Include them anyway you like—SVGs, SVG sprite, or web fonts.', 'Free, high quality, open source icon library with over 1300 icons.', 2, 'published', 'post_1757516749_9150.png', 'Hi yes', 'hi', 'first, post, here', '2025-09-10 16:05:49', '2025-09-10 16:05:49'),
(9, 'Second Post Edited', 'second-post-edited', 'posts.php?source=add_post', 'posts.php?source=add_post', 2, 'published', 'post_1757516919_7916.png', '', '', '', '2025-09-10 16:07:47', '2025-09-10 16:08:39'),
(11, 'Final Test', 'final-test', 'Select a target file size as a percentage (0 - 10000%) of the original. Smaller values compress more. For example, a 100Mb file would become 25Mb if you select 25%.\r\n\r\nSelect a target file size as a percentage (0 - 10000%) of the original. Smaller values compress more. For example, a 100Mb file would become 25Mb if you select 25%.', 'For example, a 100Mb file would become 25Mb if you select 25%. Select a target file size as a percentage (0 - 10000%) of the original. Smaller values compress more.', 2, 'draft', 'post_1757545508_3715.jpg', 'Header', 'Header description', 'first, post, here, edited', '2025-09-11 00:05:08', '2025-09-11 00:07:36');

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
(3, 1, 'PROF-20251201-0001-V3', 3, 3, '<!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proforma Invoice: PROF-20251201-0001-V3</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .company-info { float: right; text-align: right; margin-bottom: 30px; }\r\n            .client-info { margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px; }\r\n            .section { margin-bottom: 25px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 20px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total-row { background-color: #f8f9fa; font-weight: bold; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; font-size: 0.9em; }\r\n            .payment-terms { background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #ffc107; }\r\n            .bank-details { background-color: #e7f5ff; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFORMA INVOICE</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Invoice Reference: PROF-20251201-0001-V3</h3>\r\n            <p><strong>Date Issued:</strong> December 1, 2025 | <strong>Valid Until:</strong> December 31, 2025</p>\r\n        </div>\r\n        \r\n        <div class=\'company-info\'>\r\n            <h4>From:</h4>\r\n            <p><strong>OGM Business Consultancy</strong></p>\r\n            <p>Business Bay, Dubai</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: info@ogmbusiness.com</p>\r\n            <p>Phone: +971 4 123 4567</p>\r\n            <p>VAT: TRN 123456789012345</p>\r\n        </div>\r\n        \r\n        <div class=\'client-info\'>\r\n            <h4>Bill To:</h4>\r\n            <p><strong>First Client</strong></p>\r\n            <p>Attn: Mr. Heg</p>\r\n            <p>Accountant</p>\r\n            <p>JLT, Dubai.</p>\r\n            <p>United Arab Emirates</p>\r\n            <p>Email: heg@email.comm</p>\r\n            <p>Phone: 123456789</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Service Description</h4>\r\n            <p><strong>Accounting &amp; Taxation</strong> - He need some services. And more.</p>\r\n        </div>\r\n        \r\n        <table>\r\n            <thead>\r\n                <tr>\r\n                    <th>Description</th>\r\n                    <th>Quantity</th>\r\n                    <th>Unit Price (AED)</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr>\r\n            </thead>\r\n            <tbody>\r\n                <tr>\r\n                    <td>Accounting &amp; Taxation Service</td>\r\n                    <td>1</td>\r\n                    <td>4,000.00</td>\r\n                    <td>4,000.00</td>\r\n                </tr>\r\n                <tr class=\'total-row\'>\r\n                    <td colspan=\'3\' style=\'text-align: right;\'><strong>Total Amount:</strong></td>\r\n                    <td><strong>4,000.00</strong></td>\r\n                </tr>\r\n            </tbody>\r\n        </table>\r\n        \r\n        <div class=\'payment-terms\'>\r\n            <h4>Payment Terms: Bi-yearly</h4>\r\n            <table>\r\n                <tr>\r\n                    <th>Installment</th>\r\n                    <th>Due</th>\r\n                    <th>Amount (AED)</th>\r\n                </tr><tr>\r\n                    <td>1</td>\r\n                    <td>Half 1</td>\r\n                    <td>2,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Half 2</td>\r\n                    <td>2,000.00</td>\r\n                  </tr></table>\r\n        </div>\r\n        \r\n        <div class=\'bank-details\'>\r\n            <h4>Bank Transfer Details</h4>\r\n            <p><strong>Bank Name:</strong> Emirates NBD</p>\r\n            <p><strong>Account Name:</strong> OGM Business Consultancy</p>\r\n            <p><strong>Account Number:</strong> 1234 5678 9012</p>\r\n            <p><strong>IBAN:</strong> AE123456789012345678901</p>\r\n            <p><strong>Swift Code:</strong> EBILAEAD</p>\r\n            <p><strong>Branch:</strong> Business Bay, Dubai</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <h4>Notes</h4>\r\n            <ol>\r\n                <li>This is a proforma invoice and should not be considered as a demand for payment</li>\r\n                <li>Prices are valid for 30 days from the date of issue</li>\r\n                <li>Payment should be made in full before service commencement</li>\r\n                <li>All bank charges are to be borne by the client</li>\r\n                <li>Services will commence upon receipt of payment</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div style=\'float: left; width: 45%;\'>\r\n                <p><strong>Prepared by:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p> </p>\r\n                <p>Sales Consultant</p>\r\n                <p>OGM Business Consultancy</p>\r\n            </div>\r\n            \r\n            <div style=\'float: right; width: 45%; text-align: center;\'>\r\n                <p><strong>For OGM Business Consultancy:</strong></p>\r\n                <br><br>\r\n                <p>_________________________</p>\r\n                <p>Authorized Signature</p>\r\n                <p>Date: December 1, 2025</p>\r\n            </div>\r\n            <div style=\'clear: both;\'></div>\r\n        </div>\r\n    </body>\r\n    </html>', 4000.00, '[{\"installment\":1,\"amount\":\"2,000.00\",\"due_description\":\"Half 1\"},{\"installment\":2,\"amount\":\"2,000.00\",\"due_description\":\"Half 2\"}]', '0000-00-00', 2, '2025-12-01 15:05:20', 'uploads/proformas/proforma_PROF-20251201-0001-V3.html');

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
(5, 1, 'PROP-20251202-0001-V5', 5, '\r\n    <!DOCTYPE html>\r\n    <html>\r\n    <head>\r\n        <meta charset=\'UTF-8\'>\r\n        <title>Proposal: PROP-20251202-0001-V5</title>\r\n        <style>\r\n            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }\r\n            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }\r\n            .section { margin-bottom: 30px; }\r\n            .section-title { background: #f8f9fa; padding: 12px; font-weight: bold; border-left: 4px solid #007bff; margin-bottom: 15px; }\r\n            table { width: 100%; border-collapse: collapse; margin: 15px 0; }\r\n            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }\r\n            th { background-color: #f2f2f2; font-weight: bold; }\r\n            .total { font-weight: bold; font-size: 1.2em; background-color: #f8f9fa; }\r\n            .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; }\r\n            .signature-box { margin-top: 50px; }\r\n            .company-logo { text-align: center; margin-bottom: 20px; }\r\n        </style>\r\n    </head>\r\n    <body>\r\n        <div class=\'company-logo\'>\r\n            <h1>OGM BUSINESS CONSULTANCY</h1>\r\n            <h2>PROFESSIONAL SERVICE PROPOSAL</h2>\r\n        </div>\r\n        \r\n        <div class=\'header\'>\r\n            <h3>Proposal Reference: PROP-20251202-0001-V5</h3>\r\n            <p><strong>Date:</strong> December 2, 2025 | <strong>Valid Until:</strong> January 1, 2026</p>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Client Information</div>\r\n            <table>\r\n                <tr>\r\n                    <td width=\'30%\'><strong>Company Name:</strong></td>\r\n                    <td>First Client</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Contact Person:</strong></td>\r\n                    <td>Mr. Heg</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Email Address:</strong></td>\r\n                    <td>heg@email.comm</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Phone Number:</strong></td>\r\n                    <td>123456789</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Address:</strong></td>\r\n                    <td>JLT, Dubai., United Arab Emirates</td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Service Details</div>\r\n            <table>\r\n                <tr>\r\n                    <td width=\'30%\'><strong>Service Type:</strong></td>\r\n                    <td>Accounting &amp; Taxation</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Service Description:</strong></td>\r\n                    <td>He need some services. And more.</td>\r\n                </tr>\r\n                <tr>\r\n                    <td><strong>Expected Start Date:</strong></td>\r\n                    <td>December 3, 2025</td>\r\n                </tr>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Financial Proposal</div>\r\n            <table>\r\n                <thead>\r\n                    <tr>\r\n                        <th>Description</th>\r\n                        <th>Amount (AED)</th>\r\n                    </tr>\r\n                </thead>\r\n                <tbody>\r\n                    <tr>\r\n                        <td>Accounting &amp; Taxation Service</td>\r\n                        <td>4,000.00</td>\r\n                    </tr>\r\n                    <tr class=\'total\'>\r\n                        <td><strong>Total Amount</strong></td>\r\n                        <td><strong>4,000.00</strong></td>\r\n                    </tr>\r\n                </tbody>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Payment Schedule</div>\r\n            <p><strong>Payment Term:</strong> Quarterly</p>\r\n            <table>\r\n                <thead>\r\n                    <tr>\r\n                        <th>Installment</th>\r\n                        <th>Due</th>\r\n                        <th>Amount (AED)</th>\r\n                    </tr>\r\n                </thead>\r\n                <tbody><tr>\r\n                    <td>1</td>\r\n                    <td>Quarter 1</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>2</td>\r\n                    <td>Quarter 2</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>3</td>\r\n                    <td>Quarter 3</td>\r\n                    <td>1,000.00</td>\r\n                  </tr><tr>\r\n                    <td>4</td>\r\n                    <td>Quarter 4</td>\r\n                    <td>1,000.00</td>\r\n                  </tr></tbody>\r\n            </table>\r\n        </div>\r\n        \r\n        <div class=\'section\'>\r\n            <div class=\'section-title\'>Terms & Conditions</div>\r\n            <ol>\r\n                <li>This proposal is valid for 30 days from the date of issue.</li>\r\n                <li>Services will commence upon receipt of the signed proposal and initial payment.</li>\r\n                <li>All payments are to be made in AED.</li>\r\n                <li>Any additional services requested will be billed separately.</li>\r\n                <li>Either party may terminate this agreement with 30 days written notice.</li>\r\n            </ol>\r\n        </div>\r\n        \r\n        <div class=\'footer\'>\r\n            <div class=\'signature-box\'>\r\n                <div style=\'float: left; width: 45%;\'>\r\n                    <p><strong>For OGM Business Consultancy:</strong></p>\r\n                    <br><br><br>\r\n                    <p>_________________________</p>\r\n                    <p>Authorized Signature</p>\r\n                    <p>Name:  </p>\r\n                    <p>Position: Sales Consultant</p>\r\n                    <p>Date: December 2, 2025</p>\r\n                </div>\r\n                \r\n                <div style=\'float: right; width: 45%;\'>\r\n                    <p><strong>Accepted by Client:</strong></p>\r\n                    <br><br><br>\r\n                    <p>_________________________</p>\r\n                    <p>Authorized Signature</p>\r\n                    <p>Name: ___________________</p>\r\n                    <p>Position: ________________</p>\r\n                    <p>Date: ___________________</p>\r\n                </div>\r\n                <div style=\'clear: both;\'></div>\r\n            </div>\r\n        </div>\r\n    </body>\r\n    </html>', 4000.00, '[{\"installment\":1,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 1\"},{\"installment\":2,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 2\"},{\"installment\":3,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 3\"},{\"installment\":4,\"amount\":\"1,000.00\",\"due_description\":\"Quarter 4\"}]', 2, '2025-12-02 02:09:53', 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 'draft', '../uploads/proposals/proposal_PROP-20251202-0001-V5.html');

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
(1, '', '', '', 'sales', 'client', 'active', 'User', 'user@gmail.com', '$2y$10$JCtHL7tcZ8IzTzH59QGZhulgveduJkI9LDG5h.UDT8BGzVxkAt8Jq', '2025-09-05 21:41:39'),
(2, 'New1', 'Joiner', 'profile_2_1757270810.jpg', 'admin', 'employee', 'active', 'New', 'abc@email.com', '$2y$10$umjaCOlAIDTqyZ2UlvviMuB8BVh0z.0ckCTVCCaQHvMqgQxQgBp8q', '2025-09-07 15:27:08'),
(3, '', '', '', 'subscriber', 'client', 'active', 'Yo', 'xyz@email.com', '$2y$10$YbRTATseEXc4h6DEywJpg.HaGbq4SXMaPK.S6YG8HV0RjW05waUzG', '2025-09-07 22:21:41');

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
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

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
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `proforma_invoices`
--
ALTER TABLE `proforma_invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `proforma_reviews`
--
ALTER TABLE `proforma_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `proposal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
