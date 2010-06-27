<?php

/**
 * The base controller for all controller classes.
 * 
 * @category  Controllers
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
abstract class Europa_Controller
{
	/**
	 * The request used to dispatch to this controller.
	 * 
	 * @var Europa_Request
	 */
	private $_request;
	
	/**
	 * Renders the set view.
	 * 
	 * @return string
	 */
	abstract public function __toString();
	
	/**
	 * Handles action dispatching.
	 * 
	 * @return mixed
	 */
	abstract public function action();
	
	/**
	 * Constructs a new controller using the specified request.
	 * 
	 * @param Europa_Request $request The request to use.
	 * @return Europa_Controller
	 */
	public function __construct(Europa_Request $request)
	{
		$this->_request = $request;
	}
	
	/**
	 * Returns the request being used.
	 * 
	 * @return Europa_Request
	 */
	protected function _getRequest()
	{
		return $this->_request;
	}
	
	/**
	 * Redirects the request to the specified url.
	 * 
	 * @param string $uri The uri to redirect to.
	 * @return void
	 */
	protected function _redirect($uri)
	{
		$this->_request->redirect($uri);
	}
	
	/**
	 * Forwards the request to the specified controller.
	 * 
	 * @param string $to The controller to forward the request to.
	 * @return Europa_Controller
	 */
	protected function _forward($to)
	{
		$to = Europa_String::create($to)->toClass();
		$to = new $to($this->_request);
		$to->action();
		return $to;
	}
	
	/**
	 * Sniffs the passed in method for any parameters existing in the request
	 * and returns the appropriate parameters, in the order which they were
	 * defined in the action. Useful for using in conjunction with
	 * call_user_func_array().
	 * 
	 * If required parameters is not found, an exception is thrown.
	 * 
	 * @param string $action The action to map the parameters for.
	 * @param bool $caseSensitive Whether or not to be case-sensitive or not.
	 * @return array
	 */
	protected function _getMappedParams($action, $caseSensitive = false)
	{
		$methodParams  = array();
		$requestParams = array();
		foreach ($this->_getRequest()->getParams() as $name => $value) {
			$name = $caseSensitive ? strtolower($name) : $name;
			$requestParams[$name] = $value;
		}
		
		// create a reflection method
		$method = new ReflectionMethod($this, $action);

		// automatically define the parameters that will be passed to the action
		foreach ($method->getParameters() as $param) {
			$pos  = $param->getPosition();
			$name = strtolower($param->getName());

			// apply named parameters
			if (array_key_exists($name, $requestParams)) {
				$methodParams[$pos] = $requestParams[$name];
			// set default values
			} elseif ($param->isOptional()) {
				$methodParams[$pos] = $param->getDefaultValue();
			// throw exceptions when required params aren't defined
			} else {
				throw new Europa_Request_Exception(
					"A required parameter for {$method->getName()} was not defined.",
					Europa_Request_Exception::REQUIRED_METHOD_ARGUMENT_NOT_DEFINED
				);
			}

			// cast the parameter if it is scalar
			if (is_scalar($methodParams[$pos])) {
				$methodParams[$pos] = Europa_String::create($methodParams[$pos])->cast();
			}
		}
		return $methodParams;
	}
}