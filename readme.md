EuropaPHP
=========

[![Build Status](https://travis-ci.org/treshugart/EuropaPHP.png)](https://travis-ci.org/treshugart/EuropaPHP)

EuropaPHP is an extremely fast, flexible and lightweight <del>M</del>VC Framework designed for PHP 5.4+.

Why Another PHP Framework?
--------------------------

EuropaPHP is the manifestation of the shortcomings of other PHP frameworks with a strong focus on modularity, scalability, standards, the view/controller relationship and dependency injection. It is specifically designed to be paired with your favourite libraries and it will seamlessly integrate with anything.

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

To get up and running fast you can use composer to install some default modules. Just run:

    composer install --dev

And the modules `europaphp/main` and `europaphp/tests` will be installed. Once installed, you can run the base tests and begin writing your own.

Running Tests
-------------

From the install directory:

    php www/index.php test

Contributing
------------

To contirbute, just fork and submit pull-requests. Each request will be reviewed and ideally include corresponding tests. If there are any updates to the API updating the documentation is also desirable.

License
-------

Copyright (c) 2005-2011 Trey Shugart

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
