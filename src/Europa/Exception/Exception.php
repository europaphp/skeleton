<?php

namespace Europa\Exception;

class Exception extends \Exception
{
    public function __construct()
    {
        $args = func_get_args();
        $message = array_shift($args);
        parent::__construct(vsprintf($message, $args), crc32(get_class($this)));
    }
}