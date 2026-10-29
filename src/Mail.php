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

/**
 * Class Mail
 *
 * @package GLFramework
 */
class Mail
{
    public $config;
    private $from;
    private $fromName;
    private $logger;

    private $replyTo = array();

    /**
     * Mail constructor.
     *
     * @param $from
     * @param $fromName
     * @param null $config
     */
    public function __construct($from = null, $fromName = null, $config = null)
    {
        if (!$config) {
            $config = Bootstrap::getSingleton()->getConfig();
        }
        $this->config = $config;

        if (!$from) {
            $from = $this->config['mail']['from']['email'];
        }
        if (!$fromName) {
            $fromName = $this->config['mail']['from']['title'];
        }
        $this->from = $from;
        $this->fromName = $fromName;
    }

    public function setReplyTo($address, $name) {
        $this->replyTo[$address] = $name;
    }

    /**
     * Genera una vista del email compatible con clientes de correo
     *
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
     * Este transporte se genera mediante la configuración. Devuelve una instancia nueva en cada llamada
     *
     * @return \PHPMailer\PHPMailer\PHPMailer
     */
    public function getTransport()
    {
        $mailer = $this->getMailSystem()->getTransport();
        Events::dispatch('onMailTransport', array('transport' => $mailer));
        return $mailer;
    }

    /**
     * Envia el email a los contactos pasados a $to
     *
     * @param $to
     * @param $subject
     * @param $message
     * @param array $attachments Use the array key to set the attachment file name in the email
     * @param array $cc
     * @param array $bcc
     * @return int
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function send($to, $subject, $message, $attachments = array(), $cc = array(), $bcc = array())
    {
        $mail = $this->getTransport();
        $mail->Subject = $subject;
        $mail->Body = $message;
        if($this->from != '')
        {
            $mail->setFrom($this->from, $this->fromName);
        } else {
            $mail->setFrom('framework@gestionlan.com', "Manuel de Gestionlan");

        }
        $this->addRecipients($mail, 'addAddress', $to);
        $this->addRecipients($mail, 'addCC', $cc);
        $this->addRecipients($mail, 'addBCC', $bcc);
        if($this->replyTo) {
            foreach ($this->replyTo as $addr => $name) {
                $mail->addReplyTo($addr, (string) $name);
            }
        }
        foreach ($attachments as $key => $attachment) {
            $mail->addAttachment($attachment, is_int($key) ? '' : $key);
        }

        return $this->done($mail);
    }

    /**
     * TODO
     *
     * @param $to
     * @param $subject
     * @param $message
     * @return \PHPMailer\PHPMailer\PHPMailer
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function build($to, $subject, $message)
    {
        $mail = $this->getTransport();
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->setFrom($this->from, $this->fromName);
        $this->addRecipients($mail, 'addAddress', $to);
        return $mail;
    }

    /**
     * Envia el email. Devuelve el número de destinatarios (0 si falla)
     *
     * @param $mail \PHPMailer\PHPMailer\PHPMailer
     * @return int
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function done($mail)
    {
        $emails = array();
        foreach ($mail->getToAddresses() as $address) {
            $emails[$address[0]] = $address[1] !== '' ? $address[1] : null;
        }

        Events::dispatch('onEmail', array(
            'emails' => $emails,
            'subject' => $mail->Subject,
            'message' => $mail->Body,
            'transport' => $mail
        ));
        if (!$mail->send()) {
            return 0;
        }
        return count($mail->getToAddresses()) + count($mail->getCcAddresses()) + count($mail->getBccAddresses());
    }

    /**
     * Añade destinatarios. Acepta un email, una lista de emails o un array email => nombre
     *
     * @param \PHPMailer\PHPMailer\PHPMailer $mail
     * @param string $method addAddress, addCC o addBCC
     * @param string|array|null $recipients
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function addRecipients($mail, $method, $recipients)
    {
        if (empty($recipients)) {
            return;
        }
        if (!is_array($recipients)) {
            $recipients = array($recipients);
        }
        foreach ($recipients as $key => $value) {
            if (is_int($key)) {
                $address = $value;
                $name = '';
            } else {
                $address = $key;
                $name = (string) $value;
            }
            if ($address !== null && trim($address) !== '') {
                $mail->$method(trim($address), $name);
            }
        }
    }

    /**
     * TODO
     *
     * @param array $cssFiles
     * @return string
     */
    private function getCss($cssFiles = array())
    {
        $css = '';
        foreach ($cssFiles as $file) {
            if (file_exists($file)) {
                $css .= file_get_contents($file) . "\n";
            }
        }
        return $css;
    }

    /**
     * TODO
     *
     * @return Mail\MailSystem.php
     */
    private function getMailSystem()
    {
        if (isset($this->config['mail']) && isset($this->config['mail']['mailsystem'])) {
            $system = $this->config['mail']['mailsystem'];
            $class = 'GLFramework\\Mail\\' . $system;
            if (class_exists($class)) {
                return new $class($this->config);
            }
        }
        return new Mail\Mail($this->config);
    }
}
