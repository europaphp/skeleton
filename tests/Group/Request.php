<?php

class Group_Request extends Europa_Unit_Group
{
	public function getTests()
	{
		return array(
			'Test_Request_Http_SetView',
			'Test_Request_Http_SetLayout'
		);
	}
}