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
 * Time: 17:30
 */

namespace GLFramework\Model;


use GLFramework\Controller;
use GLFramework\Model;

class UserPage extends Model
{
    var $id;
    var $id_user;
    var $id_page;
    var $allowed;

    protected $table_name = "user_page";
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'id_user' => "int(11)",
            'id_page' => "int(11)",
            'allowed' => "int(1)",
        )
    );

    public function exists()
    {
        if(!parent::exists())
        {
            return count($this->db->select("SELECT id FROM {$this->table_name} WHERE id_user = {$this->id_user} AND id_page = {$this->id_page}")) > 0;

        }
        return true;
    }

    /**
     * @param $instance Controller
     * @param $user User
     * @return bool
     */
    public function isAllowed($instance, $user)
    {
        if($user)
        {
            if(!is_string($instance))
            {
                if($instance->admin && $user->admin == false)
                {
                    return false;
                }
                if($user->admin == 1) // El admin tiene acceso a cualquier pagina
                {
                    return true;
                }
            }
            $page = new Page();
            $current = $page->get_by_controller($instance);
            if($current && $current->count() > 0)
            {
                $result = $this->getByPageUser($user->id, $current->getModel()->id);
                if($result->count() > 0)
                {
                    return $result->getModel()->allowed;

                }
            }
            else
            {
                return false;
            }
        }

        return true;
    }

    public function getByPageUser($id_user, $id_page)
    {
        return $this->get(array('id_user' => $id_user, 'id_page' => $id_page));
    }

    public function getUserAllowed($controller)
    {
        $page = new Page();
        $id = $page->get(array('controller' => $controller))->getModel()->id;
        return $this->get(array('id_page' => $id));
    }


}