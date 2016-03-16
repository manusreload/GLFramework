<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 19:43
 */

namespace GLFramework;


use GLFramework\Database\Connection;
use GLFramework\Database\MySQLConnection;

class DatabaseManager
{


    /**
     * @var \mysqli
     */
//    private static $link;
    private static $selected;
    /**
     * @var Connection
     */
    private static $connection;

    /**
     * DBConnection constructor.
     */
    public function __construct()
    {

        $this->connect();
    }

    public function getConfig()
    {
        return Bootstrap::getSingleton()->getConfig();
    }

    /**
     * @return Connection
     */
    public function instanceConnector()
    {
        $config = $this->getConfig();
        if(isset($config['database']['connector']))
        {
            $connector = $config['database']['connector'];
            return new $connector();
        }
        return new MySQLConnection();
    }

    public function connect()
    {
        if (!self::$connection) {
            $config =$this->getConfig();
            self::$connection = $this->instanceConnector();
            if(self::$connection->connect($config['database']['hostname'], $config['database']['username'], $config['database']['password']))
            {
                if(self::$connection->select_database($config['database']['database']))
                {
                    self::$selected = true;
                    return true;
                }
            }
            else
            {
                throw new \Exception("Can not establish connection to database!");
            }
        }
        return self::$selected;
    }

    public static function isSelected()
    {
        return self::$selected;
    }

    public function escape_string($string)
    {
        if(!self::$connection) return $string;

        if ($string == null) return $string;
        return self::$connection->escape_string($string);

    }

    public function select($query)
    {
        if (self::$connection) {
            return self::$connection->select($query, true);
        } else {
            throw new \Exception("Database connection is not open!");
        }
    }

    public function select_first($query)
    {
        $result = $this->select($query);
        if ($result && count($result) > 0) return $result[0];
        return $result;
    }

    public function select_count($query)
    {
        $result = $this->select_first($query);
        if ($result) return current($result);
        return 0;
    }

    public function exec($query)
    {
        if (self::$connection) {
            return self::$connection->select($query, false);
        } else {
            throw new \Exception("Database connection is not open!");
        }
        throw new \Exception("Database connection is not open!");

    }


    public function insert($query)
    {
        if($this->exec($query))
            return $this->getLastInsertId();
        return false;

    }

    public function getLastInsertId()
    {
        return $this->getConnection()->getLastInsertId();
    }

    public function error()
    {
        return $this->getConnection()->getLastError();
    }

    /**
     * @return mixed
     * @deprecated
     */
    public function getLink()
    {
        return self::$link;
    }
    public function getConnection()
    {
        return self::$connection;
    }
}