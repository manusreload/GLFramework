<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 1/02/18
 * Time: 9:17
 */

namespace GLFramework\Globals;


class Globals
{
    protected static function _get($variable, $name, $default) {
        if (isset($variable[$name])) {
            return $variable[$name];
        }
        return $default;
    }

    protected static function _set(&$variable, $name, $value) {
        $variable[$name] = $value;
    }

    protected static function _delete(&$variable, $name) {
        unset($variable[$name]);
    }
    protected static function _has($variable, $name) {
        return isset($variable[$name]);
    }
}