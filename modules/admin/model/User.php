<?php
/**
 *     GLFramework, small web application framework.
 *     Copyright (C) 2017.  Manuel Muñoz Rosa
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
 * Date: 12/4/17
 * Time: 18:26
 */

namespace GLFramework\Modules\Admin;


class User extends \GLFramework\Model\User
{
    var $superadmin;

    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'superadmin' => "int(11)",
        )
    );
}