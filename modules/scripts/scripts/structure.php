<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 24/05/16
 * Time: 10:17
 */

/**
 * $controller Controller
 */
global $controller;
if($controller instanceof \GLFramework\Controller)
{
    if(isset($_GET['table']))
    {
        
        $controller->getDb()->select("DESCRIBE ?");
    }
    else
    {
        echo "Please provide 'table' param!\n";
    }
}