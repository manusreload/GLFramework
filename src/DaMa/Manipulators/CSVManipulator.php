<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 1/03/16
 * Time: 10:23
 */

namespace GLFramework\DaMa\Manipulators;


class CSVManipulator extends ManipulatorCore
{

    private $handle;
    public function open($file, $config = array())
    {
        $this->handle = fopen($file, "r");
    }

    public function next()
    {
        return fgetcsv($this->handle, null, ";", "\"");
    }
}