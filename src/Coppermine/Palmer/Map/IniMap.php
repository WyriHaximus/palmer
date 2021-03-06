<?php
/**
 * Coppermine\Palmer - Interface for reading/writing DBC files.
 * Copyright (C) 2012  Daniel S. Reichenbach <daniel@kogitoapp.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Coppermine\Palmer\Map;

/**
 * Map DBC fields using an INI file
 *
 * @TODO Define constants to map strings from Ini files to field masks
 */
class IniMap extends AbstractMap
{

    /**
     * Creates a map from a given INI file
     *
     * Returns the created map
     *
     * @param string $file name of the file to create the map from
     *
     * @return \Coppermine\Palmer\Map\IniMap|boolean
     */
    public function fromFile($file)
    {
        if (is_readable($file)) {
            print_r(parse_ini_file($file, false, INI_SCANNER_RAW));

            return new self(parse_ini_file($file));
        }

        return false;
    }
}
