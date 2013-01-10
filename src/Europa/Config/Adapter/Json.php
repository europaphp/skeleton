<?php

namespace Europa\Config\Adapter;
use Europa\Exception\Exception;

class Json
{
    private $file;

    private $errorMessages = [
        'No errors occurred.',
        'Maximum stack depth exceeded.',
        'Underflow or the modes mismatch.',
        'Unexpected control character found.',
        'Syntax error.',
        'Malformed UTF-8 characters, possibly incorrectly encoded.',
        'Unknown error.'
    ];

    public function __construct($file)
    {
        if (!is_file($this->file = $file)) {
            Exception::toss('The JSON config file "%s" does not exist.', $file);
        }
    }

    public function __invoke()
    {
        $decoded = json_decode(file_get_contents($this->file));

        if ($error = json_last_error()) {
            Exception::toss('The JSON file "%s" was unable to be parsed with message: ' . $this->errorMessages[$error], $this->file);
        }

        return $decoded;
    }
}