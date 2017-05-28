<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 28/5/17
 * Time: 22:28
 */

namespace GLFramework\Resources;


use GLFramework\Module\ModuleManager;

class ResourceManager
{

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * ResourceManager constructor.
     * @param ModuleManager $moduleManager
     */
    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    public function checkResources()
    {
        return false;
    }
}
