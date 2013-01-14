EuropaPHP
=========

[![Build Status](https://travis-ci.org/treshugart/EuropaPHP.png)](https://travis-ci.org/treshugart/EuropaPHP)

EuropaPHP is an extremely fast, flexible and lightweight <del>M</del>VC Framework designed for PHP 5.4+.

Why Another PHP Framework?
--------------------------

EuropaPHP is the manifestation of the shortcomings of other PHP frameworks with a strong focus on scalability, standards, the view/controller relationship and dependency injection. It is specifically designed to be paired with your favourite libraries and it will seamlessly integrate with anything.

Installation
------------

There are a couple ways to install Europa.

### Source

Download from Github and extract it to where you want it.

### Composer

    composer create-project treshugart/europaphp [installation path] [branch or tag]

If you want to use it as a composer package, just add `treshugart/europaphp` to your `composer.json` file.

Getting Started
---------------

Everything is already all set up for you in a sample module called `main`. To get an overview of how everything fits together, the default conventions and how to customize your app, see the readme in the `Europa\App` component directory.

Documentation
-------------

To check out the documentation, just go into the `src` directory. Each directory has a `readme` that corresponds to it. To update the documentation, fork, pull-request, rinse, repeat.

Key Features
------------

There's a lot of new stuff. For a more in depth look, check out each component's readme.

### SRP and API Design

EuropaPHP 2's major difference to previous releases is it's focus on how the Single Responsibility Principle is applied in a PHP context. As a result of this design, a lot of class functionality can be exposed through a single method. When this was the case, in order to maximise flexibility and eliminate unnecessary complexity, the class would expose that functionality using `__invoke()`. This makes the class itself interchangeable with anything that `is_callable()`. By utilising PHP 5.4, type-hints have been placed where appropriate to use `callable` so that you can use a callable class or closure to do what you need.

### Service Containers

Another major difference is how service containers are configured. Service containers take configurations via a `configure()` method. The configuration can be anything that is `callable`, however, there is a `Europa\Di\ConfigurationAbstract` class that you can extend to organise your configurations into classes. A benefit to using these classes is that you can define interfaces for these configurations and then check the container if they provide that configuration or configuration interface.

### Controllers

Controllers can now simply be closures, or anything that is `callable`. This way, you can have very lightweight controllers that don't require any other fucntionality, or you can extend the `Europa\Controller\ControllerAbstract` class to give you access to named arguments and filters.

### Configuration

An emphasis has also been placed on passing configurations into class constructors where appropriate. This is the case for routes as well as certain views and the main application component. In doing this, a `Europa\Config\Config` class was created to make using configurations easier. The configuration component ships with a few adapters to solve most of your needs: `Europa\Config\Adapter\Ini`, `Europa\Config\Adapter\Json` and `Europa\Config\Adapter\Php`. The config class allows for dot-notation to be used for option names as well as referencing other opiton values within another option value.

### Event Management

An `Event` component now exists to allow your application to create hooks at any point in it's lifecycle. As with many other parts, events can be anything that is `callable`.

### Reflection

The reflection component now contains a `FunctionReflector` for reflecting closures as well as normal functions.

### Easier Routing

The router has been completely overhauled to make it easier to define your application's structure. Since routes are passed a configuration we can use the `Config` component to read route configurations and directly pass it on to the route. As a result, all of the types of configuration files that are supported by the `Config` component are available to the router as well.

### Effortless Content Negotiation

The `View` component now ships with a `Negotiator` that will return - based on the request that is passed in - a certain view class appropriate to handle the request given request. The negotiator is configurable to a point and is `callable`, so substituting your own is very easy.

### Application Abstraction

The `App` component was added to provide a way of eliminating as much boilerplate code as possible while still giving you as much flexibility as possible. It takes a single service container that it uses to grab it's dependencies from. This service container must provide `Europa\App\AppConfigurationInterface` or be configured with `Europa\App\AppConfiguration`. This means that you can substitute dependencies into it's container if need be to alter it in any way shape or form. It comes with good defaults so you probably won't have to do anything. Additionally, it accepts configuration options in its constructor to control smaller things like paths and naming conventions.

Running Tests
-------------

From the install directory:

    php www/index.php test

Command Line Usage
------------------

To checkout available commands, run:

    php www/index.php ?

Contributing
------------

To contirbute, just fork and submit pull-requests. Each request will be reviewed and ideally will include corresponding tests and possibly a place in the documentation.

License
-------

Copyright (c) 2005-2011 Trey Shugart

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
