<?php

namespace Europa\View\Exception;

class CannotGetHelper extends \Europa\Exception\Exception
{
  public $message = 'Cannot get helper ":name" from view ":view" because: :message.';
}