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
 * Date: 1/03/16
 * Time: 10:19
 */

namespace GLFramework\DaMa;


use GLFramework\Controller;
use GLFramework\DaMa\Manipulators\CSVManipulator;
use GLFramework\DaMa\Manipulators\ManipulatorCore;
use GLFramework\DaMa\Manipulators\XLSManipulator;
use GLFramework\DaMa\Manipulators\XLSXManipulator;
use GLFramework\Log;
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
    private $current = 0;
    private $result = array();

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

    /**
     * @param $nameInModel
     * @return Association|null
     */
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
        return $association;
    }
    public function defaultValue($nameInModel, $def)
    {
        if($association = $this->getAssociation($nameInModel))
        {
            $association->setDefaultValue($def);
        }
        else
        {
            $association = new Association();
            $association->setNameInModel($nameInModel);
            $association->setDefaultValue($def);
            $this->association[] = $association;
        }
        return $association;
    }

    public function manipulator($manipulator, $nameInModel, $nameInManipulator, $fn = null)
    {
        if($association = $this->getAssociation($nameInModel))
        {
            $association->setManipulator($manipulator);
            $association->setNameInManipulator($nameInManipulator);
        }
        else
        {
            $association = new Association();
            $association->setManipulator($manipulator);
            $association->setNameInModel($nameInModel);
            $association->setNameInManipulator($nameInManipulator);
            $association->setManipulatorParser($fn);
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

    public function exec($config = array(), &$models = array())
    {
        $this->result = array();
        $this->current = 0;
        $count = 0;
        $this->init($config);
        $offset = $config['start'];
        $size = $config['size'];
        $total = 0;
        if($header = $this->getCore()->next())
        {
            $this->current++;
            while($next = $this->getCore()->next())
            {
                if($offset && $size)
                {
                    if($total >= $offset && $total < ($offset + $size))
                    {
                        $total++;
                    }
                    else
                    {
                        continue;
                    }
                }
                if(implode("", $next) != "")
                {
                    $model = $this->build($header, $next);
                    if($model && $model->valid() && $model->save(true))
                    {
                        $models[] = $model;
                        $this->result[$this->current] = $model;
                        $count++;
                    }
                }
                $this->current++;
            }
            return $count;
        }

        return false;
    }

    function getNext()
    {
        $item =  $this->getCore()->next();
        return $item;
    }
    /**
     * @param $controller Controller
     * @param array $config
     * @param array $models
     * @return bool|int
     */
    public function preview($controller, $config = array(), &$models = array(), $max = 0)
    {
        $this->result = array();
        $this->current = 0;
        $buffer = "";
        $count = 0;
        $this->init($config);
        if($header = $this->getNext())
        {
            $this->current++;
            $buffer .= "<table class='table table-bordered'>";
            while($next = $this->getNext())
            {
                if(implode("", $next) != "")
                {
                    $model = $this->build($header, $next);

                    if($model && $model->valid())
                    {
                        if($count == 0)
                        {
                            $buffer .= "<tr>";
                            foreach ($model->getFields() as $item)
                            {
                                $buffer .= "<th>$item</th>";
                            }
                            $buffer .= "<th>Actualizar</th>";
                            $buffer .= "</tr>";
                        }

                        $models[] = $model;
                        $this->result[$this->current] = $model;
                        $buffer .= "<tr>";
                        foreach ($model->getFields() as $item)
                        {
                            $buffer .= "<td>{$model->getFieldValue($item)}</td>";
                        }
                        $buffer .= "<td>" . ($model->exists()?"Si":"No") ."</td>";
                        $buffer .= "</tr>";
                        $count ++;
                    }
                }
                if($max && $count >= $max) break;
                $this->current++;
            }
            $buffer .= "</table>";
            $controller->addMessage("Total Items: " . $count, "info");
            return $buffer;
        }
        Log::d("Headers: " . print_r($header, true));
        $controller->addMessage("Error reading headers!", "danger");
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
        foreach($this->association as $association)
        {
            if($association->required)
            {
                $value = $model->{$association->nameInModel};
                if(empty($value)) return false;
            }
        }
        foreach($this->association as $association)
        {
            if($association->filterObject)
            {
                if(!call_user_func($association->filterObject, $model))
                {
                    return false;
                }
            }
        }
        foreach($this->association as $association)
        {
            if($association->manipulator)
            {
                $current = $association->manipulator->result[$this->current];
                $model->{$association->nameInModel} = $association->getManipulatorParser($current->{$association->nameInManipulator}, $current);
            }
        }
        return $model;
    }

    public function debug($count = 0, $config = array())
    {
        $tmp = array();
        $list = array();
        $this->init($config);
        $number = $count;
        if($header = $this->getCore()->next())
        {
            while($data = $this->getCore()->next())
            {
                if($data == null) break;
                $tmp = $data;
                $model = $this->build($header, $data);
                $number--;
                if($model)
                {
                    if(($count == 0 || $number >= 0) && $model && $model->valid())
                        $list[] = $model;
                }
            }
        }
        print_debug($header, $tmp, $list);
    }

    /**
     * @return DataExample
     * 
     */
    public function example()
    {
        $example = new DataExample();
        foreach($this->association as $association)
        {
            foreach ($association->nameInFile as $item)
            {
                $example->addColumn($association->nameInModel, $item);
            }
        }
        return $example;
    }

    public function getModeByFile($file, $extension = null)
    {
        if(!$extension) $extension = substr($file, strrpos($file, "."));
        if(strpos($file, ".") !== FALSE)
        {
            if($extension == ".csv") return DATA_MANIPULATION_CREATE_MODE_CSV;
            if($extension == ".xls") return DATA_MANIPULATION_CREATE_MODE_XLS;
            if($extension == ".xlsx") return DATA_MANIPULATION_CREATE_MODE_XLSX;
            if($extension == ".ods") return DATA_MANIPULATION_CREATE_MODE_ODS;
        }
    }

    public function setFileInput($file, $mode = DATA_MANIPULATION_CREATE_MODE_AUTO, $extension = null)
    {
        $this->setFilename($file);
        if($mode == DATA_MANIPULATION_CREATE_MODE_AUTO)
        {
            $mode = $this->getModeByFile($file, $extension);
        }
        if($mode == DATA_MANIPULATION_CREATE_MODE_ODS) $this->setCore(new CSVManipulator());
        else if($mode == DATA_MANIPULATION_CREATE_MODE_XLS) $this->setCore(new XLSManipulator());
        else if($mode == DATA_MANIPULATION_CREATE_MODE_XLSX) $this->setCore(new XLSXManipulator());
        else $this->setCore(new CSVManipulator());
    }

}