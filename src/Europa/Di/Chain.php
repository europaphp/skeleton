<?php

namespace Europa\Di;
use Exception;

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
    
    /**
     * Creates a new instance specified by name.
     * 
     * @param string $name The service name.
     * @param array  $args The arguments to pass, if any.
     * 
     * @return mixed
     */
    public function create($name, array $args = [])
    {
        foreach ($this->containers as $container) {
            try {
                return $container->__call($name, $args);
            } catch (Exception $e) {
                
            }
        }
        $this->throwNotExists($name);
    }
}