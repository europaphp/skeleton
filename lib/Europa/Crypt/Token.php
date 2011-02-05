<?php

namespace Europa\Crypt;

/**
 * A class for generating and sharing an non-reversible cross-platform token.
 * 
 * @category Encryption
 * @package  Crypt
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Token
{
    /**
     * The data in the token.
     * 
     * @var array
     */
    protected $data = array();
    
    /**
     * The shared key.
     * 
     * @var string
     */
    protected $sharedKey;
    
    /**
     * Constructs a new token.
     * 
     * @param string $shraedKey The shared key to use.
     * 
     * @return \Europa\Crypt\Token
     */
    public function __construct($sharedKey)
    {
        $this->sharedKey = $sharedKey;
    }
    
    /**
     * Returns the value of a token parameter.
     * 
     * @param string $name The parameter name.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        if ($this->__isset($name)) {
            return $this->data[$name];
        }
        return null;
    }
    
    /**
     * Sets the value of a token parameter.
     * 
     * @param string $name  The parameter name.
     * @param mixed  $value The parameter value.
     * 
     * @return \Europa\Crypt\Token
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }
    
    /**
     * Checks whether or not the specified token value is set.
     * 
     * @param string $name The param name.
     * 
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }
    
    /**
     * Unsets the specified token.
     * 
     * @param string $name The param name.
     */
    public function __unset($name)
    {
        if ($this->__isset($name)) {
            unset($this->data[$name]);
        }
        return $this;
    }
    
    /**
     * Compares the current token to the specified token.
     * 
     * @param mixed $token The token to compare.
     * 
     * @return bool
     */
    public function compare($token)
    {
        return $this->__toString() === (string) $token;
    }
    
    /**
     * Formats and returns the token as a string.
     * 
     * @return string
     */
    public function __toString()
    {
        return md5($this->sharedKey . json_encode($this->data));
    }
}