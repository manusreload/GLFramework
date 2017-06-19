<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 15/06/17
 * Time: 16:02
 */
if (!file_exists("vendor/autoload.php")) {
    $path = realpath(".");
    die('I can\'t find \'vendor/autoload.php\' in the current path: ' . $path . '. Ensure that is valid and/or execute \'composer update\' to install the framework!');
}
require_once "vendor/autoload.php";
return \GLFramework\Bootstrap::router(".");