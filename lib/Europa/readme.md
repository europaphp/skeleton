Components
==========

Each component is functionally organised into their own namespaces.

App
---

High-level components useful in application setup and dispatching.

Controller
----------

Abstractions for authoring single-action and RESTful controllers.

Di
--

Dependency injection providers and finders for automating dependency location and setup.

Event
-----

Event management using simple interfaces.

Filter
------

Suite of filters used throughout the framework.

Fs
--

Filesystem functionality enabling class autoloading, file location and manipulation.

Reflection
----------

An extension upon PHP's already very useful Reflection components. Adds functionality for parsing doc-blocks and doc-tags as well as using named arguments rather than absolutely positioned arguments in methods.

Request
-------

Abstracts both CLI and HTTP requests into a common interface which allows for easy testability and multiple requests to flow down a common controller path, reusing code and time.

Response
--------

Similar to the request object, each type of request has its own type of response both for the sake of convention and simplicity. An HTTP response abstracts header setting and output.

Router
------

Routers take a request and match it against a subject. If a match is found, the request is modified according to matches in the subject.

View
----

Suite of different styles of output. A view is used in conjunction with a controller's return value and a response to automate output.