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

class send extends Controller
{

    /**
     * Implementar aqui el código que ejecutara nuestra aplicación
     * @return mixed
     */
    public function run()
    {
        // TODO: Implement run() method.
        $cnf = Bootstrap::getSingleton()->getConfig();
        $appName = $cnf['app']['name'];
        $this->setTemplate("json.twig");
        $mail = new Mail();
        $content = $mail->render($this, "mail_template.twig", array('post' => $_POST));
        $mail->send('mmunoz@gestionlan.com', 'Incidencia de ' . $appName, $content);

        return array('json' => array('ok' => true));
    }
}