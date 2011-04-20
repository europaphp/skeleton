<?php

// autoloading isn't enabled yet, so required the bootstrapper
require_once dirname(__FILE__) . '/../lib/Europa/Bootstrapper.php';

use Europa\Bootstrapper as ParentBootstrapper;
use Europa\Loader;
use Europa\Route\Regex;
use Europa\ServiceLocator;
use Europa\String;

/**
 * Bootstraps the sample application.
 * 
 * @category Bootstrapping
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Bootstrapper extends ParentBootstrapper
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
        $this->base = realpath(dirname(__FILE__) . '/../');
    }
    
    /**
     * Adds load paths to the loader for autoloading.
     * 
     * @return void
     */
    public function setUpLoader()
    {
        require $this->base . '/lib/Europa/Loader.php';
        Loader::addPath($this->base . '/app');
        Loader::register();
    }
    
    /**
     * Sets up the default service locator instance and applies class mapping for
     * the services that will be used in the application.
     * 
     * @return void
     */
    public function configureServiceLocator()
    {
        $this->locator = ServiceLocator::getInstance();
        $this->locator->map('request', '\Europa\Request\Http');
        $this->locator->map('router', '\Europa\Router');
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
        $this->locator->queueMethodFor('router', 'setRoute', array(
            'default',
            new Regex('(index\.php)?/?(?<controller>.+)?', null, array('controller' => 'index'))
        ));
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
            return '\Helper' . String::create($service)->toClass();
        }));
    }
    
    /**
     * Configures the main view instance. Uses the pre-configured helper.
     * 
     * @return void
     */
    public function configureView()
    {
        $this->locator->queueMethodFor('view', 'setPath', array($this->base . '/app/View'));
        $this->locator->queueMethodFor('view', 'setHelperLocator', array($this->locator->get('helper')));
    }
    
    /**
     * Configures the main layout instance. Uses both the pre-configured view and helper.
     * 
     * @return void
     */
    public function configureLayout()
    {
        $this->locator->queueMethodFor('layout', 'setPath', array($this->base . '/app/View'));
        $this->locator->queueMethodFor('layout', '__set', array('view', $this->locator->get('view')));
        $this->locator->queueMethodFor('layout', 'setHelperLocator', array($this->locator->get('helper')));
    }
}