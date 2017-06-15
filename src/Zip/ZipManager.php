<?php

namespace GLFramework\Zip;
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 15/06/17
 * Time: 15:30
 */
class ZipManager
{
    private $file;
    /**
     * @var \ZipArchive
     */
    private $zip;

    /**
     * ZipManager constructor.
     * @param $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    private function open($mode = null)
    {
        if($this->zip)
        {
            throw new \Exception("ZIP file {$this->file} has already opened!");
        }
        $this->zip = new \ZipArchive();
        return $this->zip->open($this->file, $mode) === TRUE;
    }

    public function extractAll($to)
    {
        if($this->open())
        {
            $this->zip->extractTo($to);
        }
        return false;
    }

    public function listFiles()
    {

    }

    public function extractFile($file, $to)
    {

    }

    public function addFile($file)
    {

    }

    public function addFolder($file)
    {

    }

    public function addString($file, $string)
    {

    }

    public function save()
    {

    }

    public function close()
    {
        $this->zip->close();
        $this->zip = null;
    }

    public function getFile()
    {
        return $this->file;
    }



}