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

use Coppermine\Palmer\DBC;

/**
 * Interface that all Palmer Maps must implement
 */
interface MapInterface
{
    /**
     * @param string  $field The name of the field to add
     * @param integer $type  The type of the field to add
     * @param integer $count Number of fields to add
     *
     * @return mixed
     */
    public function addField($field, $type, $count);

    /**
     * Checks whether a given field exists in the map
     *
     * The return value of this function will be true if the field exists
     *
     * @param string $field The name of the field to check
     *
     * @return boolean
     */
    public function isField($field);

    /**
     * Removes a given field - if existent - from the map
     *
     * @param string $field
     */
    public function removeField($field);

    /**
     * Returns the number of fields in the map
     *
     * @return integer
     */
    public function getFieldCount();

    /**
     * @return array
     */
    public function getFields();

    /**
     * @param array $fields
     */
    public function setFields(array $fields);

    /**
     * Returns the offset for given field
     *
     * @param string $field
     *
     * @return integer
     */
    public function getFieldOffset($field);

    /**
     * Creates a map from a given file path
     *
     * Returns the created map
     *
     * @param string $file name of the file to create the map from
     */
    public function fromFile($file);

    /**
     * @param \Coppermine\Palmer\DBC $dbc    The DBc file to create the map from
     * @param boolean                $attach Attach a DBC map to the file
     */
    public function fromDbc(DBC $dbc, $attach = true);
}
