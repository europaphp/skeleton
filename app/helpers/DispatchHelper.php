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
     * @var Europa_Request
     */
    protected $request;

    /**
     * Constructs a dispatch helper and passes in the required parameters. The request can be auto-detected
     * if not specified, or overridden if specified.
     * 
     * @param Europa_View    $view    The view that called the helper.
     * @param array          $params  An array of parameters to pass off to the new request.
     * @param Europa_Request $request A request to override a default request with.
     * 
     * @return DispatchHelper
     */
    public function __construct(Europa_View $view, $controller, array $params = array(), Europa_Request $request = null)
    {
        // auto-detection of request or overriding of request detection
        if ($request) {
            $this->request = $request;
        } else {
            $this->request = Europa_Request::isCli() ? new Europa_Request_Cli : new Europa_Request_Http;
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
        } catch (Europa_Exception $e) {
            $e->trigger();
        } catch (Exception $e) {
            $e = new Europa_Exception($e->getMessage(), $e->getCode());
            $e->trigger();
        }
    }
}