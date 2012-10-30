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
     * The script suffix, if any.
     * 
     * @var string
     */
    private $suffix;

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
     * Sets the script suffix.
     * 
     * @param string $suffix The script suffix.
     * 
     * @return string
     */
    public function setScriptSuffix($suffix)
    {
        $this->suffix = $suffix;
        return $this;
    }

    /**
     * Returns the view script suffix.
     * 
     * @return string
     */
    public function getScriptSuffix()
    {
        return $this->suffix;
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
        if ($this->scriptLocator) {
            $locator = $this->scriptLocator;
            return $locator($this->formatScript());
        } elseif (is_file($this->script)) {
            return $this->script;
        }
    }

    /**
     * Returns a formatted script path including suffix.
     * 
     * @return string
     */
    public function formatScript()
    {
        return $this->script . ($this->script && $this->suffix ? '.' . $this->suffix : '');
    }
}