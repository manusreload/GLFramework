<?php
/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 30/09/2016
 * Time: 9:26
 */

namespace GLFramework\DaMa;


class DataExample
{

    public $columns;


    public function addColumn($name, $value)
    {
        $this->columns[$name][] = $value;
    }
    
    public function getAsCSV($file = "php://output")
    {
        $headers = array();
        $values = array();
        foreach ($this->columns as $key => $item)
        {
            foreach ($item as $name)
            {
                $headers[] = $name;
                $values[] = $key;
            }
        }
        $fp = fopen($file, "w");
        fputcsv($fp, $headers, ";");
        fputcsv($fp, $values, ";");
        fclose($fp);
    }
}