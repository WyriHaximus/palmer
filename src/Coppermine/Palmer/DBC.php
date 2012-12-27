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
use Coppermine\Palmer\Map\AbstractMap;

/**
 * Represents a DBC file
 */
class DBC implements \IteratorAggregate
{
    /**
     * Defines signature for a DBC file
     */
    const SIGNATURE = 'WDBC';

    /**
     * Defines the size of the header in bytes
     */
    const HEADER_SIZE = 20;

    /**
     * Defines the field size in bytes
     */
    const FIELD_SIZE = 4;

    /**
     * Convenience NULL-byte constant
     */
    const NULL_BYTE = "\0";

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

    /**
     * Holds a reference to this DBC on disk
     *
     * @var resource $_handle
     */
    private $_handle = null;

    /**
     * Holds path to this DBC on disk
     *
     * @var string $_path
     */
    private $_path = null;

    /**
     * Represents the index for the records in this DBC paired by ID/position
     *
     * @var array $_index
     */
    private $_index = null;

    /**
     * Amount of records in this DBC
     *
     * @var integer $_recordCount
     */
    private $_recordCount = 0;

    /**
     * Record size in bytes
     *
     * @var integer $_recordSize
     */
    private $_recordSize = 0;

    /**
     * Amount of fields in this DBC
     *
     * @var integer $_fieldCount
     */
    private $_fieldCount = 0;

    /**
     * Reference to the attached map (if any)
     *
     * @var \Coppermine\Palmer\Map\AbstractMap $_map
     */
    private $_map = null;

    /**
     * String-block contains all strings defined in the DBC file
     *
     * @var string $_stringBlock
     */
    private $_stringBlock = self::NULL_BYTE;

    /**
     * Size of the string-block
     *
     * @var integer $_stringBlockSize
     */
    private $_stringBlockSize = 1;

    /**
     * Whether this DBC is writable (enables adding records and strings)
     *
     * @var boolean $_writable
     */
    private $_writable = true;

    /**
     * Construct a new DBC
     *
     * @param string                             $path Path to the DBC file
     * @param \Coppermine\Palmer\Map\AbstractMap $map  Mapping of the DBC file
     *
     * @throws \Exception
     */
    public function __construct($path, AbstractMap $map = null)
    {
        if (!is_file($path)) {
            throw new \Exception('DBC "' . $path . '" could not be found');
        }

        $this->_path = $path;

        $this->_handle = @fopen($path, 'r+b');
        if (!$this->_handle) {
            $this->_handle = @fopen($path, 'rb');
            $this->_writable = false;
            if (!$this->_handle) {
                throw new \Exception('DBC "' . $path . '" is not readable');
            }
        }
        $size = filesize($path);

        $sig = fread($this->_handle, 4);
        if ($sig !== self::SIGNATURE) {
            throw new \Exception('DBC "' . $path . '" has an invalid signature and is therefore not valid');
        }
        if ($size < self::HEADER_SIZE) {
            throw new \Exception('DBC "' . $path . '" has a malformed header');
        }

        list(, $this->_recordCount, $this->_fieldCount, $this->_recordSize, $this->_stringBlockSize) = unpack(self::UINT . '4', fread($this->_handle, 16));

        $offset = self::HEADER_SIZE + $this->_recordCount * $this->_recordSize;

        if ($size < $offset) {
            throw new \Exception('DBC "' . $path . '" is short of ' . ($offset - $size) . ' bytes for ' . $this->_recordCount . ' records');
        }
        fseek($this->_handle, $offset);

        if ($size < $offset + $this->_stringBlockSize) {
            throw new \Exception('DBC "' . $path . '" is short of ' . ($offset + $this->_stringBlockSize - $size) . ' bytes for string-block');
        }
        $this->_stringBlock = fread($this->_handle, $this->_stringBlockSize);

        $this->attach($map);
    }

    /**
     * Destruct a DBC
     */
    public function __destruct()
    {
        $this->finalize();
        if ($this->_handle !== null) {
            fclose($this->_handle);
            $this->_handle = null;
        }
        $this->_index = null;
        $this->_map = null;
        $this->_stringBlock = null;
    }

    /**
     * Returns the amount of fields in this DBC
     *
     * @return integer
     */
    public function getFieldCount()
    {
        return $this->_fieldCount;
    }

    /**
     * Returns the resource handle
     *
     * @return resource
     */
    public function getHandle()
    {
        return $this->_handle;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new DBCIterator($this);
    }

    /**
     * Returns the map attached to this DBC
     *
     * @return \Coppermine\Palmer\Map\AbstractMap
     */
    public function getMap()
    {
        return $this->_map;
    }

    /**
     * Returns the path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Returns the amount of records
     *
     * @return integer
     */
    public function getRecordCount()
    {
        return $this->_recordCount;
    }

    /**
     * Returns the size of a record
     *
     * @return integer
     */
    public function getRecordSize()
    {
        return $this->_recordSize;
    }

    /**
     * Returns the entire string-block in this DBC
     *
     * @return string
     */
    public function getStringBlock()
    {
        return $this->_stringBlock;
    }

    /**
     * Fetches a record by zero-based position (if any)
     *
     * @param integer $position
     *
     * @return DBCRecord|null
     */
    public function getRecord($position)
    {
        if ($this->hasRecord($position)) {
            return new DBCRecord($this, $position);
        }

        return null;
    }

    /**
     * Fetches a record by ID (first field) and will ensure the index has been generated
     *
     * @param integer $index
     *
     * @return DBCRecord|null
     */
    public function getRecordByID($index)
    {
        if ($this->_index === null) {
            $this->index();
        }
        if (isset($this->_index[$index])) {
            return new DBCRecord($this, $this->_index[$index]);
        }

        return null;
    }

    /**
     * Whether this DBC has a record at the given zero-based position
     *
     * @param integer $position The record position
     *
     * @return boolean
     */
    public function hasRecord($position)
    {
        return ($position >= 0 && $position < $this->_recordCount);
    }

    /**
     * Whether this DBC has a record identified by given ID (first field)
     *
     * @param mixed $index The wanted record
     *
     * @return boolean
     */
    public function hasRecordByID($index)
    {
        if ($this->_index === null) {
            $this->index();
        }

        return (isset($this->_index[$index]));
    }

    /**
     * Whether the field given by the zero-based offset exists in this DBC
     *
     * @param integer $field The column offset
     *
     * @return boolean
     */
    public function hasField($field)
    {
        return ($field >= 0 && $field < $this->_fieldCount);
    }

    /**
     * Whether this DBC is writable
     *
     * @return boolean
     */
    public function isWritable()
    {
        return ($this->_handle !== null && $this->_writable);
    }

    /**
     * Attaches a mapping
     *
     * @param \Coppermine\Palmer\Map\AbstractMap $map
     *
     * @throws \Exception
     * @return \Coppermine\Palmer\DBC
     */
    public function attach(AbstractMap $map = null)
    {
        $this->_map = null;
        if ($map !== null) {
            $delta = $map->getFieldCount() - $this->getFieldCount();
            if ($delta !== 0) {
                throw new \Exception('Mapping holds ' . $map->getFieldCount() . ' fields, but DBC "' . $this->_path . '" expects ' . $this->getfieldCount());
            }
            $this->_map = clone $map;
        }

        return $this;
    }

    /**
     * Finalizes this writable DBC, updating its header and writing the string block
     */
    public function finalize()
    {
        $size = strlen($this->_stringBlock);
        if ($this->_handle !== null && $this->_writable && $this->_stringBlockSize !== $size) {
            fseek($this->_handle, self::HEADER_SIZE + $this->_recordCount * $this->_recordSize);
            fwrite($this->_handle, $this->_stringBlock);

            $this->_stringBlockSize = $size;

            fseek($this->_handle, 16);
            fwrite($this->_handle, pack(self::UINT, $this->_stringBlockSize));
        }
    }

    /**
     * Generates an index of this DBC consisting of ID/position pairs and optionally updates given ID to given position
     *
     * @param mixed   $index    TODO: Properly document and set type
     * @param integer $position TODO: Properly document and set type
     *
     * @return \Coppermine\Palmer\DBC
     */
    public function index($index = null, $position = null)
    {
        if ($this->_index === null) {
            $this->_index = array();
            fseek($this->_handle, DBC::HEADER_SIZE);
            for ($i = 0; $i < $this->_recordCount; $i++) {
                list(, $rid) = unpack(self::UINT, fread($this->_handle, 4));
                $this->_index[$rid] = $i;
                fseek($this->_handle, $this->_recordSize - 4, SEEK_CUR);
            }
        }
        if ($index !== null) {
            $prev = array_search($position, $this->_index, true);
            if ($prev !== false) {
                unset($this->_index[$prev]);
            }
            $this->_index[$index] = $position;
        }

        return $this;
    }

    /**
     * Creates an empty DBC using the given mapping (will overwrite any existing DBCs)
     *
     * @param string              $file  Path for the DBC
     * @param integer|AbstractMap $count Field count
     *
     * @throws \Exception
     * @return DBC
     */
    public static function create($file, $count)
    {
        $handle = @fopen($file, 'w+b');
        if (!$handle) {
            throw new \Exception('New DBC "' . $file . '" could not be created/opened for writing');
        }

        $map = null;
        if ($count instanceof AbstractMap) {
            $map = $count;
            $count = $map->getFieldCount();
        }

        fwrite($handle, self::SIGNATURE);
        fwrite($handle, pack(self::UINT . '4', 0, $count, $count * self::FIELD_SIZE, 1));
        fwrite($handle, self::NULL_BYTE);
        fclose($handle);

        $dbc = new self($file, $map);

        return $dbc;
    }

    /**
     * Adds a set of scalar values as a record or adds given arrays as records (nesting is allowed)
     *
     * @throws \Exception
     * @return DBC
     */
    public function add()
    {
        if (!$this->_writable || $this->_map === null) {
            throw new \Exception('Adding records requires DBC "' . $this->_path . '" to be writable and have a valid mapping attached');
        }

        $args = func_get_args();
        if (isset($args[0])) {
            $scalars = true;
            foreach ($args as $arg) {
                if ($scalars && !is_scalar($arg)) {
                    $scalars = false;
                }
                if (is_array($arg)) {
                    call_user_func_array(array($this, __METHOD__), $arg);
                }
            }
            if ($scalars) {
                $this->_add($args);
            }
        }

        return $this;
    }

    /**
     * Adds the given record of scalar values to the DBC being created
     *
     * @param array $record
     */
    private function _add(array $record)
    {
        $fields = $this->_map->getFields();

        fseek($this->_handle, self::HEADER_SIZE + $this->_recordCount * $this->_recordSize);

        foreach ($fields as $name => $rule) {
            $count = max($rule & 0xFF, 1);
            for ($i = 0; $i < $count; $i++) {
                $item = array_shift($record);
                $value = '';

                if ($item === null) {
                    $value = pack(DBC::UINT, 0);
                } else if ($rule & AbstractMap::UINT_MASK) {
                    $value = pack(DBC::UINT, $item);
                } else if ($rule & AbstractMap::INT_MASK) {
                    $value = pack(DBC::INT, $item);
                } else if ($rule & AbstractMap::FLOAT_MASK) {
                    $value = pack(DBC::FLOAT, $item);
                } else if ($rule & AbstractMap::STRING_MASK || $rule & AbstractMap::STRING_LOC_MASK) {
                    $offset = $this->addString($item);
                    $value = pack(DBC::UINT, $offset);
                }
                fwrite($this->_handle, $value);
                if ($rule & AbstractMap::STRING_LOC_MASK) {
                    fseek($this->_handle, DBC::LOCALIZATION * DBC::FIELD_SIZE, SEEK_CUR);
                }
            }
        }

        fseek($this->_handle, 4);
        fwrite($this->_handle, pack(self::UINT, ++$this->_recordCount));
    }

    /**
     * Returns the string found in the string-block given by the offset in bytes (if any)
     *
     * @param integer $offset
     *
     * @return null|string
     */
    public function getString($offset)
    {
        if ($offset < 1 || $offset > strlen($this->_stringBlock)) {
            return null;
        }
        $length = strpos($this->_stringBlock, self::NULL_BYTE, $offset) - $offset;

        return substr($this->_stringBlock, $offset, $length);
    }

    /**
     * Adds a string to the string-block and returns the offset in bytes
     *
     * @param string $string
     *
     * @throws \Exception
     * @return integer
     */
    public function addString($string)
    {
        if (!$this->_writable) {
            throw new \Exception('Adding strings requires DBC "' . $this->_path . '" to be writable');
        }
        $offset = strlen($this->_stringBlock);
        $this->_stringBlock .= $string . self::NULL_BYTE;

        return $offset;
    }
}
