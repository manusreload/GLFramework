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

    /**
     * DataTablesManager constructor.
     * @param $dataSource
     */
    public function __construct($dataSource)
    {
        $this->dataSource = $dataSource;
    }

    public function process($fields)
    {
        $data = array();
        foreach($this->dataSource as $model)
        {
            $data[] = $model->json($fields);
        }
        header("Content-Type: text/json");

        echo json_encode($data);
        die();
    }


}