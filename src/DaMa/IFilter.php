<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 23/11/16
 * Time: 17:57
 */

namespace GLFramework\DaMa;

/**
 * Interface IFilter
 *
 * @package GLFramework\DaMa
 */
interface IFilter
{
    /**
     * TODO
     *
     * @param $model
     * @return mixed
     */
    public function filter($model);

}