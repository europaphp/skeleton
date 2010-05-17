<?php

class Test_All extends Europa_Unit_Suite
{
	public function getTests()
	{
		return array(
			'Test_Loader',
			'Test_Request'
		);
	}
}