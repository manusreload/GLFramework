<?php

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 16/6/17
 * Time: 16:59
 */

namespace GLFramework\Cron;

class CronTask
{
    private $module;
    private $title;
    private $function;

    /**
     * CronTask constructor.
     * @param $module
     * @param $title
     * @param $function
     */
    public function __construct($module, $title, $function)
    {
        $this->module = $module;
        $this->title = $title;
        $this->function = $function;
    }


}