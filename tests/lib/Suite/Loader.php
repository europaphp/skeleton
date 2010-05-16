<?php

class Suite_Loader extends Europa_Unit_Suite
{
	public function getTests()
	{
		return array(
			'Test_Loader_AddPath',
			'Test_Loader_LoadClass',
			'Test_Loader_RegisterAutoload'
		);
	}
}