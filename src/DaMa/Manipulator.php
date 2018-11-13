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

use GLFramework\DaMa\Manipulators\CSVManipulator;
use GLFramework\DaMa\Manipulators\ManipulatorCore;
use GLFramework\DaMa\Manipulators\XLSManipulator;
use GLFramework\DaMa\Manipulators\XLSXManipulator;
use GLFramework\Log;
use GLFramework\Model;

/**
 * Class Manipulator
 *
 * @package GLFramework\DaMa
 */
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

    private $callback;

    /**
     * TODO
     *
     * @param $callback
     */
    public function setParseCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * TODO
     *
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * TODO
     *
     * @param $filename
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * TODO
     *
     * @return ManipulatorCore
     */
    public function getCore()
    {
        return $this->core;
    }

    /**
     * TODO
     *
     * @param $core
     * @return $this
     */
    public function setCore($core)
    {
        $this->core = $core;
        return $this;
    }

    /**
     * TODO
     *
     * @param $modelName
     * @return $this
     */
    public function model($modelName)
    {
        $this->modelName = $modelName;
        return $this;
    }

    /**
     * @return Association[]
     */
    public function getAssociations()
    {
        return $this->association;
    }

    /**
     * @return mixed
     */
    public function getModelName()
    {
        return $this->modelName;
    }


    /**
     * TODO
     *
     * @param $nameInFile
     * @param $nameInModel
     * @param null $fn
     * @return Association|null
     */
    public function field($nameInFile, $nameInModel, $fn = null)
    {
        if ($association = $this->getAssociation($nameInModel)) {
            $association->addNameInFile($nameInFile);
        } else {
            $association = new Association();
            $association->addNameInFile($nameInFile);
            $association->setNameInModel($nameInModel);
            $association->setParser($fn);
            $this->association[] = $association;
        }
        return $association;
    }

    /**
     * TODO
     *
     * @param $nameInModel
     * @param $value
     * @return Association|null
     */
    public function constant($nameInModel, $value)
    {
        if ($association = $this->getAssociation($nameInModel)) {
            $association->setConstant($value);
        } else {
            $association = new Association();
            $association->setNameInModel($nameInModel);
            $association->setConstant($value);
            $this->association[] = $association;
        }
        return $association;
    }

    /**
     * TODO
     *
     * @param $nameInModel
     * @param $def
     * @return Association|null
     */
    public function defaultValue($nameInModel, $def)
    {
        if ($association = $this->getAssociation($nameInModel)) {
            $association->setDefaultValue($def);
        } else {
            $association = new Association();
            $association->setNameInModel($nameInModel);
            $association->setDefaultValue($def);
            $this->association[] = $association;
        }
        return $association;
    }

    /**
     * TODO
     *
     * @param $manipulator
     * @param $nameInModel
     * @param $nameInManipulator
     * @param null $fn
     */
    public function manipulator($manipulator, $nameInModel, $nameInManipulator, $fn = null)
    {
        if ($association = $this->getAssociation($nameInModel)) {
            $association->setManipulator($manipulator);
            $association->setNameInManipulator($nameInManipulator);
        } else {
            $association = new Association();
            $association->setManipulator($manipulator);
            $association->setNameInModel($nameInModel);
            $association->setNameInManipulator($nameInManipulator);
            $association->setManipulatorParser($fn);
            $this->association[] = $association;
        }
    }

    /**
     * TODO
     *
     * @param $index
     */
    public function sheet($index)
    {
        $this->currentSheet = $index;
    }

    /**
     * TODO
     *
     * @param array $config
     * @param array $models
     * @return bool|int
     */
    public function exec($config = array(), &$models = array())
    {
        $this->result = array();
        $this->current = 0;
        $count = 0;
        $this->init($config);
        $offset = $config['start'];
        $size = $config['size'];
        $total = 0;
        if ($header = $this->getCore()->next()) {
            $this->current++;
            while ($next = $this->getCore()->next()) {
                if ($offset && $size) {
                    if ($total >= $offset && $total < ($offset + $size)) {
                        $total++;
                    } else {
                        continue;
                    }
                }
                if (implode('', $next) != '') {
                    $modelSource = null;
                    $model = $this->build($header, $next, $modelSource);
                    if ($model instanceof Model) {
                        if ($model && $model->valid() && $model->save(true)) {
                            if ($this->callback) {
                                call_user_func($this->callback, $model, $modelSource);
                            }
                            $models[] = $model;
                            $this->result[$this->current] = $model;
                            $count++;
                        }
                    }
                }
                $this->current++;
            }
            return $count;
        }

        return false;
    }

    /**
     * TODO
     *
     * @return mixed
     */
    function getNext()
    {
        $item = $this->getCore()->next();
        return $item;
    }

    /**
     * TODO
     *
     * @param $controller
     * @param array $config
     * @param array $models
     * @param int $max
     * @return bool|string
     */
    public function preview($controller, $config = array(), &$models = array(), $max = 0)
    {
        $this->result = array();
        $this->current = 0;
        $buffer = '';
        $count = 0;
        $this->init($config);
        if ($header = $this->getNext()) {
            $this->current++;
            $buffer .= "<table class='table table-bordered' border='1'>";
            while ($next = $this->getNext()) {
                if (implode('', $next) != '') {
                    $model = $this->build($header, $next);

                    if ($model && $model->valid()) {
                        if ($count == 0) {
                            $buffer .= '<tr>';
                            foreach ($model->getFields() as $item) {
                                $buffer .= "<th>$item</th>";
                            }
                            $buffer .= '<th>Actualizar</th>';
                            $buffer .= '</tr>';
                        }

                        $models[] = $model;
                        $this->result[$this->current] = $model;
                        $buffer .= '<tr>';
                        foreach ($model->getFields() as $item) {
                            $buffer .= "<td>{$model->getFieldValue($item)}</td>";
                        }
                        $buffer .= '<td>' . ($model->exists() ? 'Si' : 'No') . '</td>';
                        $buffer .= '</tr>';
                        $count++;
                    }
                }
                if ($max && $count >= $max) {
                    break;
                }
                $this->current++;
            }
            $buffer .= '</table>';
            $controller->addMessage('Total Items: ' . $count, 'info');
            return $buffer;
        }
        Log::d('Headers: ' . print_r($header, true));
        $controller->addMessage('Error reading headers!', 'danger');
        return false;
    }

    /**
     * TODO
     *
     * @param $header
     * @param $row
     * @param null $modelSource
     * @return bool
     */
    public function build($header, $row, &$modelSource = null)
    {
        $associative = array();
        foreach ($header as $key => $value) {
            if(bin2hex(substr($value, 0, 3)) == "efbbbf") {
                $value = substr($value, 3);
            }
            $associative[$value] = $row[$key];
        }
        $model = new $this->modelName();
        // Query for indexs
        $indexs = array();
        foreach ($this->association as $association) { // First search for values indexes
            if ($association->index) {
                if ($association->fill($model, $associative)) {
                    $value = $model->{$association->nameInModel};
                    if ($value && !empty($value)) {
                        $indexs[$association->nameInModel] = $value;
                    }
                }
            }
        }

        if (!empty($indexs)) { // The search for theses indexes
            $result = $model->get($indexs);
            $model = $result->getModel();
            $modelSource = $result->getModel();
        }

        foreach ($this->association as $association) {
            $association->fill($model, $associative);
        }
        foreach ($this->association as $association) {
            if ($association->required) {
                $value = $model->{$association->nameInModel};
                if (empty($value)) {
                    return false;
                }
            }
        }
        foreach ($this->association as $association) {
            if ($association->filterObject) {
                if (!call_user_func($association->filterObject, $model)) {
                    return false;
                }
            }
        }
        foreach ($this->association as $association) {
            if ($association->manipulator) {
                $current = $association->manipulator->result[$this->current];
                $model->{$association->nameInModel} = $association->getManipulatorParser($current->{$association->nameInManipulator},
                    $current);
            }
        }
        return $model;
    }

    /**
     * TODO
     *
     * @param int $count
     * @param array $config
     */
    public function debug($count = 0, $config = array())
    {
        $tmp = array();
        $list = array();
        $this->init($config);
        $number = $count;
        if ($header = $this->getCore()->next()) {
            while ($data = $this->getCore()->next()) {
                if ($data == null) {
                    break;
                }
                $tmp[] = $data;
                $model = $this->build($header, $data);
                $number--;
                if ($model) {
                    if (($count == 0 || $number >= 0) && $model && $model->valid()) {
                        $list[] = $model;
                    }
                }
            }
        }
        print_debug($header, $tmp, $list);
    }

    /**
     * TODO
     *
     * @return DataExample
     */
    public function example()
    {
        $example = new DataExample();
        foreach ($this->association as $association) {
            foreach ($association->nameInFile as $item) {
                $example->addColumn($association->nameInModel, $item);
            }
        }
        return $example;
    }

    /**
     * TODO
     *
     * @param $file
     * @param null $extension
     * @return int
     */
    public function getModeByFile($file, $extension = null)
    {
        if (!$extension) {
            $extension = strtolower(substr($file, strrpos($file, '.')));
        }
        if (strpos($extension, '.') !== false) {
            if ($extension == '.csv') {
                return DATA_MANIPULATION_CREATE_MODE_CSV;
            }
            if ($extension == '.xls') {
                return DATA_MANIPULATION_CREATE_MODE_XLS;
            }
            if ($extension == '.xlsx') {
                return DATA_MANIPULATION_CREATE_MODE_XLSX;
            }
            if ($extension == '.ods') {
                return DATA_MANIPULATION_CREATE_MODE_ODS;
            }
        }
    }

    /**
     * TODO
     *
     * @param $file
     * @param int $mode
     * @param null $extension
     */
    public function setFileInput($file, $mode = DATA_MANIPULATION_CREATE_MODE_AUTO, $extension = null)
    {
        $this->setFilename($file);
        if ($mode == DATA_MANIPULATION_CREATE_MODE_AUTO) {
            $mode = $this->getModeByFile($file, $extension);
        }
        if ($mode == DATA_MANIPULATION_CREATE_MODE_ODS) {
            $this->setCore(new CSVManipulator());
        } else if ($mode == DATA_MANIPULATION_CREATE_MODE_XLS) {
            $this->setCore(new XLSManipulator());
        } else if ($mode == DATA_MANIPULATION_CREATE_MODE_XLSX) {
            $this->setCore(new XLSXManipulator());
        } else {
            $this->setCore(new CSVManipulator());
        }
    }

    /**
     * TODO
     *
     * @param $nameInModel
     * @return Association|null
     */
    private function getAssociation($nameInModel)
    {
        foreach ($this->association as $association) {
            if ($association->getNameInModel() == $nameInModel) {
                return $association;
            }
        }
        return null;
    }

    /**
     * TODO
     *
     * @param array $config
     */
    private function init($config = array())
    {
        $this->getCore()->open($this->getFilename(), $config);
        if ($this->currentSheet !== null) {
            $this->getCore()->setSheet($this->currentSheet);
        }
    }
}
