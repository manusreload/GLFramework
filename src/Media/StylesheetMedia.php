<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 20/12/16
 * Time: 15:54
 */

namespace GLFramework\Media;

/**
 * Class StylesheetMedia
 *
 * @package GLFramework\Media
 */
class StylesheetMedia extends Media
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
        return "<link href='$src' rel='stylesheet' />";
    }
}
