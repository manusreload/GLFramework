<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 20:39
 */

namespace GLFramework;


class Model
{
    /**
     * @var DatabaseManager
     */
    var $db;
    protected $order = "";
    protected $table_name = "";
    protected $definition = array();
    /*
     * Reglas para sacar variables (desde array definicion):
     *  "'([a-z_]+)' => .*" -> "var \$$1;"
     *  - Desde DESCRIBE...
     * "([a-z_]+)\t.*" -> "var \$$1;"
     */

    /*
     * Reglas para sacar definicion (desde DESCRIBE <table>):
     *  "([a-z_]+)\t([a-z0-9(),]+).*" -> "'$1' => '$2',"
     */

    protected $hidden = array();
    protected $models = array();

    /**
     * Model constructor.
     */
    public function __construct($data = null)
    {
        $this->db = new DatabaseManager();
        $this->setData($data);
    }

    public function insert($data = null)
    {
        $fields = $this->getFields();
        $sql1 = "";
        $sql2 = "";
        foreach ($fields as $field) {
//            if($this->getFieldValue($field, $data) !== NULL)
            {
                $value = $this->db->escape_string($this->getFieldValue($field, $data));
                $sql1 .= "$field, ";
                $sql2 .= "'$value', ";
            }
        }
        if (!empty($sql1)) {
            $sql1 = substr($sql1, 0, -2);
            $sql2 = substr($sql2, 0, -2);

            return $this->db->insert("INSERT INTO {$this->table_name} ($sql1) VALUES ($sql2)");
        }
        return false;
    }

    public function update($data = null)
    {
        $fields = $this->getFields();
        $sql1 = "";
        foreach ($fields as $field) {
            $value = $this->getFieldValue($field, $data);
            if (isset($value) && $value !== '' && !$this->isIndex($field)) {
                $value = $this->db->escape_string($value);
                $sql1 .= "$field = '$value', ";
            }
        }
        if (!empty($sql1)) {
            $sql1 = substr($sql1, 0, -2);
            $index = $this->getIndex();
            $indexValue = $this->db->escape_string($this->getFieldValue($index, $data));
            if (!$indexValue) return false;
            return $this->db->exec("UPDATE {$this->table_name} SET $sql1 WHERE $index = '$indexValue'", $this->getCacheId($indexValue));
        }
        return false;
    }

    public function delete()
    {
        $index = $this->getIndex();
        $value = $this->getFieldValue($index);
        if ($value) {
            return $this->db->exec("DELETE FROM {$this->table_name} WHERE $index = '$value'", $this->getCacheId($value));
        }
        return false;
    }

    public function getCacheId($id)
    {
        return $this->table_name . "_" . $id;
    }
    /**
     * @param $id
     * @return ModelResult
     */
    public function get($id)
    {
        if (!is_array($id)) {

            $index = $this->getIndex();
            $id = $this->db->escape_string($id);
            return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE $index = '$id' ", $this->getCacheId($id)));
        } else if (is_array($id)) {
            $fieldsValue = $id;
            $fields = $this->getFields();
            $sql = "";
            foreach ($fields as $field) {
                if (isset($fieldsValue[$field])) {
                    $value = $fieldsValue[$field];
                    $sql .= $field . "= '" . $this->db->escape_string($value) . "' AND ";
                }
            }
            if (!empty($sql)) {
                $sql = substr($sql, 0, -5);
                return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE $sql"));
            }
        }
        return $this->build(array());
    }

    public function get_or($fields)
    {
        $fieldsValue = $fields;
        $fields = $this->getFields();
        $sql = "";
        foreach ($fields as $field) {
            if (isset($fieldsValue[$field])) {
                $value = $fieldsValue[$field];
                if(is_array($value))
                {
                    foreach($value as $subvalue)
                    {
                        $sql .= $field . "= '" . $this->db->escape_string($subvalue) . "' OR ";
                    }
                }
                else
                {
                    $sql .= $field . "= '" . $this->db->escape_string($value) . "' OR ";
                }
            }
        }
        if (!empty($sql)) {
            $sql = substr($sql, 0, -4);
            return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE $sql"));
        }
        return $this->build(array());
    }

    /**
     * @return ModelResult
     */
    public function get_all()
    {
        return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE 1"));
    }

    public function get_others()
    {
        $index = $this->getIndex();
        $value = $this->getFieldValue($index);
        if ($value)
            return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE $index != '$value'"));
        return $this->get_all();

    }

    public function search($fields)
    {
        if(!is_array($fields)) $fields = array($this->getIndex() => $fields);

        $fieldsValue = $fields;
        $fields = $this->getFields();
        $sql = "";
        foreach ($fields as $field) {
            if (isset($fieldsValue[$field])) {
                $value = $fieldsValue[$field];
                if($this->isString($field))
                    $sql .= $field . " LIKE '%" . $this->db->escape_string($value) . "%' OR ";
                else
                    $sql .= $field . " = '" . $this->db->escape_string($value) . "' OR ";

            }
        }
        if (!empty($sql)) {
            $sql = substr($sql, 0, -4);
            return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE $sql"));
        }
        return $this->build(array());
    }


    public function save($updateIndex = false)
    {
        if($this->exists())
        {
            return $this->update();
        }
        $result = $this->insert();
        if($result && $updateIndex)
            $this->setToIndex($result);

        return $result;
    }

    public function exists()
    {
        $indexValue = $this->getFieldValue($this->getIndex());
        if ($indexValue) {
            return $this->get($indexValue)->count() > 0;
        }
        return false;
    }

    public function getFieldValue($field, $data = null)
    {
        if ($data != null && isset($data[$field])) {
            return $data[$field];
        }
        if (isset($this->{$field})) {
            return $this->{$field};
        }
        return null;
    }

    public function getFields()
    {
        $fields = array();
        if (isset($this->definition['index'])) {
            $fields[] = $this->getIndex();
        }
        if (isset($this->definition['fields'])) {
            foreach ($this->definition['fields'] as $key => $value) {
                if (!is_numeric($key))
                    $fields[] = $key;
                else
                    $fields[] = $value;
            }
        }
        return $fields;
    }

    public function getIndex()
    {
        if (isset($this->definition['index'])) {
            if (is_array($this->definition['index']))
                return $this->definition['index']['field'];
            return $this->definition['index'];
        }
        return null;
    }
    public function setToIndex($value)
    {
        $field = $this->getIndex();
        if($field)
            $this->{$field} = $value;
    }

    public function isIndex($field)
    {
        return $this->getIndex() == $field;
    }

    /**
     * @param $result
     * @return ModelResult
     */
    public function build($result)
    {
        $modelResult = new ModelResult($this);
        $modelResult->models = $result;
        if (count($result) > 0) {
            $modelResult->model = $result[0];
        }
        if($this->order != "") $modelResult->order($this->order);
        return $modelResult;
    }

    public function setDataFromArray($data, $prefix = "", $suffix = "")
    {

    }
    public function setData($data, $allowEmpty = true)
    {
        if ($data != null) {
            if(is_array($data))
            {
                $fileds = $this->getFields();
                foreach ($fileds as $field) {
                    if (isset($data[$field]) && ($allowEmpty || $data[$field] !== '')) {
                        if(strpos($this->getFieldType($field), "varchar") !== FALSE ||
                            $this->getFieldType($field) == "text")
                        {

                            $encoding = mb_detect_encoding($data[$field]);
                            if($encoding != 'utf8')
                                $this->{$field} = mb_convert_encoding($data[$field], 'utf8', $encoding);
                            else
                                $this->{$field} = $data[$field];
                        }
                        else
                        {

                            $this->{$field} = $data[$field];
                        }
                    }
                }
            }
            else
            {
                $this->setData($this->get($data)->model);
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }
    /**
     * @return array
     */
    public function getFieldDefinition($field)
    {
        if(isset($this->definition[$field]))
            return $this->definition[$field];
        if(isset($this->definition['fields'][$field]))
            return $this->definition['fields'][$field];
        return null;
    }
    public function getFieldType($field)
    {
        $definition = $this->getFieldDefinition($field);
        if($definition)
        {
            return $definition['type'];
        }
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    public function getStructureDifferences()
    {
        $diff = new DBStructure();
        $excepted = $diff->getDefinition($this);
        return $diff->getStructureDifferences($excepted);
    }

    public function json($fields = array())
    {
        $json = array();
        if(empty($fields)) $fields = $this->getFields();
        foreach($fields as $field)
        {
            if(!in_array($field, $this->hidden))
            {
                if(isset($this->models[$field]))
                {
                    $modelTransform = $this->models[$field];

                    if(is_array($modelTransform))
                    {
                        $name = $modelTransform['name'];
                        $model = $modelTransform['model'];
                        $json[$name] = new $model($this->getFieldValue($field));
                    }
                    else
                    {
                        $name = $this->underescapeName($modelTransform);
                        $json[$name] = new $modelTransform($this->getFieldValue($field));
                    }
                }
                else
                {
                    $json[$field] = $this->getFieldValue($field);
                }
            }
        }
        return $json;
    }

    public function underescapeName($name)
    {
        if(preg_match_all("#([A-Z][a-z]*)#", $name, $matches))
        {
            return implode("_", array_map('strtolower', $matches[1]));
        }
    }
    public function isString($field)
    {
        $def = strtolower( $this->getFieldDefinition($field) );
        if(strpos($def, "varchar") !== FALSE) return true;
        if(strpos($def, "text") !== FALSE) return true;
        return false;
    }


}