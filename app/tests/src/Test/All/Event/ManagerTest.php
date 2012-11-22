<?php

namespace Test\All\Event;
use Europa\Event\Manager;
use stdClass;
use Test\Provider\Event\CustomEvent;
use Testes\Test\UnitAbstract;

class ManagerTest extends UnitAbstract
{
    public function single()
    {
        $manager = new Manager;
        $called     = false;

        $manager->bind('test', function($bool) use (&$called) {
            $called = $bool;
        });
        
        $manager->trigger('test', true);

        $this->assert($called, 'The manager did not trigger the custom event.');
    }

    public function double()
    {
        $manager = new Manager;
        $called     = [];

        $manager->bind('test', function($bool) use (&$called) {
            $called[0] = $bool;
        });

        $manager->bind('test', function($bool) use (&$called) {
            $called[1] = $bool;
        });
        
        $manager->trigger('test', true);
        
        $this->assert($called[0] && $called[1], 'The manager did not trigger both events.');
    }
    
    public function unbind()
    {
        $manager = new Manager;
        $called     = false;
        
        $manager->bind('test', function() use ($called) {
            $called = true;
        });
        
        $manager->trigger('test');
        
        $this->assert(!$called, 'The disptacher did not unbind the test event.');
    }
    
    public function customEventByInstance()
    {
        $manager = new Manager;

        $data         = new stdClass;
        $data->called = false;
        
        $manager->bind('test', new CustomEvent)->trigger('test', $data);
        $manager->bind('test', new CustomEvent)->triggerArray('test', [$data]);
        
        $this->assert($data->called, 'The manager did not trigger the custom event.');
    }
}