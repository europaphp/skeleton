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
		$this->_list['user[0][name]'] = new Europa_Form_Element_Input;
		$this->_list['user[0][bio]']  = new Europa_Form_Element_Textarea;
		$this->_list['user[0][name]']->value = 'tres';
		$this->_list['user[0][bio]']->value  = 'php dev';
		$this->_list['user[zero][name]'] = new Europa_Form_Element_Input;
		$this->_list['user[zero][bio]']  = new Europa_Form_Element_Textarea;
		$this->_list['user[zero][name]']->value = 'tres';
		$this->_list['user[zero][bio]']->value  = 'php dev';
	}
	
	/**
	 * Tests list output.
	 * 
	 * @return bool
	 */
	public function testElementExistence()
	{
		if (!$this->_list['user[0][name]'] instanceof Europa_Form_Element_Input) {
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
		return $this->_list->validate()->isValid();
	}
	
	/**
	 * Tests the toArray value when using numeric indicies.
	 * 
	 * @return bool
	 */
	public function testNumericToArray()
	{
		$toArray = $this->_list->toArray();
		return isset($toArray['user'][0]['name'])
		    && isset($toArray['user'][1]['bio'])
		    && $toArray['user'][0]['name'] === 'tres'
		    && $toArray['user'][1]['bio']  === 'php dev';
	}
	
	/**
	 * Tests the toArray value when using string indicies that aren't numeric.
	 * 
	 * @return bool
	 */
	public function testStringToArray()
	{
		$toArray = $this->_list->toArray();
		return isset($toArray['user']['zero']['name'])
		    && isset($toArray['user']['zero']['bio'])
		    && $toArray['user']['zero']['name'] === 'tres'
		    && $toArray['user']['zero']['bio']  === 'php dev';
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