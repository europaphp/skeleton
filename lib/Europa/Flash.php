<?php

class Europa_Flash
{
	static $sessionKey = 'Europa_Flash';
	
	static public function set($message, $namespace = 'default')
	{
		$session  = self::_init($namespace);
		$messages = $session->get($namespace, array());
		
		array_push($messages, $message);
		
		$session->set($namespace, $messages);
	}
	
	static public function get($namespace = 'default', $clear = true)
	{
		$session  = self::_init($namespace);
		$messages = $session->get($namespace);
		
		if ($clear) {
			self::clear();
		}
		
		return $messages;
	}
	
	static public function clear($namespace = 'default')
	{
		$session = self::_getSession();
		
		if ($namespace) {
			$session->set($namespace, null);
		} else {
			$session->destroy();
		}
	}
	
	static private function _init($namespace)
	{
		$session = self::_getSession();
		
		if (!$session->get($namespace)) {
			$session->set($namespace, array());
		}
		
		return $session;
	}
	
	static private function _getSession()
	{
		return new Europa_Session(self::$sessionKey);
	}
}