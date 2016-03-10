<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 1/03/16
 * Time: 10:19
 */

namespace GLFramework\DaMa;


use GLFramework\DaMa\Manipulators\ManipulatorCore;
use GLFramework\Model;

class Manipulator
{

    private $currentSheet;
    private $association = array();
    private $modelName;
    private $filename;
    /**
     * @var ManipulatorCore
     */
    private $core;

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param mixed $filename
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return ManipulatorCore
     */
    public function getCore()
    {
        return $this->core;
    }

    /**
     * @param ManipulatorCore $core
     * @return $this
     */
    public function setCore($core)
    {
        $this->core = $core;
        return $this;
    }
    public function model($modelName)
    {
        $this->modelName = $modelName;
        return $this;
    }

    public function field($nameInFile, $nameInModel, $fn = null)
    {
        if($fn == null)
        {
            $this->association[$nameInFile] = $nameInModel;
        }
        else
        {
            $this->association[$nameInFile] = array('field' => $nameInModel, 'fn' => $fn);

        }
        return $this;
    }

    public function sheet($index)
    {
        $this->currentSheet = $index;
    }

    private function init($config = array())
    {
        $this->getCore()->open($this->getFilename(), $config);
        if($this->currentSheet !== null)
            $this->getCore()->setSheet($this->currentSheet);
    }

    public function exec($config = array())
    {
        $count = 0;
        $this->init($config);
        if($header = $this->getCore()->next())
        {
            while($next = $this->getCore()->next())
            {
                $model = $this->build($header, $next);
                if($model->save())
                {
                    $count++;
                }
            }
            return $count;
        }

        return false;
    }

    /**
     * @param $header
     * @param $row
     * @return Model
     */
    public function build($header, $row)
    {
        $associative = array();
        foreach($header as $key => $value)
        {
            $associative[$value] = $row[$key];
        }
        $model = new $this->modelName();
        foreach($this->association as $key => $value)
        {
            $field = $value;
            if(is_array($value))
            {
                $field = $value['field'];
                $model->{$field} = call_user_func($value['fn'], $associative[$key]);
            }
            else
            {
                $model->{$field} = $associative[$key];
            }
        }
        return $model;
    }

    public function debug($number, $config = array())
    {
        $list = array();
        $this->init($config);
        if($header = $this->getCore()->next())
        {
            while($data = $this->getCore()->next())
            {
                if($data == null) break;
                $model = $this->build($header, $data);
                $number--;
                if($number > 0)
                    $list[] = $model;
            }
        }
        print_debug($header, $data, $list);
    }
}