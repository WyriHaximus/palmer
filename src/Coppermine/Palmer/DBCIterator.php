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
 * Allows iteration over DBC file records.
 */
class DBCIterator implements \Iterator
{
    /**
     * Reference to the DBC instance being iterated
     *
     * @var DBC
     */
    private $_dbc = null;

    /**
     * Current position with the DBC file
     *
     * @var integer
     */
    private $_position = 0;

    /**
     * Construct a new DBC iterator
     *
     * @param DBC $dbc
     */
    public function __construct(DBC $dbc)
    {
        $this->_dbc = $dbc;
    }

    /**
     * Destruct a DBC iterator
     */
    public function __destruct()
    {
        $this->_dbc = null;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $this->_dbc->getRecord($this->_position);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->_position++;
    }

    /**
     * Move backward to previous element
     */
    public function prev()
    {
        $this->_position--;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * Set position to a specified record.
     *
     * @param integer $position
     */
    public function seek($position)
    {
        $this->_position = $position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->_dbc->hasRecord($this->_position);
    }

}
