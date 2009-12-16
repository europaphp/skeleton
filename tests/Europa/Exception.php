<?php

class UnitTest_Europa_Exception extends Europa_Unit
{
	public function testExceptionOutput()
	{
		$exceptionString = new Europa_Exception('Testing Exception...');
		
		return (bool) preg_match('/Testing Exception\.\.\./', $exceptionString->__toString());
	}
}