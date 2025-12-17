-- Fix: Add indexes to outfits_planned using correct column name
ALTER TABLE outfits_planned ADD INDEX IF NOT EXISTS idx_user_date (user_id, planned_for);
ALTER TABLE outfits_planned ADD INDEX IF NOT EXISTS idx_outfit (outfit_id);
ALTER TABLE outfits_planned ADD INDEX IF NOT EXISTS idx_date_range (planned_for);
