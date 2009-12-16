<?php

/**
 * @package    Europa
 * @subpackage Session
 */

/**
 * Allows manipulation sessions in an object-orented fashion. Sessions are
 * created as a sub-session of the global $_SESSION array. This means you can 
 * organize these "sub-sessions" as namespaces rather than working directly with
 * the global scope.
 */
class Europa_Session
{
	private
		/**
		 * Contains the custom session id used to construct this session.
		 * 
		 * @var mixed
		 */
		$_id = null,
		
		$_session = array();
	
	
	
	/**
	 * Constructs a new/existing session. If the session key/id already exists,
	 * then the session is resumed, otherwise a new session is created.
	 * 
	 * @param string $id The session id.
	 */
	public function __construct($id = 'default')
	{
		// so conflicts don't occur between session names which can cause a 
		// script to have to wait for another script to finish before it can
		session_name($id);
		
		// start the session if not started yet
		if (!session_id()) {
			session_start();
		}
		
		$this->_id = md5($id);
		
		if (!isset($_SESSION[$this->_id])) {
			$_SESSION[$this->_id] = array();
		}
	}
	
	/**
	 * Sets a session variable.
	 * 
	 * @param string $name  The session varible to set.
	 * @param mixed  $value The value of the session variable to set.
	 * 
	 * @return void
	 */
	public function set($name, $value)
	{
		$_SESSION[$this->_id][$name] = $value;
		
		return $this;
	}
	
	public function get($name, $defaultValue = null)
	{
		if (isset($_SESSION[$this->_id][$name])) {
			return $_SESSION[$this->_id][$name];
		}
		
		return $defaultValue;
	}
	
	public function getId()
	{
		return $this->_id;
	}
	
	/**
	 * Returns the current session id.
	 * 
	 * @return string
	 */
	public function getPhpSessionId()
	{
		return session_id();
	}
	
	/**
	 * 
	 */
	public function destroy()
	{
		unset($_SESSION[$this->_id]);
		
		return $this;
	}
	
	/**
	 * Starts a session. Same as __construct, but allows for direct
	 * chaining upon construction.
	 * 
	 * @return Europa_Session
	 */
	static public function start($id = 'default')
	{
		return new self($id);
	}
}