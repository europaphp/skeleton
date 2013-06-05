<?php

namespace Europa\View\Exception;

class UnspecifiedViewScript extends \Europa\Exception\Exception
{
    public function __construct()
    {
        parent::__construct('No view script was specified.');
    }
}