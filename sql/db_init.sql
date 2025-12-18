-- ========================================
-- Virtual Wardrobe & Outfit Planner
-- Database Schema Initialization Script
-- ========================================
-- Description: Complete database schema for the Virtual Wardrobe application
-- Author: [Your Name]
-- Date: December 2025
-- Database: MySQL 5.7+ / MariaDB 10.3+
-- Character Set: UTF-8 (utf8mb4)
-- ========================================

-- Create the database with UTF-8 support
CREATE DATABASE IF NOT EXISTS wardrobe_app 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE wardrobe_app;

-- ========================================
-- TABLE NAMING CONVENTION
-- ========================================
-- All tables use 'vw_' prefix (Virtual Wardrobe)
-- This prevents naming conflicts on shared hosting environments
-- ========================================

-- ========================================
-- TABLE: vw_users
-- ========================================
-- Purpose: Store user accounts with authentication information
-- Primary Key: id (auto-increment)
-- Unique Constraints: email (for login)
-- Security: Passwords stored as bcrypt hashes (cost 12)
-- ========================================
CREATE TABLE vw_users (
  id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique user identifier',
  name VARCHAR(100) NOT NULL COMMENT 'User full name',
  email VARCHAR(150) NOT NULL UNIQUE COMMENT 'Unique email address for login',
  password VARCHAR(255) NOT NULL COMMENT 'Bcrypt hashed password',
  role VARCHAR(40) DEFAULT 'user' COMMENT 'User role: user or admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Account creation timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User accounts table';

-- ========================================
-- TABLE: vw_clothes
-- ========================================
-- Purpose: Store individual clothing items uploaded by users
-- Primary Key: id (auto-increment)
-- Foreign Keys: user_id -> vw_users(id) ON DELETE CASCADE
-- Categories: Tops, Bottoms, Shoes, Accessories
-- Tracking: Wear count, favorite status, laundry status
-- ========================================
CREATE TABLE vw_clothes (
  id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique clothing item identifier',
  user_id INT NOT NULL COMMENT 'Owner of the clothing item',
  name VARCHAR(150) NOT NULL COMMENT 'Name/description of the item',
  category VARCHAR(50) NOT NULL COMMENT 'Item category: Tops, Bottoms, Shoes, Accessories',
  colour VARCHAR(50) COMMENT 'Primary color of the item',
  image_path VARCHAR(255) NOT NULL COMMENT 'Path to uploaded item image',
  favorite TINYINT(1) DEFAULT 0 COMMENT 'Is this a favorite item? 0=No, 1=Yes',
  in_laundry TINYINT(1) DEFAULT 0 COMMENT 'Is item currently in laundry? 0=No, 1=Yes',
  wear_count INT DEFAULT 0 COMMENT 'Number of times worn',
  last_worn_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Last date item was worn',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Item creation timestamp',
  INDEX idx_user_category (user_id, category) COMMENT 'Performance index for filtering',
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Clothing items table';

-- ========================================
-- TABLE: vw_outfits
-- ========================================
-- Purpose: Store outfit combinations (top + bottom + shoes + accessory)
-- Primary Key: id (auto-increment)
-- Foreign Keys: 
--   - user_id -> vw_users(id) ON DELETE CASCADE
--   - top_id, bottom_id, shoe_id, accessory_id -> vw_clothes(id) ON DELETE SET NULL
-- Business Logic: If clothing item deleted, outfit remains with NULL reference
-- Tracking: Wear count, favorite status, last worn date
-- ========================================
CREATE TABLE vw_outfits (
  id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique outfit identifier',
  user_id INT NOT NULL COMMENT 'Owner of the outfit',
  top_id INT COMMENT 'Reference to top clothing item',
  bottom_id INT COMMENT 'Reference to bottom clothing item',
  shoe_id INT COMMENT 'Reference to shoe clothing item',
  accessory_id INT COMMENT 'Reference to accessory clothing item',
  title VARCHAR(150) COMMENT 'Optional outfit name/description',
  is_favorite TINYINT(1) DEFAULT 0 COMMENT 'Is this a favorite outfit? 0=No, 1=Yes',
  wear_count INT DEFAULT 0 COMMENT 'Number of times outfit was worn',
  last_worn_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Last date outfit was worn',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Outfit creation timestamp',
  INDEX idx_user (user_id) COMMENT 'Performance index for user queries',
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE CASCADE,
  FOREIGN KEY (top_id) REFERENCES vw_clothes(id) ON DELETE SET NULL,
  FOREIGN KEY (bottom_id) REFERENCES vw_clothes(id) ON DELETE SET NULL,
  FOREIGN KEY (shoe_id) REFERENCES vw_clothes(id) ON DELETE SET NULL,
  FOREIGN KEY (accessory_id) REFERENCES vw_clothes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Outfit combinations table';

-- ========================================
-- TABLE: vw_outfits_planned
-- ========================================
-- Purpose: Store scheduled outfits for specific dates (calendar/planner)
-- Primary Key: id (auto-increment)
-- Foreign Keys: 
--   - user_id -> vw_users(id) ON DELETE CASCADE
--   - outfit_id -> vw_outfits(id) ON DELETE CASCADE
-- Unique Constraint: (user_id, outfit_id, planned_for) prevents duplicate scheduling
-- Features: Personal notes, season hints, auto-update timestamp
-- ========================================
CREATE TABLE vw_outfits_planned (
  id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique plan identifier',
  user_id INT NOT NULL COMMENT 'Owner of the plan',
  outfit_id INT NOT NULL COMMENT 'Reference to scheduled outfit',
  planned_for DATE NOT NULL COMMENT 'Date outfit is planned for',
  note VARCHAR(255) DEFAULT NULL COMMENT 'Optional personal note for this plan',
  season_hint VARCHAR(20) DEFAULT 'all' COMMENT 'Season tag: Spring, Summer, Fall, Winter, All',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Plan creation timestamp',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
  UNIQUE KEY user_outfit_date (user_id, outfit_id, planned_for) COMMENT 'Prevent duplicate plans',
  INDEX idx_user_date (user_id, planned_for) COMMENT 'Performance index for calendar queries',
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE CASCADE,
  FOREIGN KEY (outfit_id) REFERENCES vw_outfits(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Planned outfits (calendar) table';

-- ========================================
-- TABLE: vw_shared_outfits
-- ========================================
-- Purpose: Store shared outfit links with time-limited access
-- Primary Key: id (auto-increment)
-- Foreign Keys: 
--   - outfit_id -> vw_outfits(id) ON DELETE CASCADE
--   - user_id -> vw_users(id) ON DELETE CASCADE
-- Security: Time-limited tokens (default 7 days), unique token per share
-- Business Logic: Expired shares cleaned up via cron/manual cleanup
-- ========================================
CREATE TABLE vw_shared_outfits (
  id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique share identifier',
  outfit_id INT NOT NULL COMMENT 'Reference to shared outfit',
  user_id INT NOT NULL COMMENT 'Owner who shared the outfit',
  token VARCHAR(128) NOT NULL COMMENT 'Unique share token for public URL',
  expires_at DATETIME NOT NULL COMMENT 'Expiration date/time for share link',
  is_public TINYINT(1) DEFAULT 1 COMMENT 'Is share publicly accessible? 0=No, 1=Yes',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Share creation timestamp',
  INDEX idx_token (token) COMMENT 'Performance index for token lookups',
  INDEX idx_expires (expires_at) COMMENT 'Performance index for cleanup queries',
  FOREIGN KEY (outfit_id) REFERENCES vw_outfits(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Shared outfit links table';

-- ========================================
-- TABLE: vw_password_resets
-- ========================================
-- Purpose: Store password reset tokens for forgot password flow
-- Primary Key: id (auto-increment)
-- Foreign Keys: user_id -> vw_users(id) ON DELETE CASCADE
-- Unique Constraint: token (prevents duplicate reset tokens)
-- Security: Time-limited tokens (default 30 minutes), one-time use
-- Business Logic: Tokens deleted after successful reset
-- ========================================
CREATE TABLE vw_password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique reset request identifier',
  user_id INT NOT NULL COMMENT 'User requesting password reset',
  token VARCHAR(128) NOT NULL UNIQUE COMMENT 'Unique password reset token',
  expires_at DATETIME NOT NULL COMMENT 'Token expiration date/time',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Reset request timestamp',
  INDEX idx_user_exp (user_id, expires_at) COMMENT 'Performance index for validation queries',
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Password reset tokens table';

-- ========================================
-- TABLE: vw_audit_log
-- ========================================
-- Purpose: Store activity logs for security auditing and analytics
-- Primary Key: id (auto-increment)
-- Foreign Keys: user_id -> vw_users(id) ON DELETE SET NULL
-- Features: JSON details column for flexible metadata storage
-- Business Logic: Logs preserved even if user deleted (user_id becomes NULL)
-- Use Cases: Security audits, user activity tracking, analytics
-- ========================================
CREATE TABLE vw_audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique log entry identifier',
  user_id INT NULL COMMENT 'User who performed the action (NULL if user deleted)',
  action VARCHAR(100) NOT NULL COMMENT 'Action performed (e.g., login, create_outfit, delete_item)',
  target_type VARCHAR(100) DEFAULT NULL COMMENT 'Type of target entity (e.g., outfit, clothing)',
  target_id INT DEFAULT NULL COMMENT 'ID of target entity',
  details JSON DEFAULT NULL COMMENT 'Additional metadata in JSON format',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Action timestamp',
  INDEX idx_user (user_id) COMMENT 'Performance index for user activity queries',
  INDEX idx_action (action) COMMENT 'Performance index for action type queries',
  INDEX idx_created (created_at) COMMENT 'Performance index for time-based queries',
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Activity audit log table';

-- ========================================
-- TABLE: vw_planner_updates
-- ========================================
-- Purpose: Track planner changes for real-time synchronization via Socket.IO
-- Primary Key: user_id (one row per user)
-- Foreign Keys: user_id -> vw_users(id) ON DELETE CASCADE
-- Features: Auto-update timestamp on any change
-- Use Case: Notify connected clients when planner data changes
-- ========================================
CREATE TABLE vw_planner_updates (
  user_id INT PRIMARY KEY COMMENT 'User whose planner was updated',
  last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last planner update timestamp',
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Planner update tracking for real-time sync';

-- ========================================
-- TABLE: vw_collections
-- ========================================
-- Purpose: Store user-created collections (e.g., "Travel Capsule", "Work Outfits")
-- Primary Key: id (auto-increment)
-- Foreign Keys: user_id -> vw_users(id) ON DELETE CASCADE
-- Features: Custom grouping of clothing items and outfits
-- ========================================
CREATE TABLE vw_collections (
  id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique collection identifier',
  user_id INT NOT NULL COMMENT 'Owner of the collection',
  name VARCHAR(150) NOT NULL COMMENT 'Collection name',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Collection creation timestamp',
  INDEX idx_user (user_id) COMMENT 'Performance index for user queries',
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User collections table';

-- ========================================
-- TABLE: vw_collection_items
-- ========================================
-- Purpose: Store items within collections (many-to-many relationship)
-- Primary Key: id (auto-increment)
-- Foreign Keys: collection_id -> vw_collections(id) ON DELETE CASCADE
-- Business Logic: item_type determines if item_id refers to clothing or outfit
-- ========================================
CREATE TABLE vw_collection_items (
  id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique collection item identifier',
  collection_id INT NOT NULL COMMENT 'Collection this item belongs to',
  item_type ENUM('clothing', 'outfit') NOT NULL COMMENT 'Type of item: clothing or outfit',
  item_id INT NOT NULL COMMENT 'ID of clothing item or outfit',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Item added timestamp',
  INDEX idx_collection (collection_id) COMMENT 'Performance index for collection queries',
  FOREIGN KEY (collection_id) REFERENCES vw_collections(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Collection items (junction table)';

-- ========================================
-- TABLE: vw_login_attempts
-- ========================================
-- Purpose: Track login attempts for rate limiting and security monitoring
-- Primary Key: id (auto-increment)
-- Features: IP and email tracking, success/failure status
-- Use Cases: Prevent brute-force attacks, security analytics
-- ========================================
CREATE TABLE vw_login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique attempt identifier',
  ip_address VARCHAR(45) NOT NULL COMMENT 'IP address of login attempt (supports IPv6)',
  email VARCHAR(150) COMMENT 'Email address used in attempt',
  success TINYINT(1) DEFAULT 0 COMMENT 'Was login successful? 0=No, 1=Yes',
  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Attempt timestamp',
  INDEX idx_ip_time (ip_address, attempted_at) COMMENT 'Performance index for IP-based rate limiting',
  INDEX idx_email_time (email, attempted_at) COMMENT 'Performance index for email-based rate limiting'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Login attempts for rate limiting';

-- ========================================
-- END OF SCHEMA DEFINITION
-- ========================================
-- Total Tables: 11
-- Core Tables: 8 (users, clothes, outfits, outfits_planned, shared_outfits, 
--                  password_resets, audit_log, planner_updates)
-- Feature Tables: 2 (collections, collection_items)
-- Security Tables: 1 (login_attempts)
-- ========================================
-- See docs/ERD_DIAGRAM.md for detailed entity relationship diagram
-- ========================================
