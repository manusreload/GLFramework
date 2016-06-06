<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 28/03/16
 * Time: 13:02
 */

namespace GLFramework\Cache;


abstract class Cache
{

    abstract public function connect($config = array());
    abstract public function set($key, $value, $duration = null);
    abstract public function get($key);
    abstract public function hash($key);
    abstract public function remove($key);


}