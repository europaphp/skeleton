<?php

class UnitTest_Europa_Event extends Europa_Unit
{
	static private
		$_triggered = array();
	
	
	
	public function testBindingASingleEventUsingLambdaCallback()
	{
		self::$_triggered['myCustomEvent.event1'] = false;
		
		Europa_Event::bind(
				'myCustomEvent.event1',
				create_function('Europa_Event_Object $e', 'return UnitTest_Europa_Event::event1($e);'), 
				array('bindData1' => true, 'bindData2' => true)
			);
		
		return Europa_Event::isBound('myCustomEvent.event1');
	}
	
	public function testTriggeringASingleEvent()
	{
		Europa_Event::trigger('myCustomEvent.event1', array(
				'triggerData1' => true,
				'triggerData2' => true
			));
		
		return self::$_triggered['myCustomEvent.event1'];
	}
	
	public function testBindingAnArrayOfEvents()
	{
		self::$_triggered['event2'] = false;
		self::$_triggered['event3'] = false;
		
		Europa_Event::bind(
				array('event2', 'event3'),
				array('UnitTest_Europa_Event', 'event2And3'),
				array('bindData1' => true, 'bindData2' => true)
			);
		
		return Europa_Event::isBound('event2') && Europa_Event::isBound('event3');
	}
	
	public function testTriggeringAnArrayOfEvents()
	{
		Europa_Event::trigger(
				array('event2', 'event3'),
				array('triggerData1' => true, 'triggerData2' => true)
			);
		
		return self::$_triggered['event2'] && self::$_triggered['event3'];
	}
	
	public function testUnbindingASingleEvent()
	{
		Europa_Event::unbind('myCustomEvent.event1');
		
		return !Europa_Event::isBound('myCustomEvent.event1');
	}
	
	public function testUnbindingMultipleEvents()
	{
		Europa_Event::unbind(array('event2', 'event3'));
		
		return !(Europa_Event::isBound('event2') && Europa_Event::isBound('event3'));
	}
	
	
	
	static public function event1(Europa_Event_Object $e)
	{
		if (
			   $e->bindData['bindData1']
			&& $e->bindData['bindData2']
			&& $e->triggerData['triggerData1']
			&& $e->triggerData['triggerData2']
		) {
			self::$_triggered[$e->event] = true;
		}
	}
	
	static public function event2And3(Europa_Event_Object $e)
	{
		if (
			   $e->bindData['bindData1']
			&& $e->bindData['bindData2']
			&& $e->triggerData['triggerData1']
			&& $e->triggerData['triggerData2']
		) {
			self::$_triggered[$e->event] = true;
		}
	}
}