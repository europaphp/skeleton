<?php

namespace Europa;
use Europa\Controller\Exception as ControllerException;
use Europa\Request\Http;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\MethodReflector;
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
        $this->preRender();
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
    public function forward($to, array $params = array())
    {
        // modify the request and dispach it's return value
        $request = $this->getRequest();
        $request->setParams($params);
        $request->setController($to);
        return $request->dispatch();
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
        header('Location: ' . $this->getRequest()->format($to));
        exit;
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
        
        // first check to see if the specified method exists
        // if it doesn't, assign a catch-all and re-check
        if (!method_exists($this, $method)) {
            if (method_exists($this, static::CATCH_ALL)) {
                $method = static::CATCH_ALL;
            } else {
                throw new ControllerException(
                    'The request method "' . $method . '" is not supported by "' . get_class($this) . '". Additionally'
                    . ', a catch-all action "' . static::CATCH_ALL . '" was not specified.'
                );
            }
        }

        // apply custom filters before pre-action for security
        $this->applyFiltersTo($method);
        $this->preAction();

        // call the appropriate method using named parameters
        $reflector = new MethodReflector($this, $method);
        
        // methods can return parameters for the view
        $params = call_user_func_array(
            array($this, $method),
            $reflector->mergeNamedArgs($this->request->getParams())
        );
        
        // set view params if they were specified and we have a view
        if ($params && $this->view) {
            $this->view->setParams($params);
        }
        
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
     * @return void
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
    }
}