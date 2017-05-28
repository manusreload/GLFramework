<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 16/11/16
 * Time: 11:13
 */

namespace GLFramework\Twig;

/**
 * Interface IExtra
 *
 * @package GLFramework\Twig
 */
interface IExtra
{
    /**
     * TODO
     *
     * @return mixed
     */
    public function getFilters();

    /**
     * TODO
     *
     * @return mixed
     */
    public function getFunctions();

    /**
     * TODO
     *
     * @return mixed
     */
    public function getGlobals();
}
