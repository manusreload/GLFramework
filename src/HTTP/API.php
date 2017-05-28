<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 25/04/16
 * Time: 17:01
 */

namespace GLFramework\HTTP;

use GLFramework\Bootstrap;

/**
 * Class API
 *
 * @package GLFramework\HTTP
 */
class API
{
    private $config;

    /**
     * API constructor.
     * @param $config
     */
    public function __construct($config = null)
    {
        if ($config === null) {
            $config = Bootstrap::getSingleton()->getConfig();
        }
        $this->config = $config;
    }

    /**
     * TODO
     *
     * @return mixed
     */
    public function getEndpoint()
    {
        return $this->config['api']['endpoint'];
    }

    /**
     * TODO
     *
     * @param $baseUri
     * @return string
     */
    public function getURI($baseUri)
    {
        return $this->getEndpoint() . $baseUri;
    }

    /**
     * TODO
     *
     * @return array
     */
    public function getAuth()
    {
        return array('X-Authorization: ' . $this->config['api']['autorization']);
    }

    /**
     * TODO
     *
     * @param $uri
     * @param $fields
     * @return mixed
     */
    public function post($uri, $fields)
    {
        $url = $this->getURI($uri);
        return post($url, $fields, $this->getAuth());
    }

    /**
     * TODO
     *
     * @param $uri
     * @param $fields
     * @return mixed
     */
    public function delete($uri, $fields)
    {
        $url = $this->getURI($uri);
        return custom_http_request('DELETE', $url, $fields, $this->getAuth());
    }

    /**
     * TODO
     *
     * @param $uri
     * @param array $fields
     * @return mixed
     */
    public function get($uri, $fields = array())
    {
        $fields_string = '';
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');
        $url = $this->getURI($uri) . '?' . $fields_string;
        return get($url, $this->getAuth());
    }

    /**
     * TODO
     *
     * @param $uri
     * @param $fields
     * @return mixed
     */
    public function put($uri, $fields)
    {
        $url = $this->getURI($uri);
        return custom_http_request('PUT', $url, $fields, $this->getAuth());
    }

    /**
     * TODO
     *
     * @param null $key
     * @return mixed
     */
    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->config['api'];
        }
        return $this->config['api'][$key];
    }
}
