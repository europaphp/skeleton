<?php

/**
 * Class for rendering a basic PHP view script.
 * 
 * If parsing content from a file to render, this class can be overridden
 * to provide base functionality for view manipulation while the __toString
 * method is overridden to provide custom parsing.
 * 
 * @category Views
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_View_Php extends Europa_View
{
    /**
     * Contains the child of the current view instance.
     * 
     * @var Europa_View
     */
    private $_child = null;
    
    /**
     * The script to be rendered.
     * 
     * @var string
     */
    private $_script = null;
    
    /**
     * The script suffix.
     * 
     * @var string
     */
    private $_suffix = 'php';
    
    /**
     * The helper formatter for this instance.
     * 
     * @var string
     */
    private $_helperFormatter;
    
    /**
     * The default helper formatter.
     * 
     * @var string
     */
    private static $_defaultHelperFormatter;
    
    /**
     * Construct the view and sets defaults.
     * 
     * @param string $script The script to render.
     * @param array  $params The arguments to pass to the script.
     * 
     * @return Europa_View
     */
    public function __construct($script = null, $params = null)
    {
        // set a script if defined
        $this->setScript($script)
             ->setParams($params);
    }

    /**
     * If a parameter is already set it is returned. If a parameter is not set
     * it's similar to calling a helper via Europa_View->__call(), but treats
     * the helper as a singleton and once instantiated, that instance is always
     * returned for the duration of the Europa_View object's lifespan unless
     * unset.
     * 
     * @param string $name The name of the property to get or helper to load.
     * 
     * @return mixed
     */
    public function __get($name)
    {
        // if it's already set, return it
        if (parent::__isset($name)) {
            return parent::__get($name);
        }
        
        // call the helper
        $helper = $this->__call($name, array($this));
        
        // if it had a return value, set it and return it
        if ($helper) {
            parent::__set($name, $helper);
            return $helper;
        }
        
        // otherwise just return null
        return null;
    }

    /**
     * Attempts to load and execute a helper. Returns null of not found.
     * 
     * @return mixed
     */
    public function __call($func, $args = array())
    {
        // format the helper class name for the given method
        $class = $this->formatHelper($func);
        
        // if unable to load, return null
        if (!Europa_Loader::loadClass($class)) {
            return null;
        }
        
        // reflect and create a new instance with the passed arguments
        $class = new ReflectionClass($class);
        if ($class->hasMethod('__construct')) {
            return $class->newInstanceArgs($args);
        }
        return $class->newInstance();
    }
    
    /**
     * Parses the view file and returns the result.
     * 
     * @return string
     */
    public function __toString()
    {
        // allows us to return the included file as a string
        ob_start();
        
        // include it and trigger an error for any exceptions since you can't throw
        // exceptions inside __toString
        if ($view = Europa_Loader::search($this->getScript() . '.' . $this->getSuffix())) {
            try {
                include $view;
            } catch (Exception $e) {
                trigger_error((string) $e, E_USER_ERROR);
            }
        }
        
        // return the parsed view
        return ob_get_clean() . "\n";
    }
    
    /**
     * Extends the specified view with the current view. And returns the new
     * view.
     * 
     * @param Europa_View $view The view to extend.
     * 
     * @return Europa_View
     */
    public function extend(Europa_View $view)
    {
        return $view->setChild($this);
    }
    
    /**
     * Sets the child view for the current view and returns the current view.
     * 
     * @param Europa_View $view The view to extend.
     * 
     * @return Europa_View
     */
    public function setChild(Europa_View $view)
    {
        $this->_child = $view;
        return $this;
    }
    
    /**
     * Returns the child of the current view if it exists.
     * 
     * @param Europa_View $view The view to extend.
     * 
     * @return Europa_View
     */
    public function getChild()
    {
        return $this->_child;
    }
    
    /**
     * Sets the script to be rendered.
     * 
     * @param String $script The path to the script to be rendered relative 
     * to the view path, excluding the extension.
     * 
     * @return Object Europa_View
     */
    public function setScript($script)
    {
        $this->_script = $script;
        return $this;
    }
    
    /**
     * Returns the set script.
     * 
     * @return string
     */
    public function getScript()
    {
        return $this->_script;
    }
    
    /**
     * Sets the view suffix.
     * 
     * @param string $suffix The suffix to use.
     * 
     * @return Europa_View_Php
     */
    public function setSuffix($suffix)
    {
        $this->_suffix = $suffix;
        return $this;
    }
    
    /**
     * Returns the set suffix.
     * 
     * @return string
     */
    public function getSuffix()
    {
        return $this->_suffix;
    }
    
    /**
     * Sets the helper formatter only for this instance.
     * 
     * @param mixed $formatter The helper formatter.
     * 
     * @return Europa_View_Php
     */
    public function setHelperFormatter($formatter)
    {
        if (!is_callable($formatter)) {
            throw new Europa_View_Exception(
                'The specified helper formatter is not callable.'
            );
        }
        $this->_helperFormatter = $formatter;
        return $this;
    }
    
    /**
     * Returns the helper formatter.
     * 
     * @return mixed
     */
    public function getHelperFormatter()
    {
        return $this->_helperFormatter;
    }
    
    /**
     * Sets the default helper formatter for all view instances.
     * 
     * @param mixed The helper formatter.
     * 
     * @return void
     */
    public static function setDefaultHelperFormatter($formatter)
    {
        self::$_defaultHelperFormatter = $formatter;
    }
    
    /**
     * Returns the default helper formatter.
     * 
     * @return mixed
     */
    public static function getDefaultHelperFormatter()
    {
        return self::$_defaultHelperFormatter;
    }
    
    /**
     * Returns the formatted helper class name from the helper string passed.
     * 
     * @param string $helper The helper to format.
     * 
     * @return string
     */
    protected function formatHelper($helper)
    {
        $formatter = $this->getHelperFormatter();
        
        if (!$formatter) {
            $formatter = self::getDefaultHelperFormatter();
        }
        
        if (!$formatter) {
            return Europa_String::create($helper)->toClass()->__toString();
        }
        
        return call_user_func_array($formatter, array($helper));
    }
}