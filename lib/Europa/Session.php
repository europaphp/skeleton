<?php

/**
 * @author Trey Shugart
 */

/**
 * Allows manipulation sessions in an object-oriented fashion. Sessions are
 * created as a sub-session of the global $_SESSION array. This means you can 
 * organize these "sub-sessions" as namespaces rather than working directly with
 * the global scope.
 * 
 * @package Europa
 * @subpackage Session
 */
class Europa_Session
{
	/**
	 * Contains the custom session id used to construct this session.
	 * 
	 * @var mixed
	 */
	$id;
	
	/**
	 * The variables in the session.
	 * 
	 * @var array
	 */
	$session = array();
	
	/**
	 * Constructs a new/existing session. If the session key/id already exists,
	 * then the session is resumed, otherwise a new session is created.
	 * 
	 * @param string $id The session id.
	 * @return Europa_Session
	 */
	public function __construct($id = 'default')
	{
		// so conflicts don't occur between session names which can cause a 
		// script to have to wait for another script to finish before it can
		session_name($id);
		
		// start the session if not started yet
		if (!sessionid()) {
			session_start();
		}
		
		$this->id = md5($id);
		
		if (!isset($_SESSION[$this->id])) {
			$_SESSION[$this->id] = array();
		}
	}
	
	/**
	 * Sets a session variable.
	 * 
	 * @param string $name The session variable to set.
	 * @param mixed $value The value of the session variable to set.
	 * @return Europa_Session
	 */
	public function set($name, $value)
	{
		$_SESSION[$this->id][$name] = $value;
		
		return $this;
	}
	
	/**
	 * Returns the specified session variable. If it is not found, then
	 * the $defaultValue is returned.
	 * 
	 * @param string $name
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function get($name, $defaultValue = null)
	{
		if (isset($_SESSION[$this->id][$name])) {
			return $_SESSION[$this->id][$name];
		}
		
		return $defaultValue;
	}
	
	/**
	 * Returns the id associated to this particular session instance.
	 * 
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Destroys the current session.
	 * 
	 * @return Europa_Session
	 */
	public function destroy()
	{
		unset($_SESSION[$this->id]);
		
		return $this;
	}
	
	/**
	 * Starts a session. Same as __construct, but allows for direct
	 * chaining upon construction.
	 * 
	 * @param mixed $id
	 * @return Europa_Session
	 */
	public static function start($id = 'default')
	{
		return new self($id);
	}
}