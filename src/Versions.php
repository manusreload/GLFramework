<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/1/16
 * Time: 19:38
 */

namespace GLFramework;


class Versions
{
    private $filename = "versions.json";
    public function getVersions()
    {
        return json_decode(file_get_contents($this->filename));
    }

    public function getLastVersion()
    {
        $versions = $this->getVersions();
        return $versions[0];
    }
}