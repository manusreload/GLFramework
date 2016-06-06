<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 7/03/16
 * Time: 10:39
 */

namespace GLFramework\Utils;


use GLFramework\Model;

class DataTablesManager
{
    /**
     * @var Model[]
     */
    var $dataSource;

    var $callback;

    /**
     * DataTablesManager constructor.
     * @param $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function row($row)
    {
        return call_user_func($this->callback, $row);
    }

    public function process($fields)
    {
        $data = array();
        foreach($fields as $model)
        {
            $data[] = $this->row($model);
        }
        header("Content-Type: text/json");

        echo json_encode(array("data" => $data));
        die();
    }


}