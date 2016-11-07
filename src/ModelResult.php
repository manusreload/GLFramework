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
 * Time: 21:07
 */

namespace GLFramework;

use Traversable;


class ModelResult implements \IteratorAggregate
{
    var $model_class;
    var $reflection;
    var $models;
    var $model;

    /**
     * ModelResult constructor.
     * @param $model_class
     * @param array $models
     */
    public function __construct($model_class, $models = array())
    {
        $this->reflection = new \ReflectionClass($model_class);
        $this->model_class = $model_class;
        if(!empty($models))
        {
            $this->models = $models;
            $this->model = $models[0];
        }
    }


    /**
     * Obtiene el primer modelo disponible
     * @return Model
     */
    public function getModel()
    {
        return $this->reflection->newInstance($this->model);
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
                $instances[] = $this->reflection->newInstance($model);
            }
        }
        else
        {
            for($i = 0; $i < $size; $i++)
            {
                if(isset($this->models[$i]))
                {
                    $instances[] = $this->reflection->newInstance($this->models[$i]);
                }
                else
                {
                    $instances[] = $this->reflection->newInstance();
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

    public function limit($length, $start = null)
    {
        return new ModelResult($this->model_class, array_slice($this->models, $start, $length));
    }

    public function delete()
    {
        foreach ($this->getModels() as $model)
        {
            $model->delete();
        }
    }
}