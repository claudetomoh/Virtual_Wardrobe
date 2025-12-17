-- Database Migrations for Enhanced Virtual Wardrobe
-- Run these migrations to add new features and improvements

-- ========================================
-- 1. LOGIN ATTEMPTS TRACKING (Rate Limiting)
-- ========================================
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT FALSE,
    INDEX idx_ip_attempted (ip_address, attempted_at),
    INDEX idx_email_attempted (email, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 2. SESSION MANAGEMENT
-- ========================================
CREATE TABLE IF NOT EXISTS active_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(128) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_session (user_id, session_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 3. TWO-FACTOR AUTHENTICATION
-- ========================================
CREATE TABLE IF NOT EXISTS user_2fa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    secret VARCHAR(32) NOT NULL,
    enabled BOOLEAN DEFAULT FALSE,
    backup_codes JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 4. ENHANCED CLOTHES TABLE (Advanced Attributes)
-- ========================================
ALTER TABLE clothes 
ADD COLUMN IF NOT EXISTS brand VARCHAR(100) DEFAULT NULL AFTER category,
ADD COLUMN IF NOT EXISTS price DECIMAL(10,2) DEFAULT NULL AFTER brand,
ADD COLUMN IF NOT EXISTS purchase_date DATE DEFAULT NULL AFTER price,
ADD COLUMN IF NOT EXISTS material VARCHAR(255) DEFAULT NULL AFTER purchase_date,
ADD COLUMN IF NOT EXISTS care_instructions TEXT DEFAULT NULL AFTER material,
ADD COLUMN IF NOT EXISTS season ENUM('spring', 'summer', 'fall', 'winter', 'all') DEFAULT 'all' AFTER care_instructions,
ADD COLUMN IF NOT EXISTS size VARCHAR(20) DEFAULT NULL AFTER season,
ADD COLUMN IF NOT EXISTS color VARCHAR(50) DEFAULT NULL AFTER size,
ADD COLUMN IF NOT EXISTS times_worn INT DEFAULT 0 AFTER color,
ADD COLUMN IF NOT EXISTS last_worn DATE DEFAULT NULL AFTER times_worn,
ADD COLUMN IF NOT EXISTS last_cleaned DATE DEFAULT NULL AFTER last_worn,
ADD COLUMN IF NOT EXISTS notes TEXT DEFAULT NULL AFTER last_cleaned;

-- ========================================
-- 5. OUTFIT TRACKING & ANALYTICS
-- ========================================
CREATE TABLE IF NOT EXISTS outfit_wears (
    id INT AUTO_INCREMENT PRIMARY KEY,
    outfit_id INT NOT NULL,
    user_id INT NOT NULL,
    worn_date DATE NOT NULL,
    occasion VARCHAR(100) DEFAULT NULL,
    weather VARCHAR(50) DEFAULT NULL,
    temperature INT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (outfit_id) REFERENCES outfits(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, worn_date),
    INDEX idx_outfit_date (outfit_id, worn_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 6. WISHLIST / SHOPPING LIST
-- ========================================
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    brand VARCHAR(100) DEFAULT NULL,
    category VARCHAR(50) DEFAULT NULL,
    price DECIMAL(10,2) DEFAULT NULL,
    link VARCHAR(500) DEFAULT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    notes TEXT DEFAULT NULL,
    purchased BOOLEAN DEFAULT FALSE,
    purchased_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_purchased (user_id, purchased)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 7. ERROR LOGGING
-- ========================================
CREATE TABLE IF NOT EXISTS error_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    error_level ENUM('info', 'warning', 'error', 'critical') DEFAULT 'error',
    error_message TEXT NOT NULL,
    error_file VARCHAR(500) DEFAULT NULL,
    error_line INT DEFAULT NULL,
    stack_trace TEXT DEFAULT NULL,
    request_uri VARCHAR(500) DEFAULT NULL,
    request_method VARCHAR(10) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved BOOLEAN DEFAULT FALSE,
    INDEX idx_level_created (error_level, created_at),
    INDEX idx_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 8. NOTIFICATIONS SYSTEM
-- ========================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    action_url VARCHAR(500) DEFAULT NULL,
    read_status BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, read_status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 9. USER PREFERENCES
-- ========================================
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    theme VARCHAR(20) DEFAULT 'light',
    language VARCHAR(10) DEFAULT 'en',
    currency VARCHAR(3) DEFAULT 'USD',
    date_format VARCHAR(20) DEFAULT 'Y-m-d',
    time_format VARCHAR(20) DEFAULT 'H:i',
    email_notifications BOOLEAN DEFAULT TRUE,
    push_notifications BOOLEAN DEFAULT TRUE,
    weekly_summary BOOLEAN DEFAULT TRUE,
    outfit_reminders BOOLEAN DEFAULT TRUE,
    laundry_alerts BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 10. PERFORMANCE INDEXES
-- ========================================

-- Users table indexes
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_email (email);
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_role (role);
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_created (created_at);

-- Clothes table indexes
ALTER TABLE clothes ADD INDEX IF NOT EXISTS idx_user_category (user_id, category);
ALTER TABLE clothes ADD INDEX IF NOT EXISTS idx_user_created (user_id, created_at);
ALTER TABLE clothes ADD INDEX IF NOT EXISTS idx_category (category);
ALTER TABLE clothes ADD INDEX IF NOT EXISTS idx_season (season);
ALTER TABLE clothes ADD INDEX IF NOT EXISTS idx_brand (brand);

-- Outfits table indexes
ALTER TABLE outfits ADD INDEX IF NOT EXISTS idx_user_created (user_id, created_at);
ALTER TABLE outfits ADD INDEX IF NOT EXISTS idx_user_favorite (user_id, is_favorite);
ALTER TABLE outfits ADD INDEX IF NOT EXISTS idx_shared (share_token);

-- Planned outfits indexes
ALTER TABLE outfits_planned ADD INDEX IF NOT EXISTS idx_user_date (user_id, planned_date);
ALTER TABLE outfits_planned ADD INDEX IF NOT EXISTS idx_outfit (outfit_id);
ALTER TABLE outfits_planned ADD INDEX IF NOT EXISTS idx_date_range (planned_date);

-- Collections indexes
ALTER TABLE collections ADD INDEX IF NOT EXISTS idx_user_created (user_id, created_at);

-- ========================================
-- 11. SOCIAL FEATURES (OPTIONAL)
-- ========================================
CREATE TABLE IF NOT EXISTS user_follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_id, following_id),
    INDEX idx_follower (follower_id),
    INDEX idx_following (following_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS outfit_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    outfit_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (outfit_id) REFERENCES outfits(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (outfit_id, user_id),
    INDEX idx_outfit (outfit_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS outfit_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    outfit_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (outfit_id) REFERENCES outfits(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_outfit_created (outfit_id, created_at),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 12. API TOKENS (For Mobile App / Third-party Access)
-- ========================================
CREATE TABLE IF NOT EXISTS api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    scopes JSON DEFAULT NULL,
    last_used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- MIGRATION COMPLETE
-- ========================================
-- Run this file using: mysql -u username -p database_name < migrations.sql
-- Or import via phpMyAdmin
