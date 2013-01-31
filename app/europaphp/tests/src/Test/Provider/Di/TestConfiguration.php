<?php

namespace Test\Provider\Di;
use Europa\Di\ConfigurationAbstract;

class TestConfiguration extends ConfigurationAbstract
{
    public function test()
    {
        return new TestService;
    }
}