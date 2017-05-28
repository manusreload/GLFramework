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
 * Time: 13:15
 */

namespace GLFramework\Cache;

/**
 * Class MemoryCache
 *
 * @package GLFramework\Cache
 */
class MemoryCache extends Cache
{

    private $array = array();

    /**
     * TODO
     *
     * @param array $config
     * @return bool
     */
    public function connect($config = array())
    {
        // TODO: Implement connect() method.
        return true;
    }

    /**
     * TODO
     *
     * @param $key
     * @param $value
     * @param null $duration
     */
    public function set($key, $value, $duration = null)
    {
        // TODO: Implement set() method.
        $this->array[$key] = $value;
    }

    /**
     * TODO
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        // TODO: Implement get() method.
        return $this->array[$key];
    }

    /**
     * TODO
     *
     * @param $key
     * @return bool
     */
    public function hash($key)
    {
        // TODO: Implement hash() method.
        return isset($this->array[$key]);
    }

    /**
     * TODO
     *
     * @param $key
     */
    public function remove($key)
    {
        // TODO: Implement remove() method.
        unset($this->array[$key]);
    }
}
