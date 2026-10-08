-- ========================================================
-- Community Lost and Found System - Database Schema & Sample Data
-- Database Management System: MySQL / MariaDB
-- ========================================================
CREATE DATABASE lost_and_found_db;
USE lost_and_found_db;

CREATE TABLE `roles` (
  `role_id` INT AUTO_INCREMENT PRIMARY KEY,
  `role` VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE `categories` (
  `category_id` INT AUTO_INCREMENT PRIMARY KEY,
  `category` VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE `i_status` (
  `status_id` INT AUTO_INCREMENT PRIMARY KEY,
  `status` VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE `c_status` (
  `status_id` INT AUTO_INCREMENT PRIMARY KEY,
  `status` VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `passwordhash` VARCHAR(255) NOT NULL,
  `firstname` VARCHAR(50) NOT NULL,
  `lastname` VARCHAR(50) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE RESTRICT
);

CREATE TABLE `items` (
  `item_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `status_id` INT NOT NULL DEFAULT 1,
  `item_type` ENUM('Lost', 'Found') NOT NULL,
  `category_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `location` VARCHAR(150) NOT NULL,
  `event_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_item_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_status` FOREIGN KEY (`status_id`) REFERENCES `i_status` (`status_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_item_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE RESTRICT,
  INDEX `idx_item_type` (`item_type`)
);

CREATE TABLE `claims` (
  `claim_id` INT AUTO_INCREMENT PRIMARY KEY,
  `claimer_user_id` INT NOT NULL,
  `status_id` INT NOT NULL DEFAULT 1,
  `item_id` INT NOT NULL,
  `proof_description` TEXT NOT NULL,
  `admin_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_claim_user` FOREIGN KEY (`claimer_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_claim_status` FOREIGN KEY (`status_id`) REFERENCES `c_status` (`status_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_claim_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE
);

INSERT INTO `roles` (`role_id`, `role`) VALUES
(1, 'admin'),
(2, 'staff'),
(3, 'member');

INSERT INTO `categories` (`category_id`, `category`) VALUES
(1, 'Electronics'),
(2, 'Accessories'),
(3, 'Pets'),
(4, 'Keys'),
(5, 'Bags'),
(6, 'Eyewear'),
(7, 'Clothing'),
(8, 'Other');

INSERT INTO `i_status` (`status_id`, `status`) VALUES
(1, 'Open'),
(2, 'Pending Verification'),
(3, 'Claimed'),
(4, 'Closed');

INSERT INTO `c_status` (`status_id`, `status`) VALUES
(1, 'Pending'),
(2, 'Approved'),
(3, 'Rejected');

INSERT INTO `users` (`user_id`, `role_id`, `username`, `email`, `passwordhash`, `firstname`, `lastname`, `phone`, `created_at`) VALUES
(1, 1, 'admin', 'admin@community.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', '09123456789', NOW() - INTERVAL 30 DAY),
(2, 2, 'staff_sarah', 'sarah@community.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Jenkins', '09234567890', NOW() - INTERVAL 25 DAY),
(3, 3, 'john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '09345678901', NOW() - INTERVAL 10 DAY),
(4, 3, 'jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', '09456789012', NOW() - INTERVAL 5 DAY),
(5, 3, 'alex_jones', 'alex@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alex', 'Jones', '09567890123', NOW() - INTERVAL 2 DAY);

INSERT INTO `items` (`item_id`, `user_id`, `status_id`, `item_type`, `category_id`, `title`, `description`, `location`, `event_date`, `created_at`) VALUES
(1, 3, 1, 'Lost', 1, 'iPhone 13 Pro (Space Gray)', 'Has a transparent silicone case with a sticker of a cat on the back. Screen protector is slightly cracked at top right.', 'Central Community Park, near fountain', '2026-03-10', NOW() - INTERVAL 8 DAY),
(2, 4, 1, 'Lost', 2, 'Brown Leather Wallet', 'Contains driver license, two credit cards, and approximately $45 cash. Brand is Fossil.', 'Main Street Coffee Shop', '2026-03-12', NOW() - INTERVAL 6 DAY),
(3, 5, 4, 'Lost', 3, 'Golden Retriever puppy collar with tag', 'Blue nylon collar with metal tag engraved with "Buster" and phone number 09567890123.', 'Oakwood Neighborhood Block 4', '2026-03-15', NOW() - INTERVAL 3 DAY),
(4, 2, 1, 'Found', 4, 'Silver Toyota Car Keys', 'Toyota key fob with a blue keychain strap attached and a gym locker key.', 'Community Center Parking Lot, Bay 3', '2026-03-11', NOW() - INTERVAL 7 DAY),
(5, 3, 2, 'Found', 5, 'Black Sports Backpack', 'Nike black backpack containing a water bottle, a notebook, and a red calculator.', 'Public Library Reading Room', '2026-03-13', NOW() - INTERVAL 5 DAY),
(6, 1, 1, 'Found', 6, 'Prescription Sunglasses', 'Ray-Ban aviator sunglasses with gold frames in a brown leather hard case.', 'Community Swimming Pool Bleachers', '2026-03-14', NOW() - INTERVAL 4 DAY),
(7, 2, 3, 'Found', 7, 'Childs Blue Winter Jacket', 'Size 6-7 waterproof winter jacket with dinosaur pattern inside hoodie.', 'Elementary School Playground', '2026-03-16', NOW() - INTERVAL 2 DAY);

INSERT INTO `claims` (`claim_id`, `claimer_user_id`, `status_id`, `item_id`, `proof_description`, `admin_notes`, `created_at`) VALUES
(1, 4, 1, 5, 'This is my backpack. Inside the red calculator my initials J.S. are written with a permanent marker.', 'Waiting for verification of the initials.', NOW() - INTERVAL 4 DAY),
(2, 5, 2, 7, 'Jacket left by my son Timmy after after-school sports.', 'Verified matching description and son confirmed.', NOW() - INTERVAL 1 DAY);
