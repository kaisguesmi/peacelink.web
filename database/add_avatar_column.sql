-- Add avatar column to Client table
-- Run this if the avatar column doesn't exist

USE peacelink;

-- Check if column exists and add it if it doesn't
SET @dbname = DATABASE();
SET @tablename = 'Client';
SET @columnname = 'avatar';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1', -- Column exists, do nothing
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Or simply run this if the above doesn't work:
-- ALTER TABLE Client ADD COLUMN avatar VARCHAR(255) NULL;

