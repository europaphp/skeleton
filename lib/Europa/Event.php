<?php

/**
 * @author Trey Shugart
 */

/**
 * Europa's Event class for binding, triggering and performing other 
 * common event tasks.
 * 
 * @package Europa
 * @subpackage Event
 */
class Europa_Event
{
	/**
	 * Holds event data for all registered events.
	 * 
	 * @var array
	 */
	protected static $_events = array();
	
	/**
	 * Binds a method to an event.
	 * 
	 * @param mixed $eventNames A string event name, or Array of event names to bind
	 * the passed callback an data to.
	 * @param mixed $method The callback to bind to the event. Can be any valid 
	 * callback. In PHP 5.3.0, this can be a closure. The callback takes only 
	 * one argument and that is an instance ofEuropa_Event_Data.
	 * @param mixed $bindData An optional argument of data (Array, String, whatever)
	 * to pass to the event object that gets passed to the callback as the only 
	 * argument.
	 * @return string
	 */
	public static function bind($eventNames, $method, $bindData = null)
	{
		foreach ((array) $eventNames as $eventName) {
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
	 * Unbinds a specified method from an event, or if unspecified, the whole event
	 * is unbound.
	 * 
	 * @param Mixed $eventNames - A string event name, or an Array of event names to
	 * unbind.
	 * @return Boolean True if any events are unbound, false if not.
	 */
	public static function unbind($eventNames)
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
	 * Triggers an event stack. Fires the callbacks in the order in which they 
	 * were bound.
	 * 
	 * @param mixed $eventNames A string event name, or Array of event names to
	 * trigger.
	 * @param arraty $triggerData The data to pass to the callback via 
	 * Europa_Event_Data at the time of event triggering.
	 * @return Boolean
	 */
	public static function trigger($eventNames, $triggerData = null)
	{
		// if there are no events to trigger, then return false
		if (!is_array(self::$_events)) {
			return false;
		}
		
		// return false by default
		$ret = false;
		
		// foreach event to trigger
		foreach ((array) $eventNames as $eventToTrigger) {
			// now foreach event, try and find a match
			foreach (self::$_events as $eventType => $eventHandlers) {
				// if there is a match
				if ($eventToTrigger === $eventType) {
					// foreach handler, trigger the event
					foreach ($eventHandlers as $eventHandler => $eventData) {
						// event data is passed through a single arugment of the
						// Europa_Event_Data object
						$func           = $eventData['method'];
						$e              = new Europa_Event_Data;
						$e->event       = $eventType;
						$e->bindData    = (array) $eventData['bindData'];
						$e->triggerData = (array) $triggerData;
						
						// call the function, passing the data as the only argument
						if (is_callable($func)) {
							call_user_func_array($func, array($e));
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
	 * Returns whether the event passed is bound or not.
	 * 
	 * @param String $eventName
	 * @return Boolean
	 */
	public static function isBound($eventName)
	{
		return isset(self::$_events[$eventName]);
	}
}