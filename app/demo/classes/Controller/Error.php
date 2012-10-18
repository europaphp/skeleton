<?php

namespace Controller;
use Europa\Controller\AbstractController;
use Europa\Request\Uri;

/**
 * A controller that will handle all errors.
 * 
 * @category Controllers
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright(c) 2010 Trey Shugart http://europaphp.org/license
 */
class Error extends AbstractController
{
    /**
     * Shows an error via CLI.
     * 
     * @return array
     */
    public function cli()
    {
        return [
            'command' => $this->request()->controller
        ];
    }

    /**
     * Displays the error page.
     * 
     * @return array
     */
    public function get()
    {
        $uri = Uri::detect();

        return [
            'uri' => $uri->getRootPart() . $uri->getRequestPart() . $uri->getQueryPart(),
        ];
    }
}