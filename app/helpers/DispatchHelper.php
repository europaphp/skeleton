<?php

/**
 * Creates and dispatches a request given the specified parameters.
 * 
 * @category ViewHelpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class DispatchHelper
{
    /**
     * The request object that is used for this dispatch call.
     * 
     * @var \Europa\Request
     */
    protected $request;

    /**
     * Constructs a dispatch helper and passes in the required parameters. The request can be auto-detected
     * if not specified, or overridden if specified.
     * 
     * @param \Europa\View    $view    The view that called the helper.
     * @param array           $params  An array of parameters to pass off to the new request.
     * @param \Europa\Request $request A request to override a default request with.
     * 
     * @return DispatchHelper
     */
    public function __construct(uropa\View $view, $controller, array $params = array(), Europa\Request $request = null)
    {
        // auto-detection of request or overriding of request detection
        if ($request) {
            $this->request = $request;
        } else {
            $this->request = Europa\Request::isCli() ? new \Europa\Requst\Cli : new \Europa\Requst\Http;
        }
        
        $this->request->setController($controller);
        $this->request->setParams($view->getParams());
        $this->request->setParams($params);
    }

    /**
     * Returns the dispatch result as a string.
     * 
     * @return string
     */
    public function __toString()
    {
        // attempt to dispatch and return the result
        // catch any exceptions and trigger them accordingly
        try {
            return (string) $this->request->dispatch();
        } catch (uropaxception $e) {
            $e->trigger();
        } catch (Exception $e) {
            $e = new Europaxception($e->getMessage(), $e->getCode());
            $e->trigger();
        }
    }
}