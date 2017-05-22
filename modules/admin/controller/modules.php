<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 06/04/17
 * Time: 12:07
 */

namespace GLFramework\Modules\Admin;


use GLFramework\Bootstrap;
use GLFramework\ConfigurationManager;
use GLFramework\Controller\AuthController;
use GLFramework\Module\Module;
use GLFramework\Module\ModuleManager;
use GLFramework\Module\ModuleScanner;

class modules extends AuthController
{

    var $admin = true;
    var $name = "Gestor de modulos";

    /**
     * @var Module[]
     */
    var $modules;
    var $module;
    public function run()
    {
        parent::run(); // TODO: Change the autogenerated stub
        $moduleScanner = new ModuleScanner();
        $this->modules = $moduleScanner->scan(".");

        $configManager = new ConfigurationManager(ConfigurationManager::getAutogeneratedFile());
        if(isset($this->params['name']))
        {
            $module = null;
            $name = urldecode($this->params['name']);
            foreach ($this->modules as $module1)
            {

                if($module1->title == $name)
                {
                    $module = $module1;
                    break;
                }
            }
            if($module != null)
            {
                if(isset($this->params['state']))
                {
                    $config = $configManager->load();
                    if($this->params['state'] == "enable")
                    {
                        $configManager->enableModule($config, $module);
                    }
                    else if($this->params['state'] == "disable")
                    {
                        $configManager->disableModule($config, $module);
                    }
                    if($configManager->save($config))
                    {
                        $this->addMessage("Se ha guardado correctamente");
                        $this->quit($this->getLink($this, array('name' => $this->params['name'])));
                    }
                    else
                    {
                        $this->addMessage("No se ha podido guardar la configuracion", "danger");
                    }
                }
                $this->module = $module;
                $this->module->init();
                if(isset($_POST['settings']))
                {
                    $config = $configManager->load();
                    $configManager->setModuleSettings($config, $module, $_POST['settings']);
                    if($configManager->save($config))
                    {
                        $this->addMessage("Se ha guardado correctamente");
                        $this->quit($this->getLink($this, array('name' => $this->params['name'])));
                    }
                    else
                    {
                        $this->addMessage("No se ha podido guardar la configuracion", "danger");
                    }
                }
                $this->setTemplate("module.twig");
            }
            else
            {
                $this->addMessage("No se ha encontrado el módulo", "danger");
            }
        }
    }


    /**
     * @param $config
     * @param $module Module
     * @return mixed
     */
    public function getModuleConfiguration($config, $module)
    {
        return $config['modules'][$module->getFolderContainer()][$module->getListName()];
    }

    /**
     * @param $config
     * @param $module Module
     * @param $settings
     * @return bool
     */
    public function setModuleConfiguration(&$config, $module, $settings)
    {
        $keys = &$config['modules'][$module->getFolderContainer()];
        foreach ($keys as $key => &$value)
        {
            if(is_array($value) && isset($value[$module->getListName()]))
            {
                $value[$module->getListName()] = $settings;
                return true;
            }
            elseif(strval($value) == $module->getListName())
            {
                $value =  array($module->getListName() => $settings);
//                print_debug($config);
                return true;

            }
        }
        $keys[] = array($module->getListName() => $settings);
        return false;
    }


}