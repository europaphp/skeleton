<?php

namespace Europa\View\Exception;

class UnspecifiedViewScript extends \Europa\Exception\Exception
{
  public $message = 'No view script was specified.';
}