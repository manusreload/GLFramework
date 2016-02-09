<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 19:43
 */

namespace GLFramework;


class DBConnection
{


    /**
     * @var \mysqli
     */
    private static $link;
    private static $selected;

    /**
     * DBConnection constructor.
     */
    public function __construct()
    {
        $this->connect();
    }

    public function connect()
    {
        if (!self::$link) {
            $config = Bootstrap::getSingleton()->getConfig();
            self::$link = mysqli_connect($config['database']['hostname'],
                $config['database']['username'],
                $config['database']['password']);
            if (self::$link) {
                self::$link->query("SET NAMES utf8");
                try {

//                    if (self::$link->get_connection_stats()) {
                        self::$selected = self::$link->select_db($config['database']['database']);

                        return self::$selected;
//                    }
                } catch (\Exception $ex) {
                }
                return self::$selected;
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
        if(!self::$link) return $string;

        if ($string == null) return $string;
        return mysqli_escape_string(self::$link, $string);

    }

    public function select($query)
    {
        if (self::$link) {

            $result = mysqli_query(self::$link, $query);
            $list = array();
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $list[] = $row;
                }
                return $list;
            } else {
                throw new \Exception(self::$link->error);
            }
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
        if(self::$link)
        {

            $result = mysqli_query(self::$link, $query);
            if ($result) {
                return isset($result->current_field) || true;
            }

            throw new \Exception(self::$link->error);
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
        return $this->getLink()->insert_id;
    }

    public function error()
    {
        return self::$link->error;
    }

    public function getLink()
    {
        return self::$link;
    }
}