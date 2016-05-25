<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 21:07
 */

namespace GLFramework;


use Traversable;

class ModelResult implements \IteratorAggregate
{
    var $model_class;
    var $models;
    var $model;

    /**
     * ModelResult constructor.
     * @param $model_class
     */
    public function __construct($model_class)
    {
        $rf = new \ReflectionClass($model_class);
        $this->model_class = $rf;
    }


    /**
     * Obtiene el primer modelo disponible
     * @return Model
     */
    public function getModel()
    {
        return $this->model_class->newInstance($this->model);
    }

    /**
     * @param null $size
     * @return Model[]
     * @deprecated
     */
    public function getModelsInstanced($size = null)
    {
        $instances = array();
        if($size === null)
        {
            foreach($this->models as $model)
            {
                $instances[] = $this->model_class->newInstance($model);
            }
        }
        else
        {
            for($i = 0; $i < $size; $i++)
            {
                if(isset($this->models[$i]))
                {
                    $instances[] = $this->model_class->newInstance($this->models[$i]);
                }
                else
                {
                    $instances[] = $this->model_class->newInstance();
                }
            }
        }
        return $instances;
    }

    /**
     * Obtener una lista con los modelos disponibles
     * @param null $size
     * @return Model[]
     */
    public function getModels($size = null)
    {
        return $this->getModelsInstanced($size);
    }

    /**
     * Obtiene el modelo en la posicion $count
     * @param $count
     * @return null
     */
    public function offset($count)
    {
        if(count($this->models) < $count)
        {
            return $this->models[$count];
        }
        return null;
    }

    /**
     * Devuelve el numero de elementos devueltos
     * @return int
     */
    public function count()
    {
        return count($this->models);
    }

    /**
     * Obtiene el ultimo modelo de la lista
     * @return Model|null
     */
    public function last()
    {
        if($this->count())
        {
            $models = $this->getModels();
            return $models[$this->count() - 1];
        }
        return null;
    }

    /**
     * Genera una array lista para usar con json_encode
     * @return array
     */
    public function json()
    {
        $list = array();
        foreach($this->getModels() as $model)
        {
            $list[] = $model->json();
        }
        return $list;
    }

    /**
     * Devuelve la lista al reves
     * @param null $size
     * @return array
     */
    public function reverse($size = null)
    {
        return array_reverse($this->getModels($size));
    }

    /**
     * Ordena de menor a mayor en funcion del campo indicado
     * @param $field
     * @return $this
     */
    public function order($field)
    {
        usort($this->models, function($a, $b) use($field)
        {
            return $a[$field] - $b[$field];

        });
        return $this;
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator( $this->getModels());
    }
}