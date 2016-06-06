<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 7/03/16
 * Time: 9:53
 */

namespace GLFramework\Upload;


class Upload
{

    var $upload;
    var $uploads;
    var $hash;
    /**
     * Upload constructor.
     * @param $uploads Uploads
     * @param $upload
     */
    public function __construct($uploads, $upload)
    {
        $this->uploads = $uploads;
        $this->upload = $upload;

        $this->hash = date("Y-m-d_H-i-s") . "_";
    }

    public function isMultiple()
    {
        return is_array($this->upload['name']);
    }

    public function getLength()
    {
        return count($this->upload['name']);
    }

    public function getFilename($index = null)
    {
        return $this->uploads->folder . "/" . $this->hash . $this->name($index);
    }

    public function getAbsolutePath($index = false)
    {
        return $this->uploads->dir . "/" . $this->getFilename($index);
    }

    public function move($index = false)
    {
        $source = $this->tmpName($index);
        $dest = $this->getAbsolutePath($index);
        return move_uploaded_file($source, $dest);
    }

    public function error($index = false)
    {
        if($index === false)
            return $this->upload['error'];
        return $this->upload['error'][$index];
    }

    public function name($index = false)
    {
        if($index === false)
            return $this->upload['name'];
        return $this->upload['name'][$index];
    }

    public function tmpName($index = false)
    {
        if($index === false)
            return $this->upload['tmp_name'];
        return $this->upload['tmp_name'][$index];
    }

    public function isEmpty($index = false)
    {
        return $this->error($index) == 4;
    }

    public function isSuccess($index = false)
    {
        return $this->error($index) == 0;
    }

}