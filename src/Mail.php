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

    private function getCss($cssFiles = array())
    {
        $css = "";
        foreach($cssFiles as $file)
        {
            if(file_exists($file)) $css .= file_get_contents($file) . "\n";
        }
    }
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

    public function send($to, $subject, $message, $headers = array())
    {
        $config = Bootstrap::getSingleton()->getConfig();
        if(isset($config['mail']))
        {
            if(!isset($config['mail']['mailsystem']) || $config['mail']['mailsystem'] == "PHPMailer")
            {
                $mail = new \PHPMailer();
                //Enable SMTP debugging
                // 0 = off (for production use)
                // 1 = client messages
                // 2 = client and server messages
                $mail->SMTPDebug = 0;
                //Ask for HTML-friendly debug output
                $mail->Debugoutput = 'html';

                $mail->CharSet = 'UTF-8';
                //Set the hostname of the mail server
                $mail->Host = $config['mail']['hostname'];
                //Set the SMTP port number - likely to be 25, 465 or 587
                $mail->Port = $config['mail']['port'];
                //Whether to use SMTP authentication
                $mail->SMTPAuth = true;
                //Username to use for SMTP authentication
                $mail->Username = $config['mail']['username'];
                //Password to use for SMTP authentication
                $mail->Password = $config['mail']['password'];
                //Set who the message is to be sent from
                $mail->isSMTP();

                $mail->setFrom($config['mail']['from']['email'], $config['mail']['from']['title']);
                $mail->addAddress($to);
                $mail->Subject = $subject;
                $mail->msgHTML($message);

                return $mail->send();
            }
        }
        else
        {
            return mail($to, $subject, $message, $headers);
        }

    }
}