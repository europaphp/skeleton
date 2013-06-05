<?php

namespace Europa\Di;
use Europa\Iterator\CallableIterator;
use Europa\Exception\Exception;
use Traversable;

class ContainerArray
{
    private $containers;

    public function __construct(Traversable $containers)
    {
        $this->containers = new CallableIterator($containers);
    }

    public function __invoke($name)
    {
        foreach ($this->containers as $container) {
            try {
                return $container($name);
            } catch(Exception $e) {

            }
        }

        throw new Exception\UnregisteredService($name, $this);
    }
}