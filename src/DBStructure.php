<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 18/1/16
 * Time: 9:33
 */

namespace GLFramework;


use TijsVerkoyen\CssToInlineStyles\Exception;

class DBStructure
{

    /**
     * @param $model Model
     * @return array
     */
    public function getDefinition($model)
    {

        $fields = array();
        $definition = $model->getDefinition();
        if(isset($definition['fields']))
        {

            $definitionFields = $definition['fields'];
            if(is_array($definition['index']))
            {
                $definitionFields = array( $definition['index']['field'] => $definition['index']) + $definitionFields;
            }
            else{
                $definitionFields = array( $definition['index']  => array('type' => "int(11)", 'autoincrement' => true)) + $definitionFields;
            }
            foreach($definitionFields as $field => $props)
            {
                if(is_array($props))
                {

                    $fields[$field] = array(
                        'field' => $field,
                    );

                    if(isset($props['type']))
                        $fields[$field]['type'] = $props['type'];
                    if(isset($props['default']))
                        $fields[$field]['default'] = $props['default'];
                    else
                        $fields[$field]['default'] = "";
                    if(isset($props['autoincrement']))
                        $fields[$field]['autoincrement'] = $props['autoincrement'];

                }
                else{
                    $fields[$field] = array(
                        'field' => $field,
                        'type' => $props,
                        'default' => "",
    //                'null' => 1
                    );
                }
            }
        }
        $result = array();
        $result['table'] = $model->getTableName();
        $result['fields'] = $fields;

        return $result;
    }

    public function getCurrentModelDefinitionHash()
    {
        $md5 = "";
        foreach(Bootstrap::getSingleton()->getModels() as $model)
        {
            $instance = new $model();
            $md5 .= md5(json_encode($this->getDefinition($instance)));
        }
        return md5($md5);
    }
    public function haveModelChanges()
    {
        $filename = new Filesystem("database_structure.md5");

        if($filename->exists())
        {
            $md5 = $this->getCurrentModelDefinitionHash();
            if($filename->read() == $md5) return false;
        }

        return true;

    }
    public function executeModelChanges($db)
    {
        $models = Bootstrap::getSingleton()->getModels();
        foreach ($models as $model) {
            $instance = new $model(null);
            if ($instance instanceof Model) {
                $diff = $instance->getStructureDifferences();
                foreach ($diff as $action) {
                    try{
                        $db->exec($action['sql']);

                    }catch (\Exception $ex)
                    {

                    }
                }
            }
        }
        $this->setDatabaseUpdate();
    }
    
    public function setDatabaseUpdate()
    {
        $filename = new Filesystem("database_structure.md5");
        $md5 = $this->getCurrentModelDefinitionHash();
        $filename->write($md5);
    }

    public function getCurrentStructure($table = "")
    {
        $db = new DatabaseManager();
        $res = $db->select("SHOW TABLES LIKE '$table'");
        $tables = array();
        $result = array();
        foreach($res as $row)
        {
            $tables[] = array_pop($row);
        }
        foreach($tables as $table)
        {
            $table = $db->escape_string($table);

            $info = $db->select("DESCRIBE `" . $table . "`");
            $fields = array();
            foreach ($info as $row) {
                $field = array();
                $field['field'] = ( $row['Field'] );
                $field['type'] = $row['Type'];
                $field['default'] = $row['Default'];
                if($row['Extra'] == 'auto_increment')
                {
                    $field['autoincrement'] = true;
                }
                $fields[ $field['field'] ] = $field;
            }
            $result[$table] = array(
                'table' => $table,
                'fields' => $fields
            );
        }
        return $result;
    }


    /**
     * @param $excepted
     * @param bool $drop
     * @return array
     */
    public function getStructureDifferences($excepted, $drop = false)
    {
        if(isset($excepted['table']))
        {
            $excepted = array($excepted['table'] => $excepted);
        }
        $actions = array();
        foreach($excepted as $table => $value)
        {
            $current = $this->getCurrentStructure($table);
            if(count($current) > 0)
            {
                $dbTable =  array_shift($current);
                if($this->getHash($value) != $this->getHash($dbTable))
                {
                    $subject1 = array($value['fields']);
                    $subject2 = array($dbTable['fields']);

                    foreach($subject1 as $index => $test1 )
                    {
                        $test2 = $subject2[$index];
                        // Search for add and changes
                        foreach($test1 as $key => $item)
                        {
                            if(isset($test2[$key]))
                            {
                                $item2 = $test2[$key];
                                // possible changes
                                if($this->getHash($item) != $this->getHash($item2) )
                                {
                                    $actions[] = array("sql" => $this->getAlterChange($table, $item), "action" => "alter_field");
                                }
                            }
                            else
                            {
                                // create the new instance
                                $actions[] = array("sql" => $this->getAlterAdd($table, $item), "action" => "add_field");
                            }
                        }
                        //Search for deletion
                        foreach ($test2 as $key => $item) {
                            if(!isset($test1[$key]))
                            {
                                if($drop)
                                {
                                    $actions[] = array("sql" => $this->getAlterDrop($table, $item, $key), "action" => "drop_field");
                                }
                            }
                        }
                    }
                }
            }
            else
            {
//                if(!$value instanceof \stdClass) die_stack_trace();
                $actions[] = array("sql" => $this->getCreateTable($value), "action" => "create_table");
            }
        }
        if($current)
        {

            foreach ($current as $table => $value) {
                if(!isset($excepted[$table]))
                {
                    if($drop)
                    {
                        $actions[] = array("sql" => $this->getDropTable($value), "action" => "drop_table");
                    }
                }
            }
        }
        return $actions;
    }

    public function getLength($field)
    {
        $type = $field['type'];
        if(($i = strpos($type, "(")) !== FALSE)
        {
            $j = strpos($type, ")", $i);

            return ( substr($type, $i + 1, $j - $i - 1 ));
        }
        return 0;
    }

    public function getAlterChange($table, $field)
    {
        $table = $this->validTableName($table);

        $name = $field['field'];
        $type = $field['type'];
        if(isset($field['autoincrement']) && $field['autoincrement'])
        {
            $type .= " AUTO_INCREMENT";
        }

        return "ALTER TABLE $table CHANGE {$name} {$name} {$type}";
    }

    public function getAlterAdd($table, $field)
    {
        $table = $this->validTableName($table);
        $name = $field['field'];
        $type = $field['type'];
        if(isset($field['default']) && $field['default'] != "")
        {
            $type .= " DEFAULT '" . $field['default']. "'";
        }
        return "ALTER TABLE $table ADD {$name} {$type}";
    }

    public function getAlterDrop($table, $field, $name = null)
    {
        $table = $this->validTableName($table);
        if($name == null)
        {
            $name = $field['field'];
        }
        return "ALTER TABLE $table DROP COLUMN {$name}";
    }

    public function getHash($table)
    {
        $fields = array();
        if(isset($table['fields']))
        {
            $fields = $table['fields'];
        }
        if(isset($table['field']))
        {
            $fields = array($table);
        }
        $fun = create_function('$a', 'return implode("-", $a);');
        $list = array_map($fun, $fields);
        ksort($list);
        return sha1(strtolower(implode("-", array_keys($list)) . implode("-", $list)));
    }


    public function getCreateTable($table)
    {
        $tableName = $this->validTableName($table['table']);
        $sql = "";
        foreach ($table['fields'] as $field => $value) {
            $sql .= ($sql == "")?"":", ";
            $sql .= $field . " " . $value['type'];
            if(isset($value['autoincrement']) && $value['autoincrement'])
            {
                $sql .= " AUTO_INCREMENT PRIMARY KEY";
            }
        }

        return "CREATE TABLE " . $tableName . "($sql)";
    }


    public function getDropTable($table)
    {
        return "DROP TABLE {$table['table']}";
    }

    public function validTableName($table)
    {
        return $table;
    }
}