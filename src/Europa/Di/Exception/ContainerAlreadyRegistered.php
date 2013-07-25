<?php

namespace Europa\Di\Exception;

class ContainerAlreadyRegistered extends \Europa\Exception\Exception
{
  public $message = 'The container ":name" cannot overwrite an existing container that has been saved with the same name.';
}