<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 15/1/16
 * Time: 20:27
 */

namespace GLFramework;

use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class Mail
{

    private static $mailer;
    private function getCss($cssFiles = array())
    {
        $css = "";
        foreach($cssFiles as $file)
        {
            if(file_exists($file)) $css .= file_get_contents($file) . "\n";
        }
    }

    /**
     * Genera una vista del email compatible con clientes de correo
     * @param $controller
     * @param $template
     * @param $data
     * @return string
     * @throws \TijsVerkoyen\CssToInlineStyles\Exception
     */
    public function render($controller, $template, $data)
    {
        $view = new View($controller);
        $css = array();
        $html = $view->mail($template, $data, $css);
        $cssToInlineStyles = new CssToInlineStyles();
        $cssToInlineStyles->setCSS($this->getCss($css));
        $cssToInlineStyles->setHTML($html);
        return $cssToInlineStyles->convert();
    }

    /**
     * @return Mail\MailSystem.php
     */
    private function getMailSystem()
    {
        $config = Bootstrap::getSingleton()->getConfig();
        if(isset($config['mail']))
        {
            if(isset($config['mail']['mailsystem']))
            {
                $system = $config['mail']['mailsystem'];
                $class = "GLPlanning/Mail/$system";
                if(class_exists($class)) return new $class($config);
            }
        }
        return new Mail\Mail($config);
    }

    /**
     * Este transporte se genera mediante la configuración
     * @return \Swift_Transport
     */
    public function getTransport()
    {
        if(!self::$mailer)
        {
            self::$mailer = $this->getMailSystem()->getTransport();
            Events::fire('onMailTransport', array( 'transport' => self::$mailer ));
        }
        return self::$mailer;
    }

    /**
     * Envia el email a los contactos pasados a $to
     * @param $to
     * @param $subject
     * @param $message
     * @param array $attachments Use the array key to set the attachment file name in the email
     *
     */
    public function send($to, $subject, $message, $attachments = array())
    {
        $config = Bootstrap::getSingleton()->getConfig();
//        $mailsystem = $this->getMailSystem();
        $transport = $this->getTransport();
        Events::fire('onEmail', array( 'emails' => $to, 'subject' => $subject, 'message' => $message, 'transport' => $transport));

        $mail = new \Swift_Message($subject, $message, "text/html", "UTF-8");
        $mail->setFrom($config['mail']['from']['email'], $config['mail']['from']['title']);
        $mail->setTo($to);
        foreach ($attachments as $key => $attachment)
        {
            $atta = \Swift_Attachment::fromPath($attachment);
            if(!is_int($key)) $atta->setFilename($key);
            $mail->attach($atta);
        }

        return $transport->send($mail);

    }
}