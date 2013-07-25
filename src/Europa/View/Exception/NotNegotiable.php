<?php

namespace Europa\View\Exception;

class NotNegotiable extends \Europa\Exception\Exception
{
  public $message = 'Unable to negotiate a renderer for :request.';
}