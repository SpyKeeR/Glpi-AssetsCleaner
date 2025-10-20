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

namespace GlpiPlugin\Assetscleaner;

use CommonGLPI;
use Profile;

class ProfileAssetsCleaner extends CommonGLPI
{
    public static function getTypeName($nb = 0)
    {
        return __('Assets Cleaner Rights', 'assetscleaner');
    }

    /**
     * Initialize profiles
     *
     * @return void
     */
    public static function initProfile()
    {
        $profile = new Profile();
        $profiles = $profile->find();

        foreach ($profiles as $data) {
            $profile->getFromDB($data['id']);
            // Give rights to super-admin and admin profiles
            if ($data['interface'] == 'central') {
                $profile->update([
                    'id' => $data['id'],
                    AssetsCleaner::$rightname => ALLSTANDARDRIGHT,
                ]);
            }
        }
    }
}
