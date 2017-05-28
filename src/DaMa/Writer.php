<?php
/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 24/10/2016
 * Time: 13:54
 */

namespace GLFramework\DaMa;

use GLFramework\DaMa\Writers\WriterBase;
use GLFramework\Model;
use GLFramework\ModelResult;

/**
 * Class Writer
 *
 * @package GLFramework\DaMa
 */
class Writer
{
    /**
     * @var ModelResult|Model[]
     */
    private $models;
    /**
     * @var WriterBase
     */
    private $writer;

    private $associations = array();
    private $parsers = array();

    /**
     * @var IFilter
     */
    private $filter = false;

    /**
     * Writer constructor.
     *
     * Writer constructor.
     * @param $models
     */
    public function __construct($models)
    {
        $this->models = $models;
    }


    /**
     * TODO
     *
     * @return \GLFramework\Model[]
     */
    public function getModels()
    {
        if ($this->models instanceof ModelResult) {
            return $this->models->getModels();
        }
        return $this->models;
    }

    /**
     * TODO
     *
     * @param $list
     */
    public function addModels($list)
    {
        foreach ($list as $item) {
            $this->models[] = $item;
        }
    }

    /**
     * TODO
     *
     * @param $nameInModel
     * @param $nameInFile
     * @param null $fn
     */
    public function field($nameInModel, $nameInFile, $fn = null)
    {
        $association = new Association();
        $association->setNameInModel($nameInModel);
        $association->addNameInFile($nameInFile);
        $association->setParser($fn);
        $this->associations[$nameInModel] = $association;
    }

    /**
     * TODO
     *
     * @param $writer WriterBase
     */
    public function setWriter($writer)
    {
        $this->writer = $writer;
    }

    /**
     * TODO
     *
     * @param $output
     */
    public function exec($output)
    {
        $this->writer->open($output);
        foreach ($this->models as $model) {
            if ($this->filter === false || $this->filter->filter($model)) {
                $this->writer->write($model, $this->associations);
            }
        }
        $this->writer->close();
    }

    /**
     * TODO
     *
     * @param bool|IFilter $filter IFilter
     */
    public function setFilter($filter = false)
    {
        $this->filter = $filter;
    }
}
