<?php

/**
 * @package Csv
 * @author  Trey Shugart
 */

/** 
 * Outlines common package structure.
 */
abstract class Europa_Csv_Abstract
{
	public 
		$delimiter = ',',
		$enclosure = '"',
		$escape    = '\\';
}