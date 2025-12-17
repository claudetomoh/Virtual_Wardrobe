USE wardrobe_app;

-- Rename all tables to add vw_ prefix
RENAME TABLE users TO vw_users;
RENAME TABLE clothes TO vw_clothes;
RENAME TABLE outfits TO vw_outfits;
RENAME TABLE outfits_planned TO vw_outfits_planned;
RENAME TABLE shared_outfits TO vw_shared_outfits;
RENAME TABLE password_resets TO vw_password_resets;
RENAME TABLE audit_log TO vw_audit_log;
RENAME TABLE planner_updates TO vw_planner_updates;

-- Note: If you don't have these tables yet, these commands will fail (that's OK)
RENAME TABLE collections TO vw_collections;
RENAME TABLE collection_items TO vw_collection_items;
RENAME TABLE login_attempts TO vw_login_attempts;

-- Verify tables renamed
SHOW TABLES;
