Introduction
------------

### What

EuropaPHP is an extremely fast, flexible and lightweight MVC Framework designed for PHP 5.3+.

### Why

You're a software engineer/architect that needs a solid, scalable backbone to drive your MVC layer that follows widely accepted standards. You also want the flexibility to drop in the Zend Framework (or any other library for that matter) as they are a perfect pair for each other.

Setting Up Your Application
---------------------------

Application setup has been exemplified in the bundled sample app bootstrapper: app/boot/Bootstrapper.php. Rather than use one big file of slop for setup, `\Europa\Bootstrapper` was created to facilitate maintainability by organizing each step of the setup process into smaller, appropriately named methods.

    <?php
    
    use Europa\Bootstrapper;
    
    // assuming that path is on the same level as the "Europa" directory.
    require dirname(__FILE__) . '/Europa/Bootstrapper.php';
    
    class MyAppBootstrapper extends Bootstrapper
    {
        public function registerAutoloading()
        {
            
        }
        
        public function setUpErrorHandling()
        {
            
        }
    }
    
Methods that are NOT declared as `public` in visibility are NOT called as part of the bootstrapping process. The method `boot` also has the reserved functionality of running each bootstrap method and therefore is defined as `final` and cannot be re-defined. In addition to `private` and `protected` methods and `\Europa\Bootstrapper::boot()`, magic methods (anything beginning with two underscores) are not called.

To boot up your application:

    <?php
    
    require '/path/to/your/Bootstrapper.php';
    $bootstrapper = new Bootstrapper;
    $bootstrapper->boot();

Class Loading
-------------

Class loading is handled through `\Europa\Loader`. Since autoloading is registered globally by nature, there is no reason to instantiate an instance of `\Europa\Loader`. If you structure your application properly, use descriptive names and namespaces or suffixes, you won't have any issues with conflicting load paths.

### Path Specification

Specifying paths are as simple as a single method call.

    use Europa\Loader;
    
    Loader::addPath('/path/to/my/libraries');
    Loader::addPath('/path/to/my/controllers');
    Loader::addPath('/path/to/my/models');

Europa's load paths are handled manually which significantly increases speed rather than adding them to PHP's include paths. However, we can also tell the loader to add it to PHP's include paths which is especially useful when dropping in the Zend Framework or any other library that utilizes the built-in load paths.

    Loader::addPath('/usr/bin/php', true);
    Loader::addPath('/path/to/my/libraries', true);

Since the loader searches paths in the order in which they were defined, the most frequently used paths should be specified first.

### Class Mapping

We all know that file operations can be costly. If you have a module system that contains sub-directories of modules that mimic the main application's path, you may end up with hundreds of load paths. In order to implement your own caching mechanism, the loader allows you to export a class map so that you can cache it and then apply it to the loader.

    $classMap = Loader::getMapping();

After we've cached it, we can them apply the class mapping:

    Loader::map($classMap);

Or manually map classes:

    Loader::map('\MyNamespace\MyClass', '/path/to/MyNamespace/MyClass.php');

The loader will first look for a class in the mapping and load the corresponding file if it is found. If not, it will go through the load paths and attempt to find it there. This is useful if you need to specify a handful of mappings for the most commonly used classes and don't really care if the stragglers are searched for in the load paths. In all reality, this will be a micro-optimization for a lot of applications. For some, though, it could make all the difference.

### Autoloading

At any time, we can register the `\Europa\Loader::load()` method as an autoloader by calling `\Europa\Loader::register()`.

    Loader::register();

Registering the loader as an autoloader doesn't restrict the API so you are still free to do anything you need to with the loader such as add more load paths.

### Dependencies

The loader is an integral part of EuropaPHP. It isn't required for most parts of the framework - such as if you already have compatible loading functionality - however, `\Europa\View\Php` exclusively uses the loader for loading view files. If you are using the view rendering layer, you at least need to specify a path where your views can be found.

Dependency Injection Through a Service Locator
----------------------------------------------

Something that the sample app also makes liberal use of is a service locator. `\Europa\ServiceLocator` allows you to set up, configure and automate the creation of components and their dependencies.

### Mapping

In the sample app, we have two view objects that we require, a layout and a view. The layout requires that the view be applied to it and the view also requires a separate service locator instance for locating helpers.

First we set up the mapping for these:

    <?php
    
    use Europa\ServiceLocator;
    use Europa\StringObject;
    use Europa\View\Php;
    
    class MyAppBootstrapper extends Bootstrapper
    {
        private $locator;
        
        public function setUpServiceLocator()
        {
            $this->locator = ServiceLocator::getInstance();
            $this->locator->map('layout', '\Europa\View\Php');
            $this->locator->map('view', '\Europa\View\Php');
            $this->locator->map('helper', '\Europa\ServiceLocator');
        }
    }

### Configuration - Constructor Parameters and Method-Call Automation
    
Once mapped, we configure the components. Since both the layout and the view require helpers, we configure the helper service locator to auto-format service names before they are loaded.

    public function configureViewHelpers()
    {
        $this->locator->queueMethodFor('helper', 'setFormatter', array(function($service) {
            return StringObject::create($service)->toClass() . 'Helper';
        }));
    }

By queueing a method, we tell the locator to call the specified method for the specified service using the specified parameters. Not all components take their configuration via their constructor.

Now that the helper service is setup, we can configure the views. Since the layout requires the view to be configured, we must now configure the view:

    public function configureView()
    {
        $this->locator->queueMethodFor('view', 'setServiceLocator', array($this->locator->helper));
    }

Finally, we can set up the layout which requires both a helper and a view:

    public function configureLayout()
    {
        $this->locator->setConfigFor('layout', array());
        $this->locator->queueMethodFor('layout', 'setChild', array('view', $this->locator->view));
        $this->locator->queueMethodFor('layout', 'setServiceLocator', array($this->locator->helper));
    }

By calling `\Europa\ServiceLocator::setConfigFor()` we tell the service locator to pass the specified arguments to the constructor of the service. If this is called multiple times, configuration is continually merged.

### Extending

Now when we access a layout, it is pre-configured:

    <?php
    
    use Europa\ServiceLocator;
    
    echo ServiceLocator::getInstance()->get('layout');

There is also one other way to configure a service and that is to extend `\Europa\ServiceLocator` and define methods that contain the same name as the service which you are configuring.

    <?php
    
    use Europa\ServiceLocator;
    use Europa\StringObject;
    use Europa\View\Php;
    
    class MyLocator extends ServiceLocator
    {
        public function layout()
        {
            $layout = new Php('/path/to/my/layout');
            $layout->setChild($this->get('view'));
            $layout->setServiceLocator('helper');
            return $layout;
        }
        
        public function view()
        {
            $view = new Php;
            $view->setServiceLocator($this->get('helper'));
            return $view;
        }
        
        public function helper()
        {
            $helper = new static;
            $helper->setFormatter(function($service) {
                return StringObject::create($service)->toClass() . 'Helper';
            });
            return $helper;
        }
    }

This is a little bit more straight forward if dynamic configuration isn't necessary. Also, dynamic configuration is slightly slower due to the use of dynamic function calls. However, this is still up for you to decide. You can even mix the two paradigms.

### Accessing

By explicitly creating new objects, we tell the locator not to use transient services.

    <?php
    
    use Europa\ServiceLocator;
    
    $locator = ServiceLocator::getInstance();
    $layout  = $locator->layout();
    $layout  = $locator->create('layout');

However, if we want to create the service if it doesn't exist and reuse it, we can just retrieve it:

    $layout = $locator->get('layout');
    $layout = $locator->layout;

Custom configuration can also be passed at the time of calling:

    $layout = $locator->get('layout', array('arg1', 'arg2'));
    $layout = $locator->create('layout', array('arg1', 'arg2'));
    $layout = $locator->layout('arg1', 'arg2');

If you do pass a configuration to `get` and the object already exists, it is re-configured, cached for future retrievals and returned.

If you want to set a service from an external source, you can register it:

    $locator->register('externalService', new \ExternalService);

### Multi-Instance Usage

Just in case you have separate service locator configurations, you can manage instances using `\Europa\ServiceLocator::getInstance($name)`. If the specified instance is not found, it is created and cached for later retrieval. If not specifying a name, it allows you to use the service locator like a singleton since for most applications, you won't need to retrieve more than one instance.

    <?php
    
    use Europa\ServiceLocator;
    
    // default instance
    $locator = ServiceLocator::getInstance();
    
    // a separate instance
    $locator = ServiceLocator::getInstance('myOtherConfiguration');

Requests
--------

Using a request can be as simple as instantiating the object and `echo`ing the result of it's dispatch rendering.

    <?php
    
    use Europa\Request\Http;
    
    $http = new Http;
    echo $http->dispatch()->render();

This would, by default, route the request to an `\Controller\Index` if no controller is specified. A controller can be specified simply by using a `$_REQUEST` variable by the name of controller:

    http://derp/europa?controller=test

This would route the request to the `\Controller\Test`.

### Custom Controller Parameter Name

If we don't like the parameter name "controller", all we need to do is set a different key:

    <?php
    
    use Europa\Loader;
    use Europa\Request\Http;
    use Europa\StringObject;
    
    $request = new Http;
    $request->setControllerKey('my-custom-controller-name');

### Custom Controller Naming Convention

If we don't like the naming convention of `[name]Controller`, then we set a different formatter:

    $request->setControllerFormatter(function(Http $request) {
        return StringObject::create($request->getController())->toClass() . 'Controller;
    });

### Custom Controller Paths

If you want to change the default path of `application`, all you have to do is change the load path:

    <?php
    
    Loader::addPath('/my/custom/controller/path');

Since controllers are autoloaded, you can specify more than one path to get them from making a modular system very easy to facilitate.

Requests, Routers and Routes
----------------------------

Routes in Europa are simple ways of being able to query given a string and to reverse engineer given a set of parameters.

### Querying and Reverse Engineering

Reverse engineering allows for flexible url's:

    <?php
    
    use Europa\Route\Regex;
    
    $expression = 'user/(?<username>[a-zA-Z0-9]+/?)+';
    $reverse    = 'user/:username';
    $defaults   = array('controller' => 'user');
    $route      = new Regex($expression, $reverse, $defaults);
    
    // array('controller' => 'user', 'username' => 'tres');
    $route->query('user/tres');
    
    // 'user/derper'
    $route->reverseEngineer(array('username' => 'derper'));

If the url changes, we still reverse engineer it in the same way given the parameters are the same:

    $expression = 'u/(?<username>[a-zA-Z0-9]+/?)+';
    $reverse    = 'u/:username';
    $route      = new Regex($expression, $reverse, $defaults);
    
    // 'u/derper'
    $route->reverseEngineer(array('username' => 'derper'));

### Applying Routes to Routers

The router is designed to be able to map any string to a set of parameters, not just a request.

    <?php
    
    use Europa\Router;
    use Europa\Route\Regex;
    
    $router = new Router;
    $router->setRoute('default', new Regex('(?<controller>[^\?]+)?'), ':controller', array(
        'controller' => 'index'
    ));
    
    // array('controller' => 'index')
    $router->query(null);
    
    // array('controller' => 'user')
    $router->query('user');
    
    // array('controller' => 'user/profile')
    $router->query('user/profile?id=1');

### Routing a Request

This can be used in conjunction with the request to dispatch to a controller:

    <?php
    
    use Europa\Request\Http;
    use Europa\Router;
    use Europa\Route\Regex;
    
    $router = new Router;
    $router->setRoute('default', new Regex('(?<controller>[^\?]+)?'), array(
        'controller' => 'index'
    ));
    
    $request = new Http;
    echo $request->setParams($params)->dispatch()->render();

### Handling Rogue Requests

We can also instantiate controllers manually, so if a request isn't matched, we can force an error controller:

    $request = new Http;
    if ($params = $router->query($request->getUri()->getRequest())) {
        echo $request->setParams($params)->dispatch()->render();
    } else {
        $error = new \ErrorController($request);
        echo $error->render();
    }

Controllers
-----------

Controllers in Europa differ from controller implementations in other frameworks. Each controller is in essence a "single action" controller, but each controller has the ability to support different request methods as defined in the [HTTP 1.1 specification](http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html).

### Defining Your Controllers

Below is an example of a controller that supports `GET`, `POST` and `DELETE`.

    <?php

    use Europa\Controller;

    class MyController extends Controller
    {
        public function get()
        {

        }

        public function post()
        {

        }

        public function delete()
        {

        }
    }

So if you dispatch a request to the `MyController`, depending on the request method, `get()`, `post()` or `delete()` may be called. If a request methods that is specified doesn't exist in the controller, then a `\Europa\Controller\Exception` is thrown indication that the specified method is not supported.

### Mapping Request Parameters to Action Parameters

Request parameters can be auto-mapped to action parameters just by defining parameters in your action methods:

    public function get($id)
    {

    }

In the above example, the `id` parameter is required and an exception will be thrown if it is not supplied. We can also specify optional parameters by supplying a default value:

    public function post($id = null)
    {
        if ($id) {
            // update
        } else {
            // insert
        }
    }

The `id` parameter is optional and therefore if it is not supplied in the request it will default to `null`. Since request parameters are mapped to action parameters by name, the order in which they are specified does not matter.

Views
-----

By default, the application is already setup to use a PHP layout/view system.

Validation
----------

Europa's validators are very flexible and include many different ways to validate data.

### Simple Validation

Validation with a single validator is very simple:

    <?php
    
    use Europa\Validator\Rule\Email;
    
    $email = new Email;
    $email->addMessage('Please enter a valid email.');
    $valid = $email->validate('treshugart@gmail.com')->isValid();

However, if you did this with a lot of data, your code would look like a cat took a nap on your keyboard. This is why there is validation suites.

### Suites

This is an example of the "verbose" way to build a validation suite:

    <?php
    
    use Europa\Validator\Rule\Required;
    use Europa\Validator\Rule\Number;
    use Europa\Validator\Rule\NumberRange;
    use Europa\Validator\Suite;
    
    $suite    = new Suite;
    $required = new Required;
    $number   = new Number;
    $range    = new NumberRange(1, 10);
    
    $required->addMessage('This field is required.');
    $number->addMessage('The value must be a number.');
    $number->addMessage('The value must be a number between 1 and 10.');

    $suite->add($required)->add($number)->add($range);

The same thing, but just a *bit* shorter:

    <?php
    
    use Europa\Validator\Suite;
    
    $suite = Suite::required()->addMessage('This field is required.')
        ->number()->addMessage('The value must be a number.')
        ->numberRange(1, 10)->addMessage('The value must be a number between 1 and 10.');

And now we can apply those validators to a value:

    // false
    $suite->validate(11)->isValid();

Validation suites can have suites applied to them instead of just raw validators which allows for any number of levels of validators:

    $numberSuite  = Suite::number()->numberRange(21);
    $userAgeSuite = Suite::required()->add($numberSuite);
    
    // false
    $userAgeSuite->validate(19)->isValid();

### Maps

A validation map is for mapping an array of values to a suite of suites/validators. A good example of this would be validating `$_POST` data or the data inside a model entity before saving to the database.

Using a validation map is similar to using a suite, but with a few differences. First, setting validators on the map require you to map the index of the validator to a corresponding index in the data you are validating.

    <?php
    
    use Europa\Validator\Map;
    use Europa\Validator\Rule\Email;
    
    $user = array('email' => 'treshugart@gmail.com', 'name' => 'Trey Shugart');
    $map  = new Map;
    $map->set('email', Suite::required()->email());
    
    // true
    $map->validate($user)->isValid();

You can use `\Europa\Validator\Map::add()`, however, that will only increment the index and may not correspond to a value in the data array, os it is safer to explicitly map the validator to a specific index in the data array.

The shorter way of doing the above:

    <?php
    
    use Europa\Validator\Map;
    
    $user = array('email' => 'treshugart@gmail.com', 'name' => 'Trey Shugart');
    $map  = new Map;
    
    // true
    $map->email->required()->email()->validate($user)->isValid();

You can even use sub-mappings:

    $data
        'metadata' => array(
            'name'    => 'Trey Shugart',
            'address' => array(
                'street' => 'some street'
            )
        )
    );
    
    $user = new Map;
    $meta = new Map;
    $addr = new Map;
    
    $user->set('metadata', $meta);
    $meta->name->required()->alphaNumeric()->set('address', $addr);
    $addr->street->required()->alphaNumeric();
    
    // true
    $user->validate($data)->isValid();

### Error Messages

Error messages can be applied to any type of validator whether it is a map, suite or validator. When retrieving these messages, only the failed validation messages are returned.

    <?php
    
    use Europa\Validator\Suite;
    use Europa\Validator\Required;
    
    $required = new Required;
    $required->addMessage('This field is required.');
    
    // array();
    $required->getMessages();
    $required->validate('some value')->getMessages();
    
    // array('This field is required.');
    $required->validate(null)->getMessages();

When messages are retrieved from a map or suite, they are returned as a flat, merged array comprising of all of the messages from each failed validator.

    $suite = Suite::required()->addMessage('This field is required.');
    $suite->email()->addMessage('Please enter a valid email address.');
    
    // array('This field is required.', 'Please enter a valid email address.');
    $suite->validate(null)->getMessages();

License
-------

Copyright (c) 2005-2011, Trey Shugart, EuropaPHP, All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice,
  this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice,
  this list of conditions and the following disclaimer in the documentation
  and/or other materials provided with the distribution.
* Neither the name of EuropaPHP nor the names of its contributors may be 
  used to endorse or promote products derived from this software without 
  specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.