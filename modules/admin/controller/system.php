<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 13/06/17
 * Time: 11:52
 */

namespace GLFramework\Modules\Admin;


use GLFramework\Controller\AuthController;
use GLFramework\Mail;

class system extends AuthController
{
    var $admin = true;
    var $name = "Sistema";
    var $subtemplate = "system/home.twig";

    public function run()
    {
        parent::run(); // TODO: Change the autogenerated stub
        $section = $this->params['section'];
        if($section == "home")
        {
            $this->subtemplate = "system/home.twig";
        } elseif ($section == "mail") {
            $this->subtemplate = "system/mail.twig";
            $this->mailSection();
        }
    }

    public function mailSection()
    {
        $mail = new Mail();
        $this->transport = get_class($mail->getTransport());
        $this->mail_config = $mail->config['mail'];
        if(isset($_POST['send']))
        {
            $content = $mail->render($this, 'mail/test.twig', array());
            try
            {
                if($mail->send($_POST['email'], "Test Email", $content))
                {
                    $this->addMessage("Se ha enviado correctamente el email");
                }
                else
                {
                    $this->addMessage("Se ha producido un error al enviar el email", "danger");
                }
            } catch (\Exception $ex) {
                $this->addMessage("Error al enviar el email: " . $ex->getMessage() . "\n" . display_exception($ex, 1, false));
            }
        }
    }


}