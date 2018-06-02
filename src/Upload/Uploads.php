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
 * Time: 9:47
 */

namespace GLFramework\Upload;

/**
 * Class Uploads
 *
 * @package GLFramework\Upload
 */
class Uploads
{
    var $config;
    var $dir;
    var $folder;

    /**
     * Uploads constructor.
     *
     * @param $dir
     * @param $config
     */
    public function __construct($dir, $config)
    {
        $this->config = $config;
        $this->dir = $dir;
        $this->folder = 'uploads';
        if (isset($config['app']['upload'])) {
            $this->folder = $config['app']['upload'];
        }

        if (!is_dir($this->getUploadDir()) && is_dir(dirname($this->getUploadDir()))) {
            mkdir($this->getUploadDir());
        }
    }

    /**
     * TODO
     *
     * @return string
     */
    public function getUploadDir()
    {
        return $this->folder . '/' . $this->dir;
    }

    /**
     * TODO
     *
     * @param $name
     * @param null $folder
     * @return Upload
     */
    public function allocate($name, $folder = null)
    {
        return new Upload($this, isset($_FILES[$name])?$_FILES[$name]:array(), $folder);
    }
}
