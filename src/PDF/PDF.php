<?php
/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 27/06/2016
 * Time: 11:44
 */

namespace GLFramework\PDF;


use GLFramework\Response;
use GLFramework\View;

class PDF
{

    public function render($controller, $template, $data = array())
    {
        $view = new View($controller);
        return $view->display($template, $data);
    }

    /**
     * @param $response Response
     * @param $data
     */
    public function sendAsPDF($response, $data)
    {
        
        $response->setContentType("application/pdf");
        $response->setContent($data);
    }
}