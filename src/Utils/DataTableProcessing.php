<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 14/11/16
 * Time: 9:39
 */

namespace GLFramework\Utils;


use GLFramework\Model;
use GLFramework\ModelResult;

class DataTableProcessing
{

    /**
     * @var ModelResult
     */
    var $data;
    var $columns = array();

    /**
     * DataTableProcessing constructor.
     * @param ModelResult $data
     * @param array $columns
     */
    public function __construct(ModelResult $data, $columns = null)
    {
        $this->data = $data;
        if($columns)
        {
            foreach ($columns as $item)
            {
                $this->addColumn($item);
            }
        }
    }

    public function clearColumns()
    {
        $this->columns = array();
    }

    public function translateColumnId($index)
    {
        return $this->columns[$index];
    }

    public function addColumn($name, $format = null, $index = false)
    {
        $data = new \stdClass();
        $data->format = $format;
        $data->name = $name;
        if(!$index) $this->columns[] = $data;
        else $this->columns[$index] = $data;
    }

    public function getFiledValue($model, $index)
    {
        $column = $this->translateColumnId($index);
        $value = $model->{$column->name};
        if($column->format)
        {
            $value = call_user_func($column->format, $value);
        }
        return $value;
    }

    public function filter_function($args)
    {
        return function($item) use($args)
        {
            $columns = $args['columns'];
            $search = $args['search']['value'];
            if(!empty($search))
            {
                if($item instanceof Model)
                {
                    foreach ($columns as $key => $value)
                    {
                        if($value['searchable'])
                        {
                            $columnValue = $this->getFiledValue($item, $key);
                            if(strpos($columnValue, $search) !== false)
                            {
                                return true;
                            }
                        }
                    }
                }
                return false;
            }
            return true;
        };
    }

    public function filter($args)
    {
        $result = $this->data->models;
        return $this->data->copy(array_filter($result, $this->filter_function($args)));
    }

    /**
     * @param $data ModelResult
     * @param $args
     */
    public function sort($data, $args)
    {
//        $filed = $this->translateColumnId($args['order']['0'])
//        $data->order()
    }


    public function run($query)
    {
        $result = array();
        $result['draw'] = $query['draw'];
        $total = $this->data->count();
        $filtered = $this->filter($query);
        $this->sort($filtered, $query);

        $filtered->limit($query['length'], $query['start']);
    }
}