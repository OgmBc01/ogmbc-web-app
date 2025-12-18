-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2025 at 10:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
(3, '', '', '', 'subscriber', 'client', 'active', 'Yo', 'xyz@email.com', '123456', '2025-09-07 22:21:41');

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
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
