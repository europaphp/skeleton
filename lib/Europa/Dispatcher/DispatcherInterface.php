<?php

namespace Europa\Dispatcher;
use Europa\Request\RequestInterface;
use Europa\Response\ResponseInterface;
use Europa\Router\RouterInterface;

/**
 * Represents a basic implementation of a dispatcher.
 *
 * @category Dispatcher
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface DispatcherInterface
{
    /**
     * Renders, sends all headers and outputs the result.
     * 
     * @param RequestInterface  $request  The request object to dispatch.
     * @param ResponseInterface $response The response object to output.
     * 
     * @return void
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response);
}
