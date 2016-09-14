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

    protected $hidden = array('password');

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