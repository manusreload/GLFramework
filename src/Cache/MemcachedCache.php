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
 * Time: 13:04
 */

namespace GLFramework\Cache;

/**
 * Class MemcachedCache
 *
 * @package GLFramework\Cache
 */
class MemcachedCache extends Cache
{

    /**
     * @var \Memcache
     */
    private $conn;
    private $connected = false;

    /**
     * TODO
     *
     * @param $key
     * @param $value
     * @param null $duration
     * @return bool
     */
    public function set($key, $value, $duration = null)
    {
        // TODO: Implement set() method.
        if ($this->connected) {
            return $this->conn->set($key, $value, null, $duration);
        }
    }

    /**
     * TODO
     *
     * @param $key
     * @return array|string
     */
    public function get($key)
    {
        // TODO: Implement get() method.
        if ($this->connected) {
            return $this->conn->get($key);
        }
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
        if ($this->connected) {
            return $this->get($key) != null;
        }
        return false;
    }

    /**
     * TODO
     *
     * @param $key
     * @return bool
     */
    public function remove($key)
    {
        // TODO: Implement remove() method.
        if ($this->connected) {
            return $this->conn->delete($key);
        }
    }

    /**
     * TODO
     *
     * @param array $config
     * @return bool
     */
    public function connect($config = array())
    {
        // TODO: Implement connect() method.
        if (isset($config['database']['cache'])) {
            $configCache = $config['database']['cache'];
            $this->conn = new \Memcache();
            if ($this->conn->connect($configCache['host'], $configCache['port'], $configCache['timeout'])) {
                $this->connected = true;
                return true;
            }
        }
        return false;
    }
}
