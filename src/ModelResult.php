<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 21:07
 */

namespace GLFramework;


class ModelResult
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

    public function getModels($size = null)
    {
        return $this->getModelsInstanced($size);
    }

    public function offset($count)
    {
        if(count($this->models) < $count)
        {
            return $this->models[$count];
        }
        return null;
    }

    public function count()
    {
        return count($this->models);
    }

    public function last()
    {
        if($this->count())
        {
            $models = $this->getModels();
            return $models[$this->count() - 1];
        }
        return null;
    }

    public function json()
    {
        $list = array();
        foreach($this->getModels() as $model)
        {
            $list[] = $model->json();
        }
        return $list;
    }

    public function reverse($size = null)
    {
        return array_reverse($this->getModels($size));;
    }
}