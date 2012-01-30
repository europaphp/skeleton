<?php

namespace Europa\Controller;

/**
 * Implements restful "single-action" controllers.
 * 
 * The following methods are supported with any number of user-defined parameters:
 *   - cli
 *   - connect
 *   - delete
 *   - get
 *   - head
 *   - options
 *   - post
 *   - put
 *   - trace
 *   - all, catches all requests
 * 
 * A CLI request is allowed to specify "cli" as it's method. Otherwise, only HTTP methods are allowed.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class RestController extends ControllerAbstract
{
    /**
     * The method that catches all requests.
     * 
     * @var string
     */
    const ALL = 'all';
    
    /**
     * Returns the method to action. By default this is the request method returned from the request instance that is
     * is being used.
     * 
     * @return string
     */
    public function getActionMethod()
    {
        $method = $this->getRequest()->getMethod();
        if (!method_exists($this, $method)) {
            $method = self::ALL;
        }
        return $method;
    }
}
