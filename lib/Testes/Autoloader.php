<?php

/**
 * The autoloader.
 * 
 * @category Autoloading
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) Trey Shugart 2010 http://europaphp.org/
 */
class Testes_Autoloader
{
    /**
     * The namespace to autoload.
     * 
     * @var string
     */
    const NAMESPACE = 'Testes';
    
    /**
     * The framework path.
     * 
     * @var string
     */
    protected static $frameworkPath;
    
    /**
     * The path to the tests.
     * 
     * @var string
     */
    protected static $testPath;
    
    /**
     * The test namespaces to use.
     * 
     * @var string
     */
    protected static $testNamespace;
    
    /**
     * Registers autoloading.
     * 
     * @return void
     */
    public static function register($testPath = null, $testNamespace = 'Test')
    {
        // format paths
        self::$frameworkPath = realpath(dirname(__FILE__) . '/../');
        self::$testPath      = realpath($testPath);
        self::$testNamespace = $testNamespace;
        
        // make sure the test path is valid
        if (!self::$testPath) {
            throw new Testes_Exception('The test path "' . $testPath . '" is not valid.');
        }
        
        // register with spl
        spl_autoload_register(array(self::NAMESPACE . '_Autoloader', 'autoload'));
    }
    
    /**
     * Autoloads the specified class in this namespace.
     * 
     * @return void
     */
    public static function autoload($class)
    {
        // get the base file name
        $basename = str_replace(array('_', '\\'), '/', $class) . '.php';
        
        // test for framework files
        if (strpos($class, self::NAMESPACE)) {
            include self::$frameworkPath . '/' . $basename;
        } elseif (strpos($class, self::$testNamespace) === 0) {
            include self::$testPath . '/' . $basename;
        }
    }
}