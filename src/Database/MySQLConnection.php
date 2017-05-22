<?php
/**
 *     GLFramework, small web application framework.
 *     Copyright (C) 2016.  Manuel Muñoz Rosa
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
            $result = Events::dispatch('onPDOCreated', array(&$this->pdo));
            if($this->pdo->errorCode() == 0)
            {
                $this->pdo->exec("SET NAMES utf8");
                $this->pdo->exec("SET sql_mode = \"\"");
                return true;
            }
        }catch(\Exception $ex)
        {
            Events::dispatch('onException', $ex);
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
        if(!GL_INSTALL)
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
//            if($this->getLastError())
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
        if($error[1])
            print_debug($error);
        return $error[2];
    }

    public function getPDO()
    {
        return $this->pdo;
    }

    public function disconnect()
    {
        // TODO: Implement disconnect() method.
        if($this->pdo)
        {
            $this->pdo = null;
        }
    }
}