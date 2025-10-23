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

use Glpi\Plugin\Hooks;
use GlpiPlugin\Assetscleaner\AssetsCleaner;
use GlpiPlugin\Assetscleaner\ConfigAssetsCleaner;
use GlpiPlugin\Assetscleaner\ProfileAssetsCleaner;

use function Safe\define;

define('PLUGIN_ASSETSCLEANER_VERSION', '1.0.3');

// Minimal GLPI version, inclusive
define('PLUGIN_ASSETSCLEANER_MIN_GLPI', '11.0.0');
// Maximum GLPI version, exclusive
define('PLUGIN_ASSETSCLEANER_MAX_GLPI', '11.0.99');

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_assetscleaner()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['assetscleaner'] = true;

    Plugin::registerClass(ConfigAssetsCleaner::class, ['addtabon' => 'Config']);
    Plugin::registerClass(AssetsCleaner::class);

    // Display a menu entry in Configuration
    if (Session::haveRight('config', UPDATE)) {
        // Point to main GLPI config page with our plugin tab
        $PLUGIN_HOOKS['config_page']['assetscleaner'] = 'Config$1';
        $PLUGIN_HOOKS['menu_toadd']['assetscleaner'] = ['config' => AssetsCleaner::class];
    }

    // Register the cron tasks
    $PLUGIN_HOOKS[Hooks::POST_INIT]['assetscleaner'] = 'plugin_assetscleaner_postinit';
}

/**
 * Post init hook
 *
 * @return void
 */
function plugin_assetscleaner_postinit()
{
    // All plugins are initialized
}

/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_assetscleaner()
{
    return [
        'name'         => __('Assets Cleaner', 'assetscleaner'),
        'version'      => PLUGIN_ASSETSCLEANER_VERSION,
        'author'       => 'SpyKeeR',
        'license'      => 'GPLv2+',
        'homepage'     => 'https://github.com/SpyKeeR/assetscleaner',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_ASSETSCLEANER_MIN_GLPI,
                'max' => PLUGIN_ASSETSCLEANER_MAX_GLPI,
            ],
            'php'  => [
                'min' => '8.2',
            ],
        ],
    ];
}

/**
 * Check pre-requisites before install
 * OPTIONAL, but recommended
 *
 * @return boolean
 */
function plugin_assetscleaner_check_prerequisites()
{
    // Check PHP version
    if (version_compare(PHP_VERSION, '8.2', '<')) {
        echo "PHP 8.2 or higher is required";
        return false;
    }

    // Check GLPI version
    if (version_compare(GLPI_VERSION, PLUGIN_ASSETSCLEANER_MIN_GLPI, '<')
        || version_compare(GLPI_VERSION, PLUGIN_ASSETSCLEANER_MAX_GLPI, '>=')) {
        echo "This plugin requires GLPI >= " . PLUGIN_ASSETSCLEANER_MIN_GLPI 
             . " and < " . PLUGIN_ASSETSCLEANER_MAX_GLPI;
        return false;
    }

    return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_assetscleaner_check_config($verbose = false)
{
    if (true) { // Configuration check
        return true;
    }

    if ($verbose) {
        echo __('Installed / not configured', 'assetscleaner');
    }
    return false;
}
