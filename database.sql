-- ========================================================
-- Community Lost and Found System - Database Schema & Sample Data
-- Database Management System: MySQL / MariaDB
-- ========================================================

CREATE DATABASE IF NOT EXISTS `lost_and_found_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `lost_and_found_db`;

-- Disable foreign key checks for clean re-creation
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `claims`;
DROP TABLE IF EXISTS `found_items`;
DROP TABLE IF EXISTS `lost_items`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================================
-- 1. USERS TABLE (Admins, Staff, and Community Members)
-- ========================================================
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('admin', 'staff', 'member') NOT NULL DEFAULT 'member',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- 2. LOST ITEMS TABLE (Items reported lost by community)
-- ========================================================
CREATE TABLE `lost_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `location_lost` VARCHAR(150) NOT NULL,
  `date_lost` DATE NOT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Open', 'Resolved', 'Closed') NOT NULL DEFAULT 'Open',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_lost_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- 3. FOUND ITEMS TABLE (Items found and handed in/posted)
-- ========================================================
CREATE TABLE `found_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `location_found` VARCHAR(150) NOT NULL,
  `date_found` DATE NOT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Unclaimed', 'Pending Verification', 'Claimed') NOT NULL DEFAULT 'Unclaimed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_found_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- 4. CLAIMS TABLE (Claims filed on found items)
-- ========================================================
CREATE TABLE `claims` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `found_item_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `proof_description` TEXT NOT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
  `admin_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_claim_item` FOREIGN KEY (`found_item_id`) REFERENCES `found_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_claim_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- SAMPLE DATA INSERTION
-- Note: Passwords for sample users are hashed for 'password123' ($2y$10$...)
-- ========================================================

-- Users: 1 Admin, 1 Staff, 3 Members
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@community.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', '09123456789', 'admin', NOW() - INTERVAL 30 DAY),
(2, 'staff_sarah', 'sarah@community.org', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Jenkins (Staff)', '09234567890', 'staff', NOW() - INTERVAL 25 DAY),
(3, 'john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', '09345678901', 'member', NOW() - INTERVAL 10 DAY),
(4, 'jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', '09456789012', 'member', NOW() - INTERVAL 5 DAY),
(5, 'alex_jones', 'alex@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alex Jones', '09567890123', 'member', NOW() - INTERVAL 2 DAY);

-- Lost Items
INSERT INTO `lost_items` (`id`, `user_id`, `title`, `category`, `description`, `location_lost`, `date_lost`, `image_path`, `status`, `created_at`) VALUES
(1, 3, 'iPhone 13 Pro (Space Gray)', 'Electronics', 'Has a transparent silicone case with a sticker of a cat on the back. Screen protector is slightly cracked at top right.', 'Central Community Park, near fountain', '2026-03-10', NULL, 'Open', NOW() - INTERVAL 8 DAY),
(2, 4, 'Brown Leather Wallet', 'Accessories', 'Contains driver license, two credit cards, and approximately $45 cash. Brand is Fossil.', 'Main Street Coffee Shop', '2026-03-12', NULL, 'Open', NOW() - INTERVAL 6 DAY),
(3, 5, 'Golden Retriever puppy collar with tag', 'Pets', 'Blue nylon collar with metal tag engraved with "Buster" and phone number 09567890123.', 'Oakwood Neighborhood Block 4', '2026-03-15', NULL, 'Resolved', NOW() - INTERVAL 3 DAY);

-- Found Items
INSERT INTO `found_items` (`id`, `user_id`, `title`, `category`, `description`, `location_found`, `date_found`, `image_path`, `status`, `created_at`) VALUES
(1, 2, 'Silver Toyota Car Keys', 'Keys', 'Toyota key fob with a blue keychain strap attached and a gym locker key.', 'Community Center Parking Lot, Bay 3', '2026-03-11', NULL, 'Unclaimed', NOW() - INTERVAL 7 DAY),
(2, 3, 'Black Sports Backpack', 'Bags', 'Nike black backpack containing a water bottle, a notebook, and a red calculator.', 'Public Library Reading Room', '2026-03-13', NULL, 'Pending Verification', NOW() - INTERVAL 5 DAY),
(3, 1, 'Prescription Sunglasses', 'Eyewear', 'Ray-Ban aviator sunglasses with gold frames in a brown leather hard case.', 'Community Swimming Pool Bleachers', '2026-03-14', NULL, 'Unclaimed', NOW() - INTERVAL 4 DAY),
(4, 2, 'Childs Blue Winter Jacket', 'Clothing', 'Size 6-7 waterproof winter jacket with dinosaur pattern inside hoodie.', 'Elementary School Playground', '2026-03-16', NULL, 'Claimed', NOW() - INTERVAL 2 DAY);

-- Claims
INSERT INTO `claims` (`id`, `found_item_id`, `user_id`, `proof_description`, `status`, `admin_notes`, `created_at`) VALUES
(1, 2, 4, 'This is my backpack. Inside the red calculator my initials J.S. are written with a permanent marker.', 'Pending', 'Waiting for verification of the initials.', NOW() - INTERVAL 4 DAY),
(2, 4, 5, 'Jacket left by my son Timmy after after-school sports.', 'Approved', 'Verified matching description and son confirmed.', NOW() - INTERVAL 1 DAY);
