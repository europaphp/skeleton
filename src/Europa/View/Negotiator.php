<?php

namespace Europa\View;
use Europa\Config\Config;
use Europa\Request\Http;
use Europa\Request\RequestInterface;

class Negotiator
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

    public function __construct($config = [])
    {
        $this->config = new Config($this->config, $config);
    }

    public function __invoke(RequestInterface $request)
    {
        $view = null;

        if ($this->isRequestNegotiable($request)) {
            $method = null;

            if ($suffix = $request->getUri()->getSuffix()) {
                $method = $this->suffixMap[$suffix];
            } elseif ($type = $request->accepts(array_keys($this->typeMap))) {
                $method = $this->typeMap[$type];
            }

            if ($method && method_exists($this, $method)) {
                $view = $this->$method($request);
            }
        }

        if (!$view) {
            $view = $this->resolvePhp();
        }

        return $view;
    }

    private function resolveJson($request)
    {
        if ($callback = $request->getParam($this->config['jsonp-param'])) {
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

    private function isRequestNegotiable($request)
    {
        return $request instanceof Http;
    }
}