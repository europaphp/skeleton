<?php

class AuthFilter extends \Europa\Controller\FilterAbstract
{
	public function filter()
	{
		if (!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn']) {
			$this->controller->forward('log-in');
		}
	}
}