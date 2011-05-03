<?php

namespace Helper;
use Europa\Request;
use Europa\Request\Cli;
use Europa\Request\Http;
use Europa\View;

/**
 * Creates and dispatches a request given the specified parameters.
 * 
 * @category ViewHelpers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Dispatch
{
    /**
     * The string result of the dispatch call.
     * 
     * @var string
     */
    private $result;

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
    public function __construct(View $view, $controller, array $params = array())
    {
        $request = Request::isCli() ? new Cli : new Http;
        $request->setParams($params);
        
        $controller = new $controller($request);
        $controller->action();
        
        $this->result = $controller->render();
    }

    /**
     * Returns the dispatch result as a string.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->result;
    }
}