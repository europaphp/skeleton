<?php

namespace Europa\View\Exception;

class CannotGetHelper extends \Europa\Exception\Exception
{
    public function __construct($name, $script, $message)
    {
        parent::__construct(sprintf('Cannot get helper "%s" from view "%s" because: %s.', $name, $script, $message));
    }
}