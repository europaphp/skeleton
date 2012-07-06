<?php

namespace Europa\Response;

/**
 * The abstract response.
 *
 * @category Response
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
abstract class ResponseAbstract implements ResponseInterface
{
    /**
     * The response body.
     * 
     * @var string
     */
    private $body;
    
    /**
     * Sets the response body.
     * 
     * @param string $body The body to send.
     * 
     * @return ResponseAbstract
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }
    
    /**
     * Returns the response body.
     * 
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}