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
use GLFramework\Model\Vars;
use GLFramework\Module\ModuleManager;
use GLFramework\View;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class FrameworkExtras
 *
 * @package GLFramework\Twig
 */
class FrameworkExtras extends Extra
{
    private $view;

    /**
     * FrameworkExtras constructor.
     *
     * @param $view View
     */
    public function __construct($view)
    {
        parent::__construct($view);
        $safe =  array('is_safe' => array('html'));

        $this->view = $view;
        $this->addGlobal('config', $view->getController()->config);
        $this->addGlobal('_GET', $_GET);
        $this->addGlobal('_POST', $_POST);
        $this->addGlobal('_REQUEST', $_REQUEST);
        $this->addGlobal('_SERVER', $_SERVER);
        $this->addGlobal('_COOKIE', $_COOKIE);
        $this->addGlobal('this', $view->getController());
        $this->addGlobal('render', $view);
        $this->addGlobal('manager', ModuleManager::getInstance());
        $this->addGlobal('mainconfig', Bootstrap::getSingleton()->getConfig());
        $this->addGlobal('bootstrap', Bootstrap::getSingleton());

        $this->addFunction(new \Twig_SimpleFunction('fire', array($this, 'fireEvent'), $safe));
        $this->addFunction(new \Twig_SimpleFunction('phpversion', 'phpversion'));
        $this->addFunction(new \Twig_SimpleFunction('js', array($this, 'js'), $safe));
        $this->addFunction(new \Twig_SimpleFunction('css', array($this, 'css'), $safe));
        $this->addFunction(new \Twig_SimpleFunction('vars', array($this, 'vars')));
        $this->addFunction(new \Twig_SimpleFunction('meses', array($this, 'meses')));
        $this->addFunction(new \Twig_SimpleFunction('meses', array($this, 'meses')));
        $this->addFunction(new \Twig_SimpleFunction('dump', array($this, 'dump'), $safe));
        $this->addFunction(new \Twig_SimpleFunction('tr', array($this, 'tr'), $safe));
        $this->addFilter(new \Twig_SimpleFilter('active', array($this, 'isHrefActive')));
        $this->addFilter(new \Twig_SimpleFilter('fecha_hora', array($this, 'parseFechaHora')));
        $this->addFilter(new \Twig_SimpleFilter('fecha', array($this, 'parseFecha')));
        $this->addFilter(new \Twig_SimpleFilter('hora', array($this, 'parseHora')));
        $this->addFilter(new \Twig_SimpleFilter('debug', array($this, 'debug'), $safe));
        $this->addFilter(new \Twig_SimpleFilter('number', array($this, 'isNumber')));
        $this->addFilter(new \Twig_SimpleFilter('implode', array($this, 'implode')));
        $this->addFilter(new \Twig_SimpleFilter('icon', array($this, 'icon')));
        $this->addFilter(new \Twig_SimpleFilter('mes', array($this, 'mes')));
        $this->addFilter(new \Twig_SimpleFilter('tr', array($this, 'tr'), $safe));
    }

    /**
     * TODO
     *
     * @param $name
     * @param array $args
     * @return string
     */
    public function fireEvent($name, $args = array())
    {
        return Events::dispatch($name, $args)->getString();
    }

    /**
     * TODO
     *
     * @param $url
     * @return string
     */
    public function isHrefActive($url)
    {
        if (strpos($_SERVER['REQUEST_URI'], $url) !== false) {
            return 'active';
        }
        return '';
    }

    /**
     * TODO
     *
     * @param $fecha
     * @param bool $formatFecha
     * @param bool $formatHora
     * @return string
     */
    public function parseFechaHora($fecha, $formatFecha = false, $formatHora = false)
    {
        return $this->parseFecha($fecha, $formatFecha) . ' ' . $this->parseHora($fecha, $formatHora);
    }

    /**
     * TODO
     *
     * @param $fecha
     * @param bool $formatFecha
     * @return false|string
     */
    public function parseFecha($fecha, $formatFecha = false)
    {
        if (!$formatFecha) {
            $formatFecha = 'd-m-Y';
        }
        if (!$fecha || strpos($fecha, '0000-00') !== false) {
            return '';
        }
        $time = strtotime($fecha);
        return date($formatFecha, $time);
    }

    /**
     * TODO
     *
     * @param $fecha
     * @param bool $formatHora
     * @return false|string
     */
    public function parseHora($fecha, $formatHora = false)
    {
        if (!$formatHora) {
            $formatHora = 'H:i:s';
        }
        if (!$fecha) {
            return '';
        }
        $time = strtotime($fecha);
        return date($formatHora, $time);
    }

    /**
     * TODO
     *
     * @param $data
     */
    public function debug($data)
    {
        return $this->dump($data);
    }

    /**
     * TODO
     *
     * @param $data
     * @return bool
     */
    public function isNumber($data)
    {
        return is_numeric($data);
    }

    /**
     * TODO
     *
     * @param $array
     * @param $separator
     * @return string
     */
    public function implode($array, $separator)
    {
        return implode($separator, $array);
    }

    /**
     * TODO
     *
     * @param $src
     * @param array $options
     * @return mixed
     */
    public function js($src, $options = array())
    {
        $js = new JavascriptMedia($src, $options);
        return $js->getBrowserCode();
    }

    /**
     * TODO
     *
     * @param $src
     * @param array $options
     */
    public function css($src, $options = array())
    {
        $this->view->addCSS($src, $options);
    }

    /**
     * TODO
     *
     * @param $name
     * @param null $def
     * @return null
     */
    public function vars($name, $def = null)
    {
        return Vars::getVar($name, $def);
    }

    /**
     * TODO
     *
     * @return array
     */
    public function meses()
    {
        return array(
            'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Nombiembre',
            'Diciembre'
        );
    }

    public function mes($index, $base = 1)
    {
        $meses = $this->meses();
        return $meses[$index - $base];
    }

    /**
     * TODO
     *
     * @return array
     */
    public function dia_semanas()
    {
        return array('Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo');
    }

    /**
     * TODO
     *
     * @param $text
     */
    public function icon($text)
    {
        $names = array();
    }

    public function dump($data) {
        return VarDumper::dump($data);
    }

    public function tr() {
        $translation = Bootstrap::getSingleton()->getTranslation();
        return call_user_func_array(array($translation, 'tr'), func_get_args());
    }
}
