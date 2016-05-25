<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 25/04/16
 * Time: 10:09
 */
class APIAuthorization extends \GLFramework\Model
{
    var $id;
    var $id_user;
    var $title;
    var $description;
    var $token;
    var $date;
    protected $table_name = "api_authorization";
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'id_user' => 'int(11)',
            'title' => 'varchar(64)',
            'token' => 'varchar(64)',
            'description' => 'varchar(2000)',
            'date' => 'datetime',
            
        )
    );


    public function getUser()
    {
        return new User($this->id_user);
    }
}