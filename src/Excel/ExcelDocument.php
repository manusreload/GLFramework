<?php
/**
 *     GLFramework, small web application framework.
 *     Copyright (C) 2016.  Manuel Muñoz Rosa
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Created by PhpStorm.
 * User: manus
 * Date: 13/10/16
 * Time: 17:28
 */

namespace GLFramework\Excel;

use GLFramework\Filesystem;
use GLFramework\Model;

/**
 * Class ExcelDocument
 *
 * @package GLFramework\Excel
 */
class ExcelDocument
{

    public $excel;
    /**
     * @var \PHPExcel_Worksheet
     */
    private $sheet;

    /**
     * ExcelDocument constructor.
     *
     * @param null $file
     * @param string $type
     */
    public function __construct($file = null, $type = 'Excel2007')
    {
        if ($file !== null) {
            $objReader = \PHPExcel_IOFactory::createReader($type);
            $this->excel = $objReader->load($file);
        } else {
            $this->excel = new \PHPExcel();
        }
        $this->setActiveSheet(0);
    }

    /**
     * TODO
     *
     * @param $index
     */
    public function setActiveSheet($index)
    {
        $this->sheet = $this->excel->setActiveSheetIndex($index);
    }

    /**
     * TODO
     *
     * @param $column
     * @param int $row
     * @return mixed
     */
    public function getCellValue($column, $row = 1)
    {
        return $this->sheet->getCellByColumnAndRow($column, $row);
    }

    /**
     * TODO
     *
     * @param $column
     * @param $row
     * @param $value
     * @return \PHPExcel_Cell
     */
    public function setCellValue($column, $row, $value)
    {
        return $this->sheet->setCellValueByColumnAndRow($column, $row, $value, true);
    }

    /**
     * TODO
     *
     * @param $name
     * @param $value
     * @return mixed
     */
    public function setCellValueByName($name, $value)
    {
        return $this->sheet->setCellValue($name, $value, true);
    }

    /**
     * TODO
     *
     * @param $style
     * @param $column1
     * @param $row1
     * @param null $column2
     * @param null $row2
     */
    public function setStyle($style, $column1, $row1, $column2 = null, $row2 = null)
    {
        $this->sheet->getStyleByColumnAndRow($column1, $row1, $column2, $row2)->applyFromArray($style);
    }

    /**
     * TODO
     *
     * @param $header
     * @param $models
     * @param int $offset
     * @return int
     */
    public function fillModels($header, $models, $offset = 1)
    {
        $colum = 0;
        $row = $offset;

        $styleArray = array(
            'font' => array(
                'bold' => true,
            )
        );
        foreach ($header as $name => $item) {
            $this->setCellValue($colum++, $row, $name);
        }
        $this->setStyle($styleArray, 0, $row, $colum, $row);
        $row++;
        foreach ($models as $model) {
            $colum = 0;
            foreach ($header as $name => $item) {
                $this->sheet->setCellValueByColumnAndRow($colum++, $row, $this->getValue($item, $model));
            }
            $row++;
        }
        $colum = 0;
        foreach ($header as $name => $item) {
            $this->sheet->getColumnDimensionByColumn($colum++)->setAutoSize(true);
        }

        return $row;
    }

    /**
     * TODO
     *
     * @param $name
     */
    public function sendAsDownload($name)
    {

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $name . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $filePath = Filesystem::allocate(); // Crear un archivo temporal
        $objWriter->save($filePath);
        readfile($filePath);
        unlink($filePath);
    }

    /**
     * TODO
     *
     * @param $file
     * @param null $folder
     * @return string
     */
    public function saveAsFile($file, $folder = null)
    {
        $fs = new Filesystem($file, $folder);
        $objWriter = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $objWriter->save($fs->getAbsolutePath());
        return $fs->getAbsolutePath();
    }

    /**
     * TODO
     */
    public function executeFormulas()
    {
    }

    /**
     * TODO
     *
     * @param $cell
     */
    public function executeFormula($cell)
    {
        $phpCell = $this->sheet->getCell($cell);
        $phpCell->setValue($phpCell->getFormattedValue());
    }

    /**
     * TODO
     *
     * @param $item
     * @param $model
     * @return mixed|string
     */
    private function getValue($item, $model)
    {
        if (is_string($item)) {
            if ($model instanceof Model) {
                if (isset($model->{$item})) {
                    return $model->{$item};
                }
                return $item;
            } else {
                if (isset($model[$item])) {
                    return $model[$item];
                }
                return $item;
            }
        } else if (is_callable($item)) {
            return call_user_func($item, $model);
        }
        return '';
    }
}
