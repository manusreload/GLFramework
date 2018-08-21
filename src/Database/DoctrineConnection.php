<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 23/07/18
 * Time: 10:15
 */

namespace GLFramework\Database;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use GLFramework\Bootstrap;

class DoctrineConnection extends MySQLConnection
{

    private static $em;
    public function connect($hostname, $username, $password)
    {
        if(parent::connect($hostname, $username, $password)) {
            $connection['driver'] = 'pdo_mysql';
            $connection['pdo']    = $this->getPDO();
            $config = Setup::createConfiguration(Bootstrap::isDebug());
            $entities = Bootstrap::getSingleton()->getModelsFolder();

            $driverImpl = $config->newDefaultAnnotationDriver($entities);
            $config->setMetadataDriverImpl($driverImpl);
            $em = EntityManager::create($connection, $config);
            self::$em = $em;

            return true;
        }
        return false;
    }

    /**
     * @return EntityManager
     */
    public static function getEntityManager() {
        return self::$em;
    }

}