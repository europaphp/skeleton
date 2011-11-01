<?php

namespace Europa\View;
use Europa\Fs\Locator\LocatorInterface;

/**
 * Provides an abstract implementation of a view script renderer.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class ViewScriptAbstract implements ViewScriptInterface
{
	/**
	 * The loader to use for view locating and loading.
	 * 
	 * @var \Europa\Fs\Locator\LocatorInterface
	 */
	private $locator;
	
	/**
	 * The script to be rendered.
	 * 
	 * @var string
	 */
    private $script;
    
    /**
     * Sets up a Php view renderer.
     * 
     * @param \Europa\Fs\Locator\LocatorInterface $locator The locator to use for view locating view files.
     * 
     * @return \Europa\View\ViewScriptAbstract
     */
    public function __construct(LocatorInterface $locator)
    {
    	$this->locator = $locator;
    }
    
    /**
     * Sets the script to render.
     * 
     * @param string $script The script to render.
     * 
     * @return 
     */
    public function setScript($script)
    {
    	$this->script = $script;
    	return $this;
    }
    
    /**
     * Returns the script to be rendered.
     * 
     * @return string
     */
    public function getScript()
    {
    	return $this->script;
    }
    
    /**
     * Locates the specified script and returns it. If it is not found, and exception is thrown.
     * 
     * @throws Exception If the script is not found.
     * 
     * @param string $script The script to locate.
     * 
     * @return string
     */
    public function getLocator()
    {
    	return $this->locator;
    }
}
