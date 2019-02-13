<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 2019-01-24
 * Time: 16:01
 */

namespace GLFramework\PDF;

use GLFramework\Controller;

/**
 * Class TwigPDF
 *
 * Renderiza archivos con formato Twig a PDF.
 * @package GLFramework\PDF
 */
class TwigPDF extends \TCPDF
{

    private $header = null;
    private $footer = null;
    private $footerLast = null;
    private $body = null;
    private $customRender;
    private $footerHeight;
    private $footerCallback;
    public $props = [];
    /**
     * @param $controller Controller
     * @param $template
     * @param $data array Parametros para la renderizacion de la vista Twig
     */
    public function render($controller, $template, $data = array()) {
        $data['this'] = $controller;
        $data['pdf'] = $this;
        $twig = $controller->getView()->getTwig();
        $twigTemplate = $twig->loadTemplate($template);
        if($twigTemplate instanceof \Twig_Template) {
            if($twigTemplate->hasBlock('variables')) {
                $this->footer = $twigTemplate->renderBlock('variables', $data, $twigTemplate->getBlocks());
            }
            if($twigTemplate->hasBlock('header')) {
                $this->header = $twigTemplate->renderBlock('header', $data);
            }
            $this->body = $twigTemplate->renderBlock('body', $data);
            if($twigTemplate->hasBlock('footer')) {
                $this->footer = $twigTemplate->renderBlock('footer', $data);
            }
            if($twigTemplate->hasBlock('last_footer')) {
                $this->footerLast = $twigTemplate->renderBlock('last_footer', $data);
            }

            $this->AddPage();
            $this->writeHTML($this->body, true, false, true, true, '');
            if(is_callable($this->customRender)) {
                call_user_func($this->customRender, $this);
            }
//            $this->lastPage();
        }
    }

    /**
     * @return mixed
     */
    public function getCustomRender()
    {
        return $this->customRender;
    }

    /**
     * @param mixed $customRender
     */
    public function setCustomRender($customRender)
    {
        $this->customRender = $customRender;
    }




    public function Header()
    {
        if($this->header) {
            $this->SetY(5);
            $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $this->header, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
        }

    }
    protected $last_page_flag = false;
    private $position = 0;

    public function Close() {
        $this->last_page_flag = true;
        $this->position = $this->GetY();
        parent::Close();
    }

    public function Footer()
    {

        if($this->footerCallback) {
            call_user_func($this->footerCallback, $this);
        }
        $this->SetY(-15);
        // Page number
        $this->Cell(0, 15, $this->footer, 0, false, 'C', 0, '', 0, false, 'T', 'M');

        if ($this->last_page_flag) {
            if($this->position + $this->footerHeight >  $this->getPageHeight()) {
                $this->last_page_flag = false;
                $this->AddPage();
            }
            $this->SetY(-$this->footerHeight);
//            $this->writeHTMLCell($w = 0, $h = 45, $x = '', $y = '', $this->footer, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
            $this->writeHTMLCell($w = 0, $h = $this->footerHeight, $x = '', $y = '', $this->footerLast, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
        }
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
     * @return mixed
     */
    public function getFooterHeight()
    {
        return $this->footerHeight;
    }

    /**
     * @param mixed $footerHeight
     */
    public function setFooterHeight($footerHeight)
    {
        $this->footerHeight = $footerHeight;
    }

    /**
     * @return mixed
     */
    public function getFooterCallback()
    {
        return $this->footerCallback;
    }

    /**
     * @param mixed $footerCallback
     */
    public function setFooterCallback($footerCallback)
    {
        $this->footerCallback = $footerCallback;
    }

    public function setProp($name, $value) {
        $this->props[$name] = $value;
    }



//
//    /**
//     * TODO
//     *
//     * @param $name
//     * @param $type
//     * @return mixed
//     */
//    public function output($name, $type)
//    {
//        return $this->Output($name, $type);
//    }

}