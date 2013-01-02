<?php

namespace Europa\View;

interface ViewScriptInterface
{
    public function setScript($script);
    
    public function getScript();

    public function setScriptLocator(callable $locator);

    public function getScriptLocator();

    public function locateScript();
}