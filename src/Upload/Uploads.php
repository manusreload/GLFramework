<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 7/03/16
 * Time: 9:47
 */

namespace GLFramework\Upload;


class Uploads
{
    var $config;
    var $dir;
    var $folder;

    /**
     * Uploads constructor.
     * @param $config
     */
    public function __construct($dir, $config)
    {
        $this->config = $config;
        $this->dir = $dir;
        if(isset($config['app']['upload']))
        {
            $this->folder = $config['app']['upload'];
        }
        else
        {
            $this->folder = "uploads";
        }
        if(!is_dir($this->getUploadDir()))
        {
            mkdir($this->getUploadDir());
        }

    }

    public function getUploadDir()
    {
        return $this->dir . "/" . $this->folder;
    }

    /**
     * @param $name
     * @return Upload
     */
    public function allocate($name)
    {
        return new Upload($this, $_FILES[$name]);
    }




}