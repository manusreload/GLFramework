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
    /**
     * Nombre de la tabla en la base de datos
     * @var string
     */
    protected $table_name = "";
    /**
     * Definicion del modelo
     * @var array
     */
    protected $definition = array();
    /*  Ejemplo de definicion

    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'controller' => "varchar(255)",
            'title' => "varchar(64)",
            'description' => "varchar(255)",
            'admin' => "int(11)",
        )
    );

     */
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
    /**
     * Lista de columnas que no se muestran a la funcion json()
     * @var array
     */
    protected $hidden = array();
    /**
     * Traducir las columnas en modelos en si.
     * array(
     *  'id_asp' => array('name' => 'asp', 'model' => 'User'),
     *   'id_operario' => array('name' => 'operario', 'model' => 'User'),
     *   'id_taller' => 'Taller',
     *   'id_taller_from' => array('name' => 'from', 'model' => 'Taller'),
     * )
     * @var array
     */
    protected $models = array();

    /**
     * Model constructor.
     */
    public function __construct($data = null)
    {
        $this->db = new DatabaseManager();
        $this->setData($data);
    }

    /**
     * Inserta el modelo en la tabla
     * @param null $data
     * @return bool
     */
    public function insert($data = null)
    {
        $fields = $this->getFields();
        $sql1 = "";
        $sql2 = "";
        $args = array();
        foreach ($fields as $field) {
//            if($this->getFieldValue($field, $data) !== NULL)
            {
                $value = $this->db->escape_string($this->getFieldValue($field, $data));
                $args[$field] = $value;
                $sql1 .= "$field, ";
                $sql2 .= ":$field, ";
            }
        }
        if (!empty($sql1)) {
            $sql1 = substr($sql1, 0, -2);
            $sql2 = substr($sql2, 0, -2);

            return $this->db->insert("INSERT INTO {$this->table_name} ($sql1) VALUES ($sql2)", $args);
        }
        return false;
    }

    /**
     * Si el modelo tiene indice actualiza el modelo con los datos
     * @param null $data
     * @return bool
     * @throws \Exception
     */
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

    /**
     * Eliminar el modelo de la base de datos
     * @return bool
     * @throws \Exception
     */
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
     * Obtener el modelo de la base de datos. Puede ser el id, o una lista con el nombre de las columnas
     *  y el valor esperado. Es una condición conjuntiva.
     * @param int|array $id
     * @return ModelResult
     */
    public function get($id)
    {
        if (!is_array($id)) {

            $index = $this->getIndex();
            $id = $this->db->escape_string($id);
            return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE $index = ? ", array($id), $this->getCacheId($id)));
        } else if (is_array($id)) {
            $fieldsValue = $id;
            $fields = $this->getFields();
            $sql = "";
            foreach ($fields as $field) {
                if (isset($fieldsValue[$field])) {
                    $value = $fieldsValue[$field];
                    $sql .= $field . "= :$field AND ";
                }
            }
            if (!empty($sql)) {
                $sql = substr($sql, 0, -5);
                return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE $sql", $fieldsValue));
            }
        }
        return $this->build(array());
    }

    /**
     * Obtener el modelo de la base de datos con condiciones disyuntivas
     * @param $fields
     * @return ModelResult
     * @throws \Exception
     */
    public function get_or($fields)
    {
        $args = array();
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
                        $sql .= $field . "= ? OR ";
                        $args[] = $subvalue;
                    }
                }
                else
                {
                    $sql .= $field . "= ? OR ";
                    $args[] = $value;
                }
            }
        }
        if (!empty($sql)) {
            $sql = substr($sql, 0, -4);
            return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE $sql", $args));
        }
        return $this->build(array());
    }

    /**
     * Obtener todos los modelos de la tabla
     * @return ModelResult
     */
    public function get_all()
    {
        return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE 1"));
    }

    /**
     * Obtener los modelos que no sean este.
     * @return ModelResult
     * @throws \Exception
     */
    public function get_others()
    {
        $index = $this->getIndex();
        $value = $this->getFieldValue($index);
        if ($value)
            return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE $index != ?", array($index)));
        return $this->get_all();

    }

    /**
     * Busca en forma de texto en la tabla
     * @param $fields
     * @return ModelResult
     * @throws \Exception
     */
    public function search($fields)
    {
        if(!is_array($fields)) $fields = array($this->getIndex() => $fields);

        $fieldsValue = $fields;
        $fields = $this->getFields();
        $sql = "";
        $args = array();
        foreach ($fields as $field) {
            if (isset($fieldsValue[$field])) {
                $value = $fieldsValue[$field];
                if($this->isString($field)) {
                    $sql .= $field . " LIKE ? OR ";
                    $args[] = '%' . $value . '%';
                }
                else {
                    $sql .= $field . " = ? OR ";
                    $args[] = $value;
                }

            }
        }
        if (!empty($sql)) {
            $sql = substr($sql, 0, -4);
            return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE $sql", $args));
        }
        return $this->build(array());
    }

    /**
     * Actliza si ya existe, en otro caso inserta
     * @param bool $updateIndex
     * @return bool
     */
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

    /**
     * Devuelve true si existe el modelo en la tabla
     * @return bool
     */
    public function exists()
    {
        $indexValue = $this->getFieldValue($this->getIndex());
        if ($indexValue) {
            return $this->get($indexValue)->count() > 0;
        }
        return false;
    }

    /**
     * Obtiene el valor del campo especificado, null si no existe el campo
     * @param $field
     * @param null $data
     * @return null
     */
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

    /**
     * Apartir de la definicion devuelve los campos disponibles para el modelo
     * @return array
     */
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

    /**
     * Obtiene el nombre del indice para esta tabla
     * @return null
     */
    public function getIndex()
    {
        if (isset($this->definition['index'])) {
            if (is_array($this->definition['index']))
                return $this->definition['index']['field'];
            return $this->definition['index'];
        }
        return null;
    }

    /**
     * Asigna este valor al indice del modelo
     * @param $value
     */
    public function setToIndex($value)
    {
        $field = $this->getIndex();
        if($field)
            $this->{$field} = $value;
    }

    /**
     * Devuelve true si es el indice del modelo
     * @param $field
     * @return bool
     */
    public function isIndex($field)
    {
        return $this->getIndex() == $field;
    }

    /**
     * Genera un resultado de modelos a partir de la lista develta por la consulta
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

    /**
     * Establecer estos datos al modelo, en funcion de la definicion, si no esta en la definicion no
     * se asigna al modelo.
     * @param $data
     * @param bool $allowEmpty
     * @param bool $allowUnset
     * @return $this
     */
    public function setData($data, $allowEmpty = true, $allowUnset = false)
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
                    else if($allowUnset)
                    {
                        $this->{$field} = false;
                    }
                }
            }
            else
            {
                $result = $this->get($data);
                $this->setData($result->model);
                if(empty($result->model)) $this->asDefault();
            }
        }
        if(empty($data))
        {
            $this->asDefault();
        }
        return $this;
    }

    /**
     * Devuelve la definicion original del modelo
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }
    /**
     * Obtiene la definicion para este campo
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

    /**
     * Obtiene el tipo para el campo
     * @param $field
     * @return mixed
     */
    public function getFieldType($field)
    {
        $definition = $this->getFieldDefinition($field);
        if($definition)
        {
            return $definition['type'];
        }
    }

    /**
     * Nombre de la tabla asociada al modelo.
     * @return string
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    /**
     * Genera las diferencias entre este modelo y lo que hay
     * en la base de datos.
     * @return array
     */
    public function getStructureDifferences()
    {
        $diff = new DBStructure();
        $excepted = $diff->getDefinition($this);
        return $diff->getStructureDifferences($excepted);
    }

    /**
     * Construye un array asocativo con la infrmación en json del Modelo.
     * Si se ha establecido en $this->models un modelo asociado, entonces
     * se construye tambien de forma recursiva.
     * @param array $fields
     * @return array
     */
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
                        $object =  new $model($this->getFieldValue($field));
                        $json[$name] = $object->json();
                    }
                    else
                    {
                        $name = $this->underescapeName($modelTransform);
                        $object = new $modelTransform($this->getFieldValue($field));
                        $json[$name] = $object->json();
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

    /**
     * Separa las palabras por barras bajas
     * @param $name
     * @return mixed
     */
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

    /**
     * Se ejecuta cuando estamos estableciendo los datos
     * al modelo y no hay ningun campo para rellenar.
     */
    public function asDefault(){}

    /**
     * Comprueba que el modelo sea válido, es decir, que tenga datos en
     * almenos un campo.
     * @param array $fields
     * @return bool
     */
    public function valid($fields = array())
    {
        if(empty($fields))
        {
            $fields = array_diff($this->getFields(), array($this->getIndex()));
        }
        foreach ($fields as $field)
        {
            $value = $this->getFieldValue($field);
            if(!empty($value)) return true;
        }
        return false;
    }


    /**
     * @param $baseclass
     * @param array $args
     */
    public static function newInstance($baseclass, $args = array())
    {
        print_debug($baseclass, get_class());
    }


}