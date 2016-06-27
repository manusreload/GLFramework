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
use GLFramework\Response;

class ResponseCollector extends DataCollector implements Renderable
{

    /**
     * @var Response
     */
    var $response;
    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    function collect()
    {
        // TODO: Implement collect() method.
        if($this->response)
        {
            return array(
                'contentType' => $this->response->getContentType(),
                'responseCode' => $this->response->getResponseCode(),
                'redirection' => $this->response->getRedirection()
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
        return "response";
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
            "response" => array(
                "icon" => "tags",
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => "response",
                "default" => "{}"
            )
        );
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }
    
    
}