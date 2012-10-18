<?php

namespace Europa\Request;

interface CliInterface extends RequestInterface
{
    public function getCommand();
}