<?php

namespace Testes\Finder;
use ArrayIterator;
use DirectoryIterator;
use Exception;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;
use Testes\RunableInterface;
use Testes\Suite\Suite;
use UnexpectedValueException;

class Finder implements FinderInterface
{
    /**
     * The default suite class to use.
     * 
     * @var string
     */
    const DEFAULT_SUITE_CLASS = '\Testes\Suite\Suite';
    
    /**
	 * The base directory.
	 * 
	 * @var string
	 */
	private $base;
	
	/**
	 * The directory including the namespace.
	 * 
	 * @var string
	 */
	private $dir;
	
	/**
	 * The namespace to use for classes.
	 * 
	 * @var string
	 */
	private $ns;
	
	/**
	 * The array of suites that were found.
	 * 
	 * @var string
	 */
	private $suites = array();
	
	/**
	 * The suite class to use.
	 * 
	 * @var string
	 */
	private $suiteClass = self::DEFAULT_SUITE_CLASS;

	/**
	 * Constructs a new finder instance.
	 * 
	 * @param string $dir The base directory to look in.
	 * @param string $ns  The namespace to use for class resolution.
	 * 
	 * @return Finder
	 */
	public function __construct($dir, $ns = null)
	{
		// resolve path
		$this->base = realpath($dir);
		
		// ensure the path is correct
		if ($this->base === false) {
			throw new UnexpectedValueException(
				'The directory "' . $dir . '" does not exist.'
			);
		}
		
		// resolve namespace
		$this->ns = trim($ns, '\\');
		
		// set the directory to use
		$this->dir = $this->base;
		
		// append the namespace to the dir
		if ($ns) {
			 $this->dir .= DIRECTORY_SEPARATOR;
			 $this->dir .= str_replace('\\', DIRECTORY_SEPARATOR, $this->ns);
			 $this->dir  = realpath($this->dir);
		}
		
		// recheck the dir to make sure it exists
        if ($this->dir === false) {
            throw new UnexpectedValueException(
                'The test namespace "'
                . $this->ns
                . '" does not exist in "'
                . $this->base
                . '".'
            );
        }
		
		// build the suite list
		$this->buildSuite(new SplFileInfo($this->dir));
	}
	
	/**
	 * Sets the suite class to use.
	 * 
	 * @param string $class The class to use.
	 * 
	 * @return Finder
	 */
	public function setSuiteClass($class)
	{
    	$this->suiteClass = $class;
    	return $this;
	}
	
	/**
	 * Returns the suite class being used.
	 * 
	 * @return string
	 */
	public function getSuiteClass()
	{
    	return $this->suiteClass;
	}
	
	/**
	 * Creates a suite, runs the tests and returns the suite that was run.
	 * 
	 * @return Suite
	 */
	public function run()
	{
	    $suite = $this->suiteClass;
    	$suite = new $suite;
    	$suite->addTests($this);
    	$suite->run();
    	return $suite;
	}

	/**
	 * Returns the iterator for the suites.
	 * 
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->suites);
	}
	
	/**
	 * Builds a suite for the specified directory. This method is recursive so it
	 * will build the list recusively.
	 * 
	 * @param SplFileInfo $dir The root directory to build the suite for.
	 * 
	 * @return void
	 */
	private function buildSuite(SplFileInfo $dir)
	{
		$suite = $this->detectSuite($dir);
		
		foreach ($this->getDirectoryIterator($dir) as $item) {
			if ($item->isDot()) {
				continue;
			}
			
			if ($this->isValid($item)) {
			    if ($item->isDir()) {
    				$this->buildSuite($item);
    			} else {
    				$suite->addTest($this->instantiate($item));
    			}
			}
		}
		
		$this->suites[] = $suite;
	}
	
	/**
	 * Detects and returns a suite instance for the specified directory.
	 * 
	 * @param SplFileInfo $dir The directory to get the suite for.
	 * 
	 * @return SuiteInterface
	 */
	private function detectSuite(SplFileInfo $dir)
	{
		if ($file = $this->getPhpFileForDirectory($dir)) {
			$suite = $this->instantiate($file);
		} else {
		    $suite = $this->suiteClass;
			$suite = new $suite;
		}
		
		return $suite;
	}
	
	/**
	 * Returns the corresponding PHP file for a directory if one exists. If one
	 * does not exist, it returns false.
	 * 
	 * @param SplFileInfo $dir The directory to get the PHP file for.
	 * 
	 * @return SplFileInfo | false
	 */
	private function getPhpFileForDirectory(SplFileInfo $dir)
	{
		$file = $dir->getRealpath() . '.php';
		return is_file($file) ? new SplFileInfo($file) : false;
	}
	
	/**
	 * Returns a directory iterator for the specified item.
	 * 
	 * @param SplFileInfo $item The item to check.
	 * 
	 * @return DirectoryIterator
	 */
	private function getDirectoryIterator(SplFileInfo $item)
	{
		return new DirectoryIterator($item->getRealpath());
	}
	
	/**
	 * Checks to make sure the specified file is a valid test file.
	 * 
	 * @param SplFileInfo $file The file to check.
	 * 
	 * @return bool
	 */
	private function isValid(SplFileInfo $item)
	{
    	$class = $this->formatFileToClass($item);
    	
    	try {
        	$class = new ReflectionClass($class);
        } catch (Exception $e) {
            return false;
        }
        
    	return $class->implementsInterface('\Testes\RunableInterface');
	}
	
	/**
	 * Formats the specified file into a valid class name.
	 * 
	 * @param SplFileInfo $file The file to format.
	 * 
	 * @return string
	 */
	private function formatFileToClass(SplFileInfo $file)
	{
		$class = str_replace('.php', '', $file->getRealpath());
		$class = substr($class, strlen($this->dir) + 1);
		$class = str_replace(DIRECTORY_SEPARATOR, '\\', $class);
		$class = '\\' . $this->ns . '\\' . $class;
		return $class;
	}
	
	/**
	 * Instantiates the class inside the given file.
	 * 
	 * @param SplFileInfo $file The file to instantiate the class for.
	 * 
	 * @return RunableInterface
	 */
	private function instantiate(SplFileInfo $file)
	{
		require_once $file->getRealpath();
		$class = $this->formatFileToClass($file);
		$class = new $class;
		$this->checkInstance($class);
		return $class;
	}
	
	/**
	 * Ensures that the given class instance is a valid runable suite or test.
	 * 
	 * @param mixed $class The class to check.
	 * 
	 * @return bool
	 */
	private function checkInstance($class)
	{
		if (!$class instanceof RunableInterface) {
			throw new RuntimeException(
				'The class "'
				. get_class($class)
				. '" must implement \Testes\Test\RunableInterface.'
			);
		}
	}
}