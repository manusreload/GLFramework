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
        foreach($fields as $field)
        {
            $value = $this->db->escape_string($this->getFieldValue($field, $data));
            $sql1 .= "$field, ";
            $sql2 .= "'$value', ";
        }
        if(!empty($sql1))
        {
            $sql1 = substr($sql1, 0, -2);
            $sql2 = substr($sql2, 0, -2);

            return $this->db->exec("INSERT INTO {$this->table_name} ($sql1) VALUES ($sql2)");
        }
        return false;
    }

    public function update($data = null)
    {
        $fields = $this->getFields();
        $sql1 = "";
        foreach($fields as $field)
        {
            $value = $this->getFieldValue($field, $data);
            if(!empty($value) && !$this->isIndex($field))
            {
                $value = $this->db->escape_string($value);
                $sql1 .= "$field = '$value', ";
            }
        }
        if(!empty($sql1))
        {
            $sql1 = substr($sql1, 0, -2);
            $index = $this->getIndex();
            $indexValue = $this->db->escape_string($this->getFieldValue($index, $data));
            if(!$indexValue) return false;
            return $this->db->exec("UPDATE {$this->table_name} SET $sql1 WHERE $index = '$indexValue'");
        }
        return false;
    }

    public function delete()
    {
        $index = $this->getIndex();
        $value = $this->getFieldValue($index);
        if($value)
        {
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
        $index = $this->getIndex();
        $id = $this->db->escape_string($id);
        return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE $index = '$id' "));
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
        if($value)
            return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE $index != '$value'"));
        return $this->get_all();

    }

    public function save()
    {
        $indexValue = $this->getFieldValue($this->getIndex());
        if($indexValue)
        {
            if($this->get($indexValue))
            {
                return $this->update();
            }
        }
        return $this->insert();
    }

    public function getFieldValue($field, $data = null)
    {
        if($data != null && isset($data[$field]))
        {
            return $data[$field];
        }
        if(isset($this->{$field}))
        {
            return $this->{$field};
        }
        return null;
    }

    public function getFields()
    {
        $fields = array();
        if(isset($this->definition['index']))
        {
            $fields[] = $this->definition['index'];
        }
        if(isset($this->definition['fields']))
        {
            foreach($this->definition['fields'] as $key => $value)
            {
                if(!is_numeric($key))
                    $fields[] = $key;
                else
                    $fields[] = $value;
            }
        }
        return $fields;
    }

    public function getIndex()
    {
        if(isset($this->definition['index']))
        {
            return $this->definition['index'];
        }
        return null;
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
        if(count($result) > 0)
        {
            $modelResult->model = $result[0];
        }
        return $modelResult;
    }

    public function setData($data)
    {
        if($data != null)
        {
            $fileds = $this->getFields();
            foreach($fileds as $filed)
            {
                if(isset($data[$filed]))
                {
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