<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 16:33
 */

namespace GLFramework;


use GLFramework\Middleware\ControllerMiddleware;
use GLFramework\Module\Module;
use GLFramework\Module\ModuleManager;
use GLFramework\Upload\Uploads;

abstract class Controller
{

    var $title = false;
    var $name = null;
    var $admin = false;

    private $template = null;
    private $db = null;
    private $view;
    var $messages = array();
    var $config = array();
    var $description = "";
    var $directory;

    /**
     * @var Middleware[]
     */
    private $middleware = array();
    /**
     * @var Module
     */
    var $module;
    /**
     * Parametros de la url que pasa el Router definidos en routes:
     * @var array
     */
    var $params = array();

    var $redirect = false;
    /**
     * @var Response
     */
    var $response;

    /**
     * Controller constructor.
     * @param $module Module
     * @param string $base
     */
    public function __construct($base = "", $module = null)
    {
        if($module == null)
            $module = ModuleManager::getInstance()->getMainModule();

        $this->module = $module;
        $this->config = $this->module->getConfig();
        $this->restoreMessages();
        if(empty($this->name))
            $this->name = get_class($this);
        $this->directory = dirname($base);
        $base = substr($base, 0, strrpos($base, "."));
        $this->template = $base . ".twig";

        $this->view = new View($this);
        $this->response = new Response();
        $this->addMiddleware(new ControllerMiddleware($this));
        Events::fire('afterControllerConstruct', array($this));
    }

    /**
     * Implementar aqui el código que ejecutara nuestra aplicación
     * @return mixed
     */
    abstract public function run();

    /**
     * Renderiza la vista y devuelve el código.
     * @param $data
     * @param $params
     * @return array|null|string
     */
    public function display($data, $params)
    {
        return $this->view->render($data, $params);
    }

    /**
     * Ejecuta la petición en cascada a través de los diferentes middlewares
     * @param $request Request
     * @return Response
     */
    public function call($request)
    {

        $this->params = $request->getParams();
        $this->middleware = array_reverse($this->middleware);
        reset($this->middleware);
        $this->middleware($request, $this->response);
        return $this->response;
    }

    public function middleware($request, &$response)
    {
        $that = $this;
        if($middleware = current($this->middleware))
        {
            next($this->middleware);
            $middleware->next($request, $response, function($request, $response) use($that)
            {
                $that->middleware($request, $response);
            });

        }
    }

    /**
     * Obtiene la respuesta que ha dado el controlador
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Archivo de la plantilla, se busca en los módulos en función de prioridad
     * @return null|string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Establecer el archivo de plantilla para este controlador
     * @param null|string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Asigna a la respuesta una redirección web
     * @param $url
     */
    public function redirection($url)
    {
        $this->response->setRedirection($url);
    }

    /**
     * Recupera los mensajes de la sesión
     */
    public function restoreMessages()
    {
        if(isset($_SESSION['messages']))
        {
            $this->messages = $_SESSION['messages'];
            unset($_SESSION['messages']);
        }
    }

    /**
     * Almacena en la sesion los mensajes
     */
    public function shareMessages()
    {
        $_SESSION['messages'] = $this->messages;
    }

    /**
     * Redirige y despacha la respuesta
     * @param null $redirection
     */
    public function quit($redirection = null)
    {
        if(!$this->response->getAjax())
        {

            if($redirection) $this->redirection($redirection);
            $this->shareMessages();
            if(!GL_TESTING)
            {
                Bootstrap::dispatch($this->response);
                exit;
            }
        }
    }

    /**
     *
     * @return DatabaseManager
     */
    public function getDb()
    {
        if(!$this->db)
            $this->db = new DatabaseManager();
        return $this->db;
    }

    /**
     * Muestra un mesaje en pantalla, con el estilo indicado
     * @param $message
     * @param string $type
     */
    public function addMessage($message, $type = "success")
    {
        Events::fire('onMessageDisplay', array('message' => $message, 'type' => $type));
        $this->messages[] = array('message' => $message, 'type' => $type);
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Genera un enlace al controlador indicado, puede ser un objeto un un string
     * @param string|Controller $controller
     * @param array $params
     * @param bool $fullPath
     * @return string
     * @throws \Exception
     */
    public function getLink($controller, $params = array(), $fullPath = false)
    {
        if($controller instanceof Controller) $controller = get_class($controller);
        $controller = (string) $controller;
        $url = Bootstrap::getSingleton()->getManager()->getRouter()->generate($controller, $params);
        if($fullPath) 
        {
            $protocol = "http";
            if(strpos($_SERVER['SCRIPT_URI'], "https") !== FALSE) $protocol = "https";
            return $protocol . "://" . $_SERVER['HTTP_HOST'] . $url;
        }
        return $url;
    }

    /**
     * Obtener la ruta al recurso indicado en el módulo indicado
     * @param $name
     * @param null $module Module
     * @return string
     */
    public function getResource($name, $module = null)
    {
        if($module == null) $module = $this->module;
        if(is_string($module)) $module = ModuleManager::getModuleInstanceByName($module);
        $config = $module->getConfig();
        $folders = $config['app']['resources'];
        if(!is_array($folders)) $folders = array($folders);
        foreach($folders as $folder)
        {
            $path = $module->getDirectory() . "/" . $folder . "/" . $name;
            if(file_exists($path))
            {
                $path = realpath($path);
                $base = dirname($_SERVER['SCRIPT_FILENAME']);
                $index = strpos($path, $base);
                $url = substr($path, $index + strlen($base));
                $protocol = "http";
                if(strpos($_SERVER['SCRIPT_URI'], "https") !== FALSE) $protocol = "https";

                return $protocol . "://" . $_SERVER['SERVER_NAME'] . $url;
            }
        }
    }

    /**
     * Genera un objeto de subida para este módulo
     * @return Uploads
     */
    public function getUploads()
    {
        return new Uploads($this->module->getDirectory(), $this->config);
    }

    /**
     * Establece el tipo de contenido a la respuesta
     * @param $type
     */
    public function setContentType($type)
    {
        $this->response->setContentType($type);
    }

    public function log($message, $level)
    {
        Events::fire('onLog', array('message' => $message, 'level' => $level));
    }

    /**
     * Validar la petición si es posible
     * @return bool|int|string
     */
    public function csrf()
    {
        return Events::fire('validateCSRF');
    }

    /**
     * Genera un token para validar la petición
     * @return mixed
     */
    public function generate_csrf()
    {
        if(is_module_enabled("csrf"))
        {
            return \CSRF::generate()->token;
        }
    }

    /**
     * Añade un nuevo middleware a la cabeza de la cola
     * @param Middleware $middleware
     */
    public function addMiddleware(Middleware $middleware)
    {
        $this->middleware[] = $middleware;
    }

}