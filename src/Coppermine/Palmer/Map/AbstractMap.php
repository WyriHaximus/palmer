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
 * Base map class providing the map structure
 */
abstract class AbstractMap implements MapInterface
{
    /**
     * Holds fields defined in this mapping in name/rule pairs
     *
     * @var array
     */
    protected $_fields = array();

    /**
     * Number of raw fields in this mapping
     *
     * @var integer
     */
    protected $_fieldCount = 0;

    /**
     * Construct a new mapping
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
        // TODO: Implement __destruct() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldCount()
    {
        return $this->_fieldCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * {@inheritdoc}
     */
    public function setFields(array $fields)
    {
        $this->_fields = $fields;
    }
}
