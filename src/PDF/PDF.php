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


    /**
     * @var \TCPDF
     */
    var $pdf;

    /**
     * PDF constructor.
     */
    public function __construct()
    {
        $this->pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->pdf->SetFont('helvetica', '', 10);
    }

    public function image($url)
    {
        list($width, $height) = getimagesize($url);
        $this->pdf->Image($url, '', '', 100);

    }
    public function render($controller, $template, $data = array())
    {
        $view = new View($controller);
        $data = $view->display($template, $data);
        $this->pdf->AddPage();
        $this->pdf->writeHTML($data, true, false, true, false, '');
        return $data;
    }

    public function sendAsPDF($title = null)
    {


// set font

        $this->pdf->Output($title, 'I');
        die();
        $response->setContentType("application/pdf");
        $response->setContent($data);
    }
}