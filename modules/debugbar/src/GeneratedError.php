<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 27/5/16
 * Time: 16:23
 */

namespace GLFramework\Modules\Debugbar;


use Exception;

class GeneratedError
{

    var $message;
    var $code;
    var $line;
    var $file;
    public function __construct($message, $code, $file, $line)
    {
        $this->message = $message;
        $this->code = $code;
        $this->file = $file;
        $this->line = $line;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getCode()
    {

        return $this->code;
    }

    public function getFile()
    {

        return $this->file;
    }

    public function getLine()
    {

        return $this->line;
    }


}