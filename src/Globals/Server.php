<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 18/01/18
 * Time: 16:23
 */

namespace GLFramework\Globals;


class Server extends Globals
{
    /**
     * Obtener la variable
     *
     * @param $name
     * @param null $default
     * @return null
     */
    public static function get($name, $default = null) {
        return self::_get($_SERVER, $name, $default);
    }

}