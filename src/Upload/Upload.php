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
 * Time: 9:53
 */

namespace GLFramework\Upload;

/**
 * Class Upload
 *
 * @package GLFramework\Upload
 */
class Upload
{

    var $upload;
    var $uploads;
    var $hash;

    var $folder;

    /**
     * Upload constructor.
     *
     * @param $uploads Uploads
     * @param $upload
     * @param null $folder
     */
    public function __construct($uploads, $upload, $folder = null)
    {
        $this->uploads = $uploads;
        $this->upload = $upload;

        $this->hash = date('Y-m-d_H-i-s') . '_';
        $this->folder = $folder ? ($folder . '/') : '';
    }

    /**
     * TODO
     *
     * @return mixed
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * TODO
     *
     * @param mixed $folder
     */
    public function setFolder($folder)
    {
        if (substr($folder, strrpos($folder, '/')) !== strlen($folder)) {
            $folder .= '/';
        }
        $this->folder = $folder;
    }

    /**
     * TODO
     *
     * @return bool
     */
    public function isMultiple()
    {
        return is_array($this->upload['name']);
    }

    /**
     * TODO
     *
     * @return int
     */
    public function getLength()
    {
        return count($this->upload['name']);
    }

    /**
     * TODO
     *
     * @param bool $index
     * @return string
     */
    public function getFilename($index = false)
    {
        return $this->uploads->folder . "/" . $this->folder . $this->hash . $this->name($index);
    }

    /**
     * TODO
     *
     * @param bool $index
     * @return string
     */
    public function getAbsolutePath($index = false)
    {
        return $this->uploads->dir . '/' . $this->getFilename($index);
    }

    /**
     * TODO
     *
     * @param bool $index
     * @return bool
     */
    public function move($index = false)
    {
        $source = $this->tmpName($index);
        $dest = $this->getAbsolutePath($index);
        $parent = substr($dest, 0, strrpos($dest, '/'));
        if (!is_dir($parent)) {
            mkdir($parent);
        }
        return move_uploaded_file($source, $dest);
    }

    /**
     * TODO
     *
     * @param bool $index
     * @return mixed
     */
    public function error($index = false)
    {
        if ($index === false) {
            return $this->upload['error'];
        }
        return $this->upload['error'][$index];
    }

    /**
     * TODO
     *
     * @param bool $index
     * @return mixed
     */
    public function name($index = false)
    {
        if ($index === false) {
            return $this->upload['name'];
        }
        return $this->upload['name'][$index];
    }

    /**
     * TODO
     *
     * @param $name
     * @param bool $index
     * @return mixed
     */
    public function setName($name, $index = false)
    {
        if ($index === false) {
            return $this->upload['name'] = $name;
        }
        return $this->upload['name'][$index] = $name;
    }

    /**
     * TODO
     *
     * @param bool $index
     * @return mixed
     */
    public function tmpName($index = false)
    {
        if ($index === false) {
            return $this->upload['tmp_name'];
        }
        return $this->upload['tmp_name'][$index];
    }

    /**
     * TODO
     *
     * @param bool $index
     * @return mixed
     */
    public function contentType($index = false)
    {
        if ($index === false) {
            return $this->upload['type'];
        }
        return $this->upload['type'][$index];
    }

    /**
     * TODO
     *
     * @param bool $index
     * @return bool
     */
    public function isEmpty($index = false)
    {
        return $this->error($index) === 4;
    }

    /**
     * TODO
     *
     * @param bool $index
     * @return bool
     */
    public function isSuccess($index = false)
    {
        return $this->error($index) === 0;
    }

    /**
     * TODO
     *
     * @param bool $index
     * @return string
     */
    public function url($index = false)
    {
        return 'http://' . $_SERVER['HTTP_HOST'] . '/' . $this->getFilename($index);
    }
}
