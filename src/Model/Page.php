<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 18/2/16
 * Time: 17:31
 */

namespace GLFramework\Model;


use GLFramework\Controller;
use GLFramework\Model;
use GLFramework\ModelResult;

class Page extends Model
{

    var $id;
    var $controller;
    var $admin;
    var $title;
    var $description;

    protected $table_name = "page";
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'controller' => "varchar(255)",
            'title' => "varchar(64)",
            'description' => "varchar(255)",
            'admin' => "int(11)",
        )
    );


    /**
     * @param $controller Controller|string
     * @return ModelResult
     */
    public function get_by_controller($controller)
    {

        if(!is_string($controller))
        {
            $name = get_class($controller);
        }
        else
        {
            $name = $controller;
        }
        return $this->get(array('controller' => $name));
    }

    public function generate($controller)
    {
        if(!$controller instanceof Controller)
            $controller = new $controller("", null);
        $this->controller = get_class($controller);
        $this->admin = $controller->admin;
        $this->title = $controller->name;
        $this->description = $controller->description;
    }

}