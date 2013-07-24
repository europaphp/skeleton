<?php

namespace Europa\Module\Exception;

class InvalidPath extends \Europa\Exception\Exception
{
  public $message = 'The module :name specified and invalid path: :path.';
}