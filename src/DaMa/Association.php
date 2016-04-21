<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 10/03/16
 * Time: 16:23
 */

namespace GLFramework\DaMa;


use GLFramework\Model;

class Association
{
    public $nameInFile = array();
    public $nameInModel;
    public $constant = false;
    public $index = false;
    public $parser = null;

    /**
     * @return mixed
     */
    public function getNameInFile()
    {
        return $this->nameInFile;
    }

    /**
     * @param mixed $nameInFile
     */
    public function addNameInFile($nameInFile)
    {
        $this->nameInFile[] = $nameInFile;
    }

    /**
     * @return mixed
     */
    public function getNameInModel()
    {
        return $this->nameInModel;
    }

    /**
     * @param mixed $nameInModel
     */
    public function setNameInModel($nameInModel)
    {
        $this->nameInModel = $nameInModel;
    }

    /**
     * @return null
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @param null $parser
     */
    public function setParser($parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return boolean
     */
    public function isConstant()
    {
        return $this->constant;
    }

    /**
     * @param boolean $constant
     */
    public function setConstant($constant)
    {
        $this->constant = $constant;
    }

    /**
     * @param $model Model
     * @param $row
     * @return bool
     */
    public function fill($model, $row)
    {
        if(!$this->constant)
        {
            foreach($this->nameInFile as $subkey)
            {
                if(isset($row[$subkey]))
                {
                    $model->{$this->nameInModel} = $this->parse($row[$subkey]);
                    return true;
                }
            }
        }
        else
        {
            $model->{$this->nameInModel} = $this->constant;
            return true;
        }
        return false;
    }

    private function parse($value)
    {
        if($this->parser != null)
        {
            return call_user_func($this->parser, $value);
        }
        return $value;
    }

    public function index()
    {
        $this->index = true;
        return $this;
    }

}