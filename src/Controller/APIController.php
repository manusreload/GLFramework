<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 22/04/16
 * Time: 12:59
 */

namespace GLFramework\Controller;


use GLFramework\Controller;
use GLFramework\Middleware\APIAuthorizationMiddleware;

class APIController extends Controller
{
    /**
     * APIController constructor.
     * @param string $base
     * @param Module|null $module
     */
    public function __construct($base, $module)
    {
        parent::__construct($base, $module);
        $this->addMiddleware(new APIAuthorizationMiddleware());
    }


    public function run()
    {
        // TODO: Implement run() method.
    }

    /**
     * @param $data
     * @param $params
     * @return array|null|string
     */
    public function display($data, $params)
    {
        return json_encode($data);
    }


}