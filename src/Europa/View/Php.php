<?php

namespace Europa\View;
use Europa\Di\DependencyInjectorAware;
use Europa\Di\DependencyInjectorAwareInterface;
use Europa\Exception\Exception;

class Php implements DependencyInjectorAwareInterface, ScriptAwareInterface, ViewInterface
{
    use DependencyInjectorAware, ScriptAware;

    private $child;
    
    private $extendStack = array();
    
    private $childScript;
    
    private $parentScript;

    private $context;
    
    public function render(array $context = [])
    {
        if (!$this->getScript()) {
            Exception::toss('No view script was specified.');
        }

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
            return $this->render($context);
        }
        
        return $rendered;
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
        $render = $this->render($context);
        
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

    public function context($name = null)
    {
        if (!$name) {
            return $this->context;
        }

        return isset($this->context[$name]) ? $this->context[$name] : null;
    }

    public function helper($name)
    {
        if (!$injector = $this->getDependencyInjector()) {
            Exception::toss('Cannot get helper "%s" from view "%s" because no container was set.', $name, $this->getScript());
        }

        if (!$injector->has($name)) {
            Exception::toss('Cannot get helper "%s" from view "%s" because it does not exist in the bound container.', $name, $this->getScript());
        }

        return $injector->get($name);
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
}