<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 06/04/17
 * Time: 12:28
 */

namespace GLFramework;


use Symfony\Component\Yaml\Yaml;

class ConfigurationManager
{


    var $filename;

    /**
     * ConfigurationManager constructor.
     * @param string $filename
     * @param string $folder
     */
    public function __construct($filename = "config.yml", $folder = ".")
    {
        if($folder != null)
        {
            if($folder == ".") $folder = Bootstrap::getSingleton()->getDirectory();
            $this->filename = realpath($folder) . "/" . $filename;;
        }
        else
        {
            $this->filename = $filename;
        }
    }



    public function load()
    {
        return Yaml::parse(file_get_contents($this->filename));
    }

    public function save($config)
    {
        if(!file_put_contents($this->filename, Yaml::dump($config)))
        {
            return false;
        }
        return true;
    }

    public function getFilename()
    {
        return $this->filename;
    }



}