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
 * Date: 15/1/16
 * Time: 20:27
 */

namespace GLFramework;

use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class Mail
{

    private static $mailer;
    private $from;
    private $fromName;
    public $config;

    /**
     * Mail constructor.
     * @param $from
     * @param $fromName
     * @param null $config
     */
    public function __construct($from = null, $fromName = null, $config = null)
    {
        if(!$config)
            $config = Bootstrap::getSingleton()->getConfig();
        $this->config = $config;

        if(!$from) $from = $this->config['mail']['from']['email'];
        if(!$fromName) $fromName = $this->config['mail']['from']['title'];
        $this->from = $from;
        $this->fromName = $fromName;
    }


    private function getCss($cssFiles = array())
    {
        $css = "";
        foreach($cssFiles as $file)
        {
            if(file_exists($file)) $css .= file_get_contents($file) . "\n";
        }
        return $css;
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
        if(isset($this->config['mail']))
        {
            if(isset($this->config['mail']['mailsystem']))
            {
                $system = $this->config['mail']['mailsystem'];
                $class = "GLPlanning/Mail/$system";
                if(class_exists($class)) return new $class($this->config);
            }
        }
        return new Mail\Mail($this->config);
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
            Events::dispatch('onMailTransport', array( 'transport' => self::$mailer ));
        }
        return self::$mailer;
    }

    /**
     * Envia el email a los contactos pasados a $to
     * @param $to
     * @param $subject
     * @param $message
     * @param array $attachments Use the array key to set the attachment file name in the email
     * @param array $cc
     * @return int
     */
    public function send($to, $subject, $message, $attachments = array(), $cc = array())
    {
        $transport = $this->getTransport();

        $mail = new \Swift_Message($subject, $message, "text/html", "UTF-8");
        $mail->setFrom($this->from, $this->fromName);
        $mail->setTo($to);
        $mail->setCc($cc);
        foreach ($attachments as $key => $attachment)
        {
            $atta = \Swift_Attachment::fromPath($attachment);
            if(!is_int($key)) $atta->setFilename($key);
            $mail->attach($atta);
        }

        return $this->done($mail);
    }

    /**
     * @param $to
     * @param $subject
     * @param $message
     * @return \Swift_Message
     */
    public function build($to, $subject, $message)
    {
        $mail = new \Swift_Message($subject, $message, "text/html", "UTF-8");
        $mail->setFrom($this->from, $this->fromName);
        $mail->setTo($to);
        return $mail;
    }

    /**
     * @param $mail \Swift_Message
     * @return int
     */
    public function done($mail)
    {
        $transport = $this->getTransport();
        Events::dispatch('onEmail', array( 'emails' => $mail->getTo(), 'subject' => $mail->getSubject(), 'message' => $mail->getBody(), 'transport' => $transport));
        return $transport->send($mail);
    }
}