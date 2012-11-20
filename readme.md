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

There's no wanky setup page and no unnecessary bloat. That's really all there is to it.

Running Tests
-------------

From the install directory:

    php www/index.php test

Command Line Usage
------------------

To checkout available commands, run:

    php www/index.php ?

This will spit out a bunch of command line commands and options and tell you how to use it.

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
