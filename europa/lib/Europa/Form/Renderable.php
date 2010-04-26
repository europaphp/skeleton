<?php

/**
 * Basic interface for anything that can be rendered in a form.
 * 
 * @category Forms
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
interface Europa_Form_Renderable
{
	/**
	 * A way to turn the renderable element into a string.
	 * 
	 * @return string
	 */
	public function __toString();
}