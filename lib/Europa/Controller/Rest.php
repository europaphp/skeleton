<?php

namespace Europa\Controller;

/**
 * The base controller for all controller classes.
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
 * Additionally, if an above request method is not found, the controller will look for a method called "all" to catch
 * all request that are made to the controller.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class Rest extends ControllerAbstract
{
    /**
     * Returns the method to action.
     * 
     * @return string
     */
    public function getActionMethod()
    {
        return $this->getRequest()->getMethod();
    }
}
