<?php

namespace Europa\View\Exception;

class CircularExtension extends \Europa\Exception\Exception
{
  public $message = 'View :child cannot extend itself.';
}