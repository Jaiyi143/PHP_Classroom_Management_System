-- Room Management System: single MySQL/MariaDB import.
-- Fresh installs create the tables and sample data. Existing installs keep their data.

CREATE DATABASE IF NOT EXISTS `room_management_system`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `room_management_system`;

CREATE TABLE IF NOT EXISTS `rooms` (
  `room_id` int NOT NULL AUTO_INCREMENT,
  `room_name` varchar(80) NOT NULL,
  `building` varchar(60) NOT NULL,
  `capacity` int NOT NULL DEFAULT 0,
  `status` enum('Vacant','Occupied') NOT NULL DEFAULT 'Vacant',
  PRIMARY KEY (`room_id`),
  UNIQUE KEY `room_name` (`room_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  (10, 'MTB 5', 'MTB Building', 45, 'Occupied')
ON DUPLICATE KEY UPDATE `room_id` = `room_id`;

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_sha256` char(64) NOT NULL,
  `role` enum('admin','student') NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add fields only when upgrading an older users table.
SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email') = 0,
  'ALTER TABLE `users` ADD COLUMN `email` varchar(255) DEFAULT NULL AFTER `username`',
  'SELECT 1'
);
PREPARE room_upgrade FROM @sql;
EXECUTE room_upgrade;
DEALLOCATE PREPARE room_upgrade;

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'phone') = 0,
  'ALTER TABLE `users` ADD COLUMN `phone` varchar(20) DEFAULT NULL AFTER `email`',
  'SELECT 1'
);
PREPARE room_upgrade FROM @sql;
EXECUTE room_upgrade;
DEALLOCATE PREPARE room_upgrade;

SET @sql = IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'email') = 0,
  'ALTER TABLE `users` ADD UNIQUE KEY `email` (`email`)',
  'SELECT 1'
);
PREPARE room_upgrade FROM @sql;
EXECUTE room_upgrade;
DEALLOCATE PREPARE room_upgrade;

-- Convert the former user role to student without removing any accounts.
ALTER TABLE `users` MODIFY COLUMN `role` enum('admin','user','student') NOT NULL;
UPDATE `users` SET `role` = 'student' WHERE `role` = 'user';
ALTER TABLE `users` MODIFY COLUMN `role` enum('admin','student') NOT NULL;

INSERT INTO `users` (`username`, `password_sha256`, `role`) VALUES
  ('admin', SHA2('admin123', 256), 'admin'),
  ('user', SHA2('user123', 256), 'student')
ON DUPLICATE KEY UPDATE `username` = `username`;
