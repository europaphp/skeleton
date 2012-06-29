Fs
==

Filesystem components and tools.

Directory
---------

Represents a directory. Offers functionality that isn't available by default.

File
----

Represents a file. Offers functionality that isn't available by default.

Finder
------

A file / directory finder. Great for filtering, finding and paging groups of files.

Item
----

Base filesystem object used by `Directory` and `File`.

Loader
------

The loader component is the class loader implementation used for autoloading. It requires a locator for locating class files.

    <?php
    
    use Europa\Fs\Loader;
    
    $loader = new Loader;
    $loader->getLocator()->addPath('/base/path/to/classes');
    $loader->register();

Locator
-------

Locates files based on a given base path and extension (or suffix). Take the following directory structure:

- /some/path1/sub/file1.txt
- /some/path1/sub/file1.ini
- /some/path2/sub/file1.ini
- /some/path2/sub/file2.txt

The following code could be applied:

    <?php
    
    use Europa\Fs\Locator;
    
    $locator = new Locator;
    $locator->addPath('/some/path1', 'ini');
    $locator->addPath('/some/path2', 'ini');
    
    // /some/path1/file1.ini
    $locator->locate('sub/file1');
    
    // false
    $locator->locate('sub/file2');

The locator is very useful for situations where you may have multiple paths that you need to locate a file with a conventional name from such as module or plugin systems.

Since the locator is used in multiple parts of the system, it implements the `LocatorInterface`. This means anywhere that needs a locator, you can also write your own if you need to. This can be handy if you need to locate a file on the network and cache it locally before its path is returned.
