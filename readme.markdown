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
    
Methods that are NOT declared as `public` in visibility are NOT called as part of the bootstrapping process. The method `boot` also has the reserved functionality of running each bootstrap method and therefore is defined as `final` and cannot be re-defined. In addition to `private` methods and `\Europa\Bootstrapper::boot()`, magic methods (anything beginning with two underscores) are not called.

Class Loading
-------------

Class loading is handled through `\Europa\Loader`. Since autoloading is registered globally by nature, there is no reason to instantiate an instance of `\Europa\Loader`. If you structure your application properly, use descriptive names and namespaces or suffixes, you won't have any issues with conflicting load paths.

Specifying paths are as simple as a single method call.

    use Europa\Loader;
    
    Loader::addPath('/path/to/my/libraries');
    Loader::addPath('/path/to/my/controllers');
    Loader::addPath('/path/to/my/models');

If we need to load files for anything other than php files, we can specify a second argument for the file type.

    Loader::addPath('/path/to/my/views', 'phtml');

Europa's load paths are handled manually which significantly increases speed rather than adding them to PHP's include paths. However, we can also tell the loader to add it to PHP's include paths which is especially useful when dropping in the Zend Framework or any other library that utilizes the built-in load paths.

    Loader::addPath('/usr/bin/php', 'php', true);
    Loader::addPath('/path/to/my/libraries', 'php', true);

We all know that file operations can be costly. If you have a module system that contains sub-directories of modules that mimic the main application's path, you may end up with hundreds of load paths. In order to implement your own caching mechanism, the loader allows you to export a class map so that you can cache it and then apply it to the loader.

    $classMap = Loader::getMapping();

After we've cached it, we can them apply the class mapping:

    Loader::map($classMap);

Or manually map classes:

    Loader::map('\MyNamespace\MyClass', '/path/to/MyNamespace/MyClass.php');

The loader will first look for a class in the mapping and load the corresponding file if it is found. If not, it will go through the load paths and attempt to find it there. This is useful if need to specify a handful of mappings for the most commonly used classes and don't really care if the stragglers are searched for in the load paths.

At any time, we can register the `\Europa\Loader::load()` method as an autoloader by calling `\Europa\Loader::register()`.

    Loader::register();

Registering the loader as an autoloader doesn't restrict the API so you are still free to do anything you need to with the loader such as add more load paths.

The loader is an integral part of EuropaPHP. It isn't required for most parts of the framework - such as if you already have compatible loading functionality - however, `\Europa\View\Php` exclusively uses the loader for loading view files. If you are using the view rendering layer, you at least need to specify a path where your views can be found.

The mapper can also be useful but in all reality, this will be a micro-optimization for a lot of applications. For some, though, it could make all the difference.

Dependency Injection Through a Service Locator
----------------------------------------------

Something that the sample app also makes liberal use of is a service locator. `\Europa\ServiceLocator` allows you to set up, configure and automate the creation of components and their dependencies.

In the sample app, we have two view objects that we require, a layout and a view. The layout requires that the view be applied to it and the view also requires a separate service locator instance for locating helpers.

First we set up the mapping for these:

    <?php
    
    use Europa\ServiceLocator;
    use Europa\String;
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
    
Once mapped, we configure the components. Since both the layout and the view require helpers, we configure the helper service locator to auto-format service names before they are loaded.

    public function configureViewHelpers()
    {
        $this->locator->queueMethodFor('helper', 'setFormatter', array(function($service) {
            return String::create($service)->toClass() . 'Helper';
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
        $this->locator->queueMethodFor('layout', 'setChild', array('view', $this->locator->view));
        $this->locator->queueMethodFor('layout', 'setServiceLocator', array($this->locator->helper));
    }

Now when we access a layout, it is pre-configured:

    <?php
    
    use Europa\ServiceLocator;
    
    echo ServiceLocator::getInstance()->get('layout');

There is also one other way to configure a service and that is to extend `\Europa\ServiceLocator` and define methods that contain the same name as the service which you are configuring.

    <?php
    
    use Europa\ServiceLocator;
    use Europa\String;
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
                return String::create($service)->toClass() . 'Helper';
            });
            return $helper;
        }
    }

This is a little bit more straight forward if dynamic configuration isn't necessary. Also, dynamic configuration is slightly slower due to the use of dynamic function calls. However, this is still up for you to decide.

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

Just in case you have separate service locator configurations, you can manage instances using `\Europa\ServiceLocator::getInstance($name)`. If the specified instance is not found, it is created and cached for later retrieval. If not specifying a name, it allows you to use the service locator like a singleton since for most applications, you won't need to retrieve more than one instance.

    <?php
    
    use Europa\ServiceLocator;
    
    // default instance
    $locator = ServiceLocator::getInstance();
    
    // a separate instance
    $locator = ServiceLocator::getInstance('myOtherConfiguration');

Requests
--------

Using a request can be as simple as instantiating the object and `echo`ing it.

    <?php
    
    use Europa\Request\Http;
    
    echo new Http;

This would, by default, route the request to an `\IndexController` if no controller is specified. A controller can be specified simply by using a `$_REQUEST` variable by the name of controller:

    http://derp/europa?controller=test

This would route the request to the `\TestController`.

If we don't like the parameter name "controller", all we need to do is set a different key:

    <?php
    
    use Europa\Request\Http;
    use Europa\String;
    
    $request = new Http;
    $request->setControllerKey('my-custom-controller-name');

If we don't like the naming convention of `[name]Controller`, then we set a different formatter:

    $request->setControllerFormatter(function(Http $request) {
        return '\Controller\\' . String::create($request->getController())->toClass();
    });

If you want to change the default path of `application/controllers`, all you have to do is change the load path:

    <?php
    
    use Europa\Loader;
    
    Loader::addPath('/my/custom/controller/path');

License
-------

Copyright (c) 2005-2008, Trey Shugart, EuropaPHP, All rights reserved.

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