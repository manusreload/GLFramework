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
    var $admin;

    protected $table_name = "user";
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'user_name' => "varchar(20)",
            'password' => "varchar(200)",
            'privilegios' => "text",
            'admin' => "int(11)",
            'nombre' => "text",
            'email' => "text",
        )
    );

    public function getByUserPassword($user, $password)
    {
        return $this->db->select_first("SELECT * FROM {$this->table_name} WHERE user_name = '$user' AND password = '$password'");

    }

    public function encrypt($pass)
    {
        return md5($pass);
    }

    public function getPages()
    {
        $pages = new Page();
        $userPages = new UserPage();
        $sql = "SELECT * FROM " . $userPages->getTableName() . " as up
        LEFT JOIN {$pages->getTableName()} as p ON up.id_page = p.id
        WHERE up.id_user = " . $this->id;

        return $this->db->select($sql);
    }
}