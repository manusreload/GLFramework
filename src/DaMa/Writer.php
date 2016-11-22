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
     * Writer constructor.
     * @param \GLFramework\Model[]|ModelResult $models
     */
    public function __construct($models)
    {
        $this->models = $models;
    }


    /**
     * @return \GLFramework\Model[]
     */
    public function getModels()
    {
       if($this->models instanceof ModelResult)
       {
           return $this->models->getModels();
       }
        return $this->models;
    }
    
    public function field($nameInModel, $nameInFile, $fn = null)
    {
        $association = new Association();
        $association->setNameInModel($nameInModel);
        $association->addNameInFile($nameInFile);
        $association->setParser($fn);
        $this->associations[$nameInModel] = $association;

    }

    /**
     * @param $writer WriterBase
     */
    public function setWriter($writer)
    {
        $this->writer = $writer;
    }
    
    public function exec($output)
    {
        $this->writer->open($output);
        foreach ($this->models as $model)
        {
            $this->writer->write($model, $this->associations);
        }
        $this->writer->close();
    }
    
    

}