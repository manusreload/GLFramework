<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 16/6/17
 * Time: 17:08
 * Boots the framework and check scheduled tasks.
 */
if (!file_exists("vendor/autoload.php")) {
    $path = realpath(".");
    die('I can\'t find \'vendor/autoload.php\' in the current path: ' . $path . '. Ensure that is valid and/or execute \'composer update\' to install the framework!');
}
require_once "vendor/autoload.php";

$boot = new \GLFramework\Bootstrap(".");
$boot->init();
