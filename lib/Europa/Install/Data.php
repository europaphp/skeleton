<?php

interface Europa_Install_Data
{
    public function __get($name);
    
    public function __set($name, $value);
    
    public function __isset($name);
    
    public function __unset($name);
    
    public function save();
}