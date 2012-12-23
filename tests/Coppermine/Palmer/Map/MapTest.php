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

use Coppermine\Palmer\Map\IniMap;

/**
 * Test functionality of the Ini file mapping class
 */
class MapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that a DBC map can be created.
     */
    public function testMapCanBeCreated()
    {
        $dbcMap = new IniMap();

        $dbcMap->addField('id', 0, 1);

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Test that a DBC map can be created from DBC file.
     */
    public function testMapCanBeCreatedFromFile()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * Test if a DBC file can be read using a map.
     */
    public function testMapCanBeEdited()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
