<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 24/02/16
 * Time: 10:35
 */

namespace GLFramework\Database;


class MySQLConnection extends Connection
{

    /**
     * @var \mysqli
     */
    private $link;
    public function connect($hostname, $username, $password)
    {
        try{
            $this->link = new \mysqli();
            $this->link->connect($hostname, $username, $password);
            if($this->link->connect_error == 0)
            {
                $this->link->query("SET NAMES utf8");
                return true;
            }
        }catch(\Exception $ex)
        {

        }
        return false;
    }

    public function select_database($database)
    {
        try{

            if($this->link)
            {
                $this->link->select_db($database);
                return true;
            }
        }catch(\Exception $ex)
        {

        }
        return false;
    }

    public function escape_string($value)
    {
        if($this->link)
        {
            return $this->link->escape_string($value);
        }
        return $value;
    }

    public function select($query, $returnArray = true)
    {

        $result = $this->link->query($query);
        $list = array();
        if ($result) {
            if($returnArray)
            {
                while ($row = $result->fetch_assoc()) {
                    $list[] = $row;
                }
                return $list;
            }
            return isset($result->current_field) || true;
        } else {
            throw new \Exception($this->link->error);
        }
    }

    public function getLastInsertId()
    {
        return $this->link->insert_id;
    }

    public function getLastError()
    {
        return $this->link->error;
    }
}