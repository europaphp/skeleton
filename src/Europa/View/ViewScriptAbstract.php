<?php

namespace Europa\View;
use Europa\Fs\Locator\LocatorArray;

abstract class ViewScriptAbstract implements ViewScriptInterface
{
    /**
     * The script to render.
     * 
     * @var string
     */
    private $script;

    /**
     * The locator to use for locating view scripts.
     * 
     * @var LocatorInterface
     */
    private $scriptLocator;

    /**
     * Sets the script to be rendered.
     * 
     * @param string $script The script to render.
     * 
     * @return ViewScriptInterface
     */
    public function setScript($script)
    {
        $this->script = str_replace('\\', '/', $script);
        $this->script = trim($this->script, './');
        return $this;
    }
    
    /**
     * Returns the current script.
     * 
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * Sets the script locator.
     * 
     * @param callable $scriptLocator The locator to use.
     * 
     * @return ViewScriptAbstract
     */
    public function setScriptLocator(callable $scriptLocator)
    {
        $this->scriptLocator = $scriptLocator;
        return $this;
    }
    
    /**
     * Returns the script locator.
     * 
     * @return callable
     */
    public function getScriptLocator()
    {
        return $this->scriptLocator;
    }

    /**
     * Locates the view script using the set locator.
     * 
     * @return string
     */
    public function locateScript()
    {
        return $this->scriptLocator ? call_user_func($this->scriptLocator, $this->script) : realpath($this->script);
    }
}