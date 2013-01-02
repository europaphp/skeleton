<?php

namespace Europa\Response;
use Europa\Request\RequestAbstract;

abstract class ResponseAbstract implements ResponseInterface
{
    private $body;
    
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }
    
    public function getBody()
    {
        return $this->body;
    }

    public static function detect()
    {
        return RequestAbstract::isCli() ? new Cli : new Http;
    }
}