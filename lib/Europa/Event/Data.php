<?php 

/**
 * @author Trey Shugart
 */

/**
 * The event data object that gets passed to the bound event callback whenever 
 * an event is triggered.
 * 
 * @package Europa
 * @subpackage Event
 */
class Europa_Event_Data
{
	/**
	 * Holds the name of the event that was triggered.
	 * 
	 * @var string
	 */
	public $event = null;
	
	/**
	 * Holds the data that was passed at the time of binding.
	 * 
	 * @var array
	 */
	public $bindData = array();
	
	/**
	 * Holds the data that was passed at the time of triggering.
	 * 
	 * @var array
	 */
	public $triggerData = array();
}