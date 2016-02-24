<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 17:26
 */

namespace GLFramework;


class View
{
    private $filters = array();
    private $twig;
    private $controller;

    /**
     * View constructor.
     * @param $controller Controller
     */
    public function __construct($controller)
    {
        $config = Bootstrap::getSingleton()->getConfig();
        $dir = Bootstrap::getSingleton()->getDirectory();
        $directoriesTmp = $config['app']['views'];
        if(!is_array($directoriesTmp)) $directoriesTmp = array($directoriesTmp);
        $directories = array();
        foreach($directoriesTmp as &$directory)
        {
            $directory = $dir . "/" . $directory;
            if(is_dir($directory))
            {
                $directories[] = $directory;
            }
        }
        $directories[] = __DIR__ . "/../views";
        $this->controller = $controller;
        $loader = new \Twig_Loader_Filesystem($directories);
        $this->twig = new \Twig_Environment($loader, array());
        $this->twig->addGlobal('config', Bootstrap::getSingleton()->getConfig());
        $this->twig->addGlobal('_GET', $_GET);
        $this->twig->addGlobal('_POST', $_POST);
        $this->twig->addGlobal('_REQUEST', $_REQUEST);
        $this->twig->addGlobal('this', $this->controller);
        $this->twig->addFilter(new \Twig_SimpleFilter('active', array($this, 'isHrefActive')));
        $this->twig->addFilter(new \Twig_SimpleFilter('fecha_hora', array($this, 'parseFechaHora')));
        $this->twig->addFilter(new \Twig_SimpleFilter('fecha', array($this, 'parseFecha')));
        $this->twig->addFilter(new \Twig_SimpleFilter('hora', array($this, 'parseHora')));
        $this->twig->addFilter(new \Twig_SimpleFilter('debug', array($this, 'debug')));
        $this->twig->addFilter(new \Twig_SimpleFilter('number', array($this, 'isNumber')));
        $this->twig->addFilter(new \Twig_SimpleFilter('implode', array($this, 'implode')));
    }

    public function render($data = null, $params = array())
    {
        if($this->controller->getTemplate() != null)
        {
            $data = $data == null?array():$data;
            $this->twig->addGlobal('params', $params);
            $template = $this->twig->loadTemplate($this->controller->getTemplate());
            return $template->render($data);
        }
        return $data;
    }

    public function addFilter($filter)
    {
        $this->filters[] = $filter;
    }


    public function isHrefActive($url)
    {
        if(strpos($_SERVER['REQUEST_URI'], $url) !== FALSE)
        {
            return 'active';
        }
        return "";
    }

    public function parseFechaHora($fecha)
    {
        return $this->parseFecha($fecha) . " " . $this->parseHora($fecha);
    }
    public function parseFecha($fecha)
    {
        $time = strtotime($fecha);
        return date("d-m-Y", $time);
    }

    public function parseHora($fecha)
    {
        $time = strtotime($fecha);
        return date("H:i:s", $time);
    }

    public function debug($data)
    {
        print_debug($data);
    }

    public function isNumber($data)
    {
        return is_numeric($data);
    }

    public function implode($array, $separator)
    {
        return implode($separator, $array);
    }
    /**
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }


}