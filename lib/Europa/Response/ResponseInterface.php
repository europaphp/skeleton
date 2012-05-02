<?php

namespace Europa\Response;

/**
 * Counterpart to request object, outputs headers and contents
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
     * Outputs the specified string.
     * 
     * @param string $content The content to output.
     * 
     * @return string
     */
    public function output($content);
}
