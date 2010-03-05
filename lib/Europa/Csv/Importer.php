<?php

/**
 * @author Trey Shugart
 */

/**
 * Takes a CSV file or CSV string and converts it to an array.
 * 
 * @package Europa
 * @subpackage Csv
 */
class Europa_Csv_Importer extends Europa_Csv_Abstract
{
	protected
		$_input = '';
	
	/**
	 * Constructs the Csv_Importer. The first parameter can either be a file or a string.
	 * 
	 * @return object
	 * 
	 * @param string $csvToParse A csv file or string of csv values.
	 * @param array  $config      The configuration array used to configure the importer. Config
	 *                            can also be set after instantiation using Csv_Importer::setConfig().
	 */
	public function __construct($csvToParse)
	{
		if (is_file($csvToParse)) {
			$this->_input = file_get_contents($csvToParse);
		} else {
			// will also handle instances of Csv_Exporter
			$this->_input = (string) $csvToParse;
		}
	}
	
	/**
	 * Parses the passed csv file into an array.
	 * 
	 * @return array
	 */
	public function toArray()
	{
		static $parsed;
		
		if (!isset($parsed)) {
			// pre 5.3.0
			if (version_compare(PHP_VERSION, '5.3.0', '<')) {
				$csv  = array(); 
				$max  = 5 * 1024 * 1024;
				$file = fopen('php://temp/maxmemory:' . $max, 'r+');
				
				fputs($file, $this->_input);
				rewind($file);
				
				while ($data = fgetcsv($file, 1000, $this->delimiter, $this->enclosure)) {
				    $csv[] = $data; 
				}
				
				fclose($file);
				
				$parsed = $csv;
			// 5.3.0
			} else {
				$parsed = str_getcsv($this->_input, $this->delimiter, $this->enclosure, $this->escape);
			}
		}
		
		return $parsed;
	}
}