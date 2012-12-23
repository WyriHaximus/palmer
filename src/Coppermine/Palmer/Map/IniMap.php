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
 * Mapping of fields for a DBC file.
 */
class IniMap extends AbstractMap
{
    /**
     * Instantiate and set up mapping
     */
    public function __construct()
    {
        // TODO: Implement __construct() method.
    }

    /**
     * Destruct mapping
     */
    public function __destruct()
    {
        $this->_fields = null;
    }

    /**
     * {@inheritdoc}
     */
    public function addField($field, $type, $count)
    {
        // TODO: Implement addField() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isField($field)
    {
        // TODO: Implement isField() method.
    }

    /**
     * {@inheritdoc}
     */
    public function removeField($field)
    {
        // TODO: Implement removeField() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldOffset($field)
    {
        // TODO: Implement getFieldOffset() method.
    }
}
