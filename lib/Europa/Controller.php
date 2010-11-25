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
        // set the request
        $this->_request = $request;
        
        // map properties
        $this->_mapRequestToProperties($request);
        
        // initlialize
        $this->init();
        
        // call the appropriate request method event
        $this->{$request->method()}();
    }
    
    /**
     * Renders the set view.
     * 
     * @return string
     */
    public function __toString()
    {
        // null by default
        $view = null;
        
        // pre-rendering event
        $this->preRender();
        
        // render the view
        if ($this->_view) {
            $view = $this->_view->__toString();
        }
        
        // post rendering event
        $this->postRender();
        
        // return output
        return $view;
    }
        
    /**
     * Sets the view to use. If a view is currently set, it's parameters
     * are copied to the new view.
     * 
     * @param Europa_View $view The view to use.
     * 
     * @return Europa_Controller_Standard
     */
    public function setView(Europa_View $view = null)
    {
        if ($this->_view) {
            $view->setParams($this->_view->getParams());
            unset($this->_view);
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
     * Returns the request being used.
     * 
     * @return Europa_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     * Forwards the request to the specified controller.
     * 
     * @param string $to The controller to forward the request to.
     * @return Europa_Controller
     */
    public function forward($to)
    {
        $to = (string) Europa_String::create($to)->toClass();
        $to = new $to($this->_request);
        $to->action();
        return $to;
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
     * Called when the request method is OPTIONS.
     * 
     * @return void
     */
    protected function options()
    {
        
    }
    
    /**
     * Called when the request method is GET.
     * 
     * @return void
     */
    protected function get()
    {
        
    }
    
    /**
     * Called when the request method is HEAD.
     * 
     * @return void
     */
    protected function head()
    {
        
    }
    
    /**
     * Called when the request method is POST.
     * 
     * @return void
     */
    protected function post()
    {
        
    }
    
    
    /**
     * Called when the request method is PUT.
     * 
     * @return void
     */
    protected function put()
    {
        
    }
    
    /**
     * Called when the request method is DELETE.
     * 
     * @return void
     */
    protected function delete()
    {
        
    }
    
    /**
     * Called when the request method is TRACE.
     * 
     * 
     */
    protected function trace()
    {
        
    }
    
    /**
     * Called when the request method is CONNECT.
     * 
     * @return void
     */
    protected function connect()
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
     * Sets properties from the request onto the class. If a property exists that
     * doesn't have a default value and it doesn't exist in the request, then an
     * exception is thrown.
     * 
     * @return void
     */
    private function _mapRequestToProperties(Europa_Request $request)
    {
        $params = array();
        foreach ($request->getParams() as $name => $param) {
            $params[strtolower($name)] = $param;
        }
        
        $class = new ReflectionClass($this);
        foreach ($class->getProperties() as $property) {
            if (!$property->isPublic()) {
                continue;
            }
            
            $normalcase = $property->getName();
            $lowercase  = strtolower($normalcase);
            if (isset($params[$lowercase])) {
                $this->$normalcase = $params[$lowercase];
            } elseif (!isset($this->$normalcase)) {
                throw new Europa_Controller_Exception(
                    "Required request parameter {$normalcase} was not defined."
                );
            }
            
            // cast the parameter if it is scalar
            if (is_scalar($this->$normalcase)) {
                $this->$normalcase = Europa_String::create($this->$normalcase)->cast();
            }
        }
    }
}