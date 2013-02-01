<?php

namespace Test\All\Di;
use ArrayObject;
use Europa\Di\ServiceContainer;
use Europa\Di\ServiceLocator;
use Exception;
use Test\Provider\Di\TestConfiguration;
use Testes\Test\UnitAbstract;

class ServiceContainerTest extends UnitAbstract
{
    public function container()
    {
        $configuration = new TestConfiguration;

        $container = new ServiceContainer;
        $container->configure($configuration);

        $this->assert(isset($container->test), 'The service "test" should exist.');
    }

    public function getting()
    {
        $container        = new ServiceContainer;
        $container->test1 = new ArrayObject(['test' => true]);
        $container->test2 = function($container) {
            return $container->test1;
        };

        $this->assert($container->test1['test'], 'The container should return a dependency for "test1".');
        $this->assert($container->test2['test'], 'The container should return a dependency for "test2".');
    }

    public function gettingUnregistered()
    {
        $container = new ServiceContainer;

        try {
            $container->unregisteredService;
            $this->assert(false, 'The unregistered service "unregisteredService" should throw an exception if it is not registered.');
        } catch (Exception $e) {}
    }

    public function gettingUnregisteredWithOneInAnotherContainer()
    {
        $one = ServiceContainer::one();
        $two = ServiceContainer::two();

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
        $container = new ServiceContainer;
        $container->test = new ArrayObject;
        
        $this->assert($container->test instanceof ArrayObject, 'The test service should be an instance of ArrayObject.');

        unset($container->test);

        $this->assert(!isset($container->test), 'The test service should have been removed.');
    }

    public function transient()
    {
        $container = new ServiceContainer;
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