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
     * @var DBConnection
     */
    var $db;
    protected $table_name = "";
    protected $definition = array();
    /*
     * Reglas para sacar variables (desde array definicion):
     *  "'([a-z_]+)' => .*" -> "var \$$1;"
     *  - Desde DESCRIBE...
     * "([a-z_]+)\t.*" -> "var \$$1;"
     */

    /*
     * Reglas para sacar definicion (desde DESCRIBE TABLE <table>):
     *  "([a-z_]+)\t([a-z0-9()]+).*" -> "'$1' => '$2',"
     */

    /**
     * Model constructor.
     */
    public function __construct($data = null)
    {
        $this->db = new DBConnection();
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
            if (!empty($value) && !$this->isIndex($field)) {
                $value = $this->db->escape_string($value);
                $sql1 .= "$field = '$value', ";
            }
        }
        if (!empty($sql1)) {
            $sql1 = substr($sql1, 0, -2);
            $index = $this->getIndex();
            $indexValue = $this->db->escape_string($this->getFieldValue($index, $data));
            if (!$indexValue) return false;
            return $this->db->exec("UPDATE {$this->table_name} SET $sql1 WHERE $index = '$indexValue'");
        }
        return false;
    }

    public function delete()
    {
        $index = $this->getIndex();
        $value = $this->getFieldValue($index);
        if ($value) {
            return $this->db->exec("DELETE FROM {$this->table_name} WHERE $index = '$value'");
        }
        return false;
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
            return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE $index = '$id' "));
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
        return $modelResult;
    }

    public function setData($data)
    {
        if ($data != null) {
            $fileds = $this->getFields();
            foreach ($fileds as $filed) {
                if (isset($data[$filed])) {
                    $this->{$filed} = $data[$filed];
                }
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


}