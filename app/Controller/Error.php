<?php

namespace Controller;
use Europa\Request\Uri;

/**
 * A controller that will handle all errors.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright(c) 2010 Trey Shugart http://europaphp.org/license
 */
class Error extends Base
{
    /**
     * Displays the error page.
     * 
     * @return void
     */
    public function all()
    {
        return array(
            'url' => Uri::detect()->getRequest(),
        );
    }
}