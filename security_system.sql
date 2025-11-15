-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2025 at 01:09 PM
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
-- Database: `security_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `created_at`) VALUES
(1, 2, 'USER_REGISTERED', 'Registered with email: zuccacwl@gmail.com', '2025-11-15 11:45:05'),
(2, 2, 'TICKET_CREATED', 'Ticket #3 created with title: FIX THIS', '2025-11-15 11:45:38'),
(3, 1, 'LOGIN_SUCCESS', 'User wnglkrx11311@gmail.com logged in successfully', '2025-11-15 11:45:50'),
(4, 1, 'TICKET_STATUS_CHANGED', 'Admin changed ticket #3 status to Resolved', '2025-11-15 11:46:24'),
(5, 1, 'TICKET_STATUS_CHANGED', 'Admin changed ticket #3 status to In Progress', '2025-11-15 11:46:25'),
(6, 1, 'TICKET_STATUS_CHANGED', 'Admin changed ticket #3 status to Open', '2025-11-15 11:46:26'),
(7, 1, 'TICKET_STATUS_CHANGED', 'Admin changed ticket #3 status to In Progress', '2025-11-15 11:46:27'),
(8, 1, 'TICKET_STATUS_CHANGED', 'Admin changed ticket #3 status to Resolved', '2025-11-15 11:46:27'),
(9, 1, 'TICKET_STATUS_CHANGED', 'Admin changed ticket #3 status to Open', '2025-11-15 11:46:28'),
(10, 1, 'TICKET_STATUS_CHANGED', 'Admin changed ticket #1 status to Closed', '2025-11-15 11:46:31'),
(11, 1, 'TICKET_CREATED', 'Ticket #4 created with title: FIXIT', '2025-11-15 11:48:40'),
(12, 2, 'LOGIN_FAILED', 'Wrong password for zuccacwl@gmail.com', '2025-11-15 11:49:06');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text NOT NULL,
  `status` enum('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `title`, `category`, `description`, `status`, `created_at`) VALUES
(1, 1, 'Please help', 'fix', 'fix', 'Closed', '2025-11-15 11:22:42'),
(2, 1, 'Fix 1', 'lap', 'lap', 'In Progress', '2025-11-15 11:24:49'),
(3, 2, 'FIX THIS', 'FIX', 'FIX', 'Open', '2025-11-15 11:45:38'),
(4, 1, 'FIXIT', '', 'FIXIT', 'Open', '2025-11-15 11:48:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'testing', 'wnglkrx11311@gmail.com', 'Wl4912262', 'admin', '2025-11-15 11:15:20'),
(2, 'testing2', 'zuccacwl@gmail.com', 'Wl220220', 'user', '2025-11-15 11:45:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
