<?php

namespace Testes;

/**
 * The autoloader.
 * 
 * @category Autoloading
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) Trey Shugart 2010 http://europaphp.org/
 */
class Autoloader
{
    /**
     * The NS to autoload.
     * 
     * @var string
     */
    const NS = 'Testes';
    
    /**
     * The registered namespaces.
     * 
     * array $namespace => $path
     * 
     * @var array
     */
    private static $paths = array();
    
    /**
     * The framework path.
     * 
     * @var string
     */
    private static $frameworkPath;

    /**
     * Whether or not it has been registered with SPL yet.
     * 
     * @var bool
     */
    private static $isRegistered = false;
    
    /**
     * Registers autoloading.
     * 
     * @return void
     */
    public static function register($path = null)
    {
        // format path and check path
        if ($path) {
            $temp = realpath($path);
            if (!$temp) {
                throw new Exception('The test path "' . $path . '" is not valid.');
            }

            // register the namespace and it's associated path
            self::$paths[$temp] = $temp;
        }
        
        // set defaults
        self::registerFramework();
        self::registerAutoload();
    }
    
    /**
     * Autoloads the specified class in this NS.
     * 
     * @return void
     */
    public static function autoload($class)
    {
        // get the base file name
        $basename = str_replace(array('_', '\\'), DIRECTORY_SEPARATOR, $class) . '.php';
        
        // test for framework files
        if (strpos($class, self::NS) !== false) {
            include self::$frameworkPath . '/' . $basename;
        }

        // load any of the registered files
        foreach (self::$paths as $path) {
            $path = $path . '/' . $basename;
            if (is_file($path)) {
                include $path;
                break;
            }
        }
    }

    /**
     * Registers the framework path if it hasn't been registered yet.
     * 
     * @return void
     */
    private static function registerFramework()
    {
        if (!self::$frameworkPath) {
            self::$frameworkPath = realpath(dirname(__FILE__) . '/../');
        }
    }

    /**
     * Registers autoloading if it hasn't been registered yet.
     * 
     * @return void
     */
    private static function registerAutoload()
    {
        if (!self::$isRegistered) {
            spl_autoload_register(array('\\' . self::NS . '\Autoloader', 'autoload'));
            self::$isRegistered = true;
        }
    }
}
