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

        // apply context
        $this->context = $context;

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
     * Returns a variable from the current context. If a variable is not specified, the whole context is returned.
     * 
     * @param string $name The variable name.
     * 
     * @return mixed
     */
    public function context($name)
    {
        if (!$name) {
            return $this->context;
        }

        return isset($this->context[$name]) ? $this->context[$name] : null;
    }

    /**
     * Returns the specified helper.
     * 
     * @param string $name The helper to return.
     * 
     * @return mixed
     */
    public function helper($name)
    {
        if (!$this->helpers) {
            Exception::toss('Could not return helper "%s" because it does not exist.', $name);
        }

        $helpers = $this->helpers;

        return $helpers($name);
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