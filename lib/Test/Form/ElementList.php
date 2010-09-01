<?php

/**
 * Tests for validating Europa_Form_ElementList.
 * 
 * @category Tests
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Test_Form_ElementList extends Europa_Unit_Test
{
	/**
	 * The list that is being used for the test.
	 * 
	 * @var Europa_Form_ElementList
	 */
	private $_list;
	
	/**
	 * Set up the test.
	 * 
	 * @return void
	 */
	public function setUp()
	{
		$this->_list = new Test_Form_TestElementList;
		$this->_list['name'] = new Europa_Form_Element_Input;
	}
	
	/**
	 * Tests list output.
	 * 
	 * @return bool
	 */
	public function testElementExistence()
	{
		if (!$this->_list['name'] instanceof Europa_Form_Element_Input) {
			return false;
		}
		return true;
	}
	
	/**
	 * Tests validation on all elements in the list.
	 * 
	 * @return bool
	 */
	public function testValidation()
	{
		$required = new Europa_Validator_Required;
		$required->addMessage('Name is required.');
		$this->_list['name']->setValidator($required)->value = 'not empty so it can be valid';
		
		return $this->_list->validate()->isValid();
	}
}

/**
 * Dummy class for validating the Europa_Form_ElementList
 * 
 * @category Tests
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Test_Form_TestElementList extends Europa_Form_ElementList
{
	public function __toString()
	{
		return '';
	}
}