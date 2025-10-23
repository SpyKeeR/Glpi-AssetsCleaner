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

use CommonGLPI;
use Config;
use Html;
use Session;

class ConfigAssetsCleaner extends CommonGLPI
{
    public static function getTypeName($nb = 0)
    {
        return __('Assets Cleaner Configuration', 'assetscleaner');
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Config') {
            return [
                1 => "<span class='d-flex align-items-center'><i class='ti ti-recycle me-2'></i>" 
                     . __('Assets cleanup', 'assetscleaner') . "</span>"
            ];
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'Config') {
            self::showConfigForm();
        }
        return true;
    }

    /**
     * Get default configuration values
     *
     * @return array
     */
    public static function getDefaults()
    {
        return [
            'enabled'                => 0,
            'inactive_delay_days'    => 30,
            'trash_delay_days'       => 60,
            'second_action_enabled'  => 1,
            'asset_types'            => ['Printer'], // Printer, NetworkEquipment, Phone
            'delete_related_items'   => 1,
        ];
    }

    /**
     * Get configuration value
     *
     * @param string $key Configuration key
     * @return mixed Configuration value
     */
    public static function getConfigValue($key)
    {
        $config = Config::getConfigurationValues('assetscleaner');
        $defaults = self::getDefaults();
        
        if (isset($config[$key])) {
            // Handle serialized arrays
            if (in_array($key, ['asset_types'])) {
                return json_decode($config[$key], true);
            }
            return $config[$key];
        }
        
        return $defaults[$key] ?? null;
    }

    /**
     * Set configuration value
     *
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return boolean
     */
    public static function setConfigValue($key, $value)
    {
        // Handle arrays
        if (is_array($value)) {
            $value = json_encode($value);
        }
        
        return Config::setConfigurationValues('assetscleaner', [$key => $value]);
    }

    /**
     * Display configuration form
     *
     * @return void
     */
    public static function showConfigForm()
    {
        global $CFG_GLPI;

        if (!Session::haveRight('config', UPDATE)) {
            return false;
        }

        $config = Config::getConfigurationValues('assetscleaner');
        $defaults = self::getDefaults();
        
        // Merge with defaults
        foreach ($defaults as $key => $value) {
            if (!isset($config[$key])) {
                $config[$key] = $value;
            }
        }
        
        // Decode asset_types if needed
        if (isset($config['asset_types']) && is_string($config['asset_types'])) {
            $config['asset_types'] = json_decode($config['asset_types'], true);
        }

        echo "<div class='center'>";
        echo "<form name='form' method='post' action='" . $CFG_GLPI['root_doc'] . "/plugins/assetscleaner/front/config.php'>";
        
        echo "<table class='tab_cadre_fixe'>";
        
        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='4'>" . __('Assets Cleaner Configuration', 'assetscleaner') . "</th>";
        echo "</tr>";

        // Enable/Disable plugin
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Enable automatic cleaning', 'assetscleaner') . "</td>";
        echo "<td>";
        echo "<input type='hidden' name='enabled' value='0'>";
        echo "<input type='checkbox' name='enabled' value='1' " 
             . ($config['enabled'] ? 'checked' : '') . ">";
        echo "</td>";
        echo "<td colspan='2'></td>";
        echo "</tr>";

        // Inactive delay for first action
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Days before moving to trash', 'assetscleaner') . "</td>";
        echo "<td>";
        echo "<input type='number' name='inactive_delay_days' value='" 
             . $config['inactive_delay_days'] . "' min='1' max='365'>";
        echo "</td>";
        echo "<td colspan='2'>";
        echo "<i>" . __('Number of days without inventory update before moving to trash (only applies to automatically inventoried assets)', 'assetscleaner') . "</i>";
        echo "</td>";
        echo "</tr>";

        // Enable second action (purge)
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Enable permanent deletion (purge)', 'assetscleaner') . "</td>";
        echo "<td>";
        echo "<input type='hidden' name='second_action_enabled' value='0'>";
        echo "<input type='checkbox' name='second_action_enabled' value='1' " 
             . ($config['second_action_enabled'] ? 'checked' : '') . ">";
        echo "</td>";
        echo "<td colspan='2'></td>";
        echo "</tr>";

        // Trash delay for second action
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Days in trash before purging', 'assetscleaner') . "</td>";
        echo "<td>";
        echo "<input type='number' name='trash_delay_days' value='" 
             . $config['trash_delay_days'] . "' min='1' max='365'>";
        echo "</td>";
        echo "<td colspan='2'>";
        echo "<i>" . __('Number of days in trash before permanent deletion', 'assetscleaner') . "</i>";
        echo "</td>";
        echo "</tr>";

        // Asset types to clean
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Asset types to clean', 'assetscleaner') . "</td>";
        echo "<td>";
        $available_types = [
            'Printer' => __('Printers'),
            'NetworkEquipment' => __('Network Equipment'),
            'Phone' => __('Phones'),
        ];
        foreach ($available_types as $type => $label) {
            $checked = in_array($type, $config['asset_types']) ? 'checked' : '';
            echo "<label><input type='checkbox' name='asset_types[]' value='$type' $checked> $label</label><br>";
        }
        echo "</td>";
        echo "<td colspan='2'></td>";
        echo "</tr>";

        // Delete related items
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Delete related items', 'assetscleaner') . "</td>";
        echo "<td>";
        echo "<input type='hidden' name='delete_related_items' value='0'>";
        echo "<input type='checkbox' name='delete_related_items' value='1' " 
             . ($config['delete_related_items'] ? 'checked' : '') . ">";
        echo "</td>";
        echo "<td colspan='2'>";
        echo "<i>" . __('Delete cartridges, network ports, etc. when deleting the asset', 'assetscleaner') . "</i>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='center' colspan='4'>";
        echo "<input type='submit' name='update_config' value='" . __('Save') . "' class='btn btn-primary'>";
        echo "</td>";
        echo "</tr>";

        echo "</table>";
        Html::closeForm();
        echo "</div>";
    }

    /**
     * Save configuration
     *
     * @param array $input Configuration values
     * @return boolean
     */
    public static function saveConfig($input)
    {
        $values = [];
        
        // Validate and prepare values
        $values['enabled'] = isset($input['enabled']) ? (int)$input['enabled'] : 0;
        $values['inactive_delay_days'] = isset($input['inactive_delay_days']) ? max(1, (int)$input['inactive_delay_days']) : 30;
        $values['trash_delay_days'] = isset($input['trash_delay_days']) ? max(1, (int)$input['trash_delay_days']) : 60;
        $values['second_action_enabled'] = isset($input['second_action_enabled']) ? (int)$input['second_action_enabled'] : 0;
        $values['delete_related_items'] = isset($input['delete_related_items']) ? (int)$input['delete_related_items'] : 0;
        
        // Handle asset types array
        if (isset($input['asset_types']) && is_array($input['asset_types'])) {
            $values['asset_types'] = json_encode($input['asset_types']);
        } else {
            $values['asset_types'] = json_encode([]);
        }
        
        // Save all configuration values at once
        $result = Config::setConfigurationValues('assetscleaner', $values);
        
        // GLPI 11 returns true on success, but some versions might return differently
        // Check if values were actually saved
        if ($result !== false) {
            return true;
        }
        
        return false;
    }
}

// Create alias for GLPI legacy naming convention
class_alias(ConfigAssetsCleaner::class, 'PluginAssetscleanerConfig');
