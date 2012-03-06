<?php

namespace Europa\Controller;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;
use Europa\Request\RequestInterface;
use Europa\Response\ResponseInterface;
use Europa\View\ViewInterface;

/**
 * A default implementation of the controller interface.
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
     * @var \Europa\Request\RequestInterface
     */
    private $request;
    
    /**
     * The response used to set headers for output
     *
     * @var \Europa\Response\ResponseInterface
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
     * @var \Europa\View\ViewInterface
     */
    private $view;
    
    /**
     * The result returned from the action.
     * 
     * @var mixed
     */
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
     * @param \Europa\Request\RequestInterface   $request  The request to use.
     * @param \Europa\Response\ResponseInterface $response The response to use.
     *
     * @return \Europa\Controller\ControllerAbstract
     */
    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->init();
    }
    
    /**
     * Renders a view while invoking pre and post render hooks.
     * 
     * @return string
     */
    public function render()
    {
        // if a view is not rendered, it defaults to an empty string
        $view = '';
        
        // only pre-render if a view is set
        if ($this->view) {
            $this->preRender();
        }
        
        // the view could have been disabled in preRender so we should honor that
        if ($this->view) {
            $view = $this->view->render($this->actionResult);
            $this->postRender();
        }
        
        return $view;
    }
    
    /**
     * Returns the request being used.
     * 
     * @return \Europa\Request\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Returns the request being used.
     * 
     * @return \Europa\Response\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sets the view to use.
     * 
     * @param \Europa\View\ViewInterface $view The view to use.
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
     * @return \Europa\View\ViewInterface | null
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Switches filters on or off.
     * 
     * @param bool $switch Whether or not to enable filters.
     * 
     * @return \Europa\Controller\ControllerAbstract
     */
    public function useFilters($switch = true)
    {
        $this->useFilters = $switch ? true : false;
        return $this;
    }
    
    /**
     * Executes the controller's action. Both preAction and postAction hooks are invoked. If filtering is enabled and
     * any are applied to the action, they are applied before the preAction hook.
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
        $this->postAction();
        
        // chainable
        return $this;
    }
    
    /**
     * Applies the action result to the controller.
     * 
     * @param mixed $actionResult The action result to set.
     * 
     * @return \Europa\Controller\ControllerAbstract
     */
    public function setActionResult($actionResult)
    {
        // reset
        $this->actionResult = array();
        
        // do nothing if it doesn't have a value
        if (!$actionResult) {
            return;
        }
        
        // action result should be an arary
        if (!is_array($actionResult)) {
            throw new \LogicException('If a value is returned from your action, it must be an array.');
        }
        
        $this->actionResult = $actionResult;
    }
    
    /**
     * Returns the action result.
     * 
     * @return mixed
     */
    public function getActionResult()
    {
        return $this->actionResult;
    }
    
    /**
     * Forwards to the specified controller using the same request and response.
     * 
     * @param string $to The controller to forward to.
     * 
     * @return mixed
     */
    public function forward($to)
    {
        $to = new $to($this->getRequest(), $this->getResponse());
        return $to->action();
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
    
    /**
     * Pre-view-rendering hook.
     * 
     * @return void
     */
    public function preRender()
    {
        
    }
    
    /**
     * Post-view-rendering hook.
     * 
     * @return void
     */
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
     * @param array  $params The parameters to pass to the method.
     * 
     * @return \Europa\Controller\ControllerAbstract
     */
    private function executeMethod($method, array $params = array())
    {
        // attempt to call the action
        if (method_exists($this, $method)) {
            $reflector = new MethodReflector($this, $method);
            return $reflector->invokeNamedArgs($this, $params);
        }
        
        // attempt to catch with __call
        if (method_exists($this, '__call')) {
            return $this->__call($method, $params);
        }
        
        throw new \LogicException("Method {$method} is not supported and was not trapped in __call.");
    }
}
