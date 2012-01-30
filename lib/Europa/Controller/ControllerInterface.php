<?php

namespace Europa\Controller;
use Europa\Request\RequestInterface;
use Europa\Response\ResponseInterface;

/**
 * Defines a basic implementation of controllers in Europa.
 *
 * @category Controller
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface ControllerInterface
{
    /**
     * Constructs a new controller using the specified request and response.
     *
     * @param \Europa\Request\RequestInterface   $request  The request to use.
     * @param \Europa\Response\ResponseInterface $response The response to use.
     *
     * @return \Europa\Controller\ControllerAbstract
     */
    public function __construct(RequestInterface $request, ResponseInterface $response);
    
    /**
     * Performs actioning and returns the view context.
     * 
     * @return array
     */
    public function action();
    
    /**
     * Renders the view and returns the result.
     * 
     * @return string
     */
    public function render();
}
