<?php

/**
 * -------------------------------------------------------------------------
 * AssetsCleaner plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of AssetsCleaner.
 *
 * AssetsCleaner is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * AssetsCleaner is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AssetsCleaner. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2025 by SpyKeeR.
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/SpyKeeR/Glpi-AssetsCleaner
 * -------------------------------------------------------------------------
 */

use GlpiPlugin\Assetscleaner\AssetsCleaner;
use GlpiPlugin\Assetscleaner\ConfigAssetsCleaner;

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_assetscleaner_install()
{
    global $DB;

    // Set default configuration
    $defaults = ConfigAssetsCleaner::getDefaults();
    
    // Prepare values for storage
    $config_values = [];
    foreach ($defaults as $key => $value) {
        if (is_array($value)) {
            $config_values[$key] = json_encode($value);
        } else {
            $config_values[$key] = $value;
        }
    }
    
    Config::setConfigurationValues('plugin:assetscleaner', $config_values);

    // Register the cron tasks
    CronTask::Register(
        AssetsCleaner::class,
        'CleanOldAssets',
        DAY_TIMESTAMP,
        [
            'comment' => __('Clean old assets not updated by inventory', 'assetscleaner'),
            'mode'    => CronTask::MODE_EXTERNAL,
        ]
    );

    CronTask::Register(
        AssetsCleaner::class,
        'PurgeOldTrash',
        DAY_TIMESTAMP,
        [
            'comment' => __('Purge old assets from trash', 'assetscleaner'),
            'mode'    => CronTask::MODE_EXTERNAL,
        ]
    );

    return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_assetscleaner_uninstall()
{
    // Delete configuration
    $config = new Config();
    $config->deleteConfigurationValues('plugin:assetscleaner');

    // Unregister cron tasks
    $cron = new CronTask();
    
    // Find and delete cron tasks
    $cron_tasks = $cron->find([
        'itemtype' => AssetsCleaner::class,
    ]);
    
    foreach ($cron_tasks as $task) {
        $cron->delete(['id' => $task['id']], 1);
    }

    return true;
}
