<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 24/02/16
 * Time: 10:35
 */

namespace GLFramework\Database;


use DebugBar\DataCollector\PDO\TraceablePDOStatement;
use GLFramework\Events;
use TijsVerkoyen\CssToInlineStyles\Exception;

class MySQLConnection extends Connection
{

    /**
     * @var \mysqli
     */
    private $link;
    /**
     * @var \PDO
     */
    private $pdo;
    public function connect($hostname, $username, $password)
    {
        try{
            $this->pdo = new \PDO('mysql:host=' . $hostname . ';', $username, $password);
            Events::fire('onPDOCreated', array(&$this->pdo));
            if($this->pdo->errorCode() == 0)
            {
                $this->pdo->exec("SET NAMES utf8");
                return true;
            }
        }catch(\Exception $ex)
        {
            Events::fire('onException', $ex);
        }
        return false;
    }

    public function select_database($database)
    {
//        try{

            if($this->pdo)
            {
                if($this->pdo->exec("USE " . $database) !== FALSE)
                    return true;
            }
//        }catch(\Exception $ex)
//        {
//            new Exception($this->getLastError());
//        }
        return false;
    }

    public function escape_string($value)
    {
        if($this->pdo)
        {
            return substr($this->pdo->quote($value), 1, -1);
        }
        return $value;
    }

    public function select($query, $args = array(), $returnArray = true)
    {
        if(!GL_TESTING && !GL_INSTALL)
        {
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        }
        else{
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);

        }
        $stmt = $this->pdo->prepare($query);
        $result = $stmt->execute($args);
        $list = array();
        if ($result) {
            if($returnArray)
            {
                return $stmt->fetchAll();
            }
            return true;
        } else {
            throw new \Exception($query . "\n" . $this->getLastError());
        }
    }

    public function getLastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function getLastError()
    {
        $error = $this->pdo->errorInfo();
        return $error[2];
    }

    public function getPDO()
    {
        return $this->pdo;
    }
}