<?php

namespace Europa\Di;
use Exception;
use LogicException;

/**
 * The application service locator and container.
 * 
 * @category DI
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  http://europaphp.org/license
 */
class Chain extends ContainerAbstract
{
    /**
     * The containers registered in the chain.
     * 
     * @var array
     */
    private $containers = [];

    /**
     * Creates a new instance specified by name.
     * 
     * @param string $name The service name.
     * 
     * @return mixed
     */
    public function __call($name)
    {
        foreach ($this->containers as $container) {
            if (isset($container->$name)) {
                return $container->$name;
            }
        }
        
        throw new LogicException(sprintf('Could not resolve dependency "%s" in any of the bound containers.', $name, get_class($this)));
    }

    /**
     * Adds a container to the chain.
     * 
     * @param ContainerInterface $container The container to add.
     * 
     * @return Chain
     */
    public function add(ContainerInterface $container)
    {
        $this->containers[] = $container;
        return $this;
    }
}