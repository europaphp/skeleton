<?php

namespace Europa\Response;

abstract class ResponseAbstract implements ResponseInterface
{
    private $body;

    private $status;

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
}