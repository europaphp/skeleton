<?php

namespace Europa\Router\Adapter;
use Europa\Exception\Exception;

class Json
{
    private $file;

    public function __construct($file)
    {
        if (!is_file($this->file = $file)) {
            Exception::toss('The JSON route file "%s" does not exist.', $file);
        }
    }

    public function __invoke()
    {
        return json_decode(file_get_contents($this->file));
    }
}