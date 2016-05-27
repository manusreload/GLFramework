<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 27/05/16
 * Time: 11:43
 */

namespace GLFramework\Modules\Debugbar\Collectors;


class RequestDataCollector extends \DebugBar\DataCollector\RequestDataCollector
{

    var $extra = array();

    public function collect()
    {
        $vars = array('_GET', '_POST', '_SESSION', '_COOKIE', '_SERVER');
        $data = array();

        foreach ($vars as $var) {
            if (isset($GLOBALS[$var])) {
                $data["$" . $var] = $this->getDataFormatter()->formatVar($GLOBALS[$var]);
            }
        }
        foreach ($this->extra as $name => $var) {
            $data["$" . $name] = $this->getDataFormatter()->formatVar($var);
        }

        return $data;
    }


    public function addRequestData($name, $data)
    {
        $this->extra[$name] = $data;
    }
}