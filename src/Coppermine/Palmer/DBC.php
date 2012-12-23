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

namespace Coppermine\Palmer;

/**
 * Main interface class for reading / writing DBC files.
 */
class DBC
{
    /**
     * Denotes an unsigned integer field type
     */
    const UINT = 'L';

    /**
     * Denotes a signed integer field type
     */
    const INT = 'l';

    /**
     * Denotes a float field type
     */
    const FLOAT = 'f';

    /**
     * Denotes a string field type
     */
    const STRING = 's';

    /**
     * Denotes a localized string field type
     */
    const STRING_LOC = 'sl';

    /**
     * Number of localization string fields
     */
    const LOCALIZATION = 8;
}
