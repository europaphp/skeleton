<?php

class UnitTest_Europa_Registry extends Europa_Unit
{
	public function testSettingAndGettingARegistryVariable()
	{
		Europa_Registry::set('myRegVar', true);
		
		return Europa_Registry::get('myRegVar');
	}
	
	public function testRemovingARegistryVariable()
	{
		Europa_Registry::remove('myRegVar');
		
		return !Europa_Registry::get('myRegVar');
	}
}