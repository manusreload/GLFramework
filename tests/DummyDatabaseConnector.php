<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 2019-01-09
 * Time: 16:44
 */

class DummyDatabaseConnector extends GLFramework\Database\Connection
{

    private static $data;
    private static $callback;
    public function connect($hostname, $username, $password, $port = null)
    {
        // TODO: Implement connect() method.
        return true;
    }

    public function select_database($database)
    {
        // TODO: Implement select_database() method.
        return true;
    }

    public function escape_string($value)
    {
        // TODO: Implement escape_string() method.
        return $value;
    }

    public function select($query, $arguments = array(), $returnArray = true)
    {
        // TODO: Implement select() method.
        echo $query . "\n";
        if(self::$callback) {
            return call_user_func(self::$callback, $query, $arguments);
        }
        return self::$data[$query];
    }

    public function getLastInsertId()
    {
        // TODO: Implement getLastInsertId() method.
        return false;
    }

    public function getLastError()
    {
        // TODO: Implement getLastError() method.
        return false;
    }

    public function getPDO()
    {
        // TODO: Implement getPDO() method.
        return false;
    }

    public function disconnect()
    {
        // TODO: Implement disconnect() method.
    }

    public static function setData($sql, $data) {
        self::$data[$sql] = $data;
    }

    public function setCallback($fn) {
        self::$callback = $fn;

    }


}
