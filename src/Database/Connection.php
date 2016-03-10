<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 24/02/16
 * Time: 10:35
 */
namespace GLFramework\Database;

abstract class Connection
{
    abstract public function connect($hostname, $username, $password);
    abstract public function select_database($database);
    abstract public function escape_string($value);
    abstract public function select($query, $returnArray = true);
    abstract public function getLastInsertId();
    abstract public function getLastError();

}