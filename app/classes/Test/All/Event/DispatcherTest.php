<?php

namespace Test\All\Event;
use Europa\Event\Dispatcher;
use stdClass;
use Test\Provider\Event\CustomEvent;
use Testes\Test\UnitAbstract;

class DispatcherTest extends UnitAbstract
{
    public function single()
    {
        $dispatcher = new Dispatcher;
        $called     = false;

        $dispatcher->bind('test', function($bool) use (&$called) {
            $called = $bool;
        });
        
        $dispatcher->trigger('test', [true]);

        $this->assert($called, 'The dispatcher did not trigger the custom event.');
    }

    public function double()
    {
        $dispatcher = new Dispatcher;
        $called     = [];

        $dispatcher->bind('test', function($bool) use (&$called) {
            $called[0] = $bool;
        });

        $dispatcher->bind('test', function($bool) use (&$called) {
            $called[1] = $bool;
        });
        
        $dispatcher->trigger('test', [true]);
        
        $this->assert($called[0] && $called[1], 'The dispatcher did not trigger both events.');
    }
    
    public function unbind()
    {
        $dispatcher = new Dispatcher;
        $called     = false;
        
        $dispatcher->bind('test', function() use ($called) {
            $called = true;
        });
        
        $dispatcher->trigger('test');
        
        $this->assert(!$called, 'The disptacher did not unbind the test event.');
    }
    
    public function customEventByString()
    {
        $data         = new stdClass;
        $data->called = false;
        
        (new Dispatcher)->bind('test', 'Test\Provider\Event\CustomEvent')->trigger('test', [$data]);
        
        $this->assert($data->called, 'The dispatcher did not trigger the custom event.');
    }
    
    public function customEventByInstance()
    {
        $data         = new stdClass;
        $data->called = false;
        
        (new Dispatcher)->bind('test', new CustomEvent)->trigger('test', [$data]);
        
        $this->assert($data->called, 'The dispatcher did not trigger the custom event.');
    }
}