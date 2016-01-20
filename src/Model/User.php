<?php

namespace GLFramework\Model;
use GLFramework\Model;

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 20:44
 */
class User extends Model
{
    var $id;
    var $user_name;
    var $password;
    var $privilegios;
    var $nombre;
    var $email;

    protected $table_name = "users";
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'user_name' => "varchar(20)",
            'password' => "varchar(200)",
            'privilegios' => "text",
            'nombre' => "text",
            'email' => "text",
        )
    );

}