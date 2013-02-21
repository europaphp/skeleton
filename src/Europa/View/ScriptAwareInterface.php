<?php

namespace Europa\View;
use Europa\Fs\LocatorAwareInterface;

interface ScriptAwareInterface extends LocatorAwareInterface
{
    public function setScript($script);
    
    public function getScript();
}