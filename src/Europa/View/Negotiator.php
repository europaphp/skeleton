<?php

namespace Europa\View;
use Europa\Config\Config;
use Europa\Request\Http;
use Europa\Request\RequestInterface;

class Negotiator implements NegotiatorInterface
{
    private $suffixMap = [
        'json' => 'resolveJson',
        'xml'  => 'resolveXml'
    ];

    private $typeMap = [
        'application/json'       => 'resolveJson',
        'application/javascript' => 'resolveJson',
        'text/xml'               => 'resolveXml'
    ];

    private $config = [
        'jsonp-param'     => 'callback',
        'xml-view-config' => []
    ];

    private $request;

    public function __construct(RequestInterface $request, $config = [])
    {
        $this->config  = new Config($this->config, $config);
        $this->request = $request;
    }

    public function negotiate()
    {
        $view = null;

        if ($this->isRequestNegotiable($this->request)) {
            $method = null;

            if ($suffix = $this->request->getUri()->getSuffix()) {
                $method = $this->suffixMap[$suffix];
            } elseif ($type = $this->request->accepts(array_keys($this->typeMap))) {
                $method = $this->typeMap[$type];
            }

            if ($method && method_exists($this, $method)) {
                $view = $this->$method($this->request);
            }
        }

        if (!$view) {
            $view = $this->resolvePhp($this->request);
        }

        return $view;
    }

    private function resolveJson()
    {
        if ($callback = $this->request->getParam($this->config['jsonp-param'])) {
            return new Jsonp($callback);
        }

        return new Json;
    }

    private function resolvePhp()
    {
        return new Php;
    }

    private function resolveXml()
    {
        return new Xml($this->config['xml-view-config']);
    }

    private function isRequestNegotiable()
    {
        return $this->request instanceof Http;
    }
}