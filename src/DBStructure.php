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
use TijsVerkoyen\CssToInlineStyles\Exception;

/**
 * Class DBStructure
 *
 * @package GLFramework
 */
class DBStructure
{

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
        $res = $db->exec($action['sql']);
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

        $fields = array();
        $keys = array();
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
                   }

                }
            }
        }
        $result = array();
        $result['table'] = $model->getTableName();
        $result['fields'] = $fields;
        $result['keys'] = $keys;

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
        foreach (Bootstrap::getSingleton()->getModels() as $model) {
            $instance = new $model();
            $md5 .= md5(json_encode($this->getDefinition($instance)));
        }
        return md5($md5);
    }

    /**
     * TODO
     *
     * @return bool
     */
    public function haveModelChanges()
    {
        $filename = new Filesystem('database_structure.md5');

        if ($filename->exists()) {
            $md5 = $this->getCurrentModelDefinitionHash();
            if ($filename->read() === $md5) {
                return false;
            }
        }

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
        $models = Bootstrap::getSingleton()->getModels();
        foreach ($models as $model) {
            if (class_exists($model)) {
                $instance = new $model(null);
                if ($instance instanceof Model) {
                    $diff = $instance->getStructureDifferences();
                    foreach ($diff as $action) {
                        try {
                            $this->runAction($db, $instance, $action);
                        } catch (\Exception $ex) {
//                            Debugbar::getInstance()->exceptionHandler($ex);
                            Log::getInstance()->critical($ex);
//                            return $ex;
                        }
                    }
                }
            }
        }
        $this->setDatabaseUpdate();
    }

    /**
     * TODO
     */
    public function setDatabaseUpdate()
    {
        $filename = new Filesystem("database_structure.md5");
        $md5 = $this->getCurrentModelDefinitionHash();
        $filename->write($md5);
    }

    /**
     * TODO
     *
     * @param string $table
     * @return array
     */
    public function getCurrentStructure($table = '')
    {
        $db = new DatabaseManager();
        $res = $db->select("SHOW TABLES LIKE '$table'");
        $tables = array();
        $result = array();
        foreach ($res as $row) {
            $tables[] = array_pop($row);
        }
        foreach ($tables as $table) {
            $table = $db->escape_string($table);

            $info = $db->select('DESCRIBE `' . $table . '`');
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

            $info = $db->select("SELECT 
  TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
FROM
  INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
  REFERENCED_TABLE_SCHEMA = '?' AND
  REFERENCED_TABLE_NAME = '?'", array($db->getDatabaseName(), $table));

            $keys = array();
            foreach ($info as $row) {
                $key = array();
                $key['field'] = $row['COLUMN_NAME'];
                $key['table'] = $row['REFERENCED_TABLE_NAME'];
                $key['target'] = $row['REFERENCED_COLUMN_NAME'];
                $fields[$key['field']]['index'] = true;
                $keys[] = $key;
            }

            $result[$table] = array(
                'table' => $table,
                'fields' => $fields,
                'keys' => $keys
            );
        }
        return $result;
    }

    /**
     * TODO
     *
     * @param $excepted
     * @param bool $drop
     * @return array
     */
    public function getStructureDifferences($excepted, $drop = false)
    {
        if (isset($excepted['table'])) {
            $excepted = array($excepted['table'] => $excepted);
        }
        $actions = array();
        foreach ($excepted as $table => $value) {
            $current = $this->getCurrentStructure($table);
            if (count($current) > 0) {
                $dbTable = array_shift($current);
                if ($this->getHash($value) !== $this->getHash($dbTable)) {
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
                    $test2 = $value['keys'];
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
                                'action' => 'drop_field'
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



    public function getAddKey($table, $index) {
        $column = $index['field'];
        $targetTable = $index['table'];
        $targetColumn = $index['target'];
        return "ALTER TABLE `$table` ADD FOREIGN KEY (`$column`) REFERENCES `$targetTable`(`$targetColumn`) ON DELETE CASCADE ON UPDATE CASCADE;";
    }

    public function getDropKey($table, $index) {
        return "ALTER TABLE `$table` DROP INDEX `{$index['field']}`";
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
            $fields = array($table);
        }
        $list = array_map(function($a) { return implode(' - ' , $a); }, $fields);
        ksort($list);
        return sha1(strtolower(implode('-', array_keys($list)) . implode('-', $list)));
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
}
