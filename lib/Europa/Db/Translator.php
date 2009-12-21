<?php

/**
 * @package    Europa
 * @subpackage Db
 */

/**
 * 
 */
abstract class Europa_Db_Translator
{
	protected
		/**
		 * The query to translate.
		 * 
		 * @var string
		 */
		$_query;
	
	
	
	/**
	 * Sets the query to be translated.
	 * 
	 * @param string $query The query to translate.
	 * 
	 * @return Object
	 */
	public function __construct($query)
	{
		$this->_query = (string) $query;
	}
	
	/**
	 * Returns the translated query.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->_query;
	}
}