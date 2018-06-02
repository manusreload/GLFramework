<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 1/02/18
 * Time: 9:31
 */

namespace GLFramework\Globals;


class Cookie extends Globals
{


    public static function get($name, $default = null) {
        return self::_get($_COOKIE, $name, $default);
    }

    public static function set($name, $value) {
        self::_set($_COOKIE, $name, $value);
    }
    public static function delete($name) {
        self::_delete($_COOKIE, $name);
    }

    public static function has($name) {
        return self::_has($_COOKIE, $name);
    }

}