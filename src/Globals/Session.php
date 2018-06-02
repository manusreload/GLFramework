<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 18/01/18
 * Time: 16:27
 */

namespace GLFramework\Globals;


class Session extends Globals
{

    public static function get($name, $default = null) {
        return self::_get($_SESSION, $name, $default);
    }

    public static function set($name, $value) {
        self::_set($_SESSION, $name, $value);
    }
    public static function delete($name) {
        self::_delete($_SESSION, $name);
    }

    public static function has($name) {
        return self::_has($_SESSION, $name);
    }
}