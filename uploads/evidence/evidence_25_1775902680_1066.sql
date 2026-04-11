-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: second_mysql
-- Generation Time: Apr 10, 2026 at 06:31 PM
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
-- Table structure for table `jurisdictions`
--

CREATE TABLE `jurisdictions` (
  `jurisdiction_id` int NOT NULL,
  `jurisdiction_name` varchar(100) NOT NULL,
  `country_id` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jurisdictions`
--

INSERT INTO `jurisdictions` (`jurisdiction_id`, `jurisdiction_name`, `country_id`, `is_active`, `created_at`) VALUES
(1, 'Dubai Mainland', 'United Arab Emirates', 1, '2026-03-04 13:33:26'),
(2, 'Dubai Freezone', 'United Arab Emirates', 1, '2026-03-04 13:33:26'),
(3, 'Abu Dhabi Global Market (ADGM)', 'United Arab Emirates', 1, '2026-03-04 13:33:26'),
(4, 'Dubai International Financial Centre (DIFC)', 'United Arab Emirates', 1, '2026-03-04 13:33:26'),
(5, 'Sharjah Freezone', 'United Arab Emirates', 1, '2026-03-04 13:33:26'),
(6, 'Ras Al Khaimah Freezone', 'United Arab Emirates', 1, '2026-03-04 13:33:26'),
(7, 'Saudi Arabia Mainland', 'Saudi Arabia', 1, '2026-03-04 13:33:26'),
(8, 'Riyadh Freezone', 'Saudi Arabia', 1, '2026-03-04 13:33:26'),
(9, 'Qatar Financial Centre (QFC)', 'Qatar', 1, '2026-03-04 13:33:26'),
(10, 'Qatar Mainland', 'Qatar', 1, '2026-03-04 13:33:26'),
(11, 'Abu Dhabi Global Market (ADGM)', NULL, 1, '2026-04-08 03:30:33'),
(12, 'Khalifa Industrial Zone Abu Dhabi (KIZAD / KEZAD)', NULL, 1, '2026-04-08 03:30:33'),
(13, 'Abu Dhabi Airport Free Zone (ADAFZ)', NULL, 1, '2026-04-08 03:30:33'),
(14, 'Masdar City Free Zone', NULL, 1, '2026-04-08 03:30:33'),
(15, 'twofour54', NULL, 1, '2026-04-08 03:30:33'),
(16, 'Industrial City of Abu Dhabi (ICAD)', NULL, 1, '2026-04-08 03:30:33'),
(17, 'ZonesCorp', NULL, 1, '2026-04-08 03:30:33'),
(18, 'Abu Dhabi Ports Free Zones', NULL, 1, '2026-04-08 03:30:33'),
(19, 'Jebel Ali Free Zone (JAFZA)', NULL, 1, '2026-04-08 03:30:33'),
(20, 'Dubai Multi Commodities Centre (DMCC)', NULL, 1, '2026-04-08 03:30:33'),
(21, 'Dubai Airport Free Zone (DAFZA)', NULL, 1, '2026-04-08 03:30:33'),
(22, 'Dubai International Financial Centre (DIFC)', NULL, 1, '2026-04-08 03:30:33'),
(23, 'Dubai Silicon Oasis (DSO)', NULL, 1, '2026-04-08 03:30:33'),
(24, 'Dubai Internet City (DIC)', NULL, 1, '2026-04-08 03:30:33'),
(25, 'Dubai Media City (DMC)', NULL, 1, '2026-04-08 03:30:33'),
(26, 'Dubai Knowledge Park / Academic City', NULL, 1, '2026-04-08 03:30:33'),
(27, 'Dubai Healthcare City (DHCC)', NULL, 1, '2026-04-08 03:30:33'),
(28, 'Dubai Design District (D3)', NULL, 1, '2026-04-08 03:30:33'),
(29, 'Dubai Science Park', NULL, 1, '2026-04-08 03:30:33'),
(30, 'Dubai Production City (IMPZ)', NULL, 1, '2026-04-08 03:30:33'),
(31, 'Dubai Outsource City', NULL, 1, '2026-04-08 03:30:33'),
(32, 'Dubai Industrial City', NULL, 1, '2026-04-08 03:30:33'),
(33, 'Dubai South (DWC)', NULL, 1, '2026-04-08 03:30:33'),
(34, 'Meydan Free Zone', NULL, 1, '2026-04-08 03:30:33'),
(35, 'International Free Zone Authority (IFZA)', NULL, 1, '2026-04-08 03:30:33'),
(36, 'Dubai CommerCity', NULL, 1, '2026-04-08 03:30:33'),
(37, 'Dubai Logistics City', NULL, 1, '2026-04-08 03:30:33'),
(38, 'Dubai Gold & Diamond Park', NULL, 1, '2026-04-08 03:30:33'),
(39, 'International Humanitarian City', NULL, 1, '2026-04-08 03:30:33'),
(40, 'DUQE Free Zone', NULL, 1, '2026-04-08 03:30:33'),
(41, 'Dubai Textile City', NULL, 1, '2026-04-08 03:30:33'),
(42, 'Sharjah Airport International Free Zone (SAIF Zone)', NULL, 1, '2026-04-08 03:30:33'),
(43, 'Hamriyah Free Zone', NULL, 1, '2026-04-08 03:30:33'),
(44, 'Sharjah Media City (SHAMS)', NULL, 1, '2026-04-08 03:30:33'),
(45, 'Sharjah Research Technology & Innovation Park (SRTIP)', NULL, 1, '2026-04-08 03:30:33'),
(46, 'Sharjah Publishing City (SPC Free Zone)', NULL, 1, '2026-04-08 03:30:33'),
(47, 'Sharjah Communications Technology Free Zone (COMTECH)', NULL, 1, '2026-04-08 03:30:33'),
(48, 'USA Regional Trade Center Free Zone', NULL, 1, '2026-04-08 03:30:33'),
(49, 'Ajman Free Zone (AFZ)', NULL, 1, '2026-04-08 03:30:33'),
(50, 'Ajman Media City Free Zone', NULL, 1, '2026-04-08 03:30:33'),
(51, 'Ajman NuVentures Centre Free Zone (ANCFZ)', NULL, 1, '2026-04-08 03:30:33'),
(52, 'Ras Al Khaimah Economic Zone (RAKEZ)', NULL, 1, '2026-04-08 03:30:33'),
(53, 'RAK Free Trade Zone (RAK FTZ)', NULL, 1, '2026-04-08 03:30:33'),
(54, 'RAK Maritime City Free Zone', NULL, 1, '2026-04-08 03:30:33'),
(55, 'RAK Investment Authority (RAKIA)', NULL, 1, '2026-04-08 03:30:33'),
(56, 'RAK Media City', NULL, 1, '2026-04-08 03:30:33'),
(57, 'Fujairah Free Zone (FFZ)', NULL, 1, '2026-04-08 03:30:33'),
(58, 'Fujairah Creative City', NULL, 1, '2026-04-08 03:30:33'),
(59, 'Fujairah Oil Industry Zone (FOIZ)', NULL, 1, '2026-04-08 03:30:33'),
(60, 'Fujairah Port Free Zone', NULL, 1, '2026-04-08 03:30:33'),
(61, 'Umm Al Quwain Free Trade Zone (UAQ FTZ)', NULL, 1, '2026-04-08 03:30:33'),
(62, 'Abu Dhabi Department of Economic Development (ADDED)', NULL, 1, '2026-04-08 03:30:33'),
(63, 'Dubai Department of Economy & Tourism (DET / DED)', NULL, 1, '2026-04-08 03:30:33'),
(64, 'Sharjah Economic Development Department (SEDD)', NULL, 1, '2026-04-08 03:30:33'),
(65, 'Ajman Department of Economic Development', NULL, 1, '2026-04-08 03:30:33'),
(66, 'Ras Al Khaimah Economic Department', NULL, 1, '2026-04-08 03:30:33'),
(67, 'Fujairah Department of Industry & Economy', NULL, 1, '2026-04-08 03:30:33'),
(68, 'Umm Al Quwain Department of Economic Development', NULL, 1, '2026-04-08 03:30:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jurisdictions`
--
ALTER TABLE `jurisdictions`
  ADD PRIMARY KEY (`jurisdiction_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jurisdictions`
--
ALTER TABLE `jurisdictions`
  MODIFY `jurisdiction_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
