<?php

class Package_Schema
{
	/**
	 * Contains the pQuery object used.
	 * 
	 * @var pQuery
	 */
	protected $_schema;
	
	/**
	 * Constructs a new package schema.
	 * 
	 * @return Package_Schema
	 */
	public function __construct(pQuery $schema)
	{
		$this->_schema = $schema;
	}

	/**
	 * Returns the directories for the specified package.
	 * 
	 * @param string $id The id of the package.
	 * @return array
	 */
	public function getDirectories($id)
	{
		$arr  = array();
		$dirs = $this->_schema->find('//packages/package[@id="' . $id . '"]/directory');
		foreach ($dirs as $dir) {
			$arr[] = $dir->text();
		}
		return $arr;
	}
	
	/**
	 * Returns the files for the specified package.
	 * 
	 * @param string $id The id of the package.
	 * @return array
	 */
	public function getFiles($id)
	{
		$arr   = array();
		$files = $this->_schema->find('//packages/package[@id="' . $id . '"]/file');
		foreach ($files as $file) {
			$arr[] = $file->text();
		}
		return $arr;
	}
	
	/**
	 * Retrieves the package specified by $package.
	 * 
	 * @param string $id The id of the package.
	 * @return array
	 */
	public function getDependencies($id)
	{
		$arr  = array();
		$deps = $this->_schema->find('//packages/package[@id="' . $id . '"]/dependency');
		foreach ($deps as $dep) {
			$arr[] = $dep->text();
		}
		return $arr;
	}
}