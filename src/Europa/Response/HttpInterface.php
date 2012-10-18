<?php

namespace Europa\Response;

interface HttpInterface extends ResponseInterface
{
    /**
     * Sets a header value
     * 
     * @param string $name  The header type (in camel cased format, will be converted in output)
     * @param string $value The header value
     * 
     * @return Http
     */
    public function setHeader($name, $value);

    /**
     * Sets the HTTP status code.
     * 
     * @param int $status The status to set.
     * 
     * @return Http
     */
    public function setStatus($status);
}