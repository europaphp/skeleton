<?php

namespace Helper;
use Europa\Uri as UriObject;
use Europa\View;

/**
 * A base helper that auto-loads files.
 * 
 * @category Helpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class Script
{
    /**
     * The files associated to this instance.
     * 
     * @var array
     */
    protected $files = array();
    
    /**
     * The base path to the files.
     * 
     * @var string
     */
    protected $path = null;
    
    /**
     * The suffix for all files.
     * 
     * @var string
     */
    protected $suffix = null;
    
    /**
     * The base path to all files.
     * 
     * @var string
     */
    protected static $defaultPath = null;
    
    /**
     * The default suffix for all files.
     * 
     * @var string
     */
    protected static $defaultSuffix = null;
    
    /**
     * Compiles an html tag for a single file.
     * 
     * @param string $file The file to compile an html tag for.
     * 
     * @return string
     */
    abstract protected function compileTag($file);
    
    /**
     * Constructs the helper.
     * 
     * @param \Europa\View $view  The view that called the helper.
     * @param string|array $files Specific files to load instead of the ones corresponding to the view.
     * 
     * @return ScriptHelper
     */
    public function __construct(View $view, $files = array())
    {
        $this->files = (array) $files;
        if (!$this->files) {
            $this->files = $this->getFilesFor($view);
        }
        $this->setPath(static::getDefaultPath());
        $this->setSuffix(static::getDefaultSuffix());
    }
    
    /**
     * Returns the link to the stylesheet.
     * 
     * @return string
     */
    public function __toString()
    {
        $string = '';
        foreach ($this->files as $file) {
            $file = '/' . str_replace('\\', '/', $file);
            if ($this->suffix) {
                $file .= '.' . $this->suffix;
            }
            
            if ($this->path) {
                $file = '/' . $this->path . $file;
            }
            
            if ($root = UriObject::detect()->getRoot()) {
                $file = '/' . $root . $file;
            }
            
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $file)) {
                $string .= $this->compileTag($file) . "\n";
            }
        }
        return $string;
    }
    
    /**
     * Sets a path specific to this instance.
     * 
     * @param string $path The path to use.
     * 
     * @return ScriptHelper
     */
    public function setPath($path)
    {
        $this->path = trim($path, '/');
        return $this;
    }
    
    /**
     * Returns the path.
     * 
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Sets the suffix for all files.
     * 
     * @param string $suffix The suffix to use.
     * 
     * @return ScriptHelper
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
        return $this;
    }
    
    /**
     * Returns the suffix.
     * 
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }
    
    /**
     * Recursively gets the files for the specified view.
     * 
     * @param \Europa\View $view The view to get the files for.
     * 
     * @return array
     */
    private function getFilesFor(View $view)
    {
        $files = array($view->getScript());
        if ($child = $view->getChildScript()) {
            $files[] = $child;
        }
        return $files;
    }
    
    /**
     * Sets the global path.
     * 
     * @param string $path The base path to the files.
     * 
     * @return void
     */
    public static function setDefaultPath($path)
    {
        static::$defaultPath = trim($path, '/');
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
     * Sets the global suffix.
     * 
     * @param string $path The base path to the files.
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