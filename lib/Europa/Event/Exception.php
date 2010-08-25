<?php

class Europa_Event_Exception
{
	/**
	 * Thrown when an invalid callback is bound to an event.
	 * 
	 * @var int
	 */
	const INVALID_CALLBACK = 1;
	
	/**
	 * Thrown when an invalid event is bound to a stack.
	 * 
	 * @var int
	 */
	const INVALID_EVENT = 2;
}