<?php

namespace Europa\Config\Adapter\From;
use Europa\Exception\Exception;

class Json
{
  private $errorMessages = [
    'No errors occurred.',
    'Maximum stack depth exceeded.',
    'Underflow or the modes mismatch.',
    'Unexpected control character found.',
    'Syntax error.',
    'Malformed UTF-8 characters, possibly incorrectly encoded.',
    'Unknown error.'
  ];

  public function __invoke($data)
  {
    $decoded = json_decode($data);

    if ($error = json_last_error()) {
      Exception::toss('The JSON string "%s" was unable to be parsed because: %s', $data, $this->errorMessages[$error]);
    }

    return $decoded;
  }
}