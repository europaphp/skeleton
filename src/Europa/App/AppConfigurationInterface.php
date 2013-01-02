<?php

namespace Europa\App;
use Europa\Module\ManagerConfigurationInterface;

interface AppConfigurationInterface extends ManagerConfigurationInterface
{
    public function config($defaults, $config);

    public function event();

    public function loader();

    public function modules();

    public function request();

    public function response();

    public function view();

    public function viewHelperConfiguration();

    public function viewHelpers();

    public function viewNegotiator();

    public function viewScript();
}