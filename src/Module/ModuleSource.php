<?php
/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 28/09/2016
 * Time: 12:25
 */

namespace GLFramework\Module;


class ModuleSource
{
    private $title;
    private $instance;
    protected $config;
    /**
     * ModuleSource constructor.
     * @param $title
     */
    public function __construct($title)
    {
        $this->title = $title;
        if($title)
        {
            $this->instance = ModuleManager::getModuleInstanceByName($title);
            $this->config = $this->instance->getConfig();
        }
    }

}