<?php

namespace Europa;
use Europa\Controller\Exception as ControllerException;
use Europa\Request\Http;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;
use Europa\Uri;
use Europa\View;

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
abstract class Controller
{
    /**
     * The default method to call if one matching the request method is not defined.
     * 
     * @var string
     */
    const CATCH_ALL = 'all';
    
    /**
     * The request used to dispatch to this controller.
     * 
     * @var \Europa\Request
     */
    private $request;
    
    /**
     * The view rendering the page.
     * 
     * @var \Europa\View
     */
    private $view;

    /**
     * Whether or not to apply filters to action.
     * 
     * @var bool
     */
    private $useFilters = false;
    
    /**
     * Constructs a new controller using the specified request.
     * 
     * @param \Europa\Request $request The request to use.
     * 
     * @return \Europa\Controller
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
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
        $view = $this->view ? $this->view->render() : '';
        $this->postRender();
        return $view;
    }
    
    /**
     * Returns the request being used.
     * 
     * @return \Europa\Request
     */
    public function getRequest()
    {
        return $this->request;
    }
        
    /**
     * Sets the view to use. If a view is currently set, it's parameters
     * are copied to the new view.
     * 
     * @param \Europa\View $view The view to use.
     * 
     * @return \Europa\Controller
     */
    public function setView(View $view = null)
    {
        if ($this->view) {
            $view->setParams($this->view->getParams());
        }
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
     * Forwards the request to the specified controller.
     * 
     * @param string $to The controller to forward the request to.
     * 
     * @return \Europa\Controller
     */
    public function forward($method, $controller = null, array $params = array())
    {
        $request = $this->getRequest()->setMethod($method);
        if ($controller) {
            $request->setController($controller);
        }
        return $request->setParams($params)->dispatch();
    }
    
    /**
     * Redirects the current request to the specified url.
     * 
     * @param string $to The url to redirect to.
     * 
     * @return void
     */
    public function redirect($to = null)
    {
        $to = new Uri($to);
        $to->redirect();
    }

    /**
     * Switches filters on or off.
     * 
     * @param bool $switch True of false for filter application.
     * 
     * @return \Europa\Controller
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
     * 
     * @throws \Europa\Controller\Exception
     */
    public function action()
    {
        $method = $this->request->getMethod();
        $this->applyFiltersTo($method);
        $this->preAction();
        $this->applyParamsToView($this->executeMethod($method, $this->request->getParams()));
        $this->postAction();
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
     * Initialization hook.
     * 
     * @return void
     */
    public function preAction()
    {
        
    }
    
    /**
     * Initialization hook.
     * 
     * @return void
     */
    public function postAction()
    {
        
    }
    
    /**
     * Initialization hook.
     * 
     * @return void
     */
    public function preRender()
    {
        
    }
    
    /**
     * Initialization hook.
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
     * @return \Europa\Controller
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
     * @return \Europa\Controller
     */
    private function executeMethod($method, array $params = array())
    {
        // make sure the method exists or a catch-all is defined
        if (!method_exists($this, $method)) {
            if (!method_exists($this, static::CATCH_ALL)) {
                throw new ControllerException(
                    'The request method "' . $method . '" is not supported by "' . get_class($this) . '". Additionally'
                    . ', a catch-all action "' . static::CATCH_ALL . '" was not specified.'
                );
            }
            $method = static::CATCH_ALL;
        }
        $reflector = new MethodReflector($this, $method);
        return $reflector->invokeNamedArgs($this, $params);
    }
    
    /**
     * Applies the passed parameters to the view.
     * 
     * @param mixed $params The parameters to apply to the view, if any.
     * 
     * @return \Europa\Controller
     */
    private function applyParamsToView($params)
    {
        if (is_array($params) && $this->view) {
            $this->view->setParams($params);
        }
        return $this;
    }
}