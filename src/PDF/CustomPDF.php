<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 2019-01-24
 * Time: 15:52
 */

namespace GLFramework\PDF;


class CustomPDF extends \TCPDF
{
    public function Header()
    {
        $this->writeHTML('<table><tr><td>Test</td><td>Custom!</td></tr></table>');
    }

}