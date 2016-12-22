<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 20/12/16
 * Time: 15:42
 */

namespace GLFramework\Media;


class JavascriptMedia extends Media
{

    protected function get($src)
    {
        // TODO: Implement get() method.

        return "<script src='$src'></script>";
    }
}