<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 24/02/16
 * Time: 15:02
 */

namespace GLFramework\Modules\Tests;


use GLFramework\Controller;
use GLFramework\DaMa\DataManipulation;

class tests extends Controller
{
    var $name = "Pruebas framework";
    var $admin = true;

    public function run()
    {
        if(isset($_POST['upload']))
        {
            $import = new DataManipulation();
            $manipulator = $import->createFromFile($_FILES['xls']['tmp_name'], DATA_MANIPULATION_CREATE_MODE_XLS);
            $manipulator->model('Vehiculo');
            $manipulator->field('PEDIDO', 'pedido');
            $manipulator->field('DIR CONCE', 'dir_envio');
            $manipulator->field('TIPO', 'tipo');
            $manipulator->field('ESTADO', 'estado');
            $manipulator->field('SEMANA', 'semana');
            $manipulator->field('TMAIMG', 'tmaimg');
            $manipulator->field('MODELO', 'modelo');
            $manipulator->field('DENOMINACION COMERCIAL', 'denominacion_comercial');
            $manipulator->field('COL EXT', 'color_exterior');
            $manipulator->field('COL INT', 'color_interior');
            $manipulator->field('OPCIONALES', 'opcionales');
            $manipulator->field('Nº FACTURA', 'n_factura_marca');
            $manipulator->field('FECHA FACTURA', 'fecha_factura_marca', 'fix_date');
            $manipulator->debug(5);

        }
    }

}