<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 14/03/16
 * Time: 15:06
 */

namespace GLFramework\Modules\Debugbar\Collectors;


class MySQLiCollector extends \DebugBar\DataCollector\DataCollector implements \DebugBar\DataCollector\Renderable, DebugBar\DataCollector\AssetProvider
{
    // ...

    public function getWidgets()
{
    return array(
        "database" => array(
            "icon" => "inbox",
            "widget" => "PhpDebugBar.Widgets.SQLQueriesWidget",
            "map" => "pdo",
            "default" => "[]"
        )
    );
}

    public function getAssets()
{
    return array(
        'css' => 'widgets/sqlqueries/widget.css',
        'js' => 'widgets/sqlqueries/widget.js'
    );
}

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
        return "mysqli_collector";
    }
}