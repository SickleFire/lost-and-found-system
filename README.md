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

Follow these steps to run the project locally with XAMPP on Windows.

### Requirements

- [XAMPP](https://www.apachefriends.org/) with Apache, MySQL, PHP, and the PDO MySQL extension.
- [Git for Windows](https://git-scm.com/download/win).

### 1. Clone the repository

Open PowerShell and clone the repository into XAMPP's `htdocs` directory:

```powershell
Set-Location C:\xampp\htdocs
git clone https://github.com/SickleFire/lost-and-found-system.git
```

This creates the project folder at `C:\xampp\htdocs\lost-and-found-system`.

### 2. Start XAMPP services

1. Open the **XAMPP Control Panel**.
2. Start **Apache** and **MySQL**.

### 3. Import the database

1. Open [phpMyAdmin](http://localhost/phpmyadmin/).
2. Select the **Import** tab from the phpMyAdmin home page.
3. Choose `C:\xampp\htdocs\lost-and-found-system\database.sql` and select **Import** (or **Go**).

The SQL script creates and populates the `lost_and_found_db` database, so do not create that database separately before importing.

The default connection in `db.php` uses MySQL host `localhost`, username `root`, and an empty password, as in a standard XAMPP setup. If your MySQL credentials differ, update the connection settings in `db.php`.

### 4. Open the application

Visit [http://localhost/lost-and-found-system/](http://localhost/lost-and-found-system/) in your browser.

### Updating an existing database

If you are upgrading an existing database that predates the found-to-lost item link, run this SQL once:

```sql
ALTER TABLE items
  ADD COLUMN related_lost_item_id INT DEFAULT NULL AFTER item_type,
  ADD CONSTRAINT fk_found_lost_item FOREIGN KEY (related_lost_item_id)
    REFERENCES items (item_id) ON DELETE SET NULL,
  ADD INDEX idx_related_lost_item_id (related_lost_item_id);
```

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
