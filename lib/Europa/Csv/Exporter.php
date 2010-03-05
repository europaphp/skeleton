<?php

/**
 * @author Trey Shugart
 */

/** 
 * Exports a valid array to a CSV string.
 * 
 * @package Europa
 * @subpackage Csv
 */
class Europa_Csv_Exporter extends Europa_Csv_Abstract
{
	/**
	 * Creates a new CSV object from a string, file or array. The class then
	 * intelligently handles the input appropriately.
	 * 
	 * @return Csv
	 * 
	 * @param string $csv The csv file, string or pre-parsed csv array.
	 */
	public function __construct($csv)
	{
		if ($csv instanceof Europa_Csv_Importer) {
			$this->_input = $csv->toArray();
		} else {
			$this->_input = (array) $csv;
		}
	}
	
	/**
	 * Parses the input to a string.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		static $parsed;
		
		if (!isset($parsed)) {
			/*
			 * output up to 5MB is kept in memory, if it becomes bigger
			 * it will automatically be written to a temporary file
			 */
			$max = 5 * 1024 * 1024;
			$csv = fopen('php://temp/maxmemory:' . $max, 'r+');
			
			// parse each csv row
			foreach ($this->_input as $input) {
				fputcsv($csv, $input, $this->delimiter, $this->enclosure);
			}
			
			// so we can grab the whole temporary file
			rewind($csv);

			// put it all in a variable
			$parsed = stream_get_contents($csv);
		}
		
		return $parsed;
	}
}