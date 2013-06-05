<?php

namespace Europa\View\Exception;

class NoContainer extends \Europa\Exception\Exception
{
    public function __construct($name, $script)
    {
        parent::__construct(sprintf('Cannot get helper "%s" from view "%s" because no container was set.', $name, $script));
    }
}