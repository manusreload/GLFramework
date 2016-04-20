<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 29/03/16
 * Time: 12:00
 */

namespace GLFramework;


use TijsVerkoyen\CssToInlineStyles\Exception;

class Filesystem
{

    private $file;

    private $pfile;
    /**
     * Filesystem constructor.
     * @param $file
     */
    public function __construct($file = null)
    {
        $this->file = $file;
    }


    public function allocate($filename = null, $extension = ".rnd")
    {
        if($filename == null)
            $filename = sha1(time() . "_" . microtime(true));
        $file = new Filesystem("{$filename}{$extension}"); //$this->getStorage() . "/{$filename}{$extension}";
        $file->touch();
        return $file;
    }

    private function getStorage()
    {
        $folder = $this->getFilesystemFolder();
        if(!is_dir($folder))
        {

            if(!mkdir($folder, 0777, true))
            {
                throw new Exception("Can not create Filesystem folder: '" . $folder . "'. Please verify permissions.");
            }
        }

        return $folder;
    }

    public function getFilesystemFolder()
    {

        $config = Bootstrap::getSingleton()->getConfig();
        if(isset($config['app']['filesystem']))
        {
            return $config['app']['filesystem'];
        }
        return "filesystem";
    }

    public function getFilePath()
    {
        return $this->getStorage() . "/" . $this->file;
    }

    public function getAbsolutePath()
    {
        return realpath($this->getStorage()) . "/" . $this->file;
    }

    public function touch()
    {
        return touch($this->getAbsolutePath());
    }
    public function exists()
    {
        return file_exists($this->getAbsolutePath());
    }

    public function url()
    {
        $scheme = "http://";
        if($_SERVER['HTTPS']) $scheme = "https://";
        return $scheme . $_SERVER['HTTP_HOST'] . "/" . $this->getFilePath();
    }

    public function open($mode = "rw")
    {
        return ($this->pfile = fopen($this->getAbsolutePath(), $mode));
    }

    public function close()
    {
        return fclose($this->pfile);
    }

    public function read()
    {
        return file_get_contents($this->getAbsolutePath());
    }
    public function write($content)
    {
        return file_put_contents($this->getAbsolutePath(), $content);
    }

}