<?php

namespace Europa\App;

/**
 * Handles the management of a single module.
 * 
 * @category App
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface ModuleInterface
{
    /**
     * Returns the names of all dependent modules.
     * 
     * @return array
     */
    public function getRequired();

    /**
     * Returns the name of the module.
     * 
     * @return string
     */
    public function getName();

    /**
     * Returns the module configuration. Can be a path to a configuration file or array.
     * 
     * @return mixed
     */
    public function getConfig();

    /**
     * Returns the module routes. Can be a path to the routes file or array.
     * 
     * @return mixed
     */
    public function getRoutes();

    /**
     * Returns the autoloadable class paths. Can be anything that is traversable.
     * 
     * @return mixed
     */
    public function getClassPaths();

    /**
     * Returns the base view paths. Can be anything that is traversable.
     * 
     * @return mixed
     */
    public function getViewPaths();

    /**
     * Returns the bootstrapper that should boot the module. This can be anything that is callable.
     * 
     * @return callable
     */
    public function getBootstrapper();
}