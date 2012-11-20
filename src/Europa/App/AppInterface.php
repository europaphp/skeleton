<?php

namespace Europa\App;
use Europa\Di\ServiceContainerInterface;

/**
 * Blueprint for application runner.
 * 
 * @category App
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  http://europaphp.org/license
 */
interface AppInterface
{
    /**
     * Runs the application.
     * 
     * @return App
     */
    public function __invoke();

    /**
     * Sets the service container to use.
     * 
     * @param ServiceContainerInterface $container The service container.
     * 
     * @return App
     */
    public function setServiceContainer(ServiceContainerInterface $container);

    /**
     * Returns the service container.
     * 
     * @return ServiceContainerInterface
     */
    public function getServiceContainer();
}