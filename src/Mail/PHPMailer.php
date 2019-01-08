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
 * Date: 10/5/16
 * Time: 11:01
 */

namespace GLFramework\Mail;

/**
 * Class PHPMailer
 *
 * @package GLFramework\Mail
 */
class PHPMailer extends MailSystem
{
    var $mail;

    /**
     * PHPMailer constructor.
     *
     * @param array $config
     */
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

    /**
     * TODO
     *
     * @return mixed
     */
    public function getTransport()
    {
        $config = $this->config;
        $transport = \Swift_SmtpTransport::newInstance($config['mail']['hostname'], $config['mail']['port']);
        $transport->setUsername($config['mail']['username']);
        $transport->setPassword($this->getPassword());
        if(isset($config['mail']['encryption'])) {
            $transport->setEncryption($config['mail']['encryption']);
        }
        return $transport;
    }

    /**
     * TODO
     *
     * @return bool|string
     */
    public function getPassword()
    {
        if (isset($this->config['mail']['encrypt'])) {
            switch ($this->config['mail']['encrypt']) {
                case 'base64':
                    return base64_decode($this->config['mail']['password']);
            }
        }
        return $this->config['mail']['password'];
    }
}
