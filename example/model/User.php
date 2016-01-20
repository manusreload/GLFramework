<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 19/1/16
 * Time: 19:21
 */
class User extends \GLFramework\Model\User
{
    public function __construct($data = null)
    {
        $this->definition['fields']['custom'] = "varchar(128)";
        parent::__construct($data);
    }


}