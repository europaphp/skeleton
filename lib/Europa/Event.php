<?php

/**
 * @file
 * 
 * @package    Europa
 * @subpackage Event
 */

/**
 * @class
 * 
 * @name Europa_Event
 * @desc Europa's Event class for binding, triggering and performing other common event tasks.
 */
class Europa_Event
{
	static private
		/**
		 * @property
		 * @static
		 * @private
		 * 
		 * @name _events
		 * @desc Holds event data for all registered events.
		 */
		$_events = array();
	
	
	
	/**
	 * @method
	 * @static
	 * @public
	 * 
	 * @name bind
	 * @desc Binds a method to an event.
	 * 
	 * @param Mixed           $eventNames  - A string event name, or Array of event names to bind the passed callback an data to.
	 * @param Mixed           $triggerData - The callback to bind to the event. Can be any valid callback. In PHP 5.3.0, this can
	 *                                       be a closure. The callback takes only one argument and that is an instance of
	 *                                       Europa_Event_Object.
	 * @param Mixed[Optional] $bindData    - An optional argument of data (Array, String, whatever) to pass to the event object
	 *                                       that gets passed to the callback as the only argument.
	 * 
	 * @return String
	 */
	static public function bind($eventNames, $method, $bindData = null)
	{
		if (is_string($eventNames)) {
			$eventNames = array($eventNames);
		}
		
		foreach ($eventNames as $eventName) {
			// if the event hasn't been bound yet, make an array for it
			if (!isset(self::$_events[$eventName])) {
				self::$_events[$eventName] = array();
			}
			
			// now give it some data
			self::$_events[$eventName][] = array(
					'method'   => $method,
					'bindData' => $bindData
				);
		}
		
		// return true if no errors
		return true;
	}
	
	/**
	 * @method
	 * @static
	 * @public
	 * 
	 * @name unbind
	 * @desc Unbinds a specified method from an event, or if unspecified, the whole event is unbound.
	 * 
	 * @param Mixed $eventNames - A string event name, or an Array of event names to unbind.
	 * 
	 * @return Boolean True if any events are unbound, false if not.
	 */
	static public function unbind($eventNames)
	{
		// if no events are bound yet, then we don't need to go any further
		if (!is_array(self::$_events)) {
			return false;
		}
		
		// return false by default
		$ret = false;
		
		if (is_string($eventNames)) {
			$eventNames = array($eventNames);
		}
		
		// foreach event name, if it is bound, unbind it
		foreach ($eventNames as $eventName) {
			if (self::isBound($eventName)) {
				unset(self::$_events[$eventName]);
				
				// return true if an event was unbound
				$ret = true;
			}
		}
		
		// the resulting return value
		return $ret;
	}
	
	/**
	 * @method
	 * @static
	 * @public
	 * 
	 * @name trigger
	 * @desc Triggers an event stack. Fires the callbacks in the order in which they were bound.
	 * 
	 * @param Mixed           $eventNames            - A string event name, or Array of event names to trigger.
	 * @param Array[Optional] $triggerData - The data to pass to the callback via Europa_Event_Object at the time of event triggering.
	 * 
	 * @return Boolean
	 */
	static public function trigger($eventNames, $triggerData = null)
	{
		// if there are no events to trigger, then return false
		if (!is_array(self::$_events)) {
			return false;
		}
		
		// return false by default
		$ret = false;
		
		if (is_string($eventNames)) {
			$eventNames = array($eventNames);
		}
		
		// foreach event to trigger
		foreach ($eventNames as $eventToTrigger) {
			// now foreach event, try and find a match
			foreach (self::$_events as $eventType => $eventHandlers) {
				// if there is a match
				if ($eventToTrigger === $eventType) {
					// foreach handler, trigger the event
					foreach ($eventHandlers as $eventHandler => $eventData) {
						// event data is passed through a single arugment of the
						// Europa_Event_Object object
						$func           = $eventData['method'];
						$e              = new Europa_Event_Object;
						$e->event       = $eventType;
						$e->bindData    = (array) $eventData['bindData'];
						$e->triggerData = (array) $triggerData;
						
						// call the function, passing the data as the only argument
						// if it is an array, user call user func array
						if (is_array($func)) {
							call_user_func_array($func, $e);
						} else {
							$func($e);
						}
						
						// if events were triggered, return true
						$ret = true;
					}
				}
			}
		}
		
		// return the resulting return value
		return $ret;
	}
	
	/**
	 * @method
	 * @static
	 * @public
	 * 
	 * @name isBound
	 * @desc Returns whether the event passed is bound or not.
	 * 
	 * @param String $eventName
	 * 
	 * @return Boolean
	 */
	static public function isBound($eventName)
	{
		return isset(self::$_events[$eventName]);
	}
}