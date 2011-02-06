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
     * Requires the loader.
     * 
     * @return void
     */
    public function requireLoader()
    {
        require $this->base . '/lib/Europa/Loader.php';
    }
    
    /**
     * Adds load paths to the loader for autoloading.
     * 
     * @return void
     */
    public function addLoadPaths()
    {
        \Europa\Loader::addPath($this->base . '/app/controllers');
        \Europa\Loader::addPath($this->base . '/app/views');
        \Europa\Loader::addPath($this->base . '/app/helpers');
        \Europa\Loader::addPath($this->base . '/app/filters');
        \Europa\Loader::addPath($this->base . '/app/forms');
    }
    
    /**
     * Registers autoloading.
     * 
     * @return void
     */
    public function registerAutoloading()
    {
        \Europa\Loader::registerAutoload();
    }
    
    /**
     * Sets up the service locator.
     * 
     * @return void
     */
    public function setUpServiceLocator()
    {
        $locator = \Europa\ServiceLocator::getInstance();
        $locator->map('request', '\Europa\Request\Http');
        $locator->map('router', '\Europa\Router\Request');
        $locator->map('layout', '\Europa\View\Php');
        $locator->map('view', '\Europa\View\Php');
        $locator->map('helper', '\Europa\ServiceLocator');
        
        // make sure the router gets a configured request
        $locator->setConfigFor('router', array($locator->get('request')));
        
        // helper formatter is set for every instance
        $locator->queueMethodFor('helper', 'setFormatter', array(function($service) {
            return \Europa\String::create($service)->toClass() . 'Helper';
        }));
        
        // when the view is constructed, it requires it's own service locator
        $locator->queueMethodFor('view', 'setServiceLocator', array($locator->create('helper')));
        
        // when the layout is constructed, it needs to know about its view and requires its own service locator
        $locator->queueMethodFor('layout', 'setChild', array('view', $locator->get('view')));
        $locator->queueMethodFor('layout', 'setServiceLocator', array($locator->create('helper')));
    }
}