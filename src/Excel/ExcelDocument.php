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

class ExcelDocument
{

    public $excel;
    /**
     * @var \PHPExcel_Worksheet
     */
    private $sheet;

    /**
     * ExcelDocument constructor.
     */
    public function __construct()
    {
        $this->excel = new \PHPExcel();
        $this->setActiveSheet(0);
    }

    public function setActiveSheet($index)
    {
        $this->sheet = $this->excel->setActiveSheetIndex($index);
    }

    /**
     * @param $column
     * @param $row
     * @param $value
     * @return \PHPExcel_Cell
     */
    public function setCellValue($column, $row, $value)
    {
        return $this->sheet->setCellValueByColumnAndRow($column, $row, $value, true);
    }

    public function setStyle($style, $column1, $row1, $column2 = null, $row2 = null)
    {
        $this->sheet->getStyleByColumnAndRow($column1, $row1, $column2, $row2)->applyFromArray($style);
    }

    public function fillModels($header, $models, $offset = 1)
    {
        $colum = 0;
        $row = $offset;

        $styleArray = array(
            'font'  => array(
                'bold'  => true,
            ));
        foreach ($header as $name => $item)
        {
            $this->setCellValue($colum++, $row, $name);
        }
        $this->setStyle($styleArray, 0, $row, $colum, $row);
        $row++;
        foreach ($models as $model)
        {
            $colum = 0;
            foreach ($header as $name => $item)
            {
                $this->sheet->setCellValueByColumnAndRow($colum++, $row, $this->getValue($item, $model));
            }
            $row++;
        }

        return $row;
    }


    public function getValue($item, $model)
    {
        if(is_string($item))
        {
            if(isset($model->{$item}))
                return $model->{$item};
            return $item;
        }
        else if(is_callable($item))
        {
            return call_user_func($item, $model);
        }
        return "";
    }

    public function sendAsDownload($name)
    {

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $name . '.xls"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
        $objWriter = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $filePath = Filesystem::allocate(); // Crear un archivo temporal
        $objWriter->save($filePath);
        readfile($filePath);
        unlink($filePath);
    }
}