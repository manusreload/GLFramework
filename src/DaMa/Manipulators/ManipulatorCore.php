<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 1/03/16
 * Time: 10:22
 */

namespace GLFramework\DaMa\Manipulators;


abstract class ManipulatorCore
{

    abstract public function open($file, $config = array());
    abstract public function next();

    public function setSheet($index)
    {

    }

}