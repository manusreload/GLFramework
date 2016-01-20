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
        if(!self::$link)
        {
            $config = Bootstrap::getSingleton()->getConfig();
            self::$link = mysqli_connect($config['database']['hostname'],
                $config['database']['username'],
                $config['database']['password']);
            try{

                if(self::$link->get_connection_stats())
                {
                    self::$selected = self::$link->select_db($config['database']['database']);
                    return self::$selected;
                }
            }catch(\Exception $ex)
            {
            }
            return self::$selected;
        }
        return self::$selected;
    }

    public static function isSelected()
    {
        return self::$selected;
    }

    public function escape_string($string)
    {
        if($string == null) return $string;
        return mysqli_escape_string(self::$link, $string);
    }

    public function select($query)
    {
        $result = mysqli_query(self::$link, $query);
        $list = array();
        if($result)
        {
            while($row = $result->fetch_assoc())
            {
                $list[] = $row;
            }
            return $list;
        }
        else{
            throw new \Exception(self::$link->error);
        }
    }

    public function select_first($query)
    {
        $result = $this->select($query);
        if($result && count($result) > 0) return $result[0];
        return $result;
    }

    public function select_count($query)
    {
        $result = $this->select_first($query);
        if($result) return current($result);
        return 0;
    }

    public function exec($query)
    {
        $result = mysqli_query(self::$link, $query);
        if($result)
        {
            return isset($result->current_field) || true;
        }
        return false;
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