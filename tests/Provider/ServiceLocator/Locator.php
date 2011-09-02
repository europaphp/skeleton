<?php

namespace Provider\ServiceLocator;
use Europa\Request\Http;
use Europa\ServiceLocator;

class Locator extends ServiceLocator
{
    protected function request(array $params = array())
    {
        $request = new Http($this->get('router'));
        foreach ($params as $name => $value) {
            $request->setParam($name, $value);
        }
        return $request;
    }
}