<?php

namespace Europa\Response;

class Cli extends ResponseAbstract implements CliInterface
{
    public function __construct()
    {
        $this->setStatus(self::OK);
    }

    public function __invoke()
    {
        echo $this->getBody();
        exit($this->getStatus());
    }
}