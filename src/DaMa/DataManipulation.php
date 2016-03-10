<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 1/03/16
 * Time: 10:17
 */

namespace GLFramework\DaMa;

use GLFramework\DaMa\Manipulators\CSVManipulator;
use GLFramework\DaMa\Manipulators\XLSManipulator;

define('DATA_MANIPULATION_CREATE_MODE_AUTO', 0);
define('DATA_MANIPULATION_CREATE_MODE_CSV', 1);
define('DATA_MANIPULATION_CREATE_MODE_XLS', 2);
define('DATA_MANIPULATION_CREATE_MODE_XLSX', 3);
define('DATA_MANIPULATION_CREATE_MODE_ODS', 4);

class DataManipulation
{
    public function getModeByFile($file)
    {
        if(strpos($file, ".") !== FALSE)
        {
            $ext = substr($file, strrpos($file, "."));
            if($ext == ".csv") return DATA_MANIPULATION_CREATE_MODE_CSV;
            if($ext == ".xls") return DATA_MANIPULATION_CREATE_MODE_XLS;
            if($ext == ".xlsx") return DATA_MANIPULATION_CREATE_MODE_XLSX;
            if($ext == ".ods") return DATA_MANIPULATION_CREATE_MODE_ODS;
        }
    }
    /**
     * @param $file
     * @param int $mode
     * @return Manipulator
     */
    public function createFromFile($file, $mode = DATA_MANIPULATION_CREATE_MODE_AUTO)
    {
        $manipulator = new Manipulator();
        $manipulator->setFilename($file);
        if($mode == DATA_MANIPULATION_CREATE_MODE_AUTO)
        {
            $mode = $this->getModeByFile($file);
        }
        if($mode == DATA_MANIPULATION_CREATE_MODE_ODS) $manipulator->setCore(new CSVManipulator());
        else if($mode == DATA_MANIPULATION_CREATE_MODE_XLS) $manipulator->setCore(new XLSManipulator());
        else if($mode == DATA_MANIPULATION_CREATE_MODE_XLSX) $manipulator->setCore(new XLSManipulator());
        else $manipulator->setCore(new CSVManipulator());

        return $manipulator;
    }
}