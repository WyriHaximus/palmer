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
 * Base map class providing the map structure
 */
abstract class AbstractMap implements MapInterface
{
    /**
     * Unsigned integer bit mask
     */
    const UINT_MASK = 0x0100;

    /**
     * Signed integer bit mask
     */
    const INT_MASK = 0x0200;

    /**
     * Float bit mask
     */
    const FLOAT_MASK = 0x0400;

    /**
     * String bit mask
     */
    const STRING_MASK = 0x0800;

    /**
     * Localized string bit mask
     */
    const STRING_LOC_MASK = 0x1000;

    /**
     * Sample count
     */
    const SAMPLES = 255;

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
     *
     * @param array $fields
     */
    public function __construct(array $fields = null)
    {
        $this->_fields = ($fields !== null) ? $fields : array();
        foreach ($this->_fields as $field => &$rule) {
            if ($rule === null) {
                $rule = self::UINT_MASK;
            }
            $rule = (int) $rule;
            $this->_count += self::countInBitmask($rule);
        }
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

    /**
     * {@inheritdoc}
     */
    public function isField($field)
    {
        return (isset($this->_fields[$field]));
    }

    /**
     * Calculates the number of fields used up by the given bitmask
     *
     * @param integer $bitmask A bitmask for the field type
     * @param integer $upTo    Highest number possible for a given mask
     *
     * @return integer
     */
    public static function countInBitmask($bitmask, $upTo = PHP_INT_MAX)
    {
        $count = min(max($bitmask & 0xFF, 1), $upTo);
        if ($bitmask & self::STRING_LOC_MASK) {
            $count += $count * DBC::LOCALIZATION;
        }

        return $count;
    }

    /**
     * Whether given set of bits is a probable IEEE-754 single precision floating point number
     *
     * @param integer $bits
     *
     * @return boolean
     * @see    http://stackoverflow.com/questions/2485388/heuristic-to-identify-if-a-series-of-4-bytes-chunks-of-data-are-integers-or-float/2953466#2953466
     */
    public static function isProbableFloat($bits)
    {
        // $sign = ($bits & 0x80000000) != 0;
        $exp = (($bits & 0x7F800000) >> 23) - 127;
        $mant = $bits & 0x007FFFFF;

        if (-30 <= $exp && $exp <= 30) {
            return true;
        }
        if ($mant !== 0 && ($mant & 0x0000FFFF) == 0) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldOffset($field)
    {
        $suffix = 0;
        if (!isset($this->_fields[$field])) {
            if (preg_match('#(.+?)(\d+?)$#', $field, $match) === 1) {
                list(, $field, $suffix) = $match;
                $suffix = (int) $suffix - 1;
            }
        }

        if (!isset($this->_fields[$field])) {
            return -1;
        }

        $target = $field;

        $offset = 0;
        foreach ($this->_fields as $field => $rule) {
            if ($target === $field) {
                $offset += self::countInBitmask($rule, $suffix);

                return $offset;
            }
            $offset += self::countInBitmask($rule);
        }

        return -1;
    }

    /**
     * {@inheritdoc}
     */
    public function addField($field, $type = DBC::UINT, $count = 0)
    {
        $bitmask = $count;
        if ($type === DBC::UINT) {
            $bitmask |= self::UINT_MASK;
        } else if ($type === DBC::INT) {
            $bitmask |= self::INT_MASK;
        } else if ($type === DBC::FLOAT) {
            $bitmask |= self::FLOAT_MASK;
        } else if ($type === DBC::STRING) {
            $bitmask |= self::STRING_MASK;
        } else if ($type === DBC::STRING_LOC) {
            $bitmask |= self::STRING_LOC_MASK;
        }
        if ($this->isField($field)) {
            $this->_count -= self::countInBitmask($this->_fields[$field]);
        }
        $this->_count += self::countInBitmask($bitmask);
        $this->_fields[$field] = $bitmask;
    }

    /**
     * {@inheritdoc}
     */
    public function removeField($field)
    {
        if ($this->isField($field)) {
            $this->_count -= self::countInBitmask($this->_fields[$field]);
            unset($this->_fields[$field]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fromFile($file)
    {
        // TODO: Implement fromFile() method.
    }

    /**
     * {@inheritdoc}
     */
    public function fromDbc(DBC $dbc, $attach = true)
    {
        // TODO: Implement fromDbc() method.
    }
}
