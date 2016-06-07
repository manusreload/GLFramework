<?php
/**
 *     GLFramework, small web application framework.
 *     Copyright (C) 2016.  Manuel Muñoz Rosa
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
}