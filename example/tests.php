<?php
/**
 * Punto de entrada de los test
 * Created by PhpStorm.
 * User: manus
 * Date: 16/03/16
 * Time: 9:49
 */

require_once "../vendor/autoload.php";

$bs = new \GLFramework\Bootstrap(__DIR__);
$bs->setupTest();
