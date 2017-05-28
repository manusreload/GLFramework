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
 * Date: 24/02/16
 * Time: 10:35
 */

namespace GLFramework\Database;

/**
 * Class Connection
 *
 * @package GLFramework\Database
 */
abstract class Connection
{
    /**
     * TODO
     *
     * @param $hostname
     * @param $username
     * @param $password
     * @return mixed
     */
    abstract public function connect($hostname, $username, $password);

    /**
     * TODO
     *
     * @param $database
     * @return mixed
     */
    abstract public function select_database($database);

    /**
     * TODO
     *
     * @param $value
     * @return mixed
     */
    abstract public function escape_string($value);

    /**
     * TODO
     *
     * @param $query
     * @param array $arguments
     * @param bool $returnArray
     * @return mixed
     */
    abstract public function select($query, $arguments = array(), $returnArray = true);

    /**
     * TODO
     *
     * @return mixed
     */
    abstract public function getLastInsertId();

    /**
     * TODO
     *
     * @return mixed
     */
    abstract public function getLastError();

    /**
     * TODO
     *
     * @return mixed
     */
    abstract public function getPDO();

    /**
     * TODO
     *
     * @return mixed
     */
    abstract public function disconnect();
}
