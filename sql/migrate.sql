-- Migration script: add new columns and tables if needed
-- Run this once on your existing database when updating the schema

ALTER TABLE clothes
  ADD COLUMN IF NOT EXISTS favorite TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS in_laundry TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS wear_count INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS last_worn_at TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE outfits
  ADD COLUMN IF NOT EXISTS is_favorite TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS wear_count INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS last_worn_at TIMESTAMP NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS outfits_planned (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  outfit_id INT NOT NULL,
  planned_for DATE NOT NULL,
  note VARCHAR(255) DEFAULT NULL,
  season_hint VARCHAR(20) DEFAULT 'all',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY user_outfit_date (user_id, outfit_id, planned_for)
);

CREATE TABLE IF NOT EXISTS shared_outfits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  outfit_id INT NOT NULL,
  user_id INT NOT NULL,
  token VARCHAR(128) NOT NULL,
  expires_at DATETIME NOT NULL,
  is_public TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(100) NOT NULL,
  target_type VARCHAR(100) DEFAULT NULL,
  target_id INT DEFAULT NULL,
  details JSON DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS planner_updates (
  user_id INT PRIMARY KEY,
  last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(40) DEFAULT 'user';

-- (Optional) Add indexes for performance
CREATE INDEX IF NOT EXISTS idx_clothes_user_id ON clothes(user_id);
CREATE INDEX IF NOT EXISTS idx_outfits_user_id ON outfits(user_id);