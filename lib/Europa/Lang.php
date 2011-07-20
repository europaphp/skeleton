<?php

namespace Europa;

class Lang
{
    const DEFAULT_LANGUAGE = 'en-us';
    
    private $loader;
    
    private $lang;
    
    public function __construct(Loader $loader, $lang = self::DEFAULT_LANGUAGE)
    {
        $this->loader = $loader;
        $this->lang   = $lang;
    }
}