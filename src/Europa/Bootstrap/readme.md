Bootstrapping
=============

You may know the term "bootstrap". If not, a bootstrap is responsible for preparing your application to run. The `Boot` component is responsible for the organisation of code that is intended to get your application to the point to where it can start running.

Setting Up Your Application
---------------------------

The first step in getting your application booted is to have a file that initiates bootstrapping. Lets call it `bootstrap.php`.
    
    <?php
    
    // Simply including the loader sets up a default autoloader
    // for anything in the directory that Europa's source files
    // are installed in.
    include 'lib/Europa/Fs/Loader.php';

Nothing is stopping you from filling this file with a bunch of crap and calling it good, but it is recommended you that you use a bootstrapping method to organise and conventionalise the way you set up your app.

* The default bootstrapper sets up autoloading for you based on the paths that you provide to it. If you are not using it, then you must set your own paths and register autoloading using the loader on your own terms. *

### Bootstrapping Using a Directory of Files

The `Files` bootstrap class is responsible for taking a bunch of PHP files and loading them alphabetically. Say we have a directory called `boot` that has all of our bootstrap files.

    use Europa\Bootstrap\Files;
    
    $bootstrapper = new Files;
    $bootstrapper->addFile($someFile);
    $bootstrapper->addFiles($arrayOfFilePaths);
    $bootstrapper->bootstrap();

If you want, you can use a `Finder` to get a list of files for you:

    use Europa\Bootstrap\Files;
    use Europa\Fs\Finder;

    $finder = new Finder;
    $finder->files();
    $finder->in(__DIR__);
    $finder->is('/bootstrap\.php$/');

    $bootstrapper = new Files;
    $bootstrapper->addFiles($finder->toArray());
    $bootstrapper->boot();

### Bootstrapping Using an Object

The `Provider` bootstrap abstract class is responsible for taking the child class and calling each public, non-inherited method in the order in which it is defined.

Take the following subclass:

    <?php
    
    use Europa\Bootstrap\Provider;
    
    class MyBootstrapper extends Provider
    {
        public function errorReporting()
        {
            ...
        }
        
        public function defaultDateTime()
        {
            ...
        }
    }

You can then add that to your bootstrap:

    
    $bootstrapper = new MyBootstrapper;
    $bootstrapper->bootstrap();

### Bootstrapping Using Multiple Bootstrapping Methods

There may be instances where you need to use more than one bootstrapper. This is where you'd use the `Chain` class.

    use MyBootstrapper;
    use Europa\Bootstrap\Chain;
    use Europa\Bootstrap\Files;
    use Europa\Fs\Finder;

    $finder = new Finder;
    $finder->files();
    $finder->in(__DIR__);
    $finder->is('/bootstrap\.php$/');

    $files = new Files;
    $files->addFiles($finder->toArray());

    $provider = new MyBootstrapper;
    
    $chain = new Chain;
    $chain->add($files);
    $chain->add($provider);
    $chain->bootstrap();

What's really nice about all of this is that you can organise all of your bootstrap code in to files and / or classes still have the convenience of only having to include a single file.