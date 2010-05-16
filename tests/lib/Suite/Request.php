<?php

class Suite_Request extends Europa_Unit_Suite
{
	public function getTests()
	{
		$tests = array(
			'Dispatch',
			'Params',
			'SetLayout',
			'SetView'
		);
		
		// test base on test environment for requests
		$prefix = 'Test_Request_';
		if (Europa_Request::isCli()) {
			$prefix .= 'Cli';
		} else {
			$prefix .= 'Http';
			$tests[] = 'RequestUriDetection';
			$tests[] = 'RootUriDetection';
		}
		
		// modify prefix
		foreach ($tests as $k => $test) {
			$tests[$k] = $prefix . '_' . $test;
		}
		
		return $tests;
	}
}