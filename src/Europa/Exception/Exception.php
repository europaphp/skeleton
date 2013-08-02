<?php

namespace Europa\Exception;

class Exception extends \Exception
{
  public function __construct(array $params = [])
  {
  parent::__construct($this->generateMessage($params), $this->generateCode());
  }

  private function generateMessage(array $params)
  {
  $message = $this->message;

  foreach ($params as $name => $value) {
    $message = str_replace(':' . $name, $value, $message);
  }

  return $message;
  }

  private function generateCode()
  {
  return crc32(get_class($this));
  }
}