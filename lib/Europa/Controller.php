<?php

/**
 * The base controller for all controller classes.
 * 
 * The following methods are supported with variable parameters:
 *   - cli
 *   - options
 *   - get
 *   - head
 *   - post
 *   - put
 *   - delete
 *   - trace
 *   - connect
 * 
 * @category  Controllers
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart http://europaphp.org/license
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
     * The view rendering the page.
     * 
     * @var Europa_View
     */
    private $_view;
    
    /**
     * Constructs a new controller using the specified request.
     * 
     * @param Europa_Request $request The request to use.
     * 
     * @return Europa_Controller
     */
    public function __construct(Europa_Request $request)
    {
        $this->_request = $request;
        call_user_func_array(array($this, 'init'), $this->_mapParamsTo('init'));
    }
    
    /**
     * Renders the set view.
     * 
     * @return string
     */
    public function __toString()
    {
        // pre-rendering event is always called
        $this->preRender();
        
        // render the view and call the post-render event
        // only if a view exists
        $view = $this->getView();
        if ($view) {
            $view = $view->__toString();
            $this->postRender();
        }
        
        // return output
        return $view;
    }
    
    /**
     * Returns the request being used.
     * 
     * @return Europa_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }
        
    /**
     * Sets the view to use. If a view is currently set, it's parameters
     * are copied to the new view.
     * 
     * @param Europa_View $view The view to use.
     * 
     * @return Europa_Controller
     */
    public function setView(Europa_View $view = null)
    {
        if ($this->_view) {
            $view->setAll($this->_view->getAll());
        }
        $this->_view = $view;
        return $this;
    }
    
    /**
     * Returns the view being used.
     * 
     * @return Europa_View
     */
    public function getView()
    {
        return $this->_view;
    }
    
    /**
     * Forwards the request to the specified controller.
     * 
     * @param string $to The controller to forward the request to.
     * 
     * @return Europa_Controller
     */
    public function forward($to, array $params = array())
    {
        // modify the request
        $request = $this->getRequest();
        $request->setParams($params);
        $request->setController($to);
        die($request->dispatch());
    }
    
    /**
     * Makes sure the appropriate parameters are passed to init and the request
     * method action.
     * 
     * @return void
     * 
     * @throws Europa_Controller_Exception
     */
    public function action()
    {
        // we call the approprate method for the request type
        $method = $this->getRequest()->method();
        
        // check to make sure it exists and if not, throw an exception
        if (!method_exists($this, $method)) {
            throw new Europa_Controller_Exception('The method "' . get_class($this) . '->' . $method . '()" is not supported.');
        }
        
        // pre-actioning
        $this->preAction();
        
        // call appropriate method with it's defined parameters
        call_user_func_array(array($this, $method), $this->_mapParamsTo($method));
        
        // post-actioning
        $this->postAction();
    }
    
    /**
     * Construction event.
     * 
     * @return void
     */
    protected function init()
    {
        
    }
    
    /**
     * Pre-actioning event.
     * 
     * @return void
     */
    protected function preAction()
    {
        
    }
    
    /**
     * Post-actioning event.
     * 
     * @return void
     */
    protected function postAction()
    {
        
    }
    
    /**
     * Gets called prior to rendering.
     * 
     * @return void
     */
    protected function preRender()
    {
        
    }
    
    /**
     * Gets called after rendering.
     * 
     * @return void
     */
    protected function postRender()
    {
        
    }
    
    /**
     * Maps the request parameters to the specified method.
     * 
     * @param string $method The method to get the request parameters for.
     * 
     * @return array
     */
    private function _mapParamsTo($method)
    {
        $methodParams  = array();
        $requestParams = array();
        foreach ($this->getRequest()->getParams() as $name => $value) {
            $requestParams[strtolower($name)] = $value;
        }
        
        // create a reflection method
        $method = new ReflectionMethod($this, $method);

        // automatically define the parameters that will be passed to the method
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
                    "Parameter {$param->getName()} for {$class}->{$method->getName()}() was not defined."
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