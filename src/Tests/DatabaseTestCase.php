<?php
/**
 *     GLFramework, small web application framework.
 *     Copyright (C) 2016.  Manuel Muñoz Rosa
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 16/03/16
 * Time: 11:39
 */

namespace GLFramework\Tests;

use GLFramework\DatabaseManager;

/**
 * Class DatabaseTestCase
 *
 * @package GLFramework\Tests
 */
class DatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * TODO
     *
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $db = new DatabaseManager();
        $config = $db->getConfig();
        return $this->createDefaultDBConnection($db->getConnection()->getPDO(), $config['database']['database']);
    }

    /**
     * TODO
     *
     * @param $model
     * @param $name
     * @param $value
     */
    public function fieldEqual($model, $name, $value)
    {
        $this->assertEquals($model->{$name}, $value);
    }
}
