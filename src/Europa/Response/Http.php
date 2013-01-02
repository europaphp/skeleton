<?php

namespace Europa\Response;
use ArrayIterator;
use Europa\Filter\CamelCaseSplitFilter;

class Http extends ResponseAbstract implements HttpInterface
{
    private $filter;
    
    private $headers = array();
    
    private $status = self::OK;

    private $viewContentTypeMap = [
        'Europa\View\Json'  => self::JSON,
        'Europa\View\Jsonp' => self::JSONP,
        'Europa\View\Php'   => self::HTML,
        'Europa\View\Xml'   => self::XML
    ];
    
    public function __construct()
    {
        $this->filter = new CamelCaseSplitFilter;
    }
    
    public function __set($name, $value)
    {
        $this->setHeader($this->filter($name), $value);
    }
    
    public function __get($name)
    {
        return $this->getHeader($this->filter($name));
    }
    
    public function __isset($name)
    {
        return $this->hasHeader($this->fitler($name));
    }
    
    public function __unset($name)
    {
        return $this->removeHeader($this->filter($name));
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

    public function setContentType($type)
    {
        return $this->setHeader(self::CONTENT_TYPE, $type);
    }

    public function getContentType()
    {
        return $this->getHeader(self::CONTENT_TYPE);
    }

    public function setContentTypeFromView($view)
    {
        return $this->setContentType($this->getViewContentType($view));
    }

    public function setViewContentType($view, $type)
    {
        $this->viewContentTypeMap[$view] = $type;
        return $this;
    }

    public function getViewContentType($view)
    {
        if (!is_object($view)) {
            Exception::toss('Cannot detect content type for a non-object.');
        }

        $view = get_class($view);

        if (isset($this->viewContentTypeMap[$view])) {
            return $this->viewContentTypeMap[$view];
        }

        return self::HTML;
    }

    public function setLocation($location)
    {
        return $this->setHeader(self::LOCATION, $location);
    }

    public function getLocation()
    {
        return $this->getHeader(self::LOCATION);
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
    
    public function send()
    {
        $body = $this->getBody();

        http_response_code($this->status);

        $this->headers['Content-Length'] = strlen($body);
        
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        
        echo $body;
    }
    
    public function getIterator()
    {
        return new ArrayIterator($this->headers);
    }
    
    public function count()
    {
        return count($this->headers);
    }
    
    private function filter($name)
    {
        $name = $this->filter->filter($name);
        
        foreach ($name as &$part) {
            $part = ucfirst($part);
        }
        
        return implode('-', $name);
    }
}