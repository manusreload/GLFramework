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
    /**
     * @var Association[]
     */
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
        if($association = $this->getAssociation($nameInModel))
        {
            $association->addNameInFile($nameInFile);
        }
        else
        {
            $association = new Association();
            $association->addNameInFile($nameInFile);
            $association->setNameInModel($nameInModel);
            $association->setParser($fn);
            $this->association[] = $association;
        }
        return $association;
    }

    private function getAssociation($nameInModel)
    {
        foreach($this->association as $association)
        {
            if($association->getNameInModel() == $nameInModel)
            {
                return $association;
            }
        }
        return null;
    }

    public function constant($nameInModel, $value)
    {
        if($association = $this->getAssociation($nameInModel))
        {
            $association->setConstant($value);
        }
        else
        {
            $association = new Association();
            $association->setNameInModel($nameInModel);
            $association->setConstant($value);
            $this->association[] = $association;
        }
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
                if(implode("", $next) != "")
                {
                    $model = $this->build($header, $next);
                    if($model->valid() && $model->save())
                    {
                        $count++;
                    }
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
        foreach($this->association as $association)
        {
            if($association->index)
            {
                if($association->fill($model, $associative))
                {
                    $value = $model->{$association->nameInModel};
                    if($value && !empty($value))
                    {
                        $model = $model->get(array($association->nameInModel => $value))->getModel();
                        break;
                    }
                }
            }
        }
        foreach($this->association as $association)
        {
            $association->fill($model, $associative);
        }
        return $model;
    }

    public function debug($number, $config = array())
    {
        $tmp = array();
        $list = array();
        $this->init($config);
        if($header = $this->getCore()->next())
        {
            while($data = $this->getCore()->next())
            {
                if($data == null) break;
                $tmp = $data;
                $model = $this->build($header, $data);
                $number--;
                if($number >= 0)
                    $list[] = $model;
            }
        }
        print_debug($header, $tmp, $list);
    }
}