<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 16/03/16
 * Time: 11:39
 */

namespace GLFramework\Tests;


use GLFramework\Database\MySQLConnection;
use GLFramework\DatabaseManager;

class DatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{

    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $db = new DatabaseManager();
        $config = $db->getConfig();
        return $this->createDefaultDBConnection($db->getConnection()->getPDO(), $config['database']['database']);
    }

    public function fieldEqual($model, $name, $value)
    {
        $this->assertEquals($model->{$name}, $value);
    }
}