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
    var $controller;
    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    function collect()
    {
        // TODO: Implement collect() method.
        if($this->controller)
        {
            return array(
                'name' => $this->controller->name,
                'controller' => $this->controller
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
        $this->controller = $controller;
    }
    
    
}