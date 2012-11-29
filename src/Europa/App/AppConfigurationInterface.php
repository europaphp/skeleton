<?php

namespace Europa\App;
use Europa\Module\ManagerConfigurationInterface;

interface AppConfigurationInterface extends ManagerConfigurationInterface
{
    /**
     * Returns the loader responsible for auto-loading class files.
     * 
     * @return Europa\Fs\Loader
     */
    public function loader();

    /**
     * Returns the module manager.
     * 
     * @return Europa\Module\Manager
     */
    public function modules();

    /**
     * Returns the request.
     * 
     * @return Europa\Request\RequestInterface
     */
    public function request();

    /**
     * Returns the response.
     * 
     * @return Europa\Response\ResponseInterface
     */
    public function response();

    /**
     * Returns the view responsible for rendering the response.
     * 
     * @return Europa\View\ViewInterface
     */
    public function view();

    /**
     * Returns the helper configuration.
     * 
     * @return Europa\View\HelperConfiguration
     */
    public function viewHelperConfiguration();

    /**
     * Returns a container responsible for handling view helpers.
     * 
     * @return Europa\Di\ServiceContainer
     */
    public function viewHelpers();

    /**
     * Returns the view negotiator responsible for determining what type of view to render.
     * 
     * @return Europa\View\Negotiator
     */
    public function viewNegotiator();

    /**
     * Formats the view script so it can be set on a `ViewScriptInterface` object.
     * 
     * @transient
     * 
     * @return string
     */
    public function viewScript();
}