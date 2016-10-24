<?php
/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 24/10/2016
 * Time: 13:57
 */

namespace GLFramework\DaMa\Writers;


use GLFramework\Model;

abstract class WriterBase
{

    var $fp;

    /**
     * @param $model Model
     * @param $map
     * @return mixed
     */
    abstract public function write($model, $map);

    public function open($file)
    {
        $this->fp = fopen($file, "w");
    }

    public function close()
    {
        fclose($this->fp);
    }
}