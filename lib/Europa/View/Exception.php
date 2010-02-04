<?php

class Europa_View_Exception extends Europa_Exception
{
	const
		/**
		 * Thrown when a view could not be found. Does not get thrown until a
		 * view is rendered.
		 */
		VIEW_NOT_FOUND = 1,

		/**
		 * Thrown when the script hasn't be set before being rendered.
		 */
		SCRIPT_NOT_SET = 2;
}