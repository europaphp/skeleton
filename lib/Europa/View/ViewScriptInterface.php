<?php

namespace Europa\View;
use Europa\Fs\Locator\LocatorInterface;

/**
 * Allows a script to render files.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface ViewScriptInterface extends ViewInterface
{
	/**
	 * Sets up a Php view renderer.
	 * 
	 * @param \Europa\Fs\Locator\LocatorInterface $locator The locator to use for view locating view files.
	 * 
	 * @return \Europa\View\ViewScriptAbstract
	 */
	public function __construct(LocatorInterface $locator);
	
	/**
	 * Sets the script to be rendered.
	 * 
	 * @param string $script The script to render.
	 * 
	 * @return \Europa\View\ViewScriptAbstract
	 */
    public function setScript($script);
    
    /**
     * Returns the script to be rendered.
     * 
     * @return string
     */
    public function getScript();
    
    /**
     * Locates the specified script and returns it. If it is not found, and exception is thrown.
     * 
     * @throws Exception If the script is not found.
     * 
     * @param string $script The script to locate.
     * 
     * @return string
     */
    public function getLocator();
}
