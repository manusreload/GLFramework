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
 * Date: 28/03/16
 * Time: 13:02
 */

namespace GLFramework\Cache;

/**
 * Class Cache
 *
 * @package GLFramework\Cache
 */
abstract class Cache
{
    /**
     * TODO
     *
     * @param array $config
     * @return mixed
     */
    abstract public function connect($config = array());

    /**
     * TODO
     *
     * @param $key
     * @param $value
     * @param null $duration
     * @return mixed
     */
    abstract public function set($key, $value, $duration = null);

    /**
     * TODO
     *
     * @param $key
     * @return mixed
     */
    abstract public function get($key);

    /**
     * TODO
     *
     * @param $key
     * @return mixed
     */
    abstract public function hash($key);

    /**
     * TODO
     *
     * @param $key
     * @return mixed
     */
    abstract public function remove($key);
}
