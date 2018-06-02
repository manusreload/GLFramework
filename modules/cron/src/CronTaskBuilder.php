<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 19/06/17
 * Time: 14:04
 */

namespace GLFramework\Modules\Cron;


use GLFramework\Module\Module;

class CronTaskBuilder
{
    private $tasks = array();
    public function addTask($module, $title, $function)
    {
        if ($module instanceof Module) {
            $module = $module->title;
        }
        $this->tasks[] = array('module' => $module, 'title' => $title, 'fn' => $function);
    }

    /**
     * @return array
     */
    public function getTasks()
    {
        return $this->tasks;
    }



}