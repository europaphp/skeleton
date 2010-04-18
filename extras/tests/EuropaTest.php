<?php

class EuropaTest extends Europa_Unit_All
{
	public function getTestClasses()
	{
		return array(
			'EuropaTest_Controller',
			'EuropaTest_Exception',
			'EuropaTest_Loader',
			'EuropaTest_Route',
			'EuropaTest_String',
			'EuropaTest_View'
		);
	}
}