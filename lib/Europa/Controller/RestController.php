<?php

namespace Europa\Controller;
use Europa\Request\Cli;
use Europa\Request\Http;
use Europa\Request\RequestInterface;
use UnexpectedValueException;

/**
 * Implements restful "single-action" controllers.
 * 
 * The following methods are supported with any number of user-defined parameters:
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
     * The CLI action name if a CLI request instance is passed in.
     * 
     * @var string
     */
    const CLI = 'cli';
    
    /**
     * Returns the method to action. By default this is the request method returned from the request instance that is
     * is being used.
     * 
     * @return string
     */
    public function getActionMethod()
    {
        $request = $this->getRequest();
        
        if ($request instanceof Http) {
            $method = $request->getMethod();
        } else if ($request instanceof Cli) {
            $method = self::CLI;
        } else {
            throw new UnexpectedValueException('An unsupported request "' . get_class($request) . '" was specified.');
        }
        
        if (!method_exists($this, $method) && method_exists($this, self::ALL)) {
            $method = self::ALL;
        }
        
        return $method;
    }
}
