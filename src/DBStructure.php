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
 * Date: 18/1/16
 * Time: 9:33
 */

namespace GLFramework;

use GLFramework\Modules\Debugbar\Debugbar;
use GLFramework\Utils\Profiler;
use TijsVerkoyen\CssToInlineStyles\Exception;

/**
 * Class DBStructure
 *
 * @package GLFramework
 */
class DBStructure
{

    private $hashFile = "database_structure.md5";
    private $tables = [];

    /**
     * @param string $hashFile
     */
    public function setHashFile(string $hashFile): void
    {
        $this->hashFile = $hashFile;
    }

    /**
     * @return string
     */
    public function getHashFile(): string
    {
        return $this->hashFile;
    }

    /**
     * TODO
     *
     * @param $db DatabaseManager
     * @param $model
     * @param $action
     * @return mixed
     */
    public static function runAction($db, $model, $action)
    {
        $res = $db->getConnection()->select($action['sql'], [], false);
        if ($action['action'] === 'create_table') {
            $model->onCreate();
        }
        return $res;
    }

    /**
     * TODO
     *
     * @param $model Model
     * @return array
     */
    public function getDefinition($model)
    {
        if(!($model instanceof Model)) return false;

        $fields = array();
        $keys = array();
        $unique = array();
        $definition = $model->getDefinition();
        if (isset($definition['fields'])) {
            $definitionFields = $definition['fields'];
            if (is_array($definition['index'])) {
                $definitionFields = array($definition['index']['field'] => $definition['index']) + $definitionFields;
                $definitionFields[$definition['index']['field']]['primary'] = true;
            } else {
                $definitionFields = array(
                        $definition['index'] => array(
                            'type' => 'int(11)',
                            'autoincrement' => true
                        )
                    ) + $definitionFields;
            }
            foreach ($definitionFields as $field => $props) {
                if (is_array($props)) {
                    $fields[$field] = array(
                        'field' => $field,
                    );

                    if (isset($props['type'])) {
                        $fields[$field]['type'] = $props['type'];
                    }
                    if (isset($props['default'])) {
                        $fields[$field]['default'] = $props['default'];
                    } else {
                        $fields[$field]['default'] = '';
                    }
                    if (isset($props['autoincrement'])) {
                        $fields[$field]['autoincrement'] = $props['autoincrement'];
                    }
                    if (isset($props['primary'])) {
                        $fields[$field]['primary'] = $props['primary'];
                    }
                } else {
                    $fields[$field] = array(
                        'field' => $field,
                        'type' => $props,
                        'default' => '',
                        //                'null' => 1
                    );
                }
            }
        }
        if (isset($definition['keys'])) {
            foreach ($definition['keys'] as $field => $value) {
                if (isset($fields[$field])) {
                   $model1 = key($value); // Para extrar datos del tipo key => value
                   $column = current($value);
                   $modelObj = Model::newInstance($model1);
                   if ($modelObj and $modelObj instanceof Model) {
                       $keys[] = array(
                           'field' => $field,
                           'table' => $modelObj->getTableName(),
                           'target' => $column

                       );
//                       $fields[$field]['index'] = true;
                   }

                }
            }
        }

        if (isset($definition['unique'])) {
            foreach ($definition['unique'] as $field) {
                $unique[] = array('field' => $field);
            }
        }

        $result = array();
        $result['table'] = $model->getTableName();
        $result['fields'] = $fields;
        $result['keys'] = $keys;
        $result['unique'] = $unique;
//        $result['engine'] = $model->getEngine();

        return $result;
    }

    /**
     * TODO
     *
     * @return string
     */
    public function getCurrentModelDefinitionHash()
    {
        $md5 = '';
        $bs = Bootstrap::getSingleton();
        $config = $bs->getConfig();
        foreach ($bs->getModels(true) as $model) {
            Profiler::start('getCurrentModelDefinitionHash::'.$model, 'getCurrentModelDefinitionHash');
            try {
                Profiler::start('newInstance::'.$model, 'newInstance');
                $instance = $this->getModelHash($model);
//                $instance = new $model();
                Profiler::stop('newInstance::'.$model);
                Profiler::start('getDefinition::'.$model, 'getDefinition');
                $md5 .= (json_encode(($instance))) . "\n";
                Profiler::stop('getDefinition::'.$model);
            } catch (\ArgumentCountError $exception) {
                Log::d($exception);
            }
            Profiler::stop('getCurrentModelDefinitionHash::'.$model);

        }
        if (isset($config['database'])) {
            $md5 .= json_encode($config['database']);
        }
        return md5($md5 . Bootstrap::$VERSION);

        return $md5;
    }

    private function getModelHash($file) {
        $stat = stat($file);
        return $stat['mtime'] . '-' . $stat['size'];
    }

    /**
     * TODO
     *
     * @return bool
     */
    public function haveModelChanges()
    {
        $filename = new Filesystem($this->hashFile);

        Profiler::start('haveModelChanges');
        if ($filename->exists()) {
            $md5 = $this->getCurrentModelDefinitionHash();
            if ($filename->read() === $md5) {
                Profiler::stop('haveModelChanges');
                return false;
            }
        }

        Profiler::stop('haveModelChanges');
        return true;
    }

    /**
     * TODO
     *
     * @param $db
     * @return \Exception
     */
    public function executeModelChanges($db)
    {
        $count = 0;
        $models = Bootstrap::getSingleton()->getModels();
        $instanceModels = [];
        foreach ($models as $model) {
            if (class_exists($model)) {
                $instance = new $model(null);
                $instance->db = $db;
                if ($instance instanceof Model) {
                    $instanceModels[] = $instance;
                }
            }
        }
        $this->checkForDuplicatedTables($instanceModels);
        foreach ($instanceModels as $model) {
            $this->executeModel($db, $model);
        }
        $this->setDatabaseUpdate();
        return $count;
    }
    
    
    private function checkForDuplicatedTables($models) {

        $tables = [];
        foreach ($models as $model) {
            $class = get_class($model);
            if(!isset($tables[$class])) {
                $tables[$class] = $model->getTableName();
            }

            foreach ($tables as $class1 => $tname) {
                if($tname == $model->getTableName() && $class1 != $class) {
//                    throw new \Exception("Table Name for model $class is in use by $class1. Please ensure that you dont use same tablename for multiple models.");
                }
            }
        }
    }

    /**
     * @param $db
     * @param $model Model
     */
    public function executeModel($db, $model) {
        $count = 0;
        $diff = $model->getStructureDifferences($db);
        foreach ($diff as $action) {
            try {
//                Log::d('Model: ' . $model->getTableName() . " " . $action);
                $this->runAction($db, $model, $action);
                $count++;
            } catch (\Exception $ex) {
//                            Debugbar::getInstance()->exceptionHandler($ex);
                Log::getInstance()->critical($ex);
//                            return $ex;
            }
        }
    }

    /**
     * TODO
     */
    public function setDatabaseUpdate()
    {
        $filename = new Filesystem($this->hashFile);
        $md5 = $this->getCurrentModelDefinitionHash();
        $filename->write($md5);
    }

    /**
     * TODO
     *
     * @param $db DatabaseManager
     * @param string $table
     * @return array
     */
    public function getCurrentStructure($db, $table = '')
    {
        $res = $db->getConnection()->select("SHOW TABLES LIKE '$table'");
        $tables = array();
        $result = array();
        foreach ($res as $row) {
            $tables[] = array_pop($row);
        }
        foreach ($tables as $table) {
            $table = $db->escape_string($table);

            $info = $db->getConnection()->select('DESCRIBE `' . $table . '`');
            $fields = array();
            foreach ($info as $row) {
                $field = array();
                $field['field'] = $row['Field'];
                $field['type'] = $row['Type'];
                $field['default'] = $row['Default'];
                if ($row['Extra'] === 'auto_increment') {
                    $field['autoincrement'] = true;
                } elseif ($row['Key'] === 'PRI') {
                    $field['primary'] = true;
                }
                $fields[$field['field']] = $field;
            }

            $info = $db->getConnection()->select("SELECT 
  TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
FROM
  INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
  TABLE_SCHEMA = ? AND
  TABLE_NAME = ?", array($db->getDatabaseName(), $table));

            $keys = array();
            $unique = array();
            foreach ($info as $row) {
                if ($row['REFERENCED_TABLE_NAME']) {
                    $key = array();
                    $key['field'] = $row['COLUMN_NAME'];
                    $key['table'] = $row['REFERENCED_TABLE_NAME'];
                    $key['target'] = $row['REFERENCED_COLUMN_NAME'];
//                    $fields[$key['field']]['index'] = true;
                    $keys[] = $key;
                } elseif ($row['CONSTRAINT_NAME'] != 'PRIMARY') {
                    $unique[] = $row['COLUMN_NAME'];
                }
            }

            $info2 = $db->getConnection()->select("SHOW TABLE STATUS WHERE Name = ?;", array ($table));
            if ($info2 && count($info2) > 0) {
                $info2 = $info2[0];
            }

            $engine = $info2['Engine'];
            $result[$table] = array(
                'table' => $table,
                'fields' => $fields,
                'keys' => $keys,
                'unique' => $unique,
//                'engine' => $engine,
            );
        }
        return $result;
    }

    /**
     * TODO
     *
     * @param $db DatabaseManager
     * @param $excepted
     * @param bool $drop
     * @return array
     */
    public function getStructureDifferences($db, $excepted, $drop = false)
    {
        if (isset($excepted['table'])) {
            $excepted = array($excepted['table'] => $excepted);
        }
        $actions = array();
        foreach ($excepted as $table => $value) {
            $current = $this->getCurrentStructure($db, $table);
            if (count($current) > 0) {
                $dbTable = array_shift($current);
                if ($this->getHash($value) != $this->getHash($dbTable)) {

//                    if(strtolower($value['engine']) != strtolower($dbTable['engine'])) {
//                        $actions[] = array(
//                            'sql' => $this->getChangeEngine($table, $value['engine']),
//                            'action' => 'change_engine'
//                        );
//                    }

                    $subject1 = array($value['fields']);
                    $subject2 = array($dbTable['fields']);

                    foreach ($subject1 as $index => $test1) {
                        $test2 = $subject2[$index];
                        // Search for add and changes
                        foreach ($test1 as $key => $item) {
                            if (isset($test2[$key])) {
                                $item2 = $test2[$key];
                                // possible changes
                                if ($this->getHash($item) !== $this->getHash($item2)) {
                                    $actions[] = array(
                                        'sql' => $this->getAlterChange($table, $item),
                                        'action' => 'alter_field'
                                    );
                                }
                            } else {
                                // create the new instance
                                $actions[] = array('sql' => $this->getAlterAdd($table, $item), 'action' => 'add_field');
                            }
                        }
                        //Search for deletion
                        foreach ($test2 as $key => $item) {
                            if (!isset($test1[$key]) && $drop) {
                                $actions[] = array(
                                    'sql' => $this->getAlterDrop($table, $item, $key),
                                    'action' => 'drop_field'
                                );
                            }
                        }
                    }

                    $test1 = $value['keys'];
                    $test2 = $dbTable['keys'];
                    foreach ($test1 as $key => $item) {
                        if (!$this->haveKey($item, $test2)) {
                            $actions[] = array('sql' => $this->getAddKey($table, $item), 'action' => 'add_key');
                        }
                    }
                    //Search for deletion
                    foreach ($test2 as $key => $item) {
                        if (!$this->haveKey($item, $test1)) {
                            $actions[] = array(
                                'sql' => $this->getDropKey($table, $item),
                                'action' => 'drop_key'
                            );
                        }
                    }

                    $test1 = $value['unique'];
                    $test2 = $dbTable['unique'];
                    foreach ($test1 as $key => $item) {
                        if (!$this->haveUnique($item, $test2)) {
                            $actions[] = array('sql' => $this->getAddKey($table, $item), 'action' => 'add_unique');
                        }
                    }
                    //Search for deletion
                    foreach ($test2 as $key => $item) {
                        if (!$this->haveKey($item, $test1)) {
                            $actions[] = array(
                                'sql' => $this->getDropKey($table, $item),
                                'action' => 'drop_unique'
                            );
                        }
                    }

                }
            } else {
                //                if(!$value instanceof \stdClass) die_stack_trace();
                $actions[] = array('sql' => $this->getCreateTable($value), 'action' => 'create_table');
            }
        }
        if ($current) {
            foreach ($current as $table => $value) {
                if (!isset($excepted[$table]) && $drop) {
                    $actions[] = array('sql' => $this->getDropTable($value), 'action' => 'drop_table');
                }
            }
        }
        return $actions;
    }

    /**
     * TODO
     *
     * @param $field
     * @return bool|int|string
     */
    public function getLength($field)
    {
        $type = $field['type'];
        if (($i = strpos($type, '(')) !== false) {
            $j = strpos($type, ')', $i);

            return substr($type, $i + 1, $j - $i - 1);
        }
        return 0;
    }

    private function haveKey($needle, $haystack) {
        foreach ($haystack as $value) {
            if($value['field'] == $needle['field'] &&
                $value['table'] == $needle['table'] &&
                $value['target'] == $needle['target']

            ) {
                return true;
            }
        }
        return false;
    }
    private function haveUnique($needle, $haystack) {
        foreach ($haystack as $value) {
            if($value == $needle) {
                return true;
            }
        }
        return false;
    }



    public function getAddKey($table, $index) {
        $column = $index['field'];
        $targetTable = $index['table'];
        $targetColumn = $index['target'];
        return "ALTER TABLE `$table` ADD FOREIGN KEY (`$column`) REFERENCES `$targetTable`(`$targetColumn`) ON DELETE CASCADE ON UPDATE CASCADE;";
    }


    public function getDropKey($table, $index) {
        return "ALTER TABLE `$table` DROP INDEX `{$index}`";
    }

    public function getAddUnique($table, $field)
    {
        return "ALTER TABLE `$table` ADD CONSTRAINT UNIQUE (`$field`);";
    }

    public function getDropUnique($table, $field) {
        return "ALTER TABLE `$table` ADD CONSTRAINT UNIQUE (`$field`);";
    }
    /**
     * TODO
     *
     * @param $table
     * @param $field
     * @return string
     */
    public function getAlterChange($table, $field)
    {
        $table = $this->validTableName($table);

        $name = $field['field'];
        $type = $field['type'];
        if (isset($field['autoincrement']) && $field['autoincrement']) {
            $type .= ' AUTO_INCREMENT';
        }
        if (isset($field['default']) && $field['default'] !== null && $field['default'] != '') {
            $type .= ' DEFAULT \'' . $field['default'] . '\'';
        }

        return 'ALTER TABLE ' . $table . ' CHANGE `' . $name . '` `' . $name . '` ' . $type;
    }

    /**
     * TODO
     *
     * @param $table
     * @param $field
     * @return string
     */
    public function getAlterAdd($table, $field)
    {
        $table = $this->validTableName($table);
        $name = $field['field'];
        $type = $field['type'];
        if (isset($field['default']) && $field['default'] !== '') {
            $type .= " DEFAULT '" . $field['default'] . "'";
        }
        return 'ALTER TABLE ' . $table . ' ADD `' . $name . '` ' . $type;
    }

    /**
     * TODO
     *
     * @param $table
     * @param $field
     * @param null $name
     * @return string
     */
    public function getAlterDrop($table, $field, $name = null)
    {
        $table = $this->validTableName($table);
        if ($name === null) {
            $name = $field['field'];
        }
        return "ALTER TABLE $table DROP COLUMN `{$name}`";
    }

    /**
     * TODO
     *
     * @param $table
     * @return string
     */
    public function getHash($table)
    {
        $fields = array();
        if (isset($table['fields'])) {
            $fields = $table['fields'];
        }
        if (isset($table['field'])) {
            $fields = array($table);
        }
        if (isset($table['keys'])) {
            $fields += $table['keys'];
        }
        $list = array_map(array($this, 'encode_hash'), $fields);
        ksort($list);
        return sha1(strtolower(implode('-', array_keys($list)) . implode('-', $list)));
    }
    private function encode_hash($a) {
        return implode(' - ', $a);
    }

    /**
     * TODO
     *
     * @param $table
     * @return string
     */
    public function getCreateTable($table)
    {
        $tableName = $this->validTableName($table['table']);
        $sql = '';
        foreach ($table['fields'] as $field => $value) {
            $sql .= ($sql === '') ? '' : ', ';
            $sql .= '`' . $field . '` ' . $value['type'];
            if (isset($value['autoincrement']) && $value['autoincrement']) {
                $sql .= ' AUTO_INCREMENT PRIMARY KEY';
            }
            if (isset($value['primary']) && $value['primary']) {
                $sql .= ' PRIMARY KEY';
            }
        }

        return 'CREATE TABLE ' . $tableName . '(' . $sql . ')';
    }

    /**
     * TODO
     *
     * @param $table
     * @return string
     */
    public function getDropTable($table)
    {
        return 'DROP TABLE ' . $table['table'];
    }

    /**
     * TODO
     *
     * @param $table
     * @return mixed
     */
    public function validTableName($table)
    {
        return $table;
    }

    private function getChangeEngine($table, $engine)
    {
        return "ALTER TABLE `$table` ENGINE = $engine;";
    }
}
