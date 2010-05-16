<?php

/**
 * A helper for parsing INI language files in the context of a given view.
 * 
 * @category Helpers
 * @package  LangHelper
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class LangHelper
{
	/**
	 * Contains the ini values parsed out of the ini file.
	 * 
	 * @var array
	 */
	protected $_ini = array();
	
	/**
	 * Constructs the language helper and parses the required ini file.
	 * 
	 * @param Europa_View $view The view that called the helper.
	 * @return LangHelper
	 */
	public function __construct(Europa_View $view)
	{
		$path = $this->_getIniFullPath($view);
		if ($path) {
			$this->_ini = parse_ini_file($path);
		}
	}
	
	/**
	 * Allows a language variable to be called as a method. If the first
	 * argument is an array, then named parameters are replaced. If not, then
	 * vsprintf() is used to format the value.
	 * 
	 * Named parameters are prefixed using a colon (:) in the ini value.
	 * 
	 * @param string $name The language variable to retrieve.
	 * @param array $args The arguments passed to the language variable.
	 * @return string
	 */
	public function __call($name, $args)
	{
		$lang = $this->__get($name);
		if (is_array($args[0])) {
			foreach ($args[0] as $name => $value) {
				$lang = str_replace(':' . $name, $value, $lang);
			}
		} else {
			$lang = vsprintf($lang, $args);
		}
		return $lang;
	}
	
	/**
	 * Returns the specified language variable without any formatting.
	 * 
	 * @return string
	 */
	public function __get($name)
	{
		if (isset($this->_ini[$name])) {
			return $this->_ini[$name];
		}
		return null;
	}
	
	/**
	 * Returns the full path to the INI file to parse. The view is used to
	 * determine which file should be parsed.
	 * 
	 * @param Europa_View $view The view to parse the ini file for.
	 * @return string
	 */
	protected function _getIniFullPath(Europa_View $view)
	{
		return dirname(__FILE__) . '/../lang/'
		     . $view->getScript()
		     . '.ini';
	}
}