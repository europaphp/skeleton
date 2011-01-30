<?php

namespace Europa;

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
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
abstract class Controller
{
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
        if ($this->view) {
            $view = $this->view->__toString();
            $this->postRender();
        } else {
            $view = '';
        }
        
        // return output
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
    public function redirect($to)
    {
        header('Location: ' . Request\Http::format($to));
        exit;
    }
    
    /**
     * Makes sure the appropriate parameters are passed to init and the request
     * method action.
     * 
     * @return void
     * 
     * @throws \Europa\Controller\Exception
     */
    public function action()
    {
        // we call the approprate method for the specified request method
        $request = $this->getRequest();
        $method  = $request->method();
        
        // check to make sure it exists and if not, throw an exception
        if (!method_exists($this, $method)) {
            throw new Controller\Exception('The method "{get_class($this)}::{$method}()" is not supported.');
        }

        // for manipulating the specified controller method
        $methodReflector = new Reflection\MethodReflector($this, $method);
        $classReflector  = new Reflection\ClassReflector($this);

        // use named parameters from the request for the controller method
        $params = $methodReflector->mergeNamedArgs($request->getParams());

        // apply pre-action filters
        $this->applyFilters(
            array_merge(
                array(),
                $classReflector->getDocBlock()->getTag('preFilter', true),
                $methodReflector->getDocBlock()->getTag('preFilter', true)
            ),
            $method,
            $params
        );
        
        // pre-action after applying filters
        $this->preAction();

        // call the appropriate method
        $viewParams = call_user_func_array(
            array($this, $method),
            $params
        );

        // set view params if they were specified
        if ($viewParams && $this->view) {
            $this->view->setParams($viewParams);
        }
        
        // post-action before applying filters
        $this->postAction();

        // apply post-action filters
        $this->applyFilters(
            array_merge(
                array(),
                $classReflector->getDocBlock()->getTag('postFilter', true),
                $methodReflector->getDocBlock()->getTag('postFilter', true)
            ),
            $method,
            $params
        );
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

    private function applyFilters(array $filters, &$method, array &$params = array())
    {
         // execute each behavior in the order it was defined
        foreach ($filters as $filter) {
            $class = $filter->getClass();
            $class = new $class($this, $method, $params);

            // make sure we can call the appropriate methods
            if (!$class instanceof \Europa\Controller\FilterInterface) {
                throw new Exception('The filter "{get_class($class)}" must derive from "\\Europa\\Controller\\FilterInterface".');
            }

            // general filter method
            $class->filter();

            // method for changing the method to call
            $method = $class->method();

            // method to filter any parameters
            $params = $class->params();
        }
    }
}