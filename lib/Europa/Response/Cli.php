<?php

namespace Europa\Response;

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
