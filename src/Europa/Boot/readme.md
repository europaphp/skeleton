Bootstrapping
=============

You may know the term "bootstrap". If not, a bootstrap is responsible for preparing your application to run. The `Boot` component is responsible for the organisation of code that is intended to get your application to the point to where it can start running.

Setting Up Your Application
---------------------------

The first step in getting your application booted is to have a file that initiates bootstrapping. Lets call it `boot.php`.
    
    <?php
    
    use Europa\Fs\Loader;
    
    include 'lib/Europa/Fs/Loader.php';
    
    $loader = new Loader;
    $loader->getLocator()->addPath(__DIR__ . '/classes');
    $loader->register();

We first include `Europa\Fs\Loader`. This ensures autoloading is registered for whatever path Europa is installed in. The next step is to make sure we register any other paths that we have classes in because we will need them available in the bootstrap process.

Nothing is stopping you from filling this file with a bunch of crap and calling it good, but it is recommended you that you use a bootstrapping method to organise and conventionalise the way you set up your app.

### Bootstrapping Using a Directory of Files

The `Files` bootstrap class is responsible for taking a bunch of PHP files and loading them alphabetically. Say we have a directory called `boot` that has all of our bootstrap files.

    use Europa\Boot\Files;
    
    $boot = new Files(__DIR__ . '/boot');
    $boot->boot();

This will load all `.php` files in the `boot` directory alphabetically. If we don't have `.php` files but want to load all `.inc` files that are prefixed with `boot_` (not sure why), then we can by specifying an optional regex as the second argument:

    $boot = new Files(__DIR__ . '/boot', '/^boot_(.*?)\.inc$/');

### Bootstrapping Using an Object

The `Provider` bootstrap abstract class is responsible for taking the child class and calling each public, non-inherited method in the order in which it is defined.

Take the following subclass:

    <?php
    
    namespace Boot;
    use Europa\Boot\Provider;
    
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

    use Boot\MyBootstrapper;
    
    $boot = new MyBootstrapper;
    $boot->boot();

### Bootstrapping Using Multiple Bootstrapping Methods

There may be instances where you need to use more than one bootstrapper. This is where you'd use the `Chain` class.

    use Boot\MyBootstrapper;
    use Europa\Boot\Chain;
    use Europa\Boot\Files;
    
    $boot = new Chain;
    $boot->add(new Files(__DIR__ . '/boot'));
    $boot->add(new MyBootstrapper);
    $boot->boot();

### Completing Your Bootstrap File

If we put all this together, our `boot.php` file may look like the following:

    <?php
    
    use Boot\MyBootstrapper;
    use Europa\Boot\Chain;
    use Europa\Boot\Files;
    use Europa\Fs\Loader;
    
    include 'lib/Europa/Fs/Loader.php';
    
    $loader = new Loader;
    $loader->getLocator()->addPath(__DIR__ . '/classes');
    $loader->register();
    
    $boot = new Chain;
    $boot->add(new Files(__DIR__ . '/boot'));
    $boot->add(new MyBootstrapper);
    $boot->boot();

Now all you have to do when you want to setup your application is include the `boot.php` file.

For a complete example, see the default [boot.php](../../app/boot.php) file.