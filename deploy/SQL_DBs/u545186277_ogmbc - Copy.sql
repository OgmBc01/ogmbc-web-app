-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 23, 2025 at 02:34 PM
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
-- Database: `u545186277_ogmbc`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `cat_id` int(11) NOT NULL,
  `cat_title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`cat_id`, `cat_title`) VALUES
(1, 'Business Setup'),
(2, 'Accounting & Taxation');

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
(1, 2, 'abc@email.com', '123456', 'New1', 'Joiner', 'profile_2_1757270810.jpg', 'IT', 'Engineer', 'MSc', '2021', '2025-09-07 15:27:08');

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
(11, 'Final Test on Mobile', 'final-test', 'Select a target file size as a percentage (0 - 10000%) of the original. Smaller values compress more. For example, a 100Mb file would become 25Mb if you select 25%.\r\n\r\nSelect a target file size as a percentage (0 - 10000%) of the original. Smaller values compress more. For example, a 100Mb file would become 25Mb if you select 25%.', 'For example, a 100Mb file would become 25Mb if you select 25%. Select a target file size as a percentage (0 - 10000%) of the original. Smaller values compress more.', 2, 'draft', 'post_1757545508_3715.jpg', 'Header', 'Header description', 'first, post, here, edited', '2025-09-11 00:05:08', '2025-09-11 11:01:47'),
(12, 'Why Should Startups in Dubai Consider Professional Due Diligence Services?', 'why-should-startups-in-dubai-consider-professional-due-diligence-services', 'Dubai has become a great source of business opportunities because of the city\'s strength in economy, business-friendly policies, and strategic location. However, intricate planning and informed decisions are needed to navigate the complexities of establishing and scaling a startup. Among other things, professional Due Diligence in Dubai is one of the most critical steps to ensure success for a startup.\r\nOGM Business Consultants has provided comprehensive due diligence services to Dubai startups and fostered strategic and confident business decision-making. Here\'s why due diligence services should be embraced as a part of their business journey.\r\nWhat Is Due Diligence?\r\nDue diligence systematically measures a company\'s financial, legal, operational, and market position to identify potential risks and opportunities. For startups, this often involves the following:\r\n•	Verification of the accuracy of financial records.\r\n•	Legal compliance.\r\n•	Market and competition study.\r\n•	Liability identification\r\nProfessional due diligence services by Dubai can provide a deeper understanding of a business\'s environment to startups, thus avoiding risks and taking advantage of the opportunities.\r\nWhy Do Startups Need Due Diligence?\r\n1. Informed Decision-Making\r\nEvery decision a startup takes- be it for a partnership, investment, or acquisition- embodies heavy risks. Due diligence endows the founder with correct and authentic data, through which they can make informed decisions that reflect their objectives for the business.\r\n2. Potential Investors\r\nInvestors in Dubai are extremely wary of investing in companies. They prefer businesses that are transparent and have high growth potential. Professional due diligence services in Dubai present a comprehensive and in-depth analysis of your startup\'s financial health, market position, and compliance. This, in turn, boosts the confidence of prospective investors.\r\n3. Reduced Legal and Financial Risk End\r\nDubai startups have to follow a myriad of local regulations as well as tax policies. Non-compliance has resulted in lawsuits, fines, or even the closure of businesses. Prudent due diligence will point out possible pitfalls in the legal and financial arena. In other words, your startup cannot breach the law.\r\n4. Reviewing Business Partnerships\r\nCollaborating with vendors, suppliers, or other businesses is often necessary for growth. Due diligence helps assess the credibility and stability of potential partners, ensuring your collaborations are mutually beneficial and risk-free.\r\nHow Do Professional Due Diligence Services Benefit Startups?\r\n1. Expertise and Local Knowledge\r\nThe due diligence process in Dubai is complex and demanding for the consulting firm. OGM Business Consultants is well experienced in such a complex environment and thus can serve a startup based on such experience.\r\n2. Time and Resource Optimization\r\nValue time for a startup. It has no other important strategic business activities besides processing various evaluations. Outsourcing it to professionals saves this kind of time and other resources involved in due diligence.\r\n3. Customized Solution\r\nEvery startup has its specific needs and issues. Professional services like OGM Business Consultants provide customized due diligence solutions that address specific requirements and ensure maximum relevance and value.\r\nWhy Choose OGM Business Consultants?\r\nAt OGM Business Consultants, we commit to making startups thrive in the highly competitive Dubai market. Our due diligence services in Dubai include:\r\n•	Comprehensive financial analysis.\r\n•	Conducting legal compliance checks.\r\n•	Market and competitive assessments.\r\n•	Risk identification and mitigation strategies.\r\nConclusion\r\nDue diligence services are a must for startups in Dubai. This is because they help ensure proper decision-making and reduce risks associated with opening businesses. It also can help attract investors and set strong grounds for sustainable growth.\r\nPartnering with OGM Business Consultants will give you the confidence to navigate the business landscape of Dubai and focus more on the success of your startup. Let us help you find risks, identify opportunities, and make your entrepreneurial dreams come true with precision and expertise.', '', 1, 'published', '', 'Why Should Startups in Dubai Consider Professional Due Diligence Services?', '', '', '2025-10-02 08:55:29', '2025-10-18 08:20:34');

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
(1, '', '', '', 'subscriber', 'client', 'active', 'User', 'user@gmail.com', '123456', '2025-09-05 21:41:39'),
(2, 'New1', 'Joiner', 'profile_2_1757270810.jpg', 'subscriber', 'employee', 'active', 'New', 'abc@email.com', '123456', '2025-09-07 15:27:08'),
(3, '', '', '', 'subscriber', 'client', 'active', 'Yo', 'xyz@email.com', '123456', '2025-09-07 22:21:41'),
(4, '', '', '', 'subscriber', 'client', 'active', 'abc', 'abc111@gmail.com', '123456', '2025-09-08 12:58:21'),
(5, '', '', '', 'subscriber', 'client', 'active', 'rluhuwUVbv', 'qivadaja036@gmail.com', 'lTDCf2JCWSigRA!', '2025-09-10 03:06:32'),
(6, '', '', '', 'subscriber', 'client', 'active', 'TIPyoOjKjxHaPN', 'ayisuxafe846@gmail.com', 'DAkAoTkAH8mZnq!', '2025-09-15 09:40:31'),
(7, '', '', '', 'subscriber', 'client', 'active', 'aiwzPxfQc', 'movjadkmswakhy@yahoo.com', 'TxWW3DRrKjd1YG!', '2025-09-15 11:57:31'),
(8, '', '', '', 'subscriber', 'client', 'active', 'ghJiJtGohTKwA', 'uziyuwida16@gmail.com', '9OJyJVk9JoRWE3!', '2025-09-20 10:35:40'),
(9, '', '', '', 'subscriber', 'client', 'active', 'oDKXZivm', 'vacibujesa61@gmail.com', 'llBDv8JT8Ia3US!', '2025-09-20 20:35:23'),
(10, 'hhh', 'hhh', '', 'subscriber', 'client', 'active', 'memek', 'ratujotos@gmail.com', 'memek888', '2025-10-18 08:18:14');

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
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`cat_id`);

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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
