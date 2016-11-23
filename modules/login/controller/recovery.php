<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 28/5/16
 * Time: 11:47
 */

namespace GLFramework\Modules\Login;


use GLFramework\Controller;
use GLFramework\Mail;
use GLFramework\Model;

class recovery extends Controller
{

    var $email_send = false;
    /**
     * Implementar aqui el código que ejecutara nuestra aplicación
     * @return mixed
     */
    public function run()
    {
        // TODO: Implement run() method.
        if(isset($this->params['token']))
        {
            $recovery = new \UserRecovery();
            $res = $recovery->get(array('token' => $this->params['token']));
            if($res->count() == 1)
            {
                $this->setTemplate("recovery_password.twig");
                if(isset($_POST['save']))
                {
                    if($_POST['password'] == $_POST['password2'])
                    {
                        $recovery = $res->getModel();
                        if($recovery instanceof \UserRecovery);


                        $user = Model::newInstance("User", $recovery->id_user);
                        if($user->validPassword($_POST['password']))
                        {
                            $user->password = $user->encrypt($_POST['password']);
                            $user->save();
                            $this->addMessage("Se ha guardado correctamente, acceda a login");
                            $recovery->delete();
                            $this->quit("/login");
                        }
                        else
                        {
                            $this->addMessage("La contaseña no es segura. Utilice otra contraseña más larga (min:6)", "danger");
                        }
                    }
                    else
                    {
                        $this->addMessage("Contraseñas no válidas", "danger");
                        
                    }
                }
            }
            else
            {
                $this->addMessage("Codigo de recupercion no válido!", "danger");
            }
            
            
        }
        else
        {
            if(isset($_POST['save']))
            {
                $user = Model::newInstance("User");
                $result = null;
                if(!empty($_POST['username']))
                {
                    $result = $user->get(array('user_name' => $_POST['username']));
                }
                else if(!empty($_POST['email']))
                {
                    $result = $user->get(array('email' => $_POST['email']));
                }
                if($result)
                {
                    if($result->count() > 0)
                    {
                        $user = $result->getModel();
                        $recovery = new \UserRecovery();
                        $recovery = $recovery->generateNew($user);
                        $recovery->save(true);
//                        print_debug($recovery);
                        $mail = new Mail();
                        $message = $mail->render($this, "mail/recover_account.twig", array('user' => $user, 'recovery' => $recovery));
                        if($mail->send($user->email, "Contraseña perdida", $message))
                        {
                            $this->addMessage("Se ha enviado un email al usuario. con los pasos que tiene que seguir para recuperar la cuenta");
                        }
                        else
                        {
                            $this->addMessage("Se ha producido un error al enviar el email, verifique los parametros de configuración.", "danger");
                        }

                    }
                    else
                    {
                        $this->addMessage("No se encontraron registros asociados a estos datos", "danger");
                    }
                }
                else
                {
                    $this->addMessage("No se han proporcionado datos", "danger");
                }
            }
        }
    }
}