-- Add invert_sequence column to shelly_devices table
-- Migration: Add support for configurable sequence (off→on vs on→off)
-- Date: 2025-11-04

-- Add invert_sequence column with default 1 (inverted: off→on)
ALTER TABLE shelly_devices 
ADD COLUMN invert_sequence TINYINT NOT NULL DEFAULT 1 
AFTER channel_count;

-- Update existing devices to use inverted sequence (off→on) as default
UPDATE shelly_devices SET invert_sequence = 1 WHERE invert_sequence IS NULL;
