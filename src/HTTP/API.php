<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 25/04/16
 * Time: 17:01
 */
namespace GLFramework\HTTP;

use GLFramework\Bootstrap;

class API
{
    private $config;

    /**
     * API constructor.
     * @param $config
     */
    public function __construct($config = null)
    {
        if($config == null)
        {
            $config = Bootstrap::getSingleton()->getConfig();
        }
        $this->config = $config;
    }

    public function getEndpoint()
    {
        return $this->config['api']['endpoint'];
    }

    public function getURI($baseUri)
    {
        return $this->getEndpoint() . $baseUri;
    }

    public function getAuth()
    {
        return array("X-Authorization: " . $this->config['api']['autorization']);
    }


    public function post($uri, $fields)
    {
        $url = $this->getURI($uri);
        return post($url, $fields, $this->getAuth());
    }

    public function delete($uri, $fields)
    {
        $url = $this->getURI($uri);
        return custom_http_request("DELETE", $url, $fields, $this->getAuth());
    }

    public function get($uri, $fields = array())
    {
        $fields_string = "";
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');
        $url = $this->getURI($uri) . "?" . $fields_string;
        return get($url, $this->getAuth());
    }

    /**
     * @return array|mixed|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    
}