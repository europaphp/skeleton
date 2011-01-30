<?php

class LogOutController extends AbstractController
{
	public function get()
	{
		unset($_SESSION['isLoggedIn']);
		$this->redirect();
	}
}