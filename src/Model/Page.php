<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 18/2/16
 * Time: 17:31
 */

namespace GLFramework\Model;


use GLFramework\Model;

class Page extends Model
{

    var $id;
    var $controller;
    var $admin;
    var $title;

    protected $table_name = "page";
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'controller' => "varchar(255)",
            'title' => "varchar(255)",
            'admin' => "int(11)",
        )
    );

}