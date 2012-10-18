<?php

namespace Europa\View;
use Europa\Di\ConfigurationAbstract;
use Europa\Di\ServiceContainerInterface;

class HelperConfiguration extends ConfigurationAbstract
{
    public function __construct(ServiceContainerInterface $container)
    {
        $this->container = $container;
    }

    public function capture()
    {
        return new Helper\Capture;
    }

    public function cli()
    {
        return new Helper\Cli;
    }

    public function css()
    {
        return new Helper\Css;
    }

    public function js()
    {
        return new Helper\Js;
    }

    public function json()
    {
        return new Helper\Json;
    }

    public function uri()
    {
        return new Helper\Uri($this->container->router);
    }
}