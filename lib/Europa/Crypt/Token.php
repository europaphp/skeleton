<?php

class Europa_Crypt_Token
{
    protected $data = array();
    
    protected $sharedKey;
    
    public function __construct($sharedKey)
    {
        $this->sharedKey = $sharedKey;
    }
    
    public function __get($name)
    {
        if ($this->__isset($name)) {
            return $this->data[$name];
        }
        return null;
    }
    
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }
    
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }
    
    public function __unset($name)
    {
        if ($this->__isset($name)) {
            unset($this->data[$name]);
        }
        return $this;
    }
    
    public function compare($token)
    {
        return $this->__toString() === (string) $token;
    }
    
    public function __toString()
    {
        return md5($this->sharedKey . serialize($this->data));
    }
}