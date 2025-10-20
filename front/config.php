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
 * @copyright Copyright (C) 2025 by AssetsCleaner plugin team.
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/pluginsGLPI/assetscleaner
 * -------------------------------------------------------------------------
 */

use GlpiPlugin\Assetscleaner\ConfigAssetsCleaner;

// Check if plugin is activated
$plugin = new Plugin();
if (!$plugin->isActivated('assetscleaner')) {
    Html::displayNotFoundError();
}

// Check rights
Session::checkRight('config', UPDATE);

// Process form submission
if (isset($_POST['update_config'])) {
    if (ConfigAssetsCleaner::saveConfig($_POST)) {
        Session::addMessageAfterRedirect(
            __('Configuration saved successfully', 'assetscleaner'),
            false,
            INFO
        );
    } else {
        Session::addMessageAfterRedirect(
            __('Error saving configuration', 'assetscleaner'),
            false,
            ERROR
        );
    }
    Html::back();
}

// Display page
Html::header(
    __('Assets Cleaner', 'assetscleaner'),
    $_SERVER['PHP_SELF'],
    'config',
    'plugins',
    'assetscleaner'
);

// Display configuration form
ConfigAssetsCleaner::showConfigForm();

Html::footer();
