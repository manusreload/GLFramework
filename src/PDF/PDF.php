<?php
/**
 * Created by PhpStorm.
 * User: mmuno
 * Date: 27/06/2016
 * Time: 11:44
 */

namespace GLFramework\PDF;

use GLFramework\View;

define('PDF_ORIENTATION_VERTICAL', 'P');
define('PDF_ORIENTATION_HORIZONTAL', 'L');
//if(!defined('K_TCPDF_THROW_EXCEPTION_ERROR')) {
//    define('K_TCPDF_THROW_EXCEPTION_ERROR', true);
//}
//

/**
 * Class PDF
 *
 * @package GLFramework\PDF
 */
class PDF
{
    /**
     * @var \TCPDF
     */
    var $pdf;

    /**
     * PDF constructor.
     * @param string $orientation
     */
    public function __construct($orientation = 'P', $pdf = null)
    {
        if(!$pdf) {
            $this->pdf = new \TCPDF($orientation, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        } else {
            $this->pdf = $pdf;
        }
        $this->pdf->SetFont('helvetica', '', 10);
    }

    public function setHeader($bool)
    {
        $this->pdf->setPrintHeader($bool);
    }

    /**
     * TODO
     *
     * @param $url
     */
    public function image($url)
    {
        list($width, $height) = getimagesize($url);
        $this->pdf->Image($url, '', '', 100);
    }

    /**
     * TODO
     *
     * @param $controller
     * @param $template
     * @param array $data
     * @return array|string
     */
    public function render($controller, $template, $data = array())
    {
        $view = new View($controller);
        $data = $view->display($template, $data);
        $this->pdf->AddPage();
        $this->pdf->writeHTML($data, true, false, true, false, '');
        return $data;
    }

    /**
     * TODO
     *
     * @param null $title
     * @return mixed
     */
    public function sendAsPDF($title = null)
    {
        return $this->output($title, 'I');
    }

    /**
     * TODO
     *
     * @param null $title
     * @return mixed
     */
    public function sendAsDownload($title = null)
    {
        return $this->output($title, 'D');
    }

    /**
     * TODO
     *
     * @param $file
     * @return mixed
     */
    public function saveToFile($file)
    {
        return $this->output($file, 'F');
    }

    /**
     * TODO
     *
     * @param $name
     * @param $type
     * @return mixed
     */
    public function output($name, $type)
    {
        return $this->pdf->Output($name, $type);
    }
}
