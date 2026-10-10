# Community Lost and Found System

A fully-featured, responsive web application built with **PHP**, **PDO**, **MySQL/MariaDB**, **HTML5**, **CSS3**, and **Bootstrap 5** for community members and administrators to report lost items, post found items, file and verify ownership claims, and track recovery statuses.

---

## 🌟 Features & Minimum Functional Requirements Met

1. **User Authentication & Registration**:
   - Secure member registration with server-side validation and password hashing (`password_hash`).
   - Role-based access control (`admin`, `staff`, `member`) with secure login and logout sessions.
2. **Lost & Found Item Posting**:
   - **Post Lost Item**: Community members can publish items they have lost with title, category, location, date, and description.
   - **Post Found Item**: Staff and community members can report items found.
3. **Advanced Search, Filtering & Sorting**:
   - Keyword search across titles, descriptions, and locations.
   - Filter records by category and status (`Open`, `Unclaimed`, `Pending Verification`, `Resolved`, `Claimed`).
   - Sort records by date (Newest or Oldest first).
4. **Claim Verification & Status Tracking**:
   - Community members can file claims on found items by submitting detailed ownership proof.
   - Items remain open to additional claims while claims are pending, and become unavailable only after approval.
   - Staff/Admins can review, approve, or reject claims with audit notes.
   - Automatic status updates (`Pending Verification`, `Claimed`, `Approved`/`Rejected`).
   - Personal dashboard for members (`my_items.php`) showing live claim tracking status.
5. **Administrator & Staff Dashboard**:
   - Summary statistics cards showing total users, lost items, found items, pending claims, and recovery success rates.
   - Management interfaces for items, claims, and users.
   - Printable official reports (`reports.php`) with browser print optimization.

---

## 🗄️ Relational Database Schema & ERD Architecture

The database `lost_and_found_db` consists of 4 tightly integrated relational tables:

1. **`users`**:
   - `id` (PK)
   - `username`, `email`, `password`, `full_name`, `phone`, `role` (`admin`, `staff`, `member`), `created_at`
2. **`lost_items`**:
   - `id` (PK)
   - `user_id` (FK referencing `users.id`)
   - `title`, `category`, `description`, `location_lost`, `date_lost`, `status` (`Open`, `Resolved`, `Closed`), `created_at`
3. **`found_items`**:
   - `id` (PK)
   - `user_id` (FK referencing `users.id`)
   - `title`, `category`, `description`, `location_found`, `date_found`, `status` (`Unclaimed`, `Pending Verification`, `Claimed`), `created_at`
4. **`claims`**:
   - `id` (PK)
   - `found_item_id` (FK referencing `found_items.id`)
   - `user_id` (FK referencing `users.id`)
   - `proof_description`, `status` (`Pending`, `Approved`, `Rejected`), `admin_notes`, `created_at`

---

## 🚀 Installation & Setup Instructions

1. **Prerequisites**:
   - Install **XAMPP** (or any LAMP/WAMP stack with PHP 8.0+ and MySQL / MariaDB).
2. **Database Setup**:
   - Start Apache and MySQL in XAMPP Control Panel.
   - Open **phpMyAdmin** (`http://localhost/phpmyadmin/`).
   - Import the provided `database.sql` file, or create a database named `lost_and_found_db` and run the SQL script.
   - If upgrading an existing database, run this once to add found-to-lost links:
     ```sql
     ALTER TABLE items
       ADD COLUMN related_lost_item_id INT DEFAULT NULL AFTER item_type,
       ADD CONSTRAINT fk_found_lost_item FOREIGN KEY (related_lost_item_id)
         REFERENCES items (item_id) ON DELETE SET NULL,
       ADD INDEX idx_related_lost_item_id (related_lost_item_id);
     ```
3. **Project Placement**:
   - Place the project folder (e.g. `lost-and-found-system`) inside your web server root directory (`htdocs` in XAMPP).
4. **Access the System**:
   - Open your browser and navigate to:
     `http://localhost/lost-and-found-system/index.php`

---

## 👥 Demo Credentials for Testing

- **Administrator**:
  - Username: `admin`
  - Password: `password`
- **Staff Member**:
  - Username: `staff_sarah`
  - Password: `password`
- **Community Member**:
  - Username: `john_doe` (or `jane_smith`)
  - Password: `password`
