<?php

namespace Europa\View\Exception;

class CircularExtension extends \Europa\Exception\Exception
{
    public function __construct($script)
    {
        parent::__construct('Child view cannot extend itself.');
    }
}