<?php

namespace Europa\Controller;

/**
 * Implements restful "single-action" controllers.
 * 
 * The following methods are supported with any number of user-defined parameters:
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
     * Returns the method to action. By default this is the request method returned from the request instance that is
     * is being used.
     * 
     * @return string
     */
    public function getActionMethod()
    {
        return $this->getRequest()->getMethod();
    }
}
