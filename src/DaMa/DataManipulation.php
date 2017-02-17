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
 * Date: 1/03/16
 * Time: 10:17
 */

namespace GLFramework\DaMa;

use GLFramework\DaMa\Manipulators\CSVManipulator;
use GLFramework\DaMa\Manipulators\XLSManipulator;
use GLFramework\DaMa\Manipulators\XLSXManipulator;

define('DATA_MANIPULATION_CREATE_MODE_AUTO', 0);
define('DATA_MANIPULATION_CREATE_MODE_CSV', 1);
define('DATA_MANIPULATION_CREATE_MODE_XLS', 2);
define('DATA_MANIPULATION_CREATE_MODE_XLSX', 3);
define('DATA_MANIPULATION_CREATE_MODE_ODS', 4);

class DataManipulation
{
    public function rename_extension($file, $original)
    {
        $name = $file . $this->getFileExtension($original);
        rename($file, $name);
        return $name;

    }

    public function getFileExtension($file)
    {
        return substr($file, strrpos($file, "."));
    }
    /**
     * @param $file
     * @param int $mode
     * @return Manipulator
     */
    public function createFromFile($file, $mode = DATA_MANIPULATION_CREATE_MODE_AUTO, $extension = null)
    {
        $manipulator = new Manipulator();
        $manipulator->setFileInput($file, $mode, $extension);
        return $manipulator;
    }
    public function createFromUpload($upload)
    {
        $manipulator = new Manipulator();
        $manipulator->setFileInput($upload['tmp_name'], DATA_MANIPULATION_CREATE_MODE_AUTO, $this->getFileExtension($upload['name']));
        return $manipulator;
    }
}