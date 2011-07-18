<?php

namespace Europa\Response;

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