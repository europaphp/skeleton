<?php

/**
 * An example controller sketching some of the features that can be implemented.
 * 
 * @category  Controllers
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
class IndexController extends Europa_Controller
{
	public function __toString()
	{
		$layout = new Europa_View_Php('IndexView');
		$view   = new Europa_View_Php('Index/IndexView');
		return $view->extend($layout)->__toString();
	}
	
	public function action()
	{
		
	}
}