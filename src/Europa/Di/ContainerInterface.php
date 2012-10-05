<?php

namespace Europa\Di;

/**
 * The application service locator and container.
 * 
 * @category DI
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  http://europaphp.org/license
 */
interface ContainerInterface
{
    /**
     * Locates the specified service. Can be transient.
     * 
     * @param string $name The service name.
     * 
     * @return mixed
     */
    public function __get($name);

    /**
     * Returns whether or not the specified service is available.
     * 
     * @param string $name The service name.
     * 
     * @return bool
     */
    public function __isset($name);
}