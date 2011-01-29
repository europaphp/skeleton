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
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
namespace Europa
{
    abstract class Controller
    {
        /**
         * The request used to dispatch to this controller.
         * 
         * @var \Europa\Request
         */
        private $_request;
        
        /**
         * The view rendering the page.
         * 
         * @var \Europa\View
         */
        private $_view;
        
        /**
         * Constructs a new controller using the specified request.
         * 
         * @param \Europa\Request $request The request to use.
         * 
         * @return \Europa\Controller
         */
        public function __construct(Request $request)
        {
            $this->_request = $request;
            
            $method = new Reflection\Method($this, 'init');
            call_user_func_array(
                array($this, 'init'),
                $method->mergeNamedArgs($request->getParams())
            );
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
         * @return \Europa\Request
         */
        public function getRequest()
        {
            return $this->_request;
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
            if ($this->_view) {
                $view->setParams($this->_view->getParams());
            }
            $this->_view = $view;
            return $this;
        }
        
        /**
         * Returns the view being used.
         * 
         * @return \Europa\View
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
         * @return \Europa\Controller
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
            // we call the approprate method for the request type
            $method = $this->getRequest()->method();
            
            // check to make sure it exists and if not, throw an exception
            if (!method_exists($this, $method)) {
                throw new Controller\Exception('The method "' . get_class($this) . '->' . $method . '()" is not supported.');
            }
            
            // pre-actioning
            $this->preAction();
            
            // call appropriate method with it's defined parameters
            $method = new Reflection\Method($this, $method);
            call_user_func_array(
                array($this, $method->getName()),
                $method->mergeNamedArgs($this->getRequest()->getParams())
            );
            
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
    }
}