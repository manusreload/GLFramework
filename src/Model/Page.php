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