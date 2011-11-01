<?php

namespace Europa\View;

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
	 * The script to be rendered.
	 * 
	 * @var string
	 */
    private $script;
    
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
}
