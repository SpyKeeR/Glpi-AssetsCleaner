-- Migration script for Assets Cleaner v1.0.1 to v1.0.2
-- Changes configuration context from 'plugin:assetscleaner' to 'assetscleaner'

-- ⚠️ IMPORTANT: Backup your database before running this script!
-- mysqldump -u root -p glpi > glpi_backup_before_assetscleaner_migration.sql

-- Step 1: Check existing configuration
SELECT 'Current configuration:' as info;
SELECT context, name, value 
FROM glpi_configs 
WHERE context IN ('plugin:assetscleaner', 'assetscleaner')
ORDER BY context, name;

-- Step 2: Copy configuration from old context to new context
-- (Only if upgrading from v1.0.1)
INSERT INTO glpi_configs (context, name, value)
SELECT 'assetscleaner', name, value
FROM glpi_configs
WHERE context = 'plugin:assetscleaner'
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- Step 3: Verify new configuration exists
SELECT 'New configuration:' as info;
SELECT context, name, value 
FROM glpi_configs 
WHERE context = 'assetscleaner'
ORDER BY name;

-- Step 4: Delete old configuration (optional - uncomment to execute)
-- DELETE FROM glpi_configs WHERE context = 'plugin:assetscleaner';

-- Step 5: Verify cleanup
SELECT 'Remaining old config (should be empty):' as info;
SELECT context, name, value 
FROM glpi_configs 
WHERE context = 'plugin:assetscleaner';

-- Done! 
SELECT '✓ Migration complete!' as info;
SELECT 'Remember to reconfigure the plugin if values were not migrated' as note;
