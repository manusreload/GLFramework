<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 16/11/16
 * Time: 11:07
 */

namespace GLFramework\Twig;


class Extra implements IExtra
{



    /**
     * @var array Array of TwigFilters
     */
    private $filters = array();
    private $functions = array();
    private $globals = array();
    private $view;

    /**
     * Extra constructor.
     * @param $view
     */
    public function __construct($view)
    {
        $this->view = $view;
    }


    public function addFilter($filter)
    {
        $this->filters[] = $filter;
    }

    public function addFunction($function)
    {
        $this->functions[] = $function;
    }

    public function addGlobal($key, $value)
    {
        $this->globals[$key] = $value;
    }
    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    public function getGlobals()
    {
        return $this->globals;
    }
}