<?php

namespace Europa\View;
use Europa\Di\Container;
use Europa\Di\Exception as DiException;
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
     * The views that have already extended a parent class.
     * 
     * @var array
     */
    private $extendStack = array();
    
    /**
     * The loader to use for view locating and loading.
     * 
     * @var \Europa\Fs\Locator\LocatorInterface
     */
    private $locator;
    
    /**
     * Sets the context.
     * 
     * @var array
     */
    private $context = array();
    
    /**
     * The script to be rendered.
     * 
     * @var string
     */
    private $script;
    
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
     * Executes the currently set script.
     * 
     * @return string
     */
    abstract public function execute();
    
    /**
     * Creates a new PHP view using the specified loader.
     * 
     * @param \Europa\Fs\Locator\LocatorInterface $locator The locator to use for view locating view files.
     * 
     * @return Php
     */
    public function __construct(LocatorInterface $locator)
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
            throw new \LogicException('Unable to create helper "' . $name . '" because no helper locator was set.');
        }
        
        try {
            return $this->container->__get($name)->create($args);
        } catch (\Exception $e) {
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
        if (array_key_exists($name, $this->context)) {
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
     * @param array $context The parameters to render with.
     * 
     * @return string
     */
    public function render(array $context = array())
    {
        if (!$this->script) {
            throw new \LogicException('Could not render view: No script was defined to render.');
        }
        
        // capture old context
        $oldContext = $this->getContext();
        
        // set new context
        $this->setContext($context);
        
        // execute the script
        $rendered = $this->execute();
        
        // re-set the old context
        $this->setContext($oldContext);
        
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
            return $this->render($context);
        }
        
        return $rendered;
    }
    
    /**
     * Renders the specified script without affecting the current set script.
     * 
     * @param string $script  The script to render.
     * @param array  $context The parameters to render with.
     * 
     * @return string
     */
    public function renderScript($script, array $context = array())
    {
        // capture old state
        $oldScript = $this->getScript();
        $oldParent = $this->getParentScript();
        $oldChild  = $this->getChildScript();
        
        // set new state
        $this->setScript($script);
        $this->setParentScript(null);
        $this->setChildScript(null);
        
        // capture rendered script
        $render = $this->render($context);
        
        // reapply old state
        $this->setScript($oldScript);
        $this->setParentScript($oldParent);
        $this->setChildScript($oldChild);
        
        return $render;
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
    
    /**
     * Sets the context.
     * 
     * @param array $context The context to set.
     * 
     * @return ViewScriptAbstract
     */
    public function setContext(array $context)
    {
        $this->context = $context;
        return $this;
    }
    
    /**
     * Returns the current context.
     * 
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
    
    /**
     * Sets the script to be rendered.
     * 
     * @param string $script The path to the script to be rendered relative to the view path, excluding the extension.
     * 
     * @return ViewAbstract
     */
    public function setScript($script)
    {
        $this->script = $this->formatScript($script);
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
     * Returns the full path to the script.
     * 
     * @throws \Europa\View\Exception If the script could not be found.
     * 
     * @return string
     */
    public function getScriptPathname()
    {
        return $this->locateScript($this->script);
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
     * Returns the full path to the parent script.
     * 
     * @return string
     */
    public function getParentScriptPathname()
    {
        return $this->locateScript($this->parentScript);
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
     * Returns the full path to the child script.
     * 
     * @return string
     */
    public function getChildScriptPathname()
    {
        return $this->locateScript($this->childScript);
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
     * Formats the specified script and returns it.
     * 
     * @param string $script The script to format.
     * 
     * @return string
     */
    private function formatScript($script)
    {
        return str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $script);
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
    private function locateScript($script)
    {
        if ($file = $this->locator->locate($script)) {
            return $file;
        }
        throw new \LogicException("Could not render view because {$script} does not exist.");
    }
}
