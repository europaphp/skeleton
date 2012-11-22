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

The loader component is the class loader implementation used for autoloading.

    $loader = new Europa\Fs\Loader;
    $loader->register();

You can also manually load class files with it:

    $loaded = $loader('My\Class\File');

If you want to find classes in a directory other than the Europa install path, you must use a locator  that contains a list of paths that you want to use.

    $loader->setLocator(new Europa\Fs\Locator);
    $loader->getLocator()->addPaths([
        '/path/to/one/dir',
        '/path/to/another/dir'
    ]);

If you add a locator, it will look in the defined paths before looking in the default install path.

The allows anything that is `callable`:

    $loader->setLocator(function($class) {
        return '/path/to/classes/' . str_replace('\\', DIRECTORY_SEPARATOR, $class);
    });

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
    $locator('sub/file1');
    
    // false
    $locator('sub/file2');

The locator is very useful for situations where you may have multiple paths that you need to locate a file with a conventional name from such as module or plugin systems.
