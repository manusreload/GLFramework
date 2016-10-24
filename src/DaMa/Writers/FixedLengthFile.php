<?php

namespace GLFramework\DaMa\Writers;
use GLFramework\DaMa\Association;
use GLFramework\Model;

/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 24/10/2016
 * Time: 13:54
 */
class FixedLengthFile extends WriterBase
{

    /**
     * @param $model Model
     * @param $map Association[]
     * @return mixed
     */
    public function write($model, $map)
    {
        // TODO: Implement write() method.
        $line = "";
        foreach ($map as $key => $assoc)
        {
            $size = $assoc->getFirstNameInFile();
            $value = $assoc->get($model, $key);
            $line .= $this->fixed_size($value, $size);
        }
        fwrite($this->fp, $line . "\n");
    }
    
    private function fixed_size($line, $length)
    {
        $strlen = strlen($line);
        if($strlen > $length)
        {
            return substr($line, 0, $length);
        }
        if($strlen < $length)
        {
            return str_repeat(" ", $length - $strlen) . $line;
        }
        return $line;
    }
}