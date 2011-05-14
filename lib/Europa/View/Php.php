<?php

namespace Europa\View;
use Europa\Exception;
use Europa\ServiceLocator;
use Europa\View;

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
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Php extends View
{
    /**
     * The script path to look in.
     * 
     * @var string
     */
    private $path;
    
    /**
     * The script suffix.
     * 
     * @var string
     */
    private $suffix;
    
    /**
     * The script to be rendered.
     * 
     * @var string
     */
    private $script;
    
    /**
     * The service container used for helpers.
     * 
     * @var \Europa\ServiceLocator
     */
    private $serviceLocator;
    
    /**
     * Sets the default path for views.
     * 
     * @var string
     */
    private static $defaultPath;
    
    /**
     * Sets the default suffix.
     * 
     * @var string
     */
    private static $defaultSuffix = 'php';
    
    /**
     * Construct the view and sets defaults.
     * 
     * @return View
     */
    public function __construct()
    {
        $this->setPath(static::getDefaultPath());
        $this->setSuffix(static::getDefaultSuffix());
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
        if (!$this->serviceLocator) {
            throw new Exception('Unable to create helper "' . $name . '" because no helper locator was set.');
        }
        
        try {
            array_unshift($args, $this);
            return $this->serviceLocator->create($name, $args);
        } catch (ServiceLocator\Exception $e) {
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
        if (parent::hasParam($name)) {
            return parent::getParam($name);
        }
        
        if (!$this->serviceLocator) {
            throw new Exception('Unable to get helper "' . $name . '" because no helper locator was set.');
        }
        
        try {
            return $this->serviceLocator->get($name, array($this));
        } catch (ServiceLocator\Exception $e) {
            throw new Exception('Unable to get instance of helper "' . $name . '".');
        }
    }
    
    /**
     * Parses the view file and returns the result.
     * 
     * @param array $params The parameters to render with the view.
     * 
     * @return string
     */
    public function render(array $params = array())
    {
        $fullPath = $this->getFullPath();
        $realPath = realpath($fullPath);
        if (!$realPath) {
            throw new Exception('Could not locate the view "' . $realPath . '".');
        }
        
        ob_start();
        include $realPath;
        return ob_get_clean() . PHP_EOL;
    }

    /**
     * Sets the service locator to use for calling helpers.
     * 
     * @param \Europa\ServiceLocator $serviceLocator The service locator to use for helpers.
     * 
     * @return \Europa\View
     */
    public function setHelperLocator(ServiceLocator $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }
    
    /**
     * Sets the view path.
     * 
     * @var string $path The view path.
     * 
     * @return \Europa\View\Php
     */
    public function setPath($path)
    {
        $realpath = realpath($path);
        if (!$realpath) {
            throw new Exception('The view path "' . $path . '" does not exist.');
        }
        $this->path = $realpath;
        return $this;
    }
    
    /**
     * Returns the set view path.
     * 
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Sets the view suffix to use.
     * 
     * @param string $suffix The view suffix to use. Default is "php".
     * 
     * @return \Europa\View\Php
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
        return $this;
    }
    
    /**
     * Returns the view suffix being used.
     * 
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }
    
    /**
     * Sets the script to be rendered.
     * 
     * @param string $script The path to the script to be rendered relative to the view path, excluding the extension.
     * 
     * @return View
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
     * Compiles and returns the full path from the available information.
     * 
     * @return string
     */
    public function getFullPath()
    {
        $path = $this->getPath() . DIRECTORY_SEPARATOR . $this->getScript();
        if ($suffix = $this->getSuffix()) {
            $path .= '.' . $suffix;
        }
        return $path;
    }
    
    /**
     * Sets the default path for views.
     * 
     * @param string $path The default path.
     * 
     * @return void
     */
    public static function setDefaultPath($path)
    {
        $realpath = realpath($path);
        if (!$realpath) {
            throw new Exception('The default view path "' . $path . '" does not exist.');
        }
        static::$defaultPath = $realpath;
    }
    
    /**
     * Returns the default path.
     * 
     * @return string
     */
    public static function getDefaultPath()
    {
        return static::$defaultPath;
    }
    
    /**
     * Sets the default suffix.
     * 
     * @param string $suffix The default suffix.
     * 
     * @return void
     */
    public static function setDefaultSuffix($suffix)
    {
        static::$defaultSuffix = $suffix;
    }
    
    /**
     * Returns the default suffix.
     * 
     * @return string
     */
    public static function getDefaultSuffix()
    {
        return static::$defaultSuffix;
    }
}
