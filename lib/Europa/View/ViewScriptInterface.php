<?php

namespace Europa\View;

interface ViewScriptInterface extends ViewInterface
{
    public function setScript($script);
    
    public function getScript();
}