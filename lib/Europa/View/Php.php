<?php

namespace Europa\View;
use Europa\Di\Container;

/**
 * Class for rendering a basic PHP view script.
 * 
 * If parsing content from a file to render, this class can be overridden to provide base functionality for view
 * manipulation while the __toString method is overridden to provide custom parsing.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Php extends ViewScriptAbstract
{
	/**
	 * The views that have already extended a parent class.
	 * 
	 * @var array
	 */
	private $extendStack = array();
	
	/**
	 * Sets the context.
	 * 
	 * @var array
	 */
	private $context = array();
	
	/**
	 * The parent script.
	 * 
	 * @var string
	 */
	private $parentScript;
	
	/**
	 * The child script.
	 * 
	 * @var string
	 */
	private $childScript;
	
	/**
	 * The service locator to use for locating helpers.
	 * 
	 * @var Container
	 */
	private $container;
	
	/**
	 * Attempts to call the specified method on the specified locator if it exists.
	 * 
	 * @param string $name The specified service to locate and return.
	 * @param array  $args The configuration for the service.
	 * 
	 * @return mixed
	 */
	public function __call($name, array $args = array())
	{
	    if (!$this->container) {
	        throw new \LogicException('Unable to create helper "' . $name . '" because no helper locator was set.');
	    }
	    
	    $helper = $this->container->resolve($name);
	    if ($helper->exists()) {
	        return $helper->create($args);
	    } else {
	        throw new \LogicException('Unable to create instance of helper "' . $name . '" with message: ' . $e->getMessage());
	    }
	}
	
	/**
	 * Attempts to retrieve a parameter by name. If the parameter is not found, then it attempts
	 * to use the service locator to find a helper. If nothing is found, it returns null.
	 * 
	 * @param string $name The name of the property to get or helper to load.
	 * 
	 * @return mixed
	 */
	public function __get($name)
	{
		// accessing normal parameters
	    if (array_key_exists($name, $this->context)) {
	        return $this->context[$name];
	    }
	    
	    // a container is not required
	    if (!$this->container) {
	        return null;
	    }
	    
	    // only return an instance of the helper if it exists
	    $helper = $this->container->resolve($name);
	    if ($helper->exists()) {
	        return $helper->get();
	    }
	    
	    return null;
	}
	
    /**
     * Parses the view file and returns the result.
     * 
     * @param array $context The parameters to render with.
     * 
     * @return string
     */
    public function render(array $context = array())
    {
    	$script = $this->getScript();
    	
    	// script must be set
    	if (!$script) {
    		throw new \RuntimeException('A view script must be set prior to rendering.');
    	}
    	
    	// capture the output
    	ob_start();
    	include $this->getLocator()->locate($script);
    	$rendered = ob_get_clean();
        
        // handle view extensions
        if ($this->parentScript) {
            // set the script so the parent has access to what child has been rendered
            $this->childScript = $script;

            // then set the parent script to the current script so the current instance is shared
            $this->setScript($this->parentScript);

            // reset the parent script to avoid recursion
            $this->parentScript  = null;

            // set the rendered child so the parent has access to the rendered child
            $this->renderedChild = $rendered;

            // render and return the output of the parent
            return $this->render($context);
        }
        
        return $rendered;
    }
    
    /**
     * Sets the parent script.
     * 
     * @param string $script The parent script.
     * 
     * @return ViewAbstract
     */
    public function setParentScript($script)
    {
        $this->parentScript = $this->formatScript($script);
        return $this;
    }
    
    /**
     * Returns the parent script if one exists.
     * 
     * @return string
     */
    public function getParentScript()
    {
        return $this->parentScript;
    }
    
    /**
     * Sets the child script.
     * 
     * @param string $script The child script.
     * 
     * @return ViewAbstract
     */
    public function setChildScript($script)
    {
        $this->childScript = $this->formatScript($script);
        return $this;
    }
    
    /**
     * Returns the child script if one exists.
     * 
     * @return string
     */
    public function getChildScript()
    {
        return $this->childScript;
    }
    
    /**
     * Returns the rendered child script.
     * 
     * @return string
     */
    public function getRenderedChild()
    {
        return $this->renderedChild;
    }
    
    /**
     * Allows the extending of the specified view.
     * 
     * @param string $parent The parent view.
     * 
     * @return View
     */
    public function extend($parent)
    {
        // the child is the current script
        $child = $this->getScript();
        
        // child views cannot extend themselves
        if ($parent === $child) {
            throw new \LogicException('Child view cannot extend itself.');
        }
        
        // if the child has already extended a parent, don't do anything
        if (in_array($child, $this->extendStack)) {
            return $this;
        }
        
        // the extend stack makes sure that extend doesn't trigger recursion
        $this->extendStack[] = $child;
        
        // set the parent
        $this->parentScript = $parent;
        return $this;
    }
    
    /**
     * Sets the service locator to use for locating helpers.
     * 
     * @param Container $container The Di Container for locating helpers.
     * 
     * @return ViewScriptAbstract
     */
    public function setHelperContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }
}
