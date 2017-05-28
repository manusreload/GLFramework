<?php
/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 30/09/2016
 * Time: 9:26
 */

namespace GLFramework\DaMa;

/**
 * Class DataExample
 *
 * @package GLFramework\DaMa
 */
class DataExample
{

    public $columns;

    /**
     * TODO
     *
     * @param $name
     * @param $value
     */
    public function addColumn($name, $value)
    {
        $this->columns[$name][] = $value;
    }

    /**
     * TODO
     *
     * @param string $file
     */
    public function getAsCSV($file = 'php://output')
    {
        $headers = array();
        $values = array();
        foreach ($this->columns as $key => $item) {
            foreach ($item as $name) {
                $headers[] = $name;
                $values[] = $key;
            }
        }
        $fp = fopen($file, 'wb');
        fputcsv($fp, $headers, ';');
        fputcsv($fp, $values, ';');
        fclose($fp);
    }
}
