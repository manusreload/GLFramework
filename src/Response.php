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
    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return mixed
     */
    public function getRedirection()
    {
        return $this->redirection;
    }

    /**
     * @param mixed $redirection
     */
    public function setRedirection($redirection)
    {
        $this->redirection = $redirection;
    }


    public function display()
    {
        if($this->contentType) header("Content-Type: " . $this->contentType);
        if($this->redirection) header("Location: " . $this->redirection);
        print $this->content;
    }

    public function isRedirect()
    {
        return $this->redirection !== null;
    }

    public function setUri($url)
    {
        $this->uri = $url;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }


}