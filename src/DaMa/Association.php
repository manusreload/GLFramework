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
 * Date: 10/03/16
 * Time: 16:23
 */

namespace GLFramework\DaMa;

use GLFramework\Model;

/**
 * Class Association
 *
 * @package GLFramework\DaMa
 */
class Association
{
    public $nameInFile = array();
    public $nameInModel;
    public $constant = false;
    public $index = false;
    public $required = false;
    public $parser = null;
    public $manipulatorParser = null;
    public $filterObject = null;
    public $defaultValue = null;
    /**
     * @var Manipulator
     */
    public $manipulator = null;
    public $nameInManipulator = false;

    /**
     * TODO
     *
     * @return mixed
     */
    public function getNameInFile()
    {
        return $this->nameInFile;
    }

    /**
     * TODO
     *
     * @return mixed
     */
    public function getFirstNameInFile()
    {
        return current($this->nameInFile);
    }

    /**
     * TODO
     *
     * @param mixed $nameInFile
     * @return $this
     */
    public function addNameInFile($nameInFile)
    {
        $this->nameInFile[] = $nameInFile;
        return $this;
    }

    /**
     * TODO
     *
     * @return mixed
     */
    public function getNameInModel()
    {
        return $this->nameInModel;
    }

    /**
     * TODO
     *
     * @param mixed $nameInModel
     * @return $this
     */
    public function setNameInModel($nameInModel)
    {
        $this->nameInModel = $nameInModel;
        return $this;
    }

    /**
     * TODO
     *
     * @param mixed $nameInManipulator
     * @return $this
     */
    public function setNameInManipulator($nameInManipulator)
    {
        $this->nameInManipulator = $nameInManipulator;
        return $this;
    }

    /**
     * TODO
     *
     * @param $value
     * @return $this
     */
    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;
        return $this;
    }

    /**
     * TODO
     *
     * @return null
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * TODO
     *
     * @param null $parser
     * @return $this
     */
    public function setParser($parser)
    {
        $this->parser = $parser;
        return $this;
    }

    /**
     * TODO
     *
     * @return bool
     */
    public function isConstant()
    {
        return $this->constant;
    }

    /**
     * TODO
     *
     * @param $constant
     * @return $this
     */
    public function setConstant($constant)
    {
        $this->constant = $constant;
        return $this;
    }

    /**
     * TODO
     *
     * @param $manipulator
     * @return $this
     */
    public function setManipulator($manipulator)
    {
        $this->manipulator = $manipulator;
        return $this;
    }

    /**
     * TODO
     *
     * @param $model Model
     * @param $row
     * @return bool
     */
    public function fill($model, $row)
    {
        if ($this->constant == false) {
            foreach ($this->nameInFile as $subkey) {
                if (isset($this->defaultValue) && $this->defaultValue != null) {
                    $model->{$this->nameInModel} = $this->defaultValue;
                }
                if (isset($row[$subkey])) {
                    $model->{$this->nameInModel} = $this->parse($row[$subkey], $row);
                    return true;
                }
            }
        } else {
            $model->{$this->nameInModel} = $this->constant;
            return true;
        }
        return false;
    }

    /**
     * TODO
     *
     * @param $value
     * @param $row
     * @return mixed
     */
    public function parse($value, $row)
    {
        if ($this->parser != null) {
            return call_user_func($this->parser, $value, $row);
        }
        return $value;
    }

    /**
     * TODO
     *
     * @param $value
     * @param $row
     * @return mixed
     */
    public function getManipulatorParser($value, $row)
    {
        if ($this->manipulatorParser != null) {
            return call_user_func($this->manipulatorParser, $value, $row);
        }
        return $value;
    }

    /**
     * TODO
     *
     * @param null $manipulatorParser
     * @return $this
     */
    public function setManipulatorParser($manipulatorParser)
    {
        $this->manipulatorParser = $manipulatorParser;
        return $this;
    }

    /**
     * TODO
     *
     * @return $this
     */
    public function index()
    {
        $this->index = true;
        return $this;
    }

    /**
     * TODO
     *
     * @return $this
     */
    public function required()
    {
        $this->required = true;
        return $this;
    }

    /**
     * TODO
     *
     * @param $callable
     * @return $this
     */
    public function filter($callable)
    {
        $this->filterObject = $callable;
        return $this;
    }

    /**
     * TODO
     *
     * @param $model Model
     * @param $field
     * @return mixed
     */
    public function get($model, $field)
    {
        $value = $model->getFieldValue($field);
        return $this->parse($value);
    }
}
