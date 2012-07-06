<?php

namespace Europa\Response;

/**
 * Basic response blueprint.
 *
 * @category Controller
 * @package  Europa
 * @author   Paul Carvosso-White <paulcarvossowhite@gmail.com>
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface ResponseInterface
{
    /**
     * Outputs the response body.
     * 
     * @return ResponseInterface
     */
    public function send();
}