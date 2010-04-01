<?php

/**
 * @author Trey Shugart
 */

/**
 * A view class for serializing parameters bound to the view.
 * 
 * @package Europa
 * @subpackage View
 */
class Europa_View_Xml extends Europa_View_Abstract
{
	/**
	 * Constructs and sets parameters.
	 * 
	 * @param array $params
	 */
	public function __construct($params = null)
	{
		$this->_params = (array) $params;
	}
	
	/**
	 * Serializes the parameters to a 
	 */
	public function __toString()
	{
		return $this->_serialize($this->_params);
	}
	
	/**
	 * Recursive. Serializes the input object.
	 * 
	 * @return string
	 */
	protected function _serialize($params)
	{
		$str = '';
		
		foreach ($params as $index => $element) {
			// start tag
			$str .= '<' . $element->name;
			
			// if there are attributes, render them
			if (
				isset($element->attributes)
				&& (is_array($element->attributes) || is_object($element->attributes))
			) {
				foreach ($element->attributes as $attrName => $attrValue) {
					$str .= ' ' . $attrName . '="' . $attrValue . '"';
				}
			}
			
			// render content if available
			if (isset($element->content) && $element->content) {
				$str .= '>';
				
				// render child nodes
				if (is_array($element->content) || is_object($element->content)) {
					$str .= $this->_serialize($element->content);
				// render scalar content if available
				} else {
					$str .= $element->content;
				}
				
				$str .= '</' . $this->name . '>';
			// otherwise just self-close
			} else {
				$str .= '/>';
			}
		}
		
		return $str;
	}
}