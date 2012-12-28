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

use Coppermine\Palmer\Map\AbstractMap;

/**
 * Represents a single record in a DBC file.
 */
class DBCRecord
{
    /**
     * Identifier (first field) for this record (if any)
     *
     * @var integer $id
     */
    private $_id = null;

    /**
     * Position of this record in the DBC
     *
     * @var integer $_position
     */
    private $_position = 0;

    /**
     * Offset of this record in the DBC in bytes
     *
     * @var integer $_offset
     */
    private $_offset = 0;

    /**
     * Data contained in this record in a byte-string
     *
     * @var string $_data
     */
    private $_data = null;

    /**
     * Reference to the associated DBC
     *
     * @var DBC $_dbc
     */
    private $_dbc = null;

    /**
     * Construct a new DBCRecord
     *
     * @param DBC     $dbc      Reference to the DBC class
     * @param integer $position Current record within the class
     */
    public function __construct(DBC $dbc, $position)
    {
        $this->_dbc = $dbc;
        $this->_position = $position;
        $this->_offset = DBC::HEADER_SIZE + $position * $dbc->getRecordSize();

        $handle = $dbc->getHandle();
        fseek($handle, $this->_offset);
        if ($dbc->getRecordSize() > 0) {
            $this->_data = fread($handle, $dbc->getRecordSize());
        }
    }

    /**
     * Destruct a DBCRecord
     */
    public function __destruct()
    {
        $this->_id = null;
        $this->_data = null;
        $this->_dbc = null;

        $this->_offset = 0;
        $this->_position = 0;
    }

    /**
     * Returns the identifier of this record (first field)
     *
     * @return integer
     */
    public function getId()
    {
        if ($this->_id === null) {
            $this->_id = $this->getUInt(0);
        }

        return $this->_id;
    }

    /**
     * Returns the position of this record
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->_position;
    }

    /**
     * Dumps field information for this record (optionally uses the default map attached to the associated DBC)
     *
     * @param boolean $useMap
     */
    public function dump($useMap = false)
    {
        if (!$useMap || $this->_dbc->getMap() === null) {
            $fields = $this->asArray();
        } else {
            $fields = $this->extract();
        }
        var_dump($fields);
    }

    /**
     * Returns a collection of fields contained within this record as unsigned integers
     *
     * @return array
     */
    public function asArray()
    {
        return unpack(DBC::UINT . $this->_dbc->getFieldCount(), $this->_data);
    }

    /**
     * Extracts all data from this record using mappings in either the given or default DBCMap
     *
     * @param AbstractMap $map
     *
     * @return array|null
     */
    public function extract(AbstractMap $map = null)
    {
        $map = ($map) ? $map : $this->_dbc->getMap();
        if ($map === null) {
            return null;
        }
        $bytes = 0;
        $strings = array();
        $format = array();
        $fields = $map->getFields();
        foreach ($fields as $name => $rule) {
            $count = max($rule & 0xFF, 1);
            $bytes += DBC::FIELD_SIZE * $count;
            if ($rule & AbstractMap::UINT_MASK) {
                $format[] = DBC::UINT . $count . $name;
            } else if ($rule & AbstractMap::INT_MASK) {
                $format[] = DBC::INT . $count . $name;
            } else if ($rule & AbstractMap::FLOAT_MASK) {
                $format[] = DBC::FLOAT . $count . $name;
            } else if ($rule & AbstractMap::STRING_MASK) {
                $format[] = DBC::UINT . $count . $name;
                $strings[] = $name;
            } else if ($rule & AbstractMap::STRING_LOC_MASK) {
                $bytes += DBC::FIELD_SIZE * DBC::LOCALIZATION * $count;
                $format[] = DBC::UINT . $count . $name . '/@' . $bytes;
                $strings[] = $name;
            }
        }
        $format = implode('/', $format);
        $fields = unpack($format, $this->_data);
        foreach ($strings as $string) {
            $fields[$string] = $this->_dbc->getString($fields[$string]);
        }

        return $fields;
    }

    /**
     * Reads data from this record for given field of given type
     *
     * @param string $field Name of the field to get
     * @param string $type  Optional type of the field to get
     *
     * @throws \Exception
     * @return mixed  $value The value of $field
     */
    public function get($field, $type = DBC::UINT)
    {
        if (is_string($field)) {
            if ($map = $this->_dbc->getMap()) {
                $field = $map->getFieldOffset($field);
            } else {
                throw new \Exception('Addressing fields through string values requires DBC "' . $this->_dbc->getPath() . '" to have a valid mapping attached');
            }
        }

        $offset = $field * DBC::FIELD_SIZE;
        if ($offset >= strlen($this->_data)) {
            return null;
        }

        $isString = false;
        if ($isString = ($type === DBC::STRING || $type === DBC::STRING_LOC)) {
            $type = DBC::UINT;
        }
        list(, $value) = unpack($type, substr($this->_data, $offset, DBC::FIELD_SIZE));

        if ($isString) {
            $value = $this->_dbc->getString($value);
        }

        return $value;
    }

    /**
     * Writes data into this record for given field as given type
     *
     * @param string $field The field to write to
     * @param mixed  $value The value for the field
     * @param string $type  The data type of the field to write
     *
     * @throws \Exception
     * @return \Coppermine\Palmer\DBCRecord
     */
    public function set($field, $value, $type = DBC::UINT)
    {
        if (!$this->_dbc->isWritable()) {
            throw new \Exception('Modifying records requires DBC "' . $this->_dbc->getPath() . '" to be writable');
        }

        if (is_string($field)) {
            if ($map = $this->_dbc->getMap()) {
                $field = $map->getFieldOffset($field);
            } else {
                throw new \Exception('Addressing fields through string values requires DBC "' . $this->_dbc->getPath() . '" to have a valid mapping attached');
            }
        }

        $offset = $field * DBC::FIELD_SIZE;
        if ($offset >= strlen($this->_data)) {
            return $this;
        }

        $handle = $this->_dbc->getHandle();

        $isString = null;
        if ($isString = ($type === DBC::STRING || $type === DBC::STRING_LOC)) {
            $value = $this->_dbc->addString($value);
            $type = DBC::UINT;
        }
        $value = pack($type, $value);

        fseek($handle, $this->_offset + $offset);
        fwrite($handle, $value);
        $this->_data = substr_replace($this->_data, $value, $offset, 4);

        if ($field === 0) {
            $this->_dbc->index($value, $this->_position);
        }

        return $this;
    }

    /**
     * Reads an unsigned integer for given field from this record
     *
     * @param string $field The field to retrieve
     *
     * @return integer
     */
    public function getUInt($field)
    {
        return $this->get($field, DBC::UINT);
    }

    /**
     * Writes an unsigned integer to given field into this record
     *
     * @param string  $field The field to write to
     * @param integer $uint  The value to write to
     *
     * @return DBCRecord
     */
    public function setUInt($field, $uint)
    {
        return $this->set($field, $uint, DBC::UINT);
    }

    /**
     * Reads a signed integer for given field from this record
     *
     * @param string $field The field to retrieve
     *
     * @return integer
     */
    public function getInt($field)
    {
        return $this->get($field, DBC::INT);
    }

    /**
     * Writes a signed integer for given field into this record
     *
     * @param string  $field The field to write to
     * @param integer $int   The value to write to
     *
     * @return \Coppermine\Palmer\DBCRecord
     */
    public function setInt($field, $int)
    {
        return $this->set($field, $int, DBC::INT);
    }

    /**
     * Reads a float for given field from this record
     *
     * @param string $field The field to retrieve
     *
     * @return float
     */
    public function getFloat($field)
    {
        return $this->get($field, DBC::FLOAT);
    }

    /**
     * Writes a float for given field into this record
     *
     * @param string $field The field to write to
     * @param float  $float The value to write to
     *
     * @return \Coppermine\Palmer\DBCRecord
     */
    public function setFloat($field, $float)
    {
        return $this->set($field, $float, DBC::FLOAT);
    }

    /**
     * Reads a string for given field from this record
     *
     * @param string $field The field to retrieve
     *
     * @return string
     */
    public function getString($field)
    {
        return $this->get($field, DBC::STRING);
    }

    /**
     * Writes a string for given field into this record
     *
     * @param string $field  The field to write to
     * @param string $string The value to write to
     *
     * @return \Coppermine\Palmer\DBCRecord
     */
    public function setString($field, $string)
    {
        return $this->set($field, $string, DBC::STRING);
    }
}
