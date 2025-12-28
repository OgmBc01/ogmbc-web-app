-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2025 at 10:10 AM
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD UNIQUE KEY `post_slug` (`post_slug`),
  ADD KEY `post_author` (`post_author`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`post_author`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
