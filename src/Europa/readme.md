Components
==========

Each component is functionally separted into their own namespaces.

App
---

High-level components useful in application setup and dispatching. Allows for the modular design of an application through the use of modules and is configurable through the use of service containers.

Bootstrapper
------------

Abstraction for creating classes that define methods that bootstrap your application. Doing this creates a conventional, self-documenting and maintainable way of setting up your application.

Config
------

Configuration abstraction for handling different types of configuration files like `ini` and `json`. Allows you to do things like use dot-notation in option names and have them build-sub objects and reference other configuration options from within another option's value.

Controller
----------

Offers abstracted functionality for authoring controller classes.

Di
--

Service containers and locators for finding and managing configured object instances.

Event
-----

Simple event management using string event names and `callable` event handlers.

Exception
---------

Abstraction that allows easy extension, formatting of messages using `sprintf` and easy usage.

Filter
------

Suite of filters used throughout the framework ranging from conversion to strings to camel-casing.

Fs
--

File system abstractions finding, relative location, file / directory manipulation and class loading.

Reflection
----------

An extension upon PHP's already very useful Reflection components to provide futher useful functionality such as doc block parsing and annotations for classes, methods, functions and properties.

Request
-------

Abstracts both CLI and HTTP requests into a common interface which allows for easy testability and multiple requests to flow down a common controller path, reusing code and time.

Response
--------

Similar to the request object, each type of request has its own type of response both for the sake of convention, simplicity and maintainability.

Router
------

Routers take a request and return an invokable controller.

View
----

Suite of different styles of output and a negotiator to make deciding on which one to use a breeze.