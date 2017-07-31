<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 20/12/16
 * Time: 15:42
 */

namespace GLFramework\Media;

/**
 * Class JavascriptMedia
 *
 * @package GLFramework\Media
 */
class JavascriptMedia extends Media
{
    /**
     * TODO
     *
     * @param $src
     * @return string
     */
    protected function get($src)
    {
        // TODO: Implement get() method.
        if(isset($this->options['require'])) {

            return "<script src='$src' data-main='{$this->options['require']}'></script>";
        }
        return "<script src='$src'></script>";
    }
}
