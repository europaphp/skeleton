<?php

class Package_Builder
{
	/**
	 * The pQuery object that parses the build file.
	 * 
	 * @var Package_Schema
	 */
	protected $_schema;
	
	/**
	 * An array of added packages.
	 * 
	 * @var array
	 */
	protected $_packages = array();
	
	/**
	 * An array of added files.
	 * 
	 * @var array
	 */
	protected $_files = array();
	
	/**
	 * The base path to use.
	 * 
	 * @var string
	 */
	protected $_basePath;
	
	/**
	 * Constructs a new build.
	 * 
	 * @param string $fromFile
	 * @param string $basePath
	 * @return Package
	 */
	public function __construct(Package_Schema $schema, $basePath)
	{
		$this->_schema = $schema;
		// normalize
		if ($basePath) {
			$basePath = realpath($basePath);
			$basePath = $basePath . DIRECTORY_SEPARATOR;
		}
		$this->_basePath = $basePath;
	}
	
	/**
	 * Adds the specified package to the build.
	 * 
	 * @param string $package The package to add.
	 * @return Package
	 */
	public function add($package)
	{
		// if it's already added, do nothing (prevents recursion)
		if (in_array($package, $this->_packages)) {
			return $this;
		}
		// add to list to prevent recursion
		$this->_packages[] = $package;
		// add files
		foreach ($this->_schema->getFiles($package) as $file) {
			$this->_addFile($this->_basePath . $file);
		}
		// add directories
		foreach ($this->_schema->getDirectories($package) as $directory) {
			$this->_addDirectory($this->_basePath . $directory);
		}
		// add dependencies
		foreach ($this->_schema->getDependencies($package) as $dependency) {
			$this->add($dependency);
		}
		return $this;
	}
	
	/**
	 * Saves the file.
	 * 
	 * @param string $as The path to save the file as.
	 * @return bool
	 */
	public function save($as)
	{
		return $this->_zip($as);
	}
	
	/**
	 * Zips the file, automates headers and sends it to the browser.
	 * 
	 * @param string $filename The filename to call the file.
	 * @return void
	 */
	public function push($filename)
	{
		// temporary zip file name
		$tmpZipName = md5(rand() . microtime() . rand());
		
		$this->_zip($tmpZipName);
		
		// output
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename=' . $filename);
		readfile($tmpZipName);
		
		// cleanup
		unlink($tmpZipName);
		
		exit;
	}
	
	/**
	 * Adds a file to the build.
	 * 
	 * @return Package
	 */
	protected function _addFile($file)
	{
		$file = realpath($file);
		if (!is_file($file)) {
			throw new Package_Exception(
				$file . ' is not a file.'
			);
		}
		$this->_files[] = $file;
		return $this;
	}
	
	/**
	 * Adds a whole directory to the build.
	 * 
	 * @return Package
	 */
	protected function _addDirectory($dir)
	{
		$dir = realpath($dir);
		if (!is_dir($dir)) {
			throw new Package_Exception(
				$dir . ' is not a directory.'
			);
		}
		$dirs = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir),
			RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($dirs as $item) {
			var_dump($item);
			if ($item->isFile()) {
				$this->_addFile($item->getPathname());
			}
		}
		return $this;
	}

	/**
	 * Zips up the added components/files into a zip file.
	 * 
	 * @param string $fullpath The full path to the zip file.
	 * @return Package
	 */
	protected function _zip($fullpath)
	{
		// create a new zip file object
		$zip = new ZipArchive();

		// make sure the zip can be created and open it
		if ($zip->open($fullpath, ZipArchive::OVERWRITE) !== true) {
			throw new Package_Exception(
				'Could not create ' . $fullpath
			);
		}

		// find all package files
		foreach ($this->_files as $file) {
			if (is_file($file)) {
				// normalize the file so it appears in the intended path in the zip
				$zipFile = substr($file, strlen($this->_basePath));
				$zip->addFile($file, $zipFile);
			}
		}

		// close the zip file
		$zip->close();

		return $this;
	}
}