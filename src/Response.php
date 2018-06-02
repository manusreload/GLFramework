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
 * Date: 16/03/16
 * Time: 13:05
 */

namespace GLFramework;

/**
 * Class Response
 *
 * @package GLFramework
 */
class Response
{
    private $content;
    private $uri;
    private $contentType = 'text/html';
    private $redirection = null;
    private $ajax;
    private $responseCode = 200;

    /**
     * Obtener el contenid de la respuesta
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Establecer el contenido de la respuesta
     *
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Obtener el tipo de contenido de la respuesta
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Establece el tipo de contenido de la respuesta
     *
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Obtiene la url de la redireccion
     *
     * @return mixed
     */
    public function getRedirection()
    {
        return $this->redirection;
    }

    /**
     * Establece la URI de la redireccion
     *
     * @param mixed $redirection
     */
    public function setRedirection($redirection)
    {
        $this->redirection = $redirection;
    }

    /**
     * Envia la respuesta al cliente
     */
    public function display()
    {
        \http_response_code($this->getResponseCode());
        if ($this->contentType) {
            header('Content-Type: ' . $this->contentType);
        }
        //        header('Content-Length: ' . strlen($this->content));
        if ($this->redirection) {
            header('Location: ' . $this->redirection);
        }
        Events::dispatch('beforeResponseSend', array($this));
//        echo " ";
//        print_r($_SESSION);
        session_write_close();
        print $this->content;
    }

    /**
     * TODO
     *
     * true si la respueta tiene una redireccion
     * @return bool
     */
    public function isRedirect()
    {
        return $this->redirection !== null;
    }

    /**
     * Obtener la URI de la respuesta
     *
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Establecer la URI de respuesta
     *
     * @param $url
     */
    public function setUri($url)
    {
        $this->uri = $url;
    }

    /**
     * Devuelve el estado de AJAX
     *
     * @return mixed
     */
    public function getAjax()
    {
        return $this->ajax;
    }

    /**
     * Establece el estado de AJAX
     *
     * @param mixed $ajax
     */
    public function setAjax($ajax)
    {
        $this->ajax = $ajax;
    }

    /**
     * Obtener el codigo de respuesta HTTP
     *
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Establece el codigo de respuesta HTTP
     *
     * @param int $responseCode
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
    }
}
