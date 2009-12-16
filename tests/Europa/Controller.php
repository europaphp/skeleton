<?php

class UnitTest_Europa_Controller extends Europa_Unit
{
	public function testCorrectRootUriSniffing()
	{
		 return 'europa-dev' === Europa_Controller::getActiveInstance()->rootUri;
	}
	
	public function testCorrectRequestUriSniffing()
	{
		 return 'test/Europa_Controller' === Europa_Controller::getActiveInstance()->requestUri;
	}
	
	public function testOverridingTheRouter()
	{
		
	}
	
	public function testCustomRouting()
	{
		
	}
	
	public function testSettingADefaultController()
	{
		
	}
	
	public function testSettingADefaultAction()
	{
		
	}
	
	public function testSettingParamters()
	{
		
	}
	
	public function testDispatchingWithARoute()
	{
		
	}
	
	public function testEventPreDispatch()
	{
		
	}
	
	public function testEventRouteMatched()
	{
		
	}
	
	public function testEventRouteNotMatched()
	{
		
	}
	
	public function testEventPostDispatch()
	{
		
	}
}