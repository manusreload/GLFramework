<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 18/04/16
 * Time: 11:43
 */

namespace GLFramework\Modules\CSRF;


use Exception;

class CSRFTokenVerificationFailedException extends \Exception
{

    public function __construct($message)
    {
        $message = "Se ha producido un error al verificar la petición. $message.
        Es posible que se ha intentado volver a realizar la petición.";
        parent::__construct($message);
    }


}