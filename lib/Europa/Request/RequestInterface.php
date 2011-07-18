<?php

namespace Europa\Request;

interface RequestInterface extends \Serializable
{
    public function setMethod($method);
    
    public function getMethod();
    
    public function setParam($name, $value);
    
    public function getParam($name);
}