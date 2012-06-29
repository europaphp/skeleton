<?php

namespace Europa\Controller;
use Europa\Request\RequestInterface;

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
     * Constructs a new controller using the specified request.
     *
     * @param RequestInterface $request The request to use.
     *
     * @return ControllerAbstract
     */
    public function __construct(RequestInterface $request);
    
    /**
     * Performs actioning and returns the view context.
     * 
     * @return array
     */
    public function action();
}