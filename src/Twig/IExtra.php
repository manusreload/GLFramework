<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 16/11/16
 * Time: 11:13
 */

namespace GLFramework\Twig;


interface IExtra
{

    public function getFilters();
    public function getFunctions();
    public function getGlobals();
}