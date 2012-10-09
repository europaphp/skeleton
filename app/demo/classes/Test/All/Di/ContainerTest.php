<?php

namespace Test\All\Di;
use ArrayObject;
use Europa\App\Configuration;
use Europa\Di\Container;
use Europa\Di\Locator;
use Exception;
use Testes\Test\UnitAbstract;

class ContainerTest extends UnitAbstract
{
    public function container()
    {
        $configuration = new Configuration;

        $container = new Container;
        $container->configure($configuration);

        foreach ($configuration as $service) {
            $this->assert(isset($container->$service), sprintf('The service "%s" should exist.', $service));
        }
    }

    public function getting()
    {
        $container        = new Container;
        $container->test1 = new ArrayObject(['test' => true]);
        $container->test2 = function($container) {
            return $container->test1;
        };

        $this->assert($container->test1['test'], 'The container should return a dependency for "test1".');
        $this->assert($container->test2['test'], 'The container should return a dependency for "test2".');
    }

    public function gettingUnregistered()
    {
        $container = new Container;

        try {
            $container->unregisteredService;
            $this->assert(false, 'The unregistered service "unregisteredService" should throw an exception if it is not registered.');
        } catch (Exception $e) {}
    }

    public function gettingUnregisteredWithOneInAnotherContainer()
    {
        $one = Container::one();
        $two = Container::two();

        $two->service = new ArrayObject;

        try {
            $one->service;
            $this->assert(false, 'The unregistered service "service" should throw an exception if it is not registered.');
        } catch (Exception $e) {
            $this->assert(preg_match('/however/', $e->getMessage()), 'The exception should have notified the user that another container contains a service with the same name.');
        }
    }

    public function unsetting()
    {
        $container = new Container;
        $container->test = new ArrayObject;
        $container->test();

        unset($container->test);

        $this->assert(!isset($container->test), 'The test service should have been removed.');
    }

    public function transient()
    {
        $container = new Container;
        $container->test = function() {
            return new ArrayObject(['test' => false]);
        };

        $container->test['test'] = true;

        $container->transient('test');

        $this->assert($container->test['test'] === false, 'The instance should not have been cached.');
    }

    public function errorConstructingContainer()
    {
        
    }
}