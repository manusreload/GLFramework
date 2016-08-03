<?php
/**
 *     GLFramework, small web application framework.
 *     Copyright (C) 2016.  Manuel Muñoz Rosa
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 17:26
 */

namespace GLFramework;


use GLFramework\Module\ModuleManager;

class View
{
    private $filters = array();
    private $twig;
    /**
     * @var Controller
     */
    private $controller;

    /**
     * View constructor.
     * @param $controller Controller
     */
    public function __construct($controller)
    {
        $this->controller = $controller;
        $directories = ModuleManager::getInstance()->getViews($controller->module);
        $loader = new \Twig_Loader_Filesystem($directories);
        $fs = new Filesystem("twig_cache");
        $fs->mkdir();
        $config = array();
        $config['cache'] = $fs->getAbsolutePath();
//        print_debug($config);
        $this->twig = new \Twig_Environment($loader, array());
        Events::fire('onViewCreated', array(&$this->twig));
//        $this->twig->setCache($fs->getAbsolutePath());
        $this->twig->addGlobal('config', $this->controller->config);
        $this->twig->addGlobal('_GET', $_GET);
        $this->twig->addGlobal('_POST', $_POST);
        $this->twig->addGlobal('_REQUEST', $_REQUEST);
        $this->twig->addGlobal('_SERVER', $_SERVER);
        $this->twig->addGlobal('this', $this->controller);
        $this->twig->addGlobal('render', $this);
        $this->twig->addGlobal('manager', ModuleManager::getInstance());
        $this->twig->addGlobal('mainconfig', Bootstrap::getSingleton()->getConfig());
        $this->twig->addFunction(new \Twig_SimpleFunction('fire', array($this, 'fireEvent')));
        $this->twig->addFilter(new \Twig_SimpleFilter('active', array($this, 'isHrefActive')));
        $this->twig->addFilter(new \Twig_SimpleFilter('fecha_hora', array($this, 'parseFechaHora')));
        $this->twig->addFilter(new \Twig_SimpleFilter('fecha', array($this, 'parseFecha')));
        $this->twig->addFilter(new \Twig_SimpleFilter('hora', array($this, 'parseHora')));
        $this->twig->addFilter(new \Twig_SimpleFilter('debug', array($this, 'debug')));
        $this->twig->addFilter(new \Twig_SimpleFilter('number', array($this, 'isNumber')));
        $this->twig->addFilter(new \Twig_SimpleFilter('implode', array($this, 'implode')));
    }

    /**
     * Devuelve la vista renderizada
     * @param null $data
     * @param array $params
     * @return array|null|string
     */
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

    /**
     * Devuelve la plantilla renderizada
     * @param $template
     * @param array $data
     * @return string
     */
    public function display($template, $data = array())
    {
        return $this->getTwig()->render($template, $data);
    }

    /**
     * Devulve una vista compatible con los clientes de correo
     * @param $template
     * @param array $data
     * @param array $css
     * @return string
     */
    public function mail($template, $data = array(), &$css = array())
    {
        $this->twig->addFunction(new \Twig_SimpleFunction('css', function($file) use(&$css)
        {
            $css[] = $file;
        }));
        $template = $this->twig->loadTemplate($template);
        return $template->render($data);
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
        if(!$fecha || strpos($fecha, "0000-00") !== FALSE) return "";
        $time = strtotime($fecha);
        return date("d-m-Y", $time);
    }

    public function parseHora($fecha)
    {
        if(!$fecha) return "";
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

    public function fireEvent($name, $args = array())
    {
        return implode("\n", Events::fire($name, $args));
    }

    /**
     * @return Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param Controller $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    


}