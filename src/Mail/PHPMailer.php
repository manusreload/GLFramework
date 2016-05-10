<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 10/5/16
 * Time: 11:01
 */

namespace GLFramework\Mail;


class PHPMailer extends MailSystem
{
    var $mail;
    public function __construct(array $config)
    {
        parent::__construct($config);
//        $this->mail = new \PHPMailer();
//        //Enable SMTP debugging
//        // 0 = off (for production use)
//        // 1 = client messages
//        // 2 = client and server messages
//        $this->mail->SMTPDebug = 0;
//        //Ask for HTML-friendly debug output
//        $this->mail->Debugoutput = 'html';
//
//        $this->mail->CharSet = 'UTF-8';
//        //Set the hostname of the mail server
//        $this->mail->Host = $config['mail']['hostname'];
//        //Set the SMTP port number - likely to be 25, 465 or 587
//        $this->mail->Port = $config['mail']['port'];
//        //Whether to use SMTP authentication
//        $this->mail->SMTPAuth = true;
//        //Username to use for SMTP authentication
//        $this->mail->Username = $config['mail']['username'];
//        //Password to use for SMTP authentication
//        $this->mail->Password = $config['mail']['password'];
//        //Set who the message is to be sent from
//        $this->mail->isSMTP();
//
//        $this->mail->setFrom($config['mail']['from']['email'], $config['mail']['from']['title']);
    }
    

    public function getTransport()
    {
        $config = $this->config;
        $transport = \Swift_SmtpTransport::newInstance($config['mail']['hostname'], $config['mail']['port']);
        $transport->setUsername($config['mail']['username']);
        $transport->setPassword($config['mail']['password']);
        return $transport;
    }
}