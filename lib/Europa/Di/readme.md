Di
==

DI stands for [Dependency Injection](http://en.wikipedia.org/wiki/Dependency_injection). It is also known as the [Service Locator Pattern](http://en.wikipedia.org/wiki/Service_locator_pattern) or [Inversion of Control](http://en.wikipedia.org/wiki/Inversion_of_control). If you are not familiar with this, it is the act of injecting one dependency object into a dependent object. For example:

    $controller = new Controller\Index(new Europa\Request\Http);

Or:

    $view = new Europa\View\Php;
    $view->setHelpers(new Europa\Di\Finder);

This can get very tedious and difficult to maintain because of code duplication and organisation. On top of this, there is no way to dynamically configure a dependency or to replace one with another which is useful for testing amongst other things. This is where a dependency injection container comes in.

In Europa, there are three types of containers.

- Provider
- Finder
- Chain

In essence, they all do the same thing: configure and return an object. It's the way they do it that is different.

Retrieving dependencies are done with `create()` and `get()`. Creating an object will always return a new one. Getting one will create if not exists and return it:

    <?php
    
    use Container\MyContainer;
    
    $mc = MyContainer::fetch();
    
    // accessing like a property
    $old = $mc->get('someDep');
    $new = $mc->get('someDep');
    
    // getting will cache the first one
    var_dump($old === $new); // true
    
    $old = $mc->create('someDep');
    $new = $mc->create('someDep');
    
    // calling will always return a new object
    var_dump($old === $new); // false

You can even pass arguments by index or name. If they are passed by name, their index is resolved using the method definition:

    $mc->get('someDep', [$arg1, $arg2]);
    $mc->get('someDep', ['arg1' => $arg1, 'arg2' => $arg2]);
    $mc->create('someDep', [$arg1, $arg2]);
    $mc->create('someDep', ['arg1' => $arg1, 'arg2' => $arg2]);

Providers
---------

A `Provider` is an abstract class that you extend to provide it with methods that configure and return a dependency. They are used when you don't need dynamic resolution and configuration and are more efficient than finders.

    <?php
    
    namespace Container;
    use Europa\Di\Provider;
    use Zend\Mail\Message;
    use Zend\Mail\Transport\Smtp;
    
    class MyContainer extends Provider
    {
        /**
         * Returns a mail transport.
         * 
         * @return Smtp
         */
        public function mailTransport()
        {
            return new Smtp;
        }
        
        /**
         * Returns a mail message with the from address already populated.
         * 
         * @return Message
         */
        public function mailMessage()
        {
            $message = new Message;
            $message->setFrom('you@you.com', 'Your Name');
            return $message;
        }
    }

Then to use it, it's fairly straight forward:

    use Container\MyContainer;
    
    $mc  = new MyContainer;
    $msg = $mc->create('mailMessage')->setTo('someone@else.com')->setBody('Some body.');
    $mc->get('mailTransport')->send($msg);

In a more complex example, we can automate the setup of dependencies if one requires another during setup by including it in the method definition:

    public function view($locator)
    {
        return new Php($locator);
    }
    
    public function locator()
    {
        $locator = new Europa\Fs\Locator;
        $locator->addPath('path/to/views');
        return $locator;
    }

Additionally, if you don't want the dependency set up right away, just hint it as a `Closure` and it will give you a closure that you can invoke to set up and return the dependnecy.

    public function view(Closure $locator)
    {
        return new Php($locator());
    }

### Passing Arguments

Arguments will be merged with the arguments as defined in the method definition.

Finders
-------

Finders are for dynamic resolution and configuration. They are very useful in situations where you don't know the exact type of instance you want. For example, when you are locating a controller, you know that it is a controller. However, you don't know what namespace it is under and what type of controller it is.

    <?php

    use Europa\Di\Finder;
    use Europa\Filter\ClassNameFilter;
    use Europa\Request\Http;
    
    $finder = new Finder;
    
    // the default filter is a ClassResolutionFilter that allows you to add other filters to it
    $finder->getFilter()->add(new ClassNameFilter([
        'prefix' => 'Ctrl',
        'suffix' => 'Controller'
    ]));
    
    // ensure a request is passed to the constructor of all types of instances
    $finder->config(function() {
        return [new Http];
    });
    
    // enable filters if it is a subclass of ControllerAbstract
    $finder->queue('Europa\Controller\ControllerAbstract', function($controller) {
        $controller->filter();
    });
    
    // returns an instance of Ctrl\IndexController
    $controller = $finder->get('index');

### Dynamic Constructor Arguments

The `config()` method tells the finder to pass the return value of the specified closure to the constructor of the class when it is instantiated.

To configure all types of instances with the same arguments:

    $finder->config(function() {});

To configure a specific type of instance:

    $finder->config('Object\Class\Name', function() {});

Not only can you specify an class name, you can specify interface names as well as trait names.

### Calling Methods Dynamically

The `queue()` method tells the finder to call the specified closure for the given instance type.

To queue a function for all types of instances:

    $finder->queue(function($obj) {});

To queue a function for one type of instance you type-hint the first argument:

    $finder->queue('Some\Instance\Type', function($obj) {});

### Passing Arguments

When arguments are passed, they will be merged with and override the arguments that are specified in the config.

Chains
------

A `Chain` is used when you want to link together multiple containers. It will look for the dependency in each specified container until it reaches the end of the chain. If it's not found it throws an exception just like the other containers.

    <?php
    
    use Europa\Di\Chain;
    use Europa\Di\Finder;
    use Container\MyContainer;
    
    $chain = new Chain;
    $chain->add(new MyContainer);
    $chain->add(new Finder);
    
    $dep = $chain->create('someDependency');

### Passing Arguments

When arguments are passed, they will be handled by their respective container.