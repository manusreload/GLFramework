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
 * Date: 13/1/16
 * Time: 20:39
 */

namespace GLFramework;

use GLFramework\Module\ModuleManager;

define("MODEL_FIELD_TYPE_STRING", 1);
define("MODEL_FIELD_TYPE_INT", 2);
define("MODEL_FIELD_TYPE_DOUBLE", 3);

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
     *  "'([a-z0-9_]+)' => .*" -> "var \$$1;"
     *  - Desde DESCRIBE...
     * "([a-z0-9_]+)\t.*" -> "var \$$1;"
     */

    /*
     * Reglas para sacar definicion (desde DESCRIBE <table>):
     *  "([a-z0-9_]+)\t([a-z0-9(),]+).*" -> "'$1' => '$2',"
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
     *   'id' => array('name' => 'tareas', 'model' => 'VehiculoTareas', 'field' => 'id_vehiculo'),
     *   'id_taller' => 'Taller',
     *   'id_taller_from' => array('name' => 'from', 'model' => 'Taller'),
     * )
     * @var array
     */
    protected $models = array();

    protected $updated_at_fileds = array('updated_at');
    protected $created_at_fileds = array('created_at');

    /**
     * Model constructor.
     * @param null $data
     * @throws \Exception
     */
    public function __construct($data = null)
    {
        if($this->table_name == "") throw new \Exception("El nombre de la tabla para el modelo '" . get_class($this) . "' no puede estar vacío!");
        $this->db = new DatabaseManager();
        foreach ($this->getFields() as $field)
        {
            $this->{$field} = false;
        }
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
                if(in_array($field, $this->created_at_fileds))
                {
                    $this->{$field} = now();
                }
                $value = $this->getFieldValue($field, $data);
                $args[$field] = $value;
                $sql1 .= "`$field`, ";
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
        $args = array();
        foreach ($fields as $field) {
            if(in_array($field, $this->updated_at_fileds))
            {
                $this->{$field} = now();
            }
            $value = $this->getFieldValue($field, $data);
            if (isset($value) && $value !== '' && !$this->isIndex($field)) {
                $args[] = $value;
                $sql1 .= "`$field` = ?, ";
            }
        }
        if (!empty($sql1)) {
            $sql1 = substr($sql1, 0, -2);
            $index = $this->getIndex();
            $indexValue = $this->db->escape_string($this->getFieldValue($index, $data));
            if (!$indexValue) return false;
            $args[] = $indexValue;
            return $this->db->exec("UPDATE {$this->table_name} SET $sql1 WHERE `$index` = ?",$args, $this->getCacheId($indexValue));
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
            return $this->db->exec("DELETE FROM {$this->table_name} WHERE `$index` = ?", array($value), $this->getCacheId($value));
        }
        return false;
    }

    /**
     * @param $sql
     * @param array $args
     * @return ModelResult
     * @throws \Exception
     */
    public function select($sql, $args = array())
    {
        return $this->build($this->db->select($sql, $args));
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
            return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE `$index` = ? ", array($id), $this->getCacheId($id)));
        } else if (is_array($id)) {
            $fieldsValue = $id;
            $fields = $this->getFields();
            $sql = "";
            $args = array();
            foreach ($fields as $field) {
                if (isset($fieldsValue[$field])) {
                    $args[$field] = $fieldsValue[$field];
                    $sql .= "`" . $field . "` = :$field AND ";
                }
            }
            if (!empty($sql)) {
                $sql = substr($sql, 0, -5);
                return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE $sql", $args));
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
                        $sql .= "`" . $field . "` = ? OR ";
                        $args[] = $subvalue;
                    }
                }
                else
                {
                    $sql .= "`" . $field . "` = ? OR ";
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
            return $this->build($this->db->select("SELECT * FROM {$this->table_name} WHERE `$index` != ?", array($index)));
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
                    $sql .= "`" . $field . "` LIKE ? OR ";
                    $args[] = '%' . $value . '%';
                }
                else {
                    $sql .= "`" . $field . "` = ? OR ";
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
        if($url = $this->url())
        {
            $json['url'] = fix_url($url);
        }
        if(empty($fields)) $fields = $this->getFields();
        $it = 2;
        foreach($fields as $field)
        {
            if(!in_array($field, $this->hidden))
            {
                $found = false;
                $list = array();
                foreach ($this->models as $item)
                {
                    if(isset($item['from']) && $item['from'] == $field)
                    {
                        $list[] = $item;
                        $found = true;
                    }
                }
                if($found || isset($this->models[$field]))
                {
                    if(!$found) $modelTransform = array($this->models[$field]);
                    else $modelTransform = $list;
                    foreach ($modelTransform as $mt)
                    {
                        if(is_array($mt))
                        {
                            $name = $mt['name'];
                            $model = $mt['model'];
                            $object =  new $model();
                            if(isset($mt['field']))
                            {
                                $json[$name] = $object->get(array($mt['field'] => $this->getFieldValue($field)))->json();

                            }
                            else{
                                $json[$name] = $object->get($this->getFieldValue($field))->json();
                            }
                        }
                        else
                        {
                            $name = $this->underescapeName($mt);
                            $object = new $mt($this->getFieldValue($field));
                            $json[$name] = $object->json();
                        }
                    }

                }

                $json[$field] = $this->getFieldValue($field);

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
     * @return Model
     */
    public static function newInstance($baseclass, $args = array())
    {
        $modules = ModuleManager::getInstance()->getModules();
        foreach ($modules as $module)
        {
            if(in_array($baseclass, $module->getModels()))
            {
                $classes = array("\\" . $module->title . "\\" . $baseclass, $baseclass);
                foreach ($classes as $class)
                {
                    if(class_exists($class))
                    {
                        return new $class($args);
                    }
                }
            }
        }
        $class = "\\GLFramework\\Model\\$baseclass";
        return new $class($args);

    }

    public function url()
    {
        return null;
    }


}