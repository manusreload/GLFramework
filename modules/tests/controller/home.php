<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 24/02/16
 * Time: 15:02
 */

namespace GLFramework\Modules\Tests;


use GLFramework\Controller;
use GLFramework\DaMa\DataManipulation;
use GLFramework\Filesystem;

class tests extends Controller
{
    var $name = "Pruebas framework";
    var $admin = true;

    public function run()
    {
        $filesystem = new Filesystem("testfile-no-extensions");
        $filesystem->write("hello. Current time is: " . date("Y-m-d H:i:s"));
        $url = $filesystem->url();
        $this->addMessage("File: <a href=\"$url\">Download</a>");
    }

}