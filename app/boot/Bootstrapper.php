<?php

// autoloading isn't enabled yet, so required the bootstrapper
require_once dirname(__FILE__) . '/../../lib/Europa/Bootstrapper.php';

/**
 * Bootstraps the sample application.
 * 
 * @category Bootstrapping
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Bootstrapper extends Europa\Bootstrapper
{
    /**
     * The application base path.
     * 
     * @var string
     */
    private $base;
    
    /**
     * The main service locator instance.
     * 
     * @var \Europa\ServiceLocator
     */
    private $locator;
    
    /**
     * Sets up the session.
     * 
     * @return void
     */
    public function startSession()
    {
        session_start();
    }
    
    /**
     * Sets the base path.
     * 
     * @return void
     */
    public function setBasePath()
    {
        $this->base = realpath(dirname(__FILE__) . '/../../');
    }
    
    /**
     * Adds load paths to the loader for autoloading.
     * 
     * @return void
     */
    public function setUpLoader()
    {
        require $this->base . '/lib/Europa/Loader.php';
        $loader = new \Europa\Loader;
        $loader->addPath($this->base . '/app/controllers');
        $loader->addPath($this->base . '/app/views');
        $loader->addPath($this->base . '/app/helpers');
        $loader->addPath($this->base . '/app/filters');
        $loader->addPath($this->base . '/app/forms');
        $loader->register();
    }
    
    /**
     * Sets up the default service locator instance and applies class mapping for
     * the services that will be used in the application.
     * 
     * @return void
     */
    public function configureServiceLocator()
    {
        $this->locator = \Europa\ServiceLocator::getInstance();
        $this->locator->map('request', '\Europa\Request\Http');
        $this->locator->map('router', '\Europa\Router\Request');
        $this->locator->map('layout', '\Europa\View\Php');
        $this->locator->map('view', '\Europa\View\Php');
        $this->locator->map('helper', '\Europa\ServiceLocator');
    }
    
    /**
     * Configures the router using the service locator.
     * 
     * @return void
     */
    public function configureRouter()
    {
        $this->locator->setConfigFor('router', array($this->locator->get('request')));
    }
    
    /**
     * Configures how view helpers behave using the service locator. View helper behavior
     * derives from a configured \Europa\ServiceLocator that is injected into a view instance.
     * 
     * @return void
     */
    public function configureViewHelpers()
    {
        $this->locator->queueMethodFor('helper', 'setFormatter', array(function($service) {
            return \Europa\String::create($service)->toClass() . 'Helper';
        }));
    }
    
    /**
     * Configures the main view instance. Uses the pre-configured helper.
     * 
     * @return void
     */
    public function configureView()
    {
        $this->locator->queueMethodFor('view', 'setServiceLocator', array($this->locator->get('helper')));
    }
    
    /**
     * Configures the main layout instance. Uses both the pre-configured view and helper.
     * 
     * @return void
     */
    public function configureLayout()
    {
        $this->locator->queueMethodFor('layout', 'setChild', array('view', $this->locator->get('view')));
        $this->locator->queueMethodFor('layout', 'setServiceLocator', array($this->locator->get('helper')));
    }
}