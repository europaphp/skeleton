<?php

namespace Europa\Response;

class Cli extends ResponseAbstract
{
    public function send()
    {
        echo $this->getBody();
        return $this;
    }
}