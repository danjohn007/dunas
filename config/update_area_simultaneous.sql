-- Add area and is_simultaneous columns to shelly_devices table
-- Migration: Add support for Area and simultaneous device execution
-- Date: 2025-11-05

-- Add area column (informative text field)
ALTER TABLE shelly_devices 
ADD COLUMN area VARCHAR(100) NOT NULL DEFAULT '' 
AFTER server_host;

-- Add is_simultaneous column (checkbox for simultaneous execution)
ALTER TABLE shelly_devices 
ADD COLUMN is_simultaneous TINYINT NOT NULL DEFAULT 0 
AFTER invert_sequence;
