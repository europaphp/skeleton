<?php

namespace Europa\Response;
use Europa\Filter\CamelCaseSplitFilter;

class Http extends ResponseAbstract implements HttpInterface
{
    private $headers = [];

    public function __construct()
    {
        $this->setStatus(self::OK);
    }

    public function __invoke()
    {
        $body = $this->getBody();

        http_response_code($this->status);

        $this->headers['Content-Length'] = strlen($body);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $body;

        exit;
    }

    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function getHeader($name)
    {
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }

        return null;
    }

    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }

    public function removeHeader($name)
    {
        if (isset($this->headers[$name])) {
            unset($this->headers[$name]);
        }

        return $this;
    }

    public function setHeaders($headers)
    {
        if (is_array($headers) || is_object($headers)) {
            foreach ($headers as $name => $value) {
                $this->setHeader($name, $value);
            }
        }

        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function removeHeaders()
    {
        $this->headers = [];
        return $this;
    }
}