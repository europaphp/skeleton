<?php

namespace Europa\Controller;
use LogicException;

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
 *   - patch
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
        $method = $this->request()->getMethod();
        
        // check for method, fallback to "all"
        if (!method_exists($this, $method)) {
            $method = self::ALL;
        }
        
        // check for "all", throw exception if not specified
        if (!method_exists($this, $method)) {
            throw new LogicException(sprintf(
                'If not using a specific request method in your RestController, you must specify a method named "%s".',
                self::ALL
            ));
        }
        
        return $method;
    }
}