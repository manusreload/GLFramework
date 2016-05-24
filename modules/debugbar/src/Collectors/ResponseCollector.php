<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 24/05/16
 * Time: 10:46
 */

namespace GLFramework\Modules\Debugbar\Collectors;


use DebugBar\DataCollector\DataCollector;

class ResponseCollector extends DataCollector
{

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    function collect()
    {
        // TODO: Implement collect() method.
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
}