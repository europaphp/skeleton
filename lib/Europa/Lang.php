<?php

namespace Europa;

class Lang
{
    const DEFAULT = 'en-us';
    
    private $loader;
    
    private $lang = self::DEFAULT;
    
    public function __construct(Loader $loader, $lang = self::DEFAULT)
    {
        
    }
}