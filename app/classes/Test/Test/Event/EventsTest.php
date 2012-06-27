<?php

namespace Test\Test\Event;
use Europa\Event\CallbackEvent;
use Europa\Event\Data;
use Test\Provider\Event\CustomEvent;
use Testes\Test\Test;

class EventsTest extends Test
{
    public function callback()
    {
        $callback = new CallbackEvent(function(Data $data) {
            $data->triggered = true;
        });

        $data = new Data(array(
            'triggered' => false
        ));
        
        $callback->trigger($data);
        
        $this->assert($data->triggered === true, 'The data was not modified during triggering.');
    }

    public function custom()
    {
        $event = new CustomEvent;

        $data = new Data(array(
            'triggered' => false
        ));

        $event->trigger($data);
        
        $this->assert($data->triggered === true, 'The data was not modified during triggering.');
    }
}