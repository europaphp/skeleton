<?php

namespace Europa\Response;

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
     * Outputs the specified string.
     * 
     * @param string $content The content to output.
     * 
     * @return string
     */
    public function output($content)
    {
        echo $content;
    }
}
