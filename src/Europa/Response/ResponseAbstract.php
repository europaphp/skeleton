<?php

namespace Europa\Response;
use Europa\Request;

abstract class ResponseAbstract implements ResponseInterface
{
    private $body;

    private $status;

    public function __toString()
    {
        return $this->getBody();
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setStatus($status)
    {
        $this->status = (int) $status;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public static function detect()
    {
        return Request\RequestAbstract::isCli() ? new Cli : new Http;
    }
}