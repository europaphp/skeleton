<?php

namespace Europa\View;
use Europa\Config\Config;
use Europa\Di\Locator;
use Europa\Filter\ClassNameFilter;
use Europa\Fs\Locator\LocatorArray;
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
     * The context to render.
     * 
     * @var array
     */
    private $context;

    /**
     * Default view configuration.
     * 
     * @var array | Config
     */
    private $config = [
        'helperLocator' => [
            'filters' => [
                'Europa\Filter\ClassNameFilter' => ['prefix' => 'Helper\\'],
                'Europa\Filter\ClassNameFilter' => ['prefix' => 'Europa\View\Helper\\']
            ]
        ]
    ];

    /**
     * Sets up the view.
     * 
     * @param mixed $config The configuration.
     * 
     * @return Php
     */
    public function __construct($config = [])
    {
        $this->config  = new Config($this->config, $config);
        $this->locator = new LocatorArray;
        $this->helpers = new Locator($this->config->helperLocator);
    }

    /**
     * Returns a helper.
     * 
     * @param string $name The helper name.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        try {
            return call_user_func($this->helpers, $name);
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('The helper "%s" could be returned with message: %s', $name, $e->getMessage()));
        }
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
    public function render(array $context = [])
    {
        // locate the script if there is a locator
        if ($this->locator) {
            $script = call_user_func($this->locator, $this->script) ?: $this->script;
        }
        
        // ensure the script can be found
        if (!is_file($script)) {
            throw new RuntimeException(sprintf('The script "%s" could not be located.', $this->script));
        }

        // capture the output
        ob_start();

        // set convenience variables
        $context = new Context($context);
        $helpers = $this->helpers;

        // render
        include $script;

        // get output
        $rendered = ob_get_clean();
        
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
            return $this->render($context->toArray());
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
     * @param callable $locator The locator to use.
     * 
     * @return Php
     */
    public function setScriptLocator(callable $locator)
    {
        $this->locator = $locator;
        return $this;
    }
    
    /**
     * Returns the script locator.
     * 
     * @return LocatorInterface
     */
    public function getScriptLocator()
    {
        return $this->locator;
    }
    
    /**
     * Sets the helper container used to locate helpers.
     * 
     * @param callable $helpers The helper container.
     * 
     * @return Php
     */
    public function setHelperContainer(callable $helpers)
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