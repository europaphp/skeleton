<?php

namespace Europa\Request;
use Europa\Request\Uri;

class Http extends RequestAbstract implements HttpInterface
{
    private $headers = array();

    private $uri;

    private $ip;

    public function __construct()
    {
        $this->initDefaultUri();
        $this->initDefaultParams();
        $this->initDefaultMethod();
        $this->initDefaultHeaders();
        $this->initDefaultIp();
    }

    public function __toString()
    {
        return strtoupper($this->getMethod()) . ' ' . $this->getUri()->getRequest();
    }

    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function getHeader($name)
    {
        if ($this->hasHeader($name)) {
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
        if ($this->hasHeader($name)) {
            unset($this->headers[$name]);
        }
        return $this;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function removeHeaders()
    {
        $this->headers = array();
        return $this;
    }

    public function accepts($types, $max = null)
    {
        $accepts = $this->getAcceptedContentTypes();
        $max     = $max ? $max : count($accepts);

        foreach ((array) $types as $value) {
            $position = array_search($value, $accepts);

            if ($position !== false && $position < $max) {
                return $value;
            }
        }

        return false;
    }

    public function setAcceptedContentTypes(array $types)
    {
        return $this->setHeader('Accept', implode(',', $types));
    }

    public function getAcceptedContentTypes()
    {
        $accept = $this->getHeader('Accept');
        $accept = explode(',', $accept);
        array_walk($accept, 'trim');
        return $accept;
    }

    public function removeAcceptedContentTypes()
    {
        return $this->removeHeader('Accept');
    }

    public function setUri($uri)
    {
        $this->uri = new Uri($uri);
        return $this;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function isXmlHttp()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function initDefaultUri()
    {
        return $this->setUri(Uri::detect());
    }

    private function initDefaultParams()
    {
        if ($input = file_get_contents('php://input')) {
            parse_str($input, $input);
            $this->setParams($input);
        }

        if (isset($_REQUEST)) {
            $this->setParams($_REQUEST);
        }

        return $this;
    }

    private function initDefaultMethod()
    {
        $method = static::GET;

        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
        } elseif (isset($_SERVER['REQUEST_METHOD'])) {
            $method = $_SERVER['REQUEST_METHOD'];
        }

        $this->setMethod(strtolower($method));

        return $this;
    }

    private function initDefaultHeaders()
    {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $name = substr($name, 5);
                $name = strtolower($name);
                $name = str_replace('_', ' ', $name);
                $name = ucwords($name);
                $name = str_replace(' ', '-', $name);
                $this->setHeader($name, $value);
            }
        }

        return $this;
    }

    private function initDefaultIp()
    {
        $ip = null;

        if (isset($_SERVER['HTTP_TRUE_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_TRUE_CLIENT_IP'];
        } elseif (isset($_SERVER['X_FORWARDED_FOR'])) {
            $ip = $_SERVER['X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $values = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip     = $values[0];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $this->setIp($ip);
    }
}