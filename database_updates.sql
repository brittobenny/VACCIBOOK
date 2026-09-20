-- Add missing columns to book table
-- Run this SQL in your phpMyAdmin or MySQL console

-- Add remarks column (for health centre notes)
ALTER TABLE `book` ADD `remarks` TEXT NULL AFTER `status`;

-- Verify the table structure
-- You should now have: bid, pid, sid, cid, bookdate, status, certificate, remarks
