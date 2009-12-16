<?php

class Europa_Form extends Europa_View
{
	public
		/**
		 * The form's id.
		 * 
		 * @var $id
		 */
		$id = null,
		
		/**
		 * The form's name.
		 * 
		 * @var $name
		 */
		$name = null,
		
		/**
		 * The default action which causes the form to post to the current
		 * request uri.
		 * 
		 * @var $action
		 */
		$action = '#',
		
		/**
		 * The default method for all forms is POST due to the common nature of 
		 * forms which is to POST data.
		 * 
		 * @var $method
		 */
		$method = 'post',
		
		/**
		 * The default enctype for all forms. If the form contains a file input
		 * field then this is set to 'multipart/form-data'.
		 * 
		 * @var $encType
		 */
		$enctype = 'application/x-www-form-urlencoded',
		
		/**
		 * Holds all of the elements
		 * 
		 * @var elements
		 */
		$elements = null;
	
	/**
	 * Constructs the object setting the part of the form to render.
	 * 
	 * @param string $name
	 * 
	 * @return Europa_View
	 */
	public function __construct($form = 'default')
	{
		$this->elements = new stdClass;
		
		$this->setScript($form);
	}
	
	/**
	 * Does any pre-rendering processing then renders the form.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		foreach ($this->elements as $element) {
			if ($element->type === 'file') {
				$this->enctype = 'multipart/form-data';
				
				break;
			}
		}
		
		return parent::__toString();
	}
	
	/**
	 * Fills all elements associated to this group.
	 * 
	 * @param array $values The values to fill the elements with.
	 * 
	 * @return Europa_Form_Fieldset
	 */
	public function fill($values)
	{
		foreach ($this->elements as $element) {
			$element->fill($values);
		}
		
		return $this;
	}
	
	/**
	 * Sets the default form view directory.
	 * 
	 * @return string
	 */
	protected function _getViewPath()
	{
		return './app/forms';
	}
}