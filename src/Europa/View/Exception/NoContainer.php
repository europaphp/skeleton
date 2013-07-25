<?php

namespace Europa\View\Exception;

class NoContainer extends \Europa\Exception\Exception
{
  public $message = 'Cannot get helper :name in :view because no container was set.';
}