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
use GLFramework\Model;

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
        $result = array();
        $header = array();
        $messages = array();
        foreach ($this->messages as $message)
        {
            $result[$message['type']][] = $message['message'];
        }
        $header['date'] = date("Y-m-d H:i:s");
        $header['controller'] = get_class($this);
//        $result['messages'] = $messages;

        $result['header'] = $header;
        if($data instanceof Model)
        {
            $result['data'] = $data->json();
        }
        else
        {
            $result['data'] = $data;
        }
        return json_encode($result);
    }


}