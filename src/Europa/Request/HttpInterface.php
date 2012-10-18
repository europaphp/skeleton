<?php

namespace Europa\Request;

interface HttpInterface extends RequestInterface
{
    /**
     * Returns the request method for the request.
     *
     * @return string
     */
    public function getMethod();

    /**
     * Returns the value of a single request header or null if not found.
     * 
     * @param string $name The name of the request header to retrieve.
     * 
     * @return string
     */
    public function getHeader($name);

    /**
     * Returns the request URI.
     * 
     * @return Uri
     */
    public function getUri();

    /**
     * Returns the first matched type.
     * 
     * @param array $types    The types to check.
     * @param int   $maxIndex The maximum number of items to look at in order before returning false.
     * 
     * @return bool
     */
    public function accepts($type);
}