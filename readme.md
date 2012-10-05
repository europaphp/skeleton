EuropaPHP
=========

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

That's really it.

Running Tests
-------------

From the install directory:

    bin/cli test

Command Line Usage
------------------

To checkout available commands, run:

    bin/cli

This will spit out a bunch of command line commands and options and tell you how to use it.

Configuration
-------------

By default, you can use the `Europa` class which is a DI container with pre-configured objects. This class accepts a configuration object that you can use to alter how some of the dependencies are set up.

### `controllers.filter.configs`

An array of `ClassNameFilter` configurations to use in the controller finder. By default, controllers are prefixed with the `Controller\` namespace.

### `helpers.filter.configs`

An array of `ClassNameFilter` configurations to use in the helper finder. By default, the finder looks for a helper prefixed with `Helper\`. It falls back to using `Europa\View\Helper\`.

### `paths.app`

The application path. This defaults to `app`, relative to the install path.

### `paths.root`

The root path. This will default to the install path.

### `paths.classes`

An array of class paths and suffixes to pass on to the locator used to find classes.

### `paths.langs`

An array of class paths and suffixes to pass on to the locator used to find language files.

### `paths.views`

An array of class paths and suffixes to pass on to the locator used to find view scripts. If using a view that does not support scripts or files, then this will be ignored.

### `views.cli`

The CLI view path, relative to the base view paths.

### `views.web`

The web view path, relative to the base view paths.

### `views.jsonp.callback`

The name of the JSONP callback parameter. If using a JSON content type and this parameter is found in the request, it automatically switches to JSONP.

### `views.map`

The content type to view instance mapping. This is used to determine which view will be used to render a given content type. If the content type is not recognised, it defaults to using `text/html` and the `Php` view.

If you want to use a custom view script engine, you just map `text/html` to the desired view engine. The only requirement is that your view engine implement `Europa\View\ViewInterface`. If you support scripts and want a script to be automatically chosen for you based on any of the configuration variables, then it must implement `Europa\View\ViewScriptInterface`.

Documentation
-------------

To check out the documentation, just go into the `src` directory. Each directory has a `readme` that corresponds to it. To update the documentation, fork, pull-request, rinse, repeat.

Contributing
------------

To contirbute, just fork and submit pull-requests. Each request will be reviewed and ideally will include corresponding tests and possibly a place in the documentation.

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
