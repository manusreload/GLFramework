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
use GLFramework\Utils\Profiler;

define("MODEL_FIELD_TYPE_STRING", 1);
define("MODEL_FIELD_TYPE_INT", 2);
define("MODEL_FIELD_TYPE_DOUBLE", 3);

/**
 * Class Model
 *
 * @package GLFramework
 */
class Model
{
    /**
     * @var DatabaseManager
     */
    var $db;
    protected $order = '';
    /**
     * Nombre de la tabla en la base de datos
     * @var string
     */
    protected $table_name = '';
    /**
     * Definicion del modelo
     * @var array
     */
    protected $definition = array();
    private static $modelCache = [];
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
     * @var array Campos extra para el JSON array
     */
    protected $json_extra = array();
    private $index;
    protected $engine = "INNODB";

    /**
     * Model constructor.
     *
     * @param null $data
     */
    public function __construct($data = null)
    {
        if ($this->table_name === '') {
            throw new \Exception('El nombre de la tabla para el modelo \'' . get_class($this) . '\' no puede estar vacío!');
        }
        $this->setIndexFromDefinition($this->definition);
        $this->db = Bootstrap::getSingleton()->getDatabase();
        foreach ($this->getFields() as $field) {
            $this->{$field} = false;
        }
        $this->{$this->getIndex()} = null;
        $this->setData($data);
    }

    /**
     * TODO
     *
     * @param $baseclass
     * @param array $args
     * @param null $module GLFramework\Module\Module
     * @return Model
     */
    public static function newInstance($baseclass, $args = array(), &$module = null)
    {
        $modules = ModuleManager::getInstance()->getModules();
        foreach ($modules as $module) {
            if (in_array($baseclass, $module->getModels())) {
                $classes = array('\\' . $module->modelNamespace . '\\' . $baseclass, $baseclass);
                foreach ($classes as $class) {
                    if (class_exists($class)) {

                        return new $class($args);
                    }
                }
            }
        }
        $class = '\\GLFramework\\Model\\' . $baseclass;
        if (class_exists($class)) {
            return new $class($args);
        }
        return false;
    }

    /**
     * Inserta el modelo en la tabla
     *
     * @param null $data
     * @return bool
     */
    public function insert($data = null)
    {
        $currentIndex = $this->getFieldValue($this->getIndex());
        $fields = $this->getFields();
        $sql1 = '';
        $sql2 = '';
        $args = array();
        foreach ($fields as $field) {
            // if($this->getFieldValue($field, $data) !== NULL)
            if (in_array($field, $this->created_at_fileds)) {
                $this->{$field} = now();
            }
            $value = $this->getFieldValue($field, $data);
            $args[$field] = $value;
            $sql1 .= "`$field`, ";
            $sql2 .= ":$field, ";
        }
        if (!empty($sql1)) {
            $sql1 = substr($sql1, 0, -2);
            $sql2 = substr($sql2, 0, -2);
            if($currentIndex) {
                $this->db->removeCache($this->getCacheId($currentIndex));
            }
            return $this->db->insert('INSERT INTO '.$this->table_name.' ('.$sql1.') VALUES ('.$sql2.')', $args);
        }
        return false;
    }

    /**
     * Si el modelo tiene indice actualiza el modelo con los datos
     *
     * @param null $data
     * @return bool
     * @throws \Exception
     */
    public function update($data = null)
    {
        $fields = $this->getFields();
        $sql1 = '';
        $args = array();
        foreach ($fields as $field) {
            if (in_array($field, $this->updated_at_fileds)) {
                $this->{$field} = now();
            }
            $value = $this->getFieldValue($field, $data);
            if (isset($value) && !$this->isIndex($field)) {
                $args[] = $value;
                $sql1 .= "`$field` = ?, ";
            }
        }
        if (!empty($sql1)) {
            $sql1 = substr($sql1, 0, -2);
            $index = $this->getIndex();
            $indexValue = $this->db->escape_string($this->getFieldValue($index, $data));
            if (!$indexValue) {
                return false;
            }
            $args[] = $indexValue;
            return $this->db->exec("UPDATE {$this->table_name} SET $sql1 WHERE `$index` = ?", $args, $this->getCacheId($indexValue));
        }
        return false;
    }

    /**
     * Eliminar el modelo de la base de datos
     *
     * @param null $cache
     * @return bool
     */
    public function delete($cache = null)
    {
        $index = $this->getIndex();
        $value = $this->getFieldValue($index);
        if ($value) {
            if($cache) {
                $this->db->removeCache($this->getCacheId($cache));
            }
            return $this->db->exec("DELETE FROM {$this->table_name} WHERE `$index` = ?", array($value), $this->getCacheId($value));
        }
        return false;
    }

    /**
     * TODO
     *
     * @param $sql
     * @param array $args
     * @return ModelResult
     * @throws \Exception
     */
    public function select($sql, $args = array())
    {
        return $this->build($this->db->select($sql, $args));
    }

    /**
     * TODO
     *
     * @param $id
     * @return string
     */
    public function getCacheId($id)
    {
        if(is_array($id))
        {
            $tmp = "";
            foreach ($id as $key => $value)
            {
                $tmp .= $key . "_" . $value;
            }
            $id = $tmp;
        }
        return $this->addCacheIndex($this->table_name . '_' . $id);
    }

    /**
     * Obtener el modelo de la base de datos. Puede ser el id, o una lista con el nombre de las columnas
     *  y el valor esperado. Es una condición conjuntiva.
     *
     * @param int|array $id
     * @return ModelResult
     */
    public function get($id)
    {
        if (!is_array($id)) {
            $index = $this->getIndex();
            $id = $this->db->escape_string($id);
            return $this->build($this->db->select('SELECT * FROM ' . $this->table_name . ' WHERE `' . $index . '` = ? ',
                array($id), $this->getCacheId($id)));
        }
        if (is_array($id)) {
            $fieldsValue = $id;
            $fields = $this->getFields();
            $sql = '';
            $args = array();
            foreach ($fields as $field) {
                if (isset($fieldsValue[$field])) {
                    $args[$field] = $fieldsValue[$field];
                    $sql .= '`' . $field . '` = :' . $field . ' AND ';
                }
            }
            if (!empty($sql)) {
                $sql = substr($sql, 0, -5);
                return $this->build($this->db->select('SELECT * FROM ' . $this->table_name . ' WHERE ' . $sql, $args, $this->getCacheId($id)));
            }
        }
        return $this->build(array());
    }

    /**
     * Obtener el modelo de la base de datos con condiciones disyuntivas
     *
     * @param $fields
     * @return ModelResult
     * @throws \Exception
     */
    public function get_or($fields)
    {
        $args = array();
        $fieldsValue = $fields;
        $fields = $this->getFields();
        $sql = '';
        foreach ($fields as $field) {
            if (isset($fieldsValue[$field])) {
                $value = $fieldsValue[$field];
                if (is_array($value)) {
                    foreach ($value as $subvalue) {
                        $sql .= '`' . $field . '` = ? OR ';
                        $args[] = $subvalue;
                    }
                } else {
                    $sql .= '`' . $field . '` = ? OR ';
                    $args[] = $value;
                }
            }
        }
        if (!empty($sql)) {
            $sql = substr($sql, 0, -4);
            return $this->build($this->db->select('SELECT * FROM ' . $this->table_name . ' WHERE ' . $sql, $args));
        }
        return $this->build(array());
    }

    /**
     * Obtener todos los modelos de la tabla
     *
     * @return ModelResult
     */
    public function get_all()
    {
        return $this->build($this->db->select('SELECT * FROM ' . $this->table_name . ' WHERE 1', [], $this->getCacheId('all')));
    }

    /**
     * Obtener los modelos que no sean este.
     *
     * @return ModelResult
     * @throws \Exception
     */
    public function get_others()
    {
        $index = $this->getIndex();
        $value = $this->getFieldValue($index);
        if (is_string($value) && strlen($value) > 0) {
            return $this->build($this->db->select('SELECT * FROM ' . $this->table_name . ' WHERE `' . $index . '` != ?', array((string)$value), $this->getCacheId("not_" . $value)));
        }
        return $this->get_all();
    }

    /**
     * Busca en forma de texto en la tabla
     *
     * @param $fields
     * @param int $limit
     * @return ModelResult
     */
    public function search($fields, $limit = -1)
    {
        if (!is_array($fields)) {
            $fields = array($this->getIndex() => $fields);
        }

        $fieldsValue = $fields;
        $fields = $this->getFields();
        $sql = '';
        $args = array();
        foreach ($fields as $field) {
            if (isset($fieldsValue[$field])) {
                $value = $fieldsValue[$field];
                if ($this->isString($field)) {
                    $sql .= '`' . $field . '` LIKE ? OR ';
                    $args[] = '%' . $value . '%';
                } else {
                    $sql .= '`' . $field . '` = ? OR ';
                    $args[] = $value;
                }
            }
        }
        if (!empty($sql)) {
            $sql = substr($sql, 0, -4);
            if($limit > 0) {
                $sql .= " LIMIT $limit";
            }
            return $this->build($this->db->select('SELECT * FROM ' . $this->table_name . ' WHERE ' . $sql, $args));
        }
        return $this->build(array());
    }

    /**
     * Actualiza si ya existe, en otro caso inserta
     *
     * @param bool $updateIndex
     * @return bool
     */
    public function save($updateIndex = false)
    {
        $this->clearCacheTableIndex();
        if ($this->exists()) {
            return $this->update();
        }
        $result = $this->insert();
        if ($result && $updateIndex) {
            $this->setToIndex($result);
        }

        return $result;
    }

    /**
     * Devuelve true si existe el modelo en la tabla
     *
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
     *
     * @param $field
     * @param null $data
     * @return null
     */
    public function getFieldValue($field, $data = null)
    {
        if ($data !== null && isset($data[$field])) {
            return $data[$field];
        }
        if (isset($this->{$field})) {
            return $this->{$field};
        }
        return null;
    }

    /**
     * Apartir de la definicion devuelve los campos disponibles para el modelo
     *
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
                if (!is_numeric($key)) {
                    $fields[] = $key;
                } else {
                    $fields[] = $value;
                }
            }
        }
        return $fields;
    }

    public function setIndexFromDefinition($definition){
        if (isset($definition['index'])) {
            if (is_array($definition['index'])) {
                $this->index = $definition['index']['field'];
            } else {
                $this->index = $definition['index'];
            }
        }
    }
    /**
     * Obtiene el nombre del indice para esta tabla
     *
     * @return null
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Asigna este valor al indice del modelo
     *
     * @param $value
     */
    public function setToIndex($value)
    {
        $field = $this->getIndex();
        if ($field) {
            $this->{$field} = $value;
        }
    }


    /**
     * Devuelve true si es el indice del modelo
     *
     * @param $field
     * @return bool
     */
    public function isIndex($field)
    {
        return $this->index == $field;
    }

    public function getKeys() {
        if(isset($this->definition['keys'])) {
            return $this->definition['keys'];
        }
    }

    /**
     * Genera un resultado de modelos a partir de la lista develta por la consulta
     *
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
        if ($this->order !== '') {
            $modelResult->order($this->order);
        }
        return $modelResult;
    }

    /**
     * Rellenar los datos desde un array. Si se especifica un indice, se tomara como: { columna1: [valor, valor...], columna2: [valor, valor...] }
     *
     * @param $data
     * @param bool $index
     */
    public function setDataFromArray($data, $index = false)
    {
        if($index !== false)
        {
            $fields = $this->getFields();
            foreach ($fields as $field)
            {
                if (isset($data[$field][$index]))
                {
                    $this->{$field} = $data[$field][$index];
                }
            }
        }
        else
        {
            $this->setData($data);
        }
    }

    /**
     * Establecer estos datos al modelo, en funcion de la definicion, si no esta en la definicion no
     * se asigna al modelo.
     *
     * @param $data
     * @param bool $allowEmpty
     * @param bool $allowUnset
     * @return $this
     */
    public function setData($data, $allowEmpty = true, $allowUnset = false)
    {
        if ($data != null) {
            if (is_array($data)) {
                $fileds = $this->getFields();
                foreach ($fileds as $field) {
                    if (isset($data[$field]) && ($allowEmpty || $data[$field] !== '')) {
                        if (strpos($this->getFieldType($field), 'varchar') !== false || $this->getFieldType($field) === 'text') {
                            $encoding = mb_detect_encoding($data[$field]);
                            if ($encoding !== 'utf8') {
                                $this->{$field} = mb_convert_encoding($data[$field], 'utf8', $encoding);
                            } else {
                                $this->{$field} = $data[$field];
                            }
                        } else {
                            $this->{$field} = $data[$field];
                        }
                    } elseif ($allowUnset) {
                        $this->{$field} = false;
                    }
                }
            } else {
                $result = $this->get($data);
                $this->setData($result->model);
                if (empty($result->model)) {
                    $this->asDefault();
                }
            }
        }
        if (empty($data)) {
            $this->asDefault();
        }
        return $this;
    }

    /**
     * Devuelve la definicion original del modelo
     *
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Obtiene la definicion para este campo
     *
     * @param $field
     * @return mixed|null
     */
    public function getFieldDefinition($field)
    {
        if (isset($this->definition[$field])) {
            return $this->definition[$field];
        }
        if (isset($this->definition['fields'][$field])) {
            return $this->definition['fields'][$field];
        }
        return null;
    }

    /**
     * Obtiene el tipo para el campo
     *
     * @param $field
     * @return mixed
     */
    public function getFieldType($field)
    {
        $definition = $this->getFieldDefinition($field);
        if ($definition && isset($definition['type'])) {
            return $definition['type'];
        }
    }

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    /**
     * Genera las diferencias entre este modelo y lo que hay
     * en la base de datos.
     *
     * @param $db DatabaseManager
     * @param $drop bool
     * @return array
     */
    public function getStructureDifferences($db, $drop = false)
    {
        $diff = new DBStructure();
        $excepted = $diff->getDefinition($this);
        return $diff->getStructureDifferences($db, $excepted, $drop);
    }

    /**
     * Construye un array asocativo con la infrmación en json del Modelo.
     * Si se ha establecido en $this->models un modelo asociado, entonces
     * se construye tambien de forma recursiva.
     *
     * @param array $fields
     * @param bool $recursive Especifica si devuelve las asociaciones con otros modelos
     * @param int $limit
     * @return array
     */
    public function json($fields = array(), $recursive = true, $limit = 16)
    {
        if ($limit === 0) {
            return null;
        }
        $json = array();
        if ($url = $this->url()) {
            $json['url'] = fix_url($url);
        }
        if (empty($fields)) {
            $fields = $this->getFields();
        }
        $it = 2;
        foreach ($fields as $field) {
            if (!in_array($field, $this->hidden)) {
                $found = false;
                $list = array();
                foreach ($this->models as $item) {
                    if (isset($item['from']) && $item['from'] === $field) {
                        $list[] = $item;
                        $found = true;
                    }
                }
                if ($found || isset($this->models[$field])) {
                    if ($recursive) {
                        $modelTransform = $list;
                        if (!$found) {
                            $modelTransform = array($this->models[$field]);
                        }
                        foreach ($modelTransform as $mt) {
                            $filter = array();
                            if (is_array($mt)) {
                                $name = $mt['name'];
                                $model = $mt['model'];
                                $object = new $model();
                                if (isset($fields[$name])) {
                                    $filter = $fields[$name];
                                }
                                $recursive2 = isset($mt['recursive']) ? $mt['recursive'] : $recursive;
                                if (isset($mt['field'])) {
                                    $json[$name] = $object->get(array($mt['field'] => $this->getFieldValue($field)))
                                                          ->json($filter, $recursive2, $limit - 1);
                                } else {
                                    $json[$name] = $object->get($this->getFieldValue($field))
                                                          ->json($filter, $recursive2, $limit - 1);
                                }
                            } else {
                                $name = $this->underescapeName($mt);
                                if (isset($fields[$name])) {
                                    $filter = $fields[$name];
                                }
                                $object = new $mt($this->getFieldValue($field));
                                $json[$name] = $object->json($filter, $recursive, $limit);
                            }
                        }
                    }
                }
                $json[$field] = $this->getFieldValue($field);
            }
        }

        if ($recursive && $this->json_extra) {
            foreach ($this->json_extra as $key => $function) {
                $json[$key] = call_user_func(array($this, $function));
            }
        }
        return $json;
    }

    /**
     * @param array $fields
     * @param bool $recursive
     * @param int $limit
     * @return array
     */
    public function export($fields = array(), $recursive = true, $limit = 16)
    {
        if ($limit === 0) {
            return null;
        }
        $json = array();
        if ($url = $this->url()) {
            $json['url'] = fix_url($url);
        }
        if (empty($fields)) {
            $fields = $this->getFields();
        }
        foreach ($fields as $field) {
            if (!in_array($field, $this->hidden)) {
                $found = false;
                $list = array();
                foreach ($this->models as $item) {
                    if (isset($item['from']) && $item['from'] === $field) {
                        $list[] = $item;
                        $found = true;
                    }
                }
                if ($found || isset($this->models[$field])) {
                    if ($recursive) {
                        $modelTransform = $list;
                        if (!$found) {
                            $modelTransform = array($this->models[$field]);
                        }
                        foreach ($modelTransform as $mt) {
                            $filter = array();
                            if (is_array($mt)) {
                                $name = $mt['name'];
                                $model = $mt['model'];
                                if(class_exists($model)) {
                                    $object = new $model();
                                    if (isset($fields[$name])) {
                                        $filter = $fields[$name];
                                    }
                                    $recursive2 = isset($mt['recursive']) ? $mt['recursive'] : $recursive;
                                    if (isset($mt['field'])) {
                                        $json[$name] = $object->get(array($mt['field'] => $this->getFieldValue($field)))
                                            ->export($filter, $recursive2, $limit - 1);
                                    } else {
                                        $item = new $object($this->getFieldValue($field));
                                        $json[$name] = $item->export($filter, $recursive2, $limit - 1);
                                    }
                                }
                            } else {
                                $name = $this->underescapeName($mt);
                                if (isset($fields[$name])) {
                                    $filter = $fields[$name];
                                }
                                if(class_exists($mt)) {
                                    $object = new $mt($this->getFieldValue($field));
                                    $json[$name] = $object->json($filter, $recursive, $limit);
                                }
                            }
                        }
                    }
                }
                $json[$field] = $this->getFieldValue($field);
            }
        }

        if ($recursive) {
            foreach ($this->json_extra as $key => $function) {
                $json[$key] = call_user_func(array($this, $function));
            }
        }
        return $json;
    }

    /**
     * Separa las palabras por barras bajas
     *
     * @param $name
     * @return mixed
     */
    public function underescapeName($name)
    {
        if (preg_match_all('#([A-Z][a-z]*)#', $name, $matches)) {
            return implode('_', array_map('strtolower', $matches[1]));
        }
    }

    /**
     * TODO
     *
     * @param $field
     * @return bool
     */
    public function isString($field)
    {
        $def = strtolower($this->getFieldDefinition($field));
        if (strpos($def, 'varchar') !== false) {
            return true;
        }
        if (strpos($def, 'text') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Se ejecuta cuando estamos estableciendo los datos
     * al modelo y no hay ningun campo para rellenar.
     */
    public function asDefault()
    {
    }

    /**
     * Comprueba que el modelo sea válido, es decir, que tenga datos en
     * almenos un campo.
     *
     * @param array $fields
     * @return bool
     */
    public function valid($fields = array())
    {
        if (empty($fields)) {
            $fields = array_diff($this->getFields(), array($this->getIndex()));
        }
        foreach ($fields as $field) {
            $value = $this->getFieldValue($field);
            if (!empty($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * TODO
     *
     * @return null
     */
    public function url()
    {
        return null;
    }

    /**
     * TODO
     */
    public function onCreate()
    {
    }

    public function clearCache($key)
    {
        $this->db->removeCache($this->getCacheId($key));
    }

    private function addCacheIndex($value) {
        $cache = $this->db->getCache();
        if($cache) {
            $name = '__index_' . $this->table_name;
            $index = $cache->get($name);
            $index[$value] = true;
            $cache->set($name, $index);
        }
        return $value;
    }

    public function clearCacheTableIndex() {
        $cache = $this->db->getCache();
        if($cache) {
            $name = '__index_' . $this->table_name;
            $values = $cache->get($name);
            if($values) {
                foreach ($values as $key => $value) {
                    $cache->remove($key);
                }
            }
            $cache->remove($name);
        }
    }

    /**
     * Recuento de filas en la tabla
     */
    public function count()
    {
        return $this->db->select_count("SELECT count(1) FROM `{$this->getTableName()}` ");
    }

    public function getEngine() {
        return $this->engine;
    }
}
