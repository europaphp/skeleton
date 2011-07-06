<?php

namespace Europa;

/**
 * Dispatches the request to the controller, takes the rendered content and passes to response to output
 *
 * @category Controller
 * @package  Europa
 * @author   Paul Carvosso-White <paulcarvossowhite@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Dispatcher
{
    /**
     * Dispatches the request and passes to the response to output
     * 
     * @param Europa\Request  $request  The request object to dispatch
     * @param Europa\Response $response The response object to output
     * 
     * @return void
     */
    public function dispatch(Request $request, Response $response)
    {
        $controller = $request->formatController();
        if (!Loader::load($controller)) {
            $strErr = 'Could not load controller ' . $controller . '.';
            throw new Exception($strErr, \Europa\Request\Exception::CONTROLLER_NOT_FOUND);
        }

        $controller = new $controller($request, $response);
        if (!$controller instanceof Controller) {
            throw new Exception(
                'Class ' . get_class($controller)  . ' is not a valid controller instance. Controller classes must '
                . 'derive from \Europa\Controller.'
            );
        }
                
        //controller does it's magic
        $controller->action();
        
        //have the response output the rendered response from the controller
        $response->output($controller->render());          
    }   
    
}