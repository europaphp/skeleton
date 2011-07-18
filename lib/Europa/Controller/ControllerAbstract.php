<?php

namespace Europa\Controller;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;
use Europa\Request\RequestInterface;
use Europa\Response\ResponseInterface;
use Europa\View\ViewInterface;

/**
 * The base controller for all controller classes.
 * 
 * The following methods are supported with any number of user-defined parameters:
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
 * Additionally, if an above request method is not found, the controller will look for a method called "all" to catch
 * all request that are made to the controller.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class ControllerAbstract implements ControllerInterface
{
    /**
     * The request used to dispatch to this controller.
     * 
     * @var RequestInterface
     */
    private $request;
    
    /**
     * The response used to set headers for output
     *
     * @var ResponseInterface
     */
    private $response;

    /**
     * Whether or not to apply filters to action.
     * 
     * @var bool
     */
    private $useFilters = false;
    
    /**
     * The view being rendered.
     * 
     * @var ViewInterface
     */
    private $view;
    
    private $actionResult = array();
    
    /**
     * Returns the method that the controller should call during actioning.
     * 
     * @return string
     */
    abstract public function getActionMethod();
    
    /**
     * Constructs a new controller using the specified request and response.
     *
     * @param RequestInterface  $request  The request to use.
     * @param ResponseInterface $response The response to set headers on
     *
     * @return \Europa\Controller
     */
    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->init();
    }
    
    /**
     * Renders the set view if it exists. If it does not exist, an empty string is returned.
     * 
     * @return string
     */
    public function render()
    {
        $this->preRender();
        $view = $this->view ? $this->view->render($this->actionResult) : '';
        $this->postRender();
        return $view;
    }
    
    /**
     * Returns the request being used.
     * 
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Returns the request being used.
     * 
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sets the view to use. If a view is currently set, it's parameters are copied to the new view.
     * 
     * @param \Europa\ViewInterface $view The view to use.
     * 
     * @return \Europa\Controller\ControllerAbstract
     */
    public function setView(ViewInterface $view = null)
    {
        $this->view = $view;
        return $this;
    }
    
    /**
     * Returns the view being used.
     * 
     * @return \Europa\View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Switches filters on or off.
     * 
     * @param bool $switch True of false for filter application.
     * 
     * @return \Europa\Controller\ControllerAbstract
     */
    public function useFilters($switch = true)
    {
        $this->useFilters = $switch ? true : false;
        return $this;
    }
    
    /**
     * Makes sure the appropriate parameters are passed to init and the request method action.
     * 
     * @return void
     */
    public function action()
    {
        // the method to execute
        $method = $this->getActionMethod();
        
        // apply all detected filters to the specified method
        $this->applyFiltersTo($method);
        
        // pre-action hook
        $this->preAction();
        
        // the return value of the action is the view context
        $result = $this->executeMethod($method, $this->request->getParams());
        $this->setActionResult($result);
        
        // post-action hook
        $this->postAction($context);
        
        return $context;
    }
    
    /**
     * Initialization hook.
     * 
     * @return void
     */
    public function init()
    {
        
    }
    
    /**
     * Pre-action hook.
     * 
     * @return void
     */
    public function preAction()
    {
        
    }
    
    /**
     * Post-action hook.
     * 
     * @return void
     */
    public function postAction()
    {
        
    }
    
    public function preRender()
    {
        
    }
    
    public function postRender()
    {
        
    }

    /**
     * Applies filters to the specified method.
     * 
     * @param string $method The method to apply the filters to.
     * 
     * @return \Europa\Controller\ControllerAbstract
     */
    private function applyFiltersTo($method)
    {
        if (!$this->useFilters) {
            return;
        }
        
        $class         = new ClassReflector($this);
        $method        = new MethodReflector($this, $method);
        $classFilters  = $class->getDocBlock()->getTag('filter', true);
        $methodFilters = $method->getDocBlock()->getTag('filter', true);
        
        foreach (array_merge($classFilters, $methodFilters) as $filter) {
            $filter = $filter->getInstance();
            $filter->filter($this);
        }
        
        return $this;
    }
    
    /**
     * Executes the specified method.
     * 
     * @param string $method The method to execute.
     * 
     * @return \Europa\Controller\ControllerAbstract
     */
    private function executeMethod($method, array $params = array())
    {
        // if the action method exists, then call it
        if (method_exists($this, $method)) {
            $reflector = new MethodReflector($this, $method);
            return $reflector->invokeNamedArgs($this, $params);
        }
        
        // attempt to catch with __call
        if (method_exists($this, '__call')) {
            return $this->__call($method, $params);
        }
        
        throw new Exception("Method {$method} is not supported and was not trapped in __call.");
    }
    
    /**
     * Applies the action result to the controller.
     * 
     * @param mixed $actionResult The action result to set.
     * 
     * @return \Europa\Controller\ControllerAbstract
     */
    private function setActionResult($actionResult)
    {
        if (is_array($actionResult)) {
            $this->actionResult = $actionResult;
        }
        return $this;
    }
}
