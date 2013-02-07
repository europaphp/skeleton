<?php

namespace Europa\View;
use Europa\Fs\Locator\LocatorArray;

abstract class ViewScriptAbstract implements ViewScriptInterface
{
    private $script;

    private $suffix;

    private $scriptLocator;

    public function setScript($script)
    {
        $this->script = str_replace('\\', '/', $script);
        $this->script = trim($this->script, './');
        return $this;
    }
    
    public function getScript()
    {
        return $this->script;
    }

    public function setScriptLocator(callable $scriptLocator)
    {
        $this->scriptLocator = $scriptLocator;
        return $this;
    }
    
    public function getScriptLocator()
    {
        return $this->scriptLocator;
    }

    public function locateScript()
    {
        if ($this->scriptLocator) {
            $locator = $this->scriptLocator;
            return $locator($this->script);
        } elseif (is_file($this->script)) {
            return $this->script;
        }
    }
}