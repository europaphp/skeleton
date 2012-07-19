<?php

namespace Controller;
use Europa\Controller\RestController;
use Europa\Request\Uri;

/**
 * A controller that will handle all errors.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright(c) 2010 Trey Shugart http://europaphp.org/license
 */
class Error extends RestController
{
    /**
     * Displays the error page.
     * 
     * @return void
     */
    public function all()
    {
        $uri = Uri::detect();
        return array(
            'uri' => $uri->getRootPart() . $uri->getRequestPart() . $uri->getQueryPart(),
        );
    }
    
    /**
     * Generates command line error information.
     * 
     * @return void
     */
    public function cli()
    {
        
    }
}