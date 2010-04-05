<?php

class Europa_Build
{
	protected $_xml;
	
	protected $_components = array();
	
	protected $_basePath;
	
	protected $_zipBase;
	
	public function __construct($fromFile, $basePath = './', $zipBase = null)
	{
		if (!is_file($fromFile)) {
			throw new Europa_Build_Exception(
				'Build file <strong>'
				. $fromFile
				. '</strong> cannot be found.',
				Europa_Build_Exception::FILE_NOT_FOUND
			);
		}
		
		// the xml file defining the release schema
		$this->_xml = new pQuery($fromFile);
		
		// normalize base path
		if ($basePath) {
			$basePath = realpath($basePath);
			$basePath = $basePath . DIRECTORY_SEPARATOR;
		}
		
		// normalize zip base path
		if ($zipBase) {
			$zipBase = trim($zipBase, '/\\') . DIRECTORY_SEPARATOR;
		}
		
		// set the normalized base path
		$this->_basePath = $basePath;
		$this->_zipBase  = $zipBase;
	}
	
	public function addComponent($component)
	{
		// if it's already added, do nothing
		if (in_array($component, $this->_components)) {
			return $this;
		}
		
		$component = $this->_xml->find('//component[@id="' . $component . '"]');
		
		if ($component->length) {
			// add to list of components
			$this->_components[] = $component->attr('id');
			
			// if this component has any dependencies, add them also
			foreach ($component->find('//dependency') as $dependency) {
				$dep = $dependency->text();
				
				// dependency on all components
				if ($dep === '*') {
					foreach ($this->_xml->find('//component') as $comp) {
						$this->addComponent($comp->attr('id'));
					}
				} else {
					$this->addComponent($dep);
				}
			}
		}
		
		return $this;
	}
	
	public function save($as)
	{
		return $this->zip($as);
	}
	
	public function push($filename)
	{
		// temporary zip file name
		$tmpZipName = md5(rand() . microtime() . rand());
		
		$this->zip($tmpZipName);
		
		// output
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename=' . $filename);
		readfile($tmpZipName);
		
		// cleanup
		unlink($tmpZipName);
		
		exit;
	}
	
	protected function zip($fullpath)
	{
		$zip = new ZipArchive();
		
		// make sure the zip can be created
		if (!$zip->open($fullpath, ZipArchive::OVERWRITE)) {
			throw new Europa_Build_Exception(
				'Cannot create file '
				. $fullpath
				. '</strong>.',
				Europa_Build_Exception::ZIP_CREATE_FAIL
			);
		}
		
		// add component files
		foreach ($this->_components as $component) {
			foreach ($this->_xml->find('//component[@id="' . $component . '"]/file') as $file) {
				$file = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $file->text());
				
				if (is_file($this->_basePath . $file)) {
					$zip->addFile($this->_basePath . $file, $this->_zipBase . $file);
				}
			}
		}
		
		$zip->close();
		
		return $this;
	}
}