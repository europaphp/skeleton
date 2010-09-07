<?php

/**
 * An example of an abstract controller to act as a base class for all
 * controllers.
 * 
 * @category  Controllers
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
abstract class Europa_Controller_Action extends Europa_Controller
{
	/**
	 * Throws an exception if an action doesn't exist.
	 * 
	 * @param string $action The action that was called.
	 * @param array $args The arguments passed to the action.
	 * @return void
	 */
	public function __call($action, $args)
	{
		$class = get_class($this);
		// by default, an action must exist
		throw new Europa_Controller_Exception(
			"Action {$class}->{$action}() does not exist.",
			Europa_Controller_Exception::ACTION_NOT_FOUND
		);
	}
	
	/**
	 * Implements action dispatching.
	 * 
	 * @return void
	 */
	public function action()
	{
		$action = $this->_formatAction();
		return call_user_func_array(array($this, $action), $this->_mapActionArguments($action));
	}

	/**
	 * Formats the action and returns it.
	 * 
	 * @return string
	 */
	protected function _formatAction()
	{
		$action = $this->getRequest()->getParam('action', 'index');
		return Europa_String::create($action)->toClass()->__toString() . 'Action';
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
	 * @return array
	 */
	protected function _mapActionArguments($action)
	{
		$methodParams  = array();
		$requestParams = array();
		foreach ($this->getRequest()->getParams() as $name => $value) {
			$requestParams[strtolower($name)] = $value;
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
				$class = get_class($this);
				throw new Europa_Request_Exception(
					"Parameter {$param->getName()} for {$class}->{$method->getName()}() was not defined.",
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