Event
=====

The event component is a super-simple way to bind and trigger events. Instead of building out complex event interfaces it relies on a straight forward way of calling events.

As long as your bound callback `is_callable()`, it can be triggered. This means any class that has an `__invoke()` method, `array($class, 'method')`, closures, lambda functions and any defined function.

The Event component provides you with a default `Manager` that will solve 99% of your problems. For the rest, there is a `ManagerInterface` if you need to implement an edge case.

Binding and Triggering Events
=============================

Events are bound using strings and something callable:

    <?php
    
    use Europa\Event\Manager;
    
    $manager = new Manager;
    
    $cruel = function() {
        echo 'Cruel ';
    };
    
    // not sure what I want to say to the world
    $manager->bind('hello.pre-world', function($text) {
        echo $text . ' ';
    });
    
    // the world sucks
    $manager->bind('hello.pre-world', $cruel);
    
    // make it really exciting
    $manager->bind('hello.post-world', function() {
        echo '!!!!!!!';
    });

You can unbind whole events, or just a certain callback:

    // I really didn't want to make it that exciting
    $manager->unbind('hello.post-world');

    // you know, it's not so bad anymore
    $manager->unbind('hello.pre-world', $cruel);

Triggering is simple and also allows you to pass in data that is passed on to every event:

    // outputs "Hello World!"
    $manager->trigger('hello.pre-world', ['Hello ']);
    echo 'World!';
    $manager->trigger('hello.post-world');

If you've got some callable classes out there, go ahead and use them:

    <?php
    
    namespace Event;
    
    class MyEvent
    {
        public function __invoke()
        {
            echo 'Triggered my event!';
        }
    }

And trigger it:

    use Europa\Event\Manager;
    use Event\MyEvent;
    
    $manager = new Manager;
    $manager->bind('myevent', new MyEvent);
    
    // outputs "Triggered my event!"
    $manager->trigger('myevent');

### Passing Data to the Handler

You can also pass data to the handler at the time of triggering.

    $manager->bind('myEvent', function($arg1, $arg2) {
        // do something
    });
    
    $manager->trigger('myEvent', $arg1, $arg2);
    $maanger->triggerArray('myEvent', [$arg1, $arg2]);