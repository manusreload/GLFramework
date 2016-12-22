<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 16/11/16
 * Time: 11:11
 */

namespace GLFramework\Twig;


use GLFramework\Bootstrap;
use GLFramework\Events;
use GLFramework\Media\JavascriptMedia;
use GLFramework\Media\StylesheetMedia;
use GLFramework\Module\ModuleManager;
use GLFramework\View;

class FrameworkExtras extends Extra
{

    private $view;

    /**
     * FrameworkExtras constructor.
     * @param $view View
     */
    public function __construct($view)
    {
        $this->view = $view;
        $this->addGlobal('config', $view->getController()->config);
        $this->addGlobal('_GET', $_GET);
        $this->addGlobal('_POST', $_POST);
        $this->addGlobal('_REQUEST', $_REQUEST);
        $this->addGlobal('_SERVER', $_SERVER);
        $this->addGlobal('this', $view->getController());
        $this->addGlobal('render', $view);
        $this->addGlobal('manager', ModuleManager::getInstance());
        $this->addGlobal('mainconfig', Bootstrap::getSingleton()->getConfig());
        $this->addGlobal('bootstrap', Bootstrap::getSingleton());

        $this->addFunction(new \Twig_SimpleFunction('fire', array($this, 'fireEvent')));
        $this->addFunction(new \Twig_SimpleFunction('phpversion', 'phpversion'));
        $this->addFunction(new \Twig_SimpleFunction('js', array($this, 'js')));
        $this->addFunction(new \Twig_SimpleFunction('css', array($this, 'css')));
        $this->addFilter(new \Twig_SimpleFilter('active', array($this, 'isHrefActive')));
        $this->addFilter(new \Twig_SimpleFilter('fecha_hora', array($this, 'parseFechaHora')));
        $this->addFilter(new \Twig_SimpleFilter('fecha', array($this, 'parseFecha')));
        $this->addFilter(new \Twig_SimpleFilter('hora', array($this, 'parseHora')));
        $this->addFilter(new \Twig_SimpleFilter('debug', array($this, 'debug')));
        $this->addFilter(new \Twig_SimpleFilter('number', array($this, 'isNumber')));
        $this->addFilter(new \Twig_SimpleFilter('implode', array($this, 'implode')));
    }

    public function fireEvent($name, $args = array())
    {
        return implode("\n", Events::fire($name, $args));
    }

    public function isHrefActive($url)
    {
        if(strpos($_SERVER['REQUEST_URI'], $url) !== FALSE)
        {
            return 'active';
        }
        return "";
    }

    public function parseFechaHora($fecha, $formatFecha = false, $formatHora = false)
    {
        return $this->parseFecha($fecha, $formatFecha) . " " . $this->parseHora($fecha, $formatHora);
    }
    public function parseFecha($fecha, $formatFecha = false)
    {
        if(!$formatFecha) $formatFecha = "d-m-Y";
        if(!$fecha || strpos($fecha, "0000-00") !== FALSE) return "";
        $time = strtotime($fecha);
        return date($formatFecha, $time);
    }

    public function parseHora($fecha, $formatHora = false)
    {
        if(!$formatHora) $formatHora = "H:i:s";
        if(!$fecha) return "";
        $time = strtotime($fecha);
        return date($formatHora, $time);
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

    public function js($src, $options = array())
    {
        $js = new JavascriptMedia($src, $options);
        return $js->getBrowserCode();
    }
    public function css($src, $options = array())
    {
        $css = new StylesheetMedia($src, $options);
        return $css->getBrowserCode();
    }

}