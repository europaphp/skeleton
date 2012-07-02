<?php

namespace Europa\View;
use Europa\Di\ContainerInterface;
use Europa\Fs\LocatorInterface;
use Exception;
use LogicException;
use RuntimeException;

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
class Php implements ViewScriptInterface
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
     * The locator to use for locating view scripts.
     * 
     * @var LocatorInterface
     */
    private $locator;
    
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
     * The script to render.
     * 
     * @var string
     */
    private $script;
    
    /**
     * Calls a helper from the specified container.
     * 
     * @see Php::setHelpers()
     * 
     * @param string $name The name of the helper to create.
     * @param array  $args The arguments to configure it with.
     * 
     * @throws LogicException If the container does not exist.
     * 
     * @return mixed
     */
    public function __call($name, array $args = array())
    {
        if ($this->helpers) {
            return $this->helpers->get($name, $args);
        }
        
        throw new LogicException(
            'You must set a helper container using "setHelpers" before trying to call a helper.'
        );
    }
    
    /**
     * Calls a helper from the specified container.
     * 
     * @see Php::setHelpers()
     * 
     * @param string $name The name of the helper to create.
     * 
     * @throws LogicException If the container does not exist.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        if ($this->helpers) {
            return $this->helpers->get($name);
        }
        
        throw new LogicException(
            'You must set a helper container using "setHelpers" before trying to get a helper.'
        );
    }
    
    /**
     * Normalises and sets the script to render.
     * 
     * @return Php
     */
    public function setScript($script)
    {
        $this->script = str_replace('\\', '/', $script);
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
     * Returns the parent script.
     * 
     * @return string
     */
    public function getParentScript()
    {
        return $this->parentScript;
    }
    
    /**
     * Returns the child script.
     * 
     * @return string
     */
    public function getChildScript()
    {
        return $this->childScript;
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
        // locate the script if there is a locator
        if ($this->locator) {
            $script = $this->locator->locate($this->script);
        } else {
            $script = $this->script;
        }
        
        // ensure the script can be found
        if (!$script) {
            throw new RuntimeException(sprintf('The script "%s" could not be located.', $this->script));
        }
        
        // capture the output
        try {
            ob_start();
            extract($context);
            include $this->locator->locate($script);
            $rendered = ob_get_clean();
        } catch (Exception $e) {
            throw new LogicException(sprintf(
                'Unable to render view "%s" with message: %s',
                $this->script,
                $e->getMessage()
            ));
        }
        
        // handle view extensions
        if ($this->parentScript) {
            // set the script so the parent has access to what child has been rendered
            $this->childScript = $this->script;

            // then set the parent script to the current script so the current instance is shared
            $this->script = $this->parentScript;

            // reset the parent script to avoid recursion
            $this->parentScript = null;

            // set the rendered child so the parent has access to the rendered child
            $this->child = $rendered;
            
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
        $oldScript = $this->script;
        $oldParent = $this->parentScript;
        $oldChild  = $this->childScript;
        
        // set new state
        $this->script       = $script;
        $this->parentScript = null;
        $this->childScript  = null;
        
        // capture rendered script
        $render = $this->render($context);
        
        // reapply old state
        $this->script       = $oldScript;
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
        $child = $this->script;
        
        // child views cannot extend themselves
        if ($parent === $child) {
            throw new LogicException('Child view cannot extend itself.');
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
     * Sets the script locator.
     * 
     * @param LocatorInterface $locator The locator to use.
     * 
     * @return Php
     */
    public function setLocator(LocatorInterface $locator)
    {
        $this->locator = $locator;
        return $this;
    }
    
    /**
     * Returns the script locator.
     * 
     * @return LocatorInterface
     */
    public function getLocator()
    {
        return $this->locator;
    }
    
    /**
     * Sets the helper container used to locate helpers.
     * 
     * @param ContainerInterface $helpers The helper container.
     * 
     * @return Php
     */
    public function setHelpers(ContainerInterface $helpers)
    {
        $this->helpers = $helpers;
        return $this;
    }
    
    /**
     * Returns the helper container.
     * 
     * @return ContainerInterface
     */
    public function getHelpers()
    {
        return $this->helpers;
    }
}