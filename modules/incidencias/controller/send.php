<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 16/11/16
 * Time: 13:37
 */

namespace GLFramework\Modules\Incidencias;


use GLFramework\Bootstrap;
use GLFramework\Controller;
use GLFramework\Mail;

class send extends Controller\AuthController
{
    public function __construct($base = null, $module = null)
    {
        parent::__construct($base = '', $module = null);
        $this->setRequireLogin(false);
    }


    /**
     * Implementar aqui el código que ejecutara nuestra aplicación
     * @return mixed
     */
    public function run()
    {
        // TODO: Implement run() method.
        $cnf = Bootstrap::getSingleton()->getConfig();
        $appName = $cnf['app']['name'];
        $email = $this->config['email']?:$this->config['app']['settings']['email']['default'];
        $this->setTemplate("json.twig");
        $mail = new Mail($_POST['email'], $_POST['nombre']);
        $content = $mail->render($this, "mail_template.twig", array('post' => $_POST));
        $mail->send($email, 'Incidencia de ' . $appName, $content, array(), array($_POST['email']));

        return array('json' => array('ok' => true));
    }
}