<?php 

/**
 * @package    Europa
 * @subpackage Event
 * @subpackage Object
 */

/**
 * @name Europa_Event_Object       
 * @desc The event object that gets passed to the bound event callback whenever an event is triggered.
 */
class Europa_Event_Object
{
	public
		/**
		 * @property
		 * @public
		 * 
		 * @name event
		 * @desc Holds the name of the event that was triggered.
		 */
		$event       = null,
		
		/**
		 * @property
		 * @public
		 * 
		 * @name bindData
		 * @desc Holds the data that was passed at the time of binding.
		 */
		$bindData    = array(),
		
		/**
		 * @property
		 * @public
		 * 
		 * @name triggerData
		 * @desc Holds the data that was passed at the time of triggering.
		 */
		$triggerData = array();
}