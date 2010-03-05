<?php

/**
 * @author Trey Shugart
 */

/** 
 * Outlines common package structure.
 * 
 * @package Europa
 * @subpackage Csv
 */
abstract class Europa_Csv_Abstract
{
	/**
	 * The delimiter for the values.
	 * 
	 * @var string
	 */
	public $delimiter = ',';
	
	/**
	 * The enclosure for the values.
	 * 
	 * @var string
	 */
	public $enclosure = '"';
	
	/**
	 * The character used to escape the enclosures if they
	 * appear in values.
	 * 
	 * @var string
	 */
	public $escape    = '\\';
}