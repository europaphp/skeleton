<?php

namespace Europa\Router;
use ArrayAccess;
use Countable;
use Europa\Request\RequestInterface;
use IteratorAggregate;

interface RouterInterface extends ArrayAccess, Countable, IteratorAggregate
{
    public function __invoke(RequestInterface $request);
}