<?php

namespace Europa\View;
use Europa\Config\Config;
use Europa\Di\Locator;
use Europa\Exception\Exception;
use Europa\Filter\ClassNameFilter;
use Europa\Fs\Locator\LocatorArray;

/**
 * Class for rendering a basic PHP view script.
 * 
 * If parsing content from a file to render, this class can be overridden to provide base
 * functionality for view manipulation while the __toString method is overridden to provide custom
 * parsing.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Php extends ViewScriptAbstract
{
    /**
     * The child script that was rendered, if any.
     * 
     * @var string
     */
    private $child;
    
    /**
     * The container to use for helpers.
     * 
     * @var Container
     */
    private $helpers;
    
    /**
     * The views that have already extended a parent class.
     * 
     * @var array
     */
    private $extendStack = array();
    
    /**
     * The child script.
     * 
     * @var string
     */
    private $childScript;
    
    /**
     * The parent script.
     * 
     * @var string
     */
    private $parentScript;

    /**
     * The context to render.
     * 
     * @var array
     */
    private $context;

    /**
     * Returns a helper.
     * 
     * @param string $name The helper name.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        if (!$this->helpers) {
            Exception::toss('The helper "%s" could not be returned because no helper container was set.', $name);
        }

        try {
            return call_user_func($this->helpers, $name);
        } catch (\Exception $e) {
            Exception::toss('The helper "%s" could not be returned with message: %s', $name, $e->getMessage());
        }
    }
    
    /**
     * Parses the view file and returns the result.
     * 
     * @param array $context The parameters to render with.
     * 
     * @return string
     */
    public function __invoke(array $context = [])
    {
        // A script must be set.
        if (!$this->getScript()) {
            Exception::toss('No view script was specified.');
        }

        // ensure the script can be found
        if (!$script = $this->locateScript()) {
            Exception::toss('The script "%s" does not exist.', $this->formatScript());
        }

        // capture the output
        ob_start();

        // render
        include $script;

        // get output
        $rendered = ob_get_clean();
        
        // handle view extensions
        if ($this->parentScript) {
            // set the script so the parent has access to what child has been rendered
            $this->childScript = $this->getScript();

            // then set the parent script to the current script so the current instance is shared
            $this->setScript($this->parentScript);

            // reset the parent script to avoid recursion
            $this->parentScript = null;

            // set the rendered child so the parent has access to the rendered child
            $this->child = $rendered;
            
            // render and return the output of the parent
            return $this->__invoke($context);
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
        $oldParent = $this->parentScript;
        $oldChild  = $this->childScript;
        
        // set new state
        $this->setScript($script);
        $this->parentScript = null;
        $this->childScript  = null;
        
        // capture rendered script
        $render = $this->render($context);
        
        // reapply old state
        $this->setScript($oldScript);
        $this->parentScript = $oldParent;
        $this->childScript  = $oldChild;
        
        return $render;
    }
    
    /**
     * Returns the rendered child script.
     * 
     * @return string
     */
    public function renderChild()
    {
        return $this->child;
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
            Exception::toss('Child view cannot extend itself.');
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
     * Sets the helper container used to locate helpers.
     * 
     * @param callable $helpers The helper container.
     * 
     * @return Php
     */
    public function setHelperServiceContainer(callable $helpers)
    {
        $this->helpers = $helpers;
        return $this;
    }
    
    /**
     * Returns the helper container.
     * 
     * @return callable
     */
    public function getHelperContainer()
    {
        return $this->helpers;
    }
}