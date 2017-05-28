<?php
/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 24/10/2016
 * Time: 13:57
 */

namespace GLFramework\DaMa\Writers;

use GLFramework\Model;

/**
 * Class WriterBase
 *
 * @package GLFramework\DaMa\Writers
 */
abstract class WriterBase
{

    var $fp;

    /**
     * TODO
     *
     * @param $model Model
     * @param $map
     * @return mixed
     */
    abstract public function write($model, $map);

    /**
     * TODO
     *
     * @param $file
     */
    public function open($file)
    {
        $this->fp = fopen($file, 'wb');
    }

    /**
     * TODO
     */
    public function close()
    {
        fclose($this->fp);
    }
}
