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
 * Date: 7/03/16
 * Time: 10:39
 */

namespace GLFramework\Utils;

use GLFramework\Model;

/**
 * Class DataTablesManager
 *
 * @package GLFramework\Utils
 */
class DataTablesManager
{
    /**
     * @var Model[]
     */
    var $dataSource;

    var $callback;

    /**
     * DataTablesManager constructor.
     *
     * @param $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * TODO
     *
     * @param $row
     * @return mixed
     */
    public function row($row)
    {
        return call_user_func($this->callback, $row);
    }

    /**
     * TODO
     *
     * @param $fields
     */
    public function process($fields)
    {
        $data = array();
        foreach ($fields as $model) {
            $data[] = $this->row($model);
        }
        header('Content-Type: text/json');

        echo json_encode(array('data' => $data));
        die();
    }
}
