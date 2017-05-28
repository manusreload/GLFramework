<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 16/11/16
 * Time: 11:07
 */

namespace GLFramework\Twig;

/**
 * Class Extra
 *
 * @package GLFramework\Twig
 */
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
     *
     * @param $view
     */
    public function __construct($view)
    {
        $this->view = $view;
    }

    /**
     * TODO
     *
     * @param $filter
     */
    public function addFilter($filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * TODO
     *
     * @param $function
     */
    public function addFunction($function)
    {
        $this->functions[] = $function;
    }

    /**
     * TODO
     *
     * @param $key
     * @param $value
     */
    public function addGlobal($key, $value)
    {
        $this->globals[$key] = $value;
    }

    /**
     * TODO
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * TODO
     *
     * @return array
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * TODO
     *
     * @return array
     */
    public function getGlobals()
    {
        return $this->globals;
    }
}
