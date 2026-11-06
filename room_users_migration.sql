-- Run once if the users table was created before sign-up was added.
ALTER TABLE `users`
  ADD COLUMN `email` varchar(255) DEFAULT NULL AFTER `username`,
  ADD COLUMN `phone` varchar(20) DEFAULT NULL AFTER `email`,
  ADD UNIQUE KEY `email` (`email`);
