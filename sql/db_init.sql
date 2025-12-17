CREATE DATABASE IF NOT EXISTS wardrobe_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wardrobe_app;

-- Virtual Wardrobe tables with 'vw_' prefix to avoid conflicts on shared hosting

CREATE TABLE vw_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(40) DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vw_clothes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(50) NOT NULL,
  colour VARCHAR(50),
  image_path VARCHAR(255) NOT NULL,
  favorite TINYINT(1) DEFAULT 0,
  in_laundry TINYINT(1) DEFAULT 0,
  wear_count INT DEFAULT 0,
  last_worn_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE CASCADE
);

CREATE TABLE vw_outfits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  top_id INT,
  bottom_id INT,
  shoe_id INT,
  accessory_id INT,
  title VARCHAR(150),
  is_favorite TINYINT(1) DEFAULT 0,
  wear_count INT DEFAULT 0,
  last_worn_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE CASCADE,
  FOREIGN KEY (top_id) REFERENCES vw_clothes(id) ON DELETE SET NULL,
  FOREIGN KEY (bottom_id) REFERENCES vw_clothes(id) ON DELETE SET NULL,
  FOREIGN KEY (shoe_id) REFERENCES vw_clothes(id) ON DELETE SET NULL,
  FOREIGN KEY (accessory_id) REFERENCES vw_clothes(id) ON DELETE SET NULL
);

CREATE TABLE vw_outfits_planned (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  outfit_id INT NOT NULL,
  planned_for DATE NOT NULL,
  note VARCHAR(255) DEFAULT NULL,
  season_hint VARCHAR(20) DEFAULT 'all',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY user_outfit_date (user_id, outfit_id, planned_for),
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE CASCADE,
  FOREIGN KEY (outfit_id) REFERENCES vw_outfits(id) ON DELETE CASCADE
);

CREATE TABLE vw_shared_outfits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  outfit_id INT NOT NULL,
  user_id INT NOT NULL,
  token VARCHAR(128) NOT NULL,
  expires_at DATETIME NOT NULL,
  is_public TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (outfit_id) REFERENCES vw_outfits(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE CASCADE
);

CREATE TABLE vw_password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(128) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_exp (user_id, expires_at),
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE CASCADE
);

CREATE TABLE vw_audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(100) NOT NULL,
  target_type VARCHAR(100) DEFAULT NULL,
  target_id INT DEFAULT NULL,
  details JSON DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE SET NULL
);

-- Planner updates table to notify clients for changes (per-user)
CREATE TABLE vw_planner_updates (
  user_id INT PRIMARY KEY,
  last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE CASCADE
);

-- Collections feature
CREATE TABLE vw_collections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES vw_users(id) ON DELETE CASCADE
);

CREATE TABLE vw_collection_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  collection_id INT NOT NULL,
  item_type ENUM('clothing', 'outfit') NOT NULL,
  item_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (collection_id) REFERENCES vw_collections(id) ON DELETE CASCADE
);

-- Login attempts for rate limiting
CREATE TABLE vw_login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(45) NOT NULL,
  email VARCHAR(150),
  success TINYINT(1) DEFAULT 0,
  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ip_time (ip_address, attempted_at),
  INDEX idx_email_time (email, attempted_at)
);
