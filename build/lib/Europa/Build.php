<?php

class Europa_Build
{
	protected $xml;
	
	protected $components = array();
	
	protected $basePath;
	
	protected $zipBase;
	
	public function __construct($fromFile, $basePath = './', $zipBase = null)
	{
		if (!is_file($fromFile)) {
			throw new Europa_Build_Exception(
				'Build file <strong>'
				. $fromFile
				. '</strong> cannot be found.'
				, Europa_Build_Exception::FILE_NOT_FOUND
			);
		}
		
		// the xml file defining the release schema
		$this->xml = new pQuery($fromFile);
		
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
		$this->basePath = $basePath;
		$this->zipBase  = $zipBase;
	}
	
	public function addComponent($component)
	{
		// if it's already added, do nothing
		if (in_array($component, $this->components)) {
			return $this;
		}
		
		$component = $this->xml->find('//component[@id="' . $component . '"]');
		
		if ($component->length) {
			// add to list of components
			$this->components[] = $component->attr('id');
			
			// if this component has any dependencies, add them also
			foreach ($component->find('//dependency') as $dependency) {
				$this->addComponent($dependency->text());
			}
		}
		
		return $this;
	}
	
	public function save($as)
	{
		return $this->zip($as);
	}
	
	public function output($filename)
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
				. '</strong>.'
				, Europa_Build_Exception::ZIP_CREATE_FAIL
			);
		}
		
		// add component files
		foreach ($this->components as $component) {
			foreach ($this->xml->find('//component[@id="' . $component . '"]/file') as $file) {
				$file = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $file->text());
				
				if (is_file($this->basePath . $file)) {
					$zip->addFile($this->basePath . $file, $this->zipBase . $file);
				}
			}
		}
		
		$zip->close();
		
		return $this;
	}
}