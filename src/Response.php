<?php
/**
 * Created by PhpStorm.
 * User: manus
 * Date: 16/03/16
 * Time: 13:05
 */

namespace GLFramework;


class Response
{
    private $content;
    private $uri;
    private $contentType = null;
    private $redirection = null;
    private $ajax;
    private $responseCode = 200;
    /**
     * Obtener el contenid de la respuesta
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Establecer el contenido de la respuesta
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Obtener el tipo de contenido de la respuesta
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Establece el tipo de contenido de la respuesta
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Obtiene la url de la redireccion
     * @return mixed
     */
    public function getRedirection()
    {
        return $this->redirection;
    }

    /**
     * Establece la URI de la redireccion
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
        Events::fire('beforeResponseSend', array($this));
        \http_response_code($this->getResponseCode());
        if($this->contentType) header("Content-Type: " . $this->contentType);
        if($this->redirection) header("Location: " . $this->redirection);
        print $this->content;
    }

    /**
     * true si la respueta tiene una redireccion
     * @return bool
     */
    public function isRedirect()
    {
        return $this->redirection !== null;
    }

    /**
     * Establecer la URI de respuesta
     * @param $url
     */
    public function setUri($url)
    {
        $this->uri = $url;
    }

    /**
     * Obtener la URI de la respuesta
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Devuelve el estado de AJAX
     * @return mixed
     */
    public function getAjax()
    {
        return $this->ajax;
    }

    /**
     * Establece el estado de AJAX
     * @param mixed $ajax
     */
    public function setAjax($ajax)
    {
        $this->ajax = $ajax;
    }

    /**
     * Obtener el codigo de respuesta HTTP
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Establece el codigo de respuesta HTTP
     * @param int $responseCode
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
    }

    


}