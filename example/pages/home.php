<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 19/1/16
 * Time: 19:11
 */

namespace MyNameSpace;

class home extends \GLFramework\Controller
{

    var $variable;
    public function run()
    {
        // TODO: Implement run() method.
        $this->variable = "Hola mundo";
    }
}