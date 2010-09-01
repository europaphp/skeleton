<?php

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
	public function testAddition()
	{
		if (!$this->_list['name'] instanceof Europa_Form_Element_Input) {
			return false;
		}
		return true;
	}
}

class Test_Form_TestElementList extends Europa_Form_ElementList
{
	public function __toString()
	{
		return '';
	}
}