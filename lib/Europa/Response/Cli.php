<?php

namespace Europa\Response;
use Europa\View\ViewInterface;

/**
 * Counterpart to request object, outputs headers and contents
 *
 * @category Controller
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Cli implements ResponseInterface
{
    /**
     * Outputs the specified view.
     * 
     * @param ViewInterface $view    The view to output.
     * @param array         $context The context to render the view with.
     * 
     * @return void
     */
    public function output(ViewInterface $view = null, array $context = [])
    {
        if ($view) {
            echo $view->render($context);
        }
    }
}