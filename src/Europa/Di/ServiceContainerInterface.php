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
interface ServiceContainerInterface
{
    /**
     * Returns an instance of the specified service.
     * 
     * @param string $name The name of the service to return.
     * 
     * @return mixed
     */
    public function __invoke($name);

    /**
     * Since you can't infer a property and call it at the same time, you have to proxy it via __call().
     * 
     * @param string $name The service name.
     * @param array  $args The service arguments. These are ignored.
     * 
     * @return mixed
     */
    public function __call($name, array $args = []);

    /**
     * Registers the specified service.
     * 
     * @param string $name  The service name.
     * @param mixed  $value The service.
     * 
     * @return void
     */
    public function __set($name, $value);

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

    /**
     * Unregisters the service.
     * 
     * @param string $name The service name.
     * 
     * @return void
     */
    public function __unset($name);

    /**
     * Marks the service as transient.
     * 
     * @return ServiceContainerInterface
     */
    public function transient($name);

    /**
     * Returns whether or not the specified service is transient.
     * 
     * @return bool
     */
    public function isTransient($name);
}