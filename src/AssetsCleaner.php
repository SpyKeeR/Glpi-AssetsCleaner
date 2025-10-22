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
 * @link      https://github.com/SpyKeeR/assetscleaner
 * -------------------------------------------------------------------------
 */

namespace GlpiPlugin\Assetscleaner;

use CommonDBTM;
use CommonGLPI;
use CronTask;
use Session;
use Toolbox;

class AssetsCleaner extends CommonDBTM
{
    public static $rightname = 'plugin_assetscleaner';

    /**
     * Get the type name
     *
     * @param integer $nb Number of items
     * @return string Type name
     */
    public static function getTypeName($nb = 0)
    {
        return __('Assets Cleaner', 'assetscleaner');
    }

    /**
     * Get menu name
     *
     * @return string Menu name
     */
    public static function getMenuName()
    {
        return __('Assets Cleaner', 'assetscleaner');
    }

    /**
     * Give localized information about 1 task
     *
     * @param string $name Name of the task
     * @return array Array of strings
     */
    public static function cronInfo($name)
    {
        switch ($name) {
            case 'CleanOldAssets':
                return [
                    'description' => __('Clean old assets not updated by inventory', 'assetscleaner'),
                ];
            
            case 'PurgeOldTrash':
                return [
                    'description' => __('Purge old assets from trash', 'assetscleaner'),
                ];
        }
        return [];
    }

    /**
     * Execute the first cron task: Clean old assets
     * Move to trash assets not updated by inventory for X days
     *
     * @param CronTask $task Object of CronTask class for log / stat
     * @return int >0 : done, <0 : to be run again, 0 : nothing to do
     */
    public static function cronCleanOldAssets($task)
    {
        global $DB;

        $config = ConfigAssetsCleaner::getConfigValue('enabled');
        
        if (!$config) {
            $task->log(__('Assets Cleaner is disabled', 'assetscleaner'));
            return 0;
        }

        $inactive_delay = ConfigAssetsCleaner::getConfigValue('inactive_delay_days');
        $asset_types = ConfigAssetsCleaner::getConfigValue('asset_types');
        
        if (empty($asset_types)) {
            $task->log(__('No asset types configured', 'assetscleaner'));
            return 0;
        }

        $total_processed = 0;
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$inactive_delay} days"));
        
        $task->log("Cutoff date for inactive assets: $cutoff_date (older than {$inactive_delay} days)");

        foreach ($asset_types as $itemtype) {
            // Validate itemtype
            if (!class_exists($itemtype)) {
                $task->log(sprintf(__('Invalid itemtype: %s', 'assetscleaner'), $itemtype));
                continue;
            }

            $table = getTableForItemType($itemtype);
            if (!$table) {
                $task->log("No table found for itemtype: $itemtype");
                continue;
            }

            // Check if table has the required column
            if (!$DB->fieldExists($table, 'last_inventory_update')) {
                $task->log("Warning: Table $table does not have 'last_inventory_update' column, skipping $itemtype");
                continue;
            }

            // Build query to find old assets not in trash
            $query = [
                'SELECT' => ['id', 'name', 'last_inventory_update'],
                'FROM'   => $table,
                'WHERE'  => [
                    'is_deleted'  => 0,  // Not already in trash
                    'is_template' => 0,  // Not a template
                    'is_dynamic'  => 1,  // Only assets managed by inventory
                    'last_inventory_update' => ['<', $cutoff_date],  // Older than cutoff
                ],
            ];

            $iterator = $DB->request($query);
            $count = count($iterator);

            $task->log(sprintf("Found %d %s to process", $count, $itemtype::getTypeName($count)));

            if ($count == 0) {
                continue;
            }

            $processed = 0;
            $failed = 0;
            
            foreach ($iterator as $data) {
                $item = new $itemtype();
                
                if (!$item->getFromDB($data['id'])) {
                    $failed++;
                    continue;
                }

                // Move to trash (0 = soft delete, move to trash)
                if ($item->delete(['id' => $data['id']], 0)) {
                    $processed++;
                    $task->log(sprintf(
                        "✓ Moved to trash: %s \"%s\" (ID: %d, last update: %s)",
                        $itemtype::getTypeName(1),
                        $data['name'],
                        $data['id'],
                        $data['last_inventory_update'] ?? 'never'
                    ));
                } else {
                    $failed++;
                    $task->log(sprintf(
                        "✗ Failed to move to trash: %s \"%s\" (ID: %d)",
                        $itemtype::getTypeName(1),
                        $data['name'],
                        $data['id']
                    ));
                }
            }

            $total_processed += $processed;
            $task->log(sprintf(
                "Summary for %s: %d moved to trash, %d failed",
                $itemtype::getTypeName(2),
                $processed,
                $failed
            ));
        }

        if ($total_processed > 0) {
            $task->setVolume($total_processed);
            return 1;
        }

        return 0;
    }

    /**
     * Execute the second cron task: Purge old trash
     * Permanently delete assets that have been in trash for too long
     *
     * @param CronTask $task Object of CronTask class for log / stat
     * @return int >0 : done, <0 : to be run again, 0 : nothing to do
     */
    public static function cronPurgeOldTrash($task)
    {
        global $DB;

        $config = ConfigAssetsCleaner::getConfigValue('enabled');
        $second_action_enabled = ConfigAssetsCleaner::getConfigValue('second_action_enabled');
        
        if (!$config || !$second_action_enabled) {
            $task->log(__('Second action is disabled', 'assetscleaner'));
            return 0;
        }

        $trash_delay = ConfigAssetsCleaner::getConfigValue('trash_delay_days');
        $asset_types = ConfigAssetsCleaner::getConfigValue('asset_types');
        $delete_related = ConfigAssetsCleaner::getConfigValue('delete_related_items');
        
        if (empty($asset_types)) {
            $task->log(__('No asset types configured', 'assetscleaner'));
            return 0;
        }

        $total_purged = 0;
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$trash_delay} days"));
        
        $task->log("Purge cutoff date: $cutoff_date (in trash for more than {$trash_delay} days)");

        foreach ($asset_types as $itemtype) {
            // Validate itemtype
            if (!class_exists($itemtype)) {
                $task->log(sprintf(__('Invalid itemtype: %s', 'assetscleaner'), $itemtype));
                continue;
            }

            $table = getTableForItemType($itemtype);
            if (!$table) {
                $task->log("No table found for itemtype: $itemtype");
                continue;
            }

            // Check if table has the required column
            if (!$DB->fieldExists($table, 'last_inventory_update')) {
                $task->log("Warning: Table $table does not have 'last_inventory_update' column, skipping $itemtype");
                continue;
            }

            // Build query to find old trashed assets
            // An asset in trash with old last_inventory_update means it was put in trash long ago
            $query = [
                'SELECT' => ['id', 'name', 'last_inventory_update'],
                'FROM'   => $table,
                'WHERE'  => [
                    'is_deleted'  => 1,  // Already in trash
                    'is_template' => 0,  // Not a template
                    'is_dynamic'  => 1,  // Only assets managed by inventory
                    'last_inventory_update' => ['<', $cutoff_date],  // Older than cutoff
                ],
            ];

            $iterator = $DB->request($query);
            $count = count($iterator);
            
            $task->log(sprintf("Found %d trashed %s to purge", $count, $itemtype::getTypeName($count)));

            if ($count == 0) {
                continue;
            }

            $purged = 0;
            $failed = 0;
            
            foreach ($iterator as $data) {
                $item = new $itemtype();
                
                if (!$item->getFromDB($data['id'])) {
                    $failed++;
                    continue;
                }

                // Delete related items if configured
                if ($delete_related) {
                    self::deleteRelatedItems($item, $task);
                }

                // Permanently delete (purge) - 1 = force purge
                if ($item->delete(['id' => $data['id']], 1)) {
                    $purged++;
                    $task->log(sprintf(
                        "✓ Purged: %s \"%s\" (ID: %d, last update: %s)",
                        $itemtype::getTypeName(1),
                        $data['name'],
                        $data['id'],
                        $data['last_inventory_update'] ?? 'never'
                    ));
                } else {
                    $failed++;
                    $task->log(sprintf(
                        "✗ Failed to purge: %s \"%s\" (ID: %d)",
                        $itemtype::getTypeName(1),
                        $data['name'],
                        $data['id']
                    ));
                }
            }

            $total_purged += $purged;
            $task->log(sprintf(
                "Summary for %s: %d purged, %d failed",
                $itemtype::getTypeName(2),
                $purged,
                $failed
            ));
        }

        if ($total_purged > 0) {
            $task->setVolume($total_purged);
            return 1;
        }

        return 0;
    }

    /**
     * Delete related items (cartridges, network ports, etc.)
     *
     * @param CommonDBTM $item The item to delete related items for
     * @param CronTask $task The cron task for logging
     * @return void
     */
    protected static function deleteRelatedItems($item, $task)
    {
        global $DB;

        $itemtype = $item->getType();
        $items_id = $item->getID();

        // Delete network ports
        $iterator = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => 'glpi_networkports',
            'WHERE'  => [
                'itemtype' => $itemtype,
                'items_id' => $items_id,
            ],
        ]);

        $np_deleted = 0;
        foreach ($iterator as $data) {
            $networkport = new \NetworkPort();
            if ($networkport->delete(['id' => $data['id']], 1)) {
                $np_deleted++;
            }
        }

        if ($np_deleted > 0) {
            $task->log(sprintf(
                __('  Deleted %d network ports', 'assetscleaner'),
                $np_deleted
            ));
        }

        // For printers, delete cartridges
        if ($itemtype == 'Printer') {
            $iterator = $DB->request([
                'SELECT' => ['id'],
                'FROM'   => 'glpi_cartridgeitems_printermodels',
                'WHERE'  => [
                    'printermodels_id' => $item->fields['printermodels_id'] ?? 0,
                ],
            ]);

            // Note: This is a simplified approach. In a real scenario, you might want to
            // delete cartridge items more carefully based on the printer model relationship
        }

        // Delete Infocom data
        $infocom = new \Infocom();
        if ($infocom->getFromDBforDevice($itemtype, $items_id)) {
            if ($infocom->delete(['id' => $infocom->getID()], 1)) {
                $task->log(__('  Deleted infocom data', 'assetscleaner'));
            }
        }

        // Delete contracts, documents, and tickets associations
        $tables = [
            'glpi_contracts_items' => 'Contract associations',
            'glpi_documents_items' => 'Document associations',
            'glpi_items_tickets' => 'Ticket associations',
        ];

        foreach ($tables as $table => $description) {
            $deleted = $DB->delete($table, [
                'itemtype' => $itemtype,
                'items_id' => $items_id,
            ]);

            if ($deleted) {
                $task->log(sprintf(
                    __('  Deleted %d %s', 'assetscleaner'),
                    $deleted,
                    $description
                ));
            }
        }
    }
}
