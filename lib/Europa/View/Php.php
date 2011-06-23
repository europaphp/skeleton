<?php

namespace Europa\View;
use Europa\Di\Container;
use Europa\Di\Exception as DiException;
use Europa\Exception;
use Europa\Fs\Locator;

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
class Php implements ViewInterface
{
    /**
     * The views that have already extended a parent class.
     * 
     * @var array
     */
    private $extendStack = array();
    
    /**
     * The loader to use for view locating and loading.
     * 
     * @var \Europa\Fs\Locator
     */
    private $locator;
    
    /**
     * The parent associated to the current view.
     * 
     * @var View
     */
    private $parent;
    
    /**
     * The script to be rendered.
     * 
     * @var string
     */
    private $script;
    
    /**
     * The service locator to use for locating helpers.
     * 
     * @var Europa\Container\Container
     */
    private $container;
    
    /**
     * Creates a new PHP view using the specified loader.
     * 
     * @param \Europa\Loader\FileLoader $loader The loader to use for view locating and loading.
     * 
     * @return \Europa\View\Php
     */
    public function __construct(Locator $locator)
    {
        $this->locator = $locator;
    }
    
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
            throw new Exception('Unable to create helper "' . $name . '" because no helper locator was set.');
        }
        
        try {
            return $this->container->__get($name)->create($args);
        } catch (DiException $e) {
            throw new Exception('Unable to create instance of helper "' . $name . '".');
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
        if (isset($this->context[$name])) {
            return $this->context[$name];
        }
        if (!$this->container) {
            return null;
        }
        try {
            return $this->container->__get($name)->get();
        } catch (DiException $e) {
            
        }
        return null;
    }
    
    /**
     * Parses the view file and returns the result.
     * 
     * @return string
     */
    public function render(array $context = array())
    {
        if (!$this->script) {
            throw new Exception('Could not render view: No script was defined to render.');
        }
        
        // render the script
        if ($file = $this->locator->locate($this->script)) {
            $this->context = $context;
            ob_start();
            include $file;
            $rendered = ob_get_clean();
            
            // if there is a parent, render up the stack
            if ($this->parentScript) {
                // set the script so the parent has access to what child has been rendered
                $this->childScript = $this->script;

                // then set the parent script to the current script so the current instance is shared
                $this->script = $this->parentScript;

                // reset the parent script to avoid recursion
                $this->parentScript  = null;

                // set the rendered child so the parent has access to the rendered child
                $this->renderedChild = $rendered;

                // render and return the output of the parent
                return $this->render();
            }
            return $rendered;
        }
        
        throw new Exception("Could not render view because {$this->script} does not exist.");
    }
    
    /**
     * Sets the service locator to use for locating helpers.
     * 
     * @param \Europa\dependency $dependency The service locator for locating helpers.
     * 
     * @return \Europa\View\Php
     */
    public function setHelperContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }
    
    /**
     * Sets the script to be rendered.
     * 
     * @param string $script The path to the script to be rendered relative to the view path, excluding the extension.
     * 
     * @return \Europa\View\Php
     */
    public function setScript($script)
    {
        $this->script = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $script);
        return $this;
    }
    
    /**
     * Returns the set script.
     * 
     * @return string
     */
    public function getScript()
    {
        return $this->script;
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
        $child = $this->script;
        
        // child views cannot extend themselves
        if ($parent === $child) {
            throw new Exception('Child view cannot extend itself.');
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
}
