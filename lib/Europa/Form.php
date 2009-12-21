<?php

/**
 * 
 */

/**
 * 
 */
class Europa_Form extends Europa_View
{
	const
		/**
		 * The exception that is thrown if the elements property is attempted to
		 * be overwritten.
		 */
		CANNOT_SET_ELEMENTS_PROPERTY = 25;
	
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
		$elements;
	
	/**
	 * Constructs the object setting the part of the form to render.
	 * 
	 * @param string $name
	 * @return Europa_View
	 */
	public function __construct($form = 'default')
	{
		$this->setScript($form);
		
		$this->elements = new stdClass;
		$this->buttons  = new stdClass;
		
		// default submit
		$this->buttons->submit = new Europa_Form_Element('submit');
		
		// default name/label
		$this->buttons->submit->name  = 'submit';
		$this->buttons->submit->value = 'Submit';
	}
	
	/**
	 * Checks to see if the user is attempting to overwrite the elements
	 * property.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		if ($name === 'elements') {
			throw new Europa_Form_Exception(
				'You cannot set the value of the "elements" property.',
				self::EXCEPTION_CANNOT_SET_ELEMENTS_PROPERTY
			);
		}
		
		$this->$name = $value;
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
	 * Overrides the parent method to return the script path to the forms.
	 * 
	 * @return string
	 */
	public function getScript()
	{
		return 'forms/' . $this->_script;
	}
	
	/**
	 * Sets the default form view directory.
	 * 
	 * @return string
	 */
	protected function _getViewFullPath()
	{
		return realpath('./app/views/' . $this->getScript() . '.php');
	}
}