-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2026 at 01:03 PM
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
-- Database: `room_management_system`
--

CREATE DATABASE IF NOT EXISTS `room_management_system`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `room_management_system`;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_name` varchar(80) NOT NULL,
  `building` varchar(60) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0,
  `status` enum('Vacant','Occupied') NOT NULL DEFAULT 'Vacant'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_name`, `building`, `capacity`, `status`) VALUES
(1, 'COMP Lab 1', 'Engineering Building', 30, 'Occupied'),
(2, 'COMP Lab 2', 'Engineering Building', 30, 'Vacant'),
(3, 'COMP Lab 3', 'Engineering Building', 30, 'Vacant'),
(4, 'ELEX LAB 1', 'Engineering Building', 30, 'Occupied'),
(5, 'ELEX LAB 2', 'Engineering Building', 30, 'Vacant'),
(6, 'MT 1', 'MET Building', 35, 'Vacant'),
(7, 'MT 2', 'MET Building', 35, 'Occupied'),
(8, 'MT 3', 'MET Building', 35, 'Vacant'),
(9, 'Study Room', 'Library', 25, 'Vacant'),
(10, 'MTB 5', 'MTB Building', 45, 'Occupied');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `room_name` (`room_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_sha256` char(64) NOT NULL,
  `role` enum('admin','user') NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`username`, `password_sha256`, `role`) VALUES
  ('admin', SHA2('admin123', 256), 'admin'),
  ('user', SHA2('user123', 256), 'user')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
