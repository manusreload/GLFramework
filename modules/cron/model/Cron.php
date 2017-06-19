<?php
/**
 * Created by PhpStorm.
 * User: mmunoz
 * Date: 19/06/17
 * Time: 13:02
 */

class Cron extends \GLFramework\Model
{
    public $id;
    public $title;
    public $cron;
    public $enabled;
    public $function;
    public $last_run;
    protected $table_name = 'cron_tasks';
    protected $definition = array(
        'index' => 'id',
        'fields' => array(
            'title' => "varchar(64)",
            'cron' => "varchar(20)",
            'enabled' => "int(1)",
            'function' => "varchar(128)",
            'last_run' => "datetime",
        )
    );

    public function run()
    {
        $method = instance_method($this->function);
    }
}