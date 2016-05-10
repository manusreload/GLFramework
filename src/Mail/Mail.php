<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 10/5/16
 * Time: 11:06
 */

namespace GLFramework\Mail;


class Mail extends MailSystem
{

    public function getTransport()
    {
        // TODO: Implement getTransport() method.
        return new \Swift_MailTransport();
    }
}