<?php

class AllTests extends Europa_Unit_Suite
{
	public function getTests()
	{
		return array(
			'Suite_Loader',
			'Suite_Request'
		);
	}
}