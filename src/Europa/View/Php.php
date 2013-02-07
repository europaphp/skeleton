<?php

namespace Europa\View;
use Europa\Config\Config;
use Europa\Di\Locator;
use Europa\Exception\Exception;
use Europa\Filter\ClassNameFilter;
use Europa\Fs\Locator\LocatorArray;

class Php extends ViewScriptAbstract
{
    private $child;
    
    private $helpers;
    
    private $extendStack = array();
    
    private $childScript;
    
    private $parentScript;

    private $context;
    
    public function __invoke(array $context = [])
    {
        // A script must be set.
        if (!$this->getScript()) {
            Exception::toss('No view script was specified.');
        }

        // ensure the script can be found
        if (!$script = $this->locateScript()) {
            Exception::toss('The script "%s" does not exist.', $this->getScript());
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

    public function context($name = null)
    {
        if (!$name) {
            return $this->context;
        }

        return isset($this->context[$name]) ? $this->context[$name] : null;
    }

    public function helper($name)
    {
        if (!$this->helpers) {
            Exception::toss('Could not return helper "%s" because a helper container was not specified.', $name);
        }

        $helpers = $this->helpers;

        return $helpers($name);
    }
    
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
        $render = $this->__invoke($context);
        
        // reapply old state
        $this->setScript($oldScript);
        $this->parentScript = $oldParent;
        $this->childScript  = $oldChild;
        
        return $render;
    }
    
    public function renderChild()
    {
        return $this->child;
    }
    
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
    
    public function setServiceContainer(callable $helpers)
    {
        $this->helpers = $helpers;
        return $this;
    }
    
    public function getServiceContainer()
    {
        return $this->helpers;
    }
}