-- Add other_id column to vw_outfits table for "Other" category items
ALTER TABLE vw_outfits 
ADD COLUMN other_id INT NULL AFTER accessory_id,
ADD FOREIGN KEY (other_id) REFERENCES vw_clothes(id) ON DELETE SET NULL;
