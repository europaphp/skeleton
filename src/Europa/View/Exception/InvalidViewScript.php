<?php

namespace Europa\View\Exception;

class InvalidViewScript extends \Europa\Exception\Exception
{
    public function __construct($script)
    {
        parent::__construct(sprintf('The script "%s" does not exist.', $script));
    }
}