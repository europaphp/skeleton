<?php

namespace Europa\Response;

/**
 * Command line response.
 *
 * @category Response
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Cli extends ResponseAbstract
{
    /**
     * Outputs the response body.
     * 
     * @return ResponseInterface
     */
    public function send()
    {
        echo $this->getBody();
        return $this;
    }
}