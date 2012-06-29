Event
=====

The event component is a super-simple way to bind and trigger events. Instead of building out complex event interfaces it relies on a straight forward way of calling events.

As long as your bound callback `is_callable()`, it can be triggered. This means any class that has an `__invoke()` method, `array($class, 'method')`, closures and lambda functions.

The Event component provides you with a default `Dispatcher` that will solve 99% of your problems. For the rest, there is a `DispatcherInterface` if you need to solve an edge case.

Binding and Triggering Events
=============================

Events are bound using strings and something callable:

    <?php
    
    use Europa\Event\Dispatcher;
    
    $dispatcher = new Dispatcher;
    
    $cruel = function() {
        echo 'Cruel ';
    };
    
    // not sure what I want to say to the world
    $dispatcher->bind('hello.pre-world', function($text) {
        echo $text . ' ';
    });
    
    // the world sucks
    $dispatcher->bind('hello.pre-world', $cruel);
    
    // make it really exciting
    $dispatcher->bind('hello.post-world', function() {
        echo '!!!!!!!';
    });

You can unbind whole events, or just a certain callback:

    // I really didn't want to make it that exciting
    $dispatcher->unbind('hello.post-world');

    // you know, it's not so bad anymore
    $dispatcher->unbind('hello.pre-world', $cruel);

Triggering is simple and also allows you to pass in data that is passed on to every event:

    // outputs "Hello World!"
    $dispatcher->trigger('hello.pre-world', ['Hello ']);
    echo 'World!';
    $dispatcher->trigger('hello.post-world');
