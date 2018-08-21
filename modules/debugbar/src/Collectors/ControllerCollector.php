<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 24/05/16
 * Time: 10:46
 */

namespace GLFramework\Modules\Debugbar\Collectors;


use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use GLFramework\Controller;
use GLFramework\Response;

class ControllerCollector extends DataCollector implements Renderable
{


    /**
     * @var Controller
     */
    var $controllerStorage;
    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    function collect()
    {
        // TODO: Implement collect() method.
        if($this->controllerStorage)
        {
            return array(
                'name' => $this->controllerStorage->name,
                'controller' => $this->getVariables()
            );
        }
    }

    /**
     * Returns the unique name of the collector
     *
     * @return string
     */
    function getName()
    {
        // TODO: Implement getName() method.
        return "controller";
    }

    /**
     * Returns a hash where keys are control names and their values
     * an array of options as defined in {@see DebugBar\JavascriptRenderer::addControl()}
     *
     * @return array
     */
    public function getWidgets()
    {
        return array(
            "controller" => array(
                "icon" => "tags",
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => "controller",
                "default" => "{}"
            )
        );
    }

    /**
     * @param $controller Controller
     */
    public function setController($controller)
    {
        $this->controllerStorage = $controller;
    }

    public function getVariables()
    {
        $array = array();
        $rf = new \ReflectionClass($this->controllerStorage);
        $p = $rf->getProperties();
        foreach ($p as $item)
        {
            if($item->isPublic())
            {
                $value = $this->controllerStorage->{$item->name};
                if(is_array($value)) {
                    $array[$item->name] = json_encode($value);
                } else if(is_string($value)) {
                    $array[$item->name] = $value;
                } else {
                    $array[$item->name] = (array) ($value);
                }
            }
        }
        return json_encode($array, 128);

    }
    
    
}