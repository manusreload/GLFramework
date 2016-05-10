<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 10/5/16
 * Time: 11:01
 */

namespace GLFramework\Mail;


abstract class MailSystem
{
    protected $config = array();

    /**
     * BaseMail constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return \Swift_Transport
     */
    public abstract function getTransport();

}