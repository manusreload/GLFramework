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

use GLFramework\Filesystem;

define('DATA_MANIPULATION_CREATE_MODE_AUTO', 0);
define('DATA_MANIPULATION_CREATE_MODE_CSV', 1);
define('DATA_MANIPULATION_CREATE_MODE_XLS', 2);
define('DATA_MANIPULATION_CREATE_MODE_XLSX', 3);
define('DATA_MANIPULATION_CREATE_MODE_ODS', 4);

/**
 * Class DataManipulation
 *
 * @package GLFramework\DaMa
 */
class DataManipulation
{
    /**
     * TODO
     *
     * @param $file
     * @param $original
     * @return string
     */
    public function rename_extension($file, $original)
    {
        $name = $file . $this->getFileExtension($original);
        rename($file, $name);
        return $name;
    }

    /**
     * TODO
     *
     * @param $file
     * @return string
     */
    public function getFileExtension($file)
    {
        return strtolower(substr($file, strrpos($file, '.')));
    }

    /**
     * TODO
     *
     * @param $file
     * @param int $mode
     * @param null $extension
     * @return Manipulator
     */
    public function createFromFile($file, $mode = DATA_MANIPULATION_CREATE_MODE_AUTO, $extension = null)
    {
        $manipulator = new Manipulator();
        $manipulator->setFileInput($file, $mode, $extension);
        return $manipulator;
    }

    /**
     * TODO
     *
     * @param $upload
     * @param bool $store
     * @return Manipulator
     */
    public function createFromUpload($upload, $store = false)
    {
        $manipulator = new Manipulator();
        $manipulator->setFileInput($upload['tmp_name'], DATA_MANIPULATION_CREATE_MODE_AUTO,
            $this->getFileExtension($upload['name']));
        if ($store) {
            $fs = new Filesystem(remove_file_extension($upload['name']) . '_' . date('d-m-Y_H-i-s') . get_file_extension($upload['name']), 'uploads/' . $store);
            copy($upload['tmp_name'], $fs->getAbsolutePath());
        }
        return $manipulator;
    }
}
