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
                    if($model && $model->valid() && $model->save())
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
     * @param $controller Controller
     * @param array $config
     * @return bool|int
     */
    public function preview($controller, $config = array())
    {
        $buffer = "";
        $count = 0;
        $this->init($config);
        if($header = $this->getCore()->next())
        {
            $buffer .= "<table class='table table-bordered'>";
            while($next = $this->getCore()->next())
            {
                if(implode("", $next) != "")
                {
                    $model = $this->build($header, $next);
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
                    if($model && $model->valid() && $model->save())
                    {
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
            }
            $buffer .= "</table>";
            $controller->addMessage("Total Items: " . $count, "info");
            return $buffer;
        }

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
                if($number >= 0 && $model->valid())
                    $list[] = $model;
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

    public function getModeByFile($file)
    {
        if(strpos($file, ".") !== FALSE)
        {
            $ext = substr($file, strrpos($file, "."));
            if($ext == ".csv") return DATA_MANIPULATION_CREATE_MODE_CSV;
            if($ext == ".xls") return DATA_MANIPULATION_CREATE_MODE_XLS;
            if($ext == ".xlsx") return DATA_MANIPULATION_CREATE_MODE_XLSX;
            if($ext == ".ods") return DATA_MANIPULATION_CREATE_MODE_ODS;
        }
    }

    public function setFileInput($file, $mode = DATA_MANIPULATION_CREATE_MODE_AUTO)
    {
        $this->setFilename($file);
        if($mode == DATA_MANIPULATION_CREATE_MODE_AUTO)
        {
            $mode = $this->getModeByFile($file);
        }
        if($mode == DATA_MANIPULATION_CREATE_MODE_ODS) $this->setCore(new CSVManipulator());
        else if($mode == DATA_MANIPULATION_CREATE_MODE_XLS) $this->setCore(new XLSManipulator());
        else if($mode == DATA_MANIPULATION_CREATE_MODE_XLSX) $this->setCore(new XLSXManipulator());
        else $this->setCore(new CSVManipulator());
    }
}