Application
===========

The application layer serves as a component responsible for setting up and running your application.

Setting Up Your Application
---------------------------

You may know the term "bootstrap". If not, a bootstrap is responsible for preparing your application to run. The first step in getting your application booted is to have a file that initiates bootstrapping. Lets call it `boot.php`.
    
    <?php
    
    use Europa\Fs\Loader;
    
    include 'lib/Europa/Fs/Loader.php';
    
    $loader = new Loader;
    $loader->getLocator()->addPath(__DIR__ . '/classes');
    $loader->register();

We first include `Europa\Fs\Loader`. This ensures autoloading is registered for whatever path Europa is installed in. The next step is to make sure we register any other paths that we have classes in because we will need them available in the bootstrap process.

Nothing is stopping you from filling this file with a bunch of crap and calling it good, but it is recommended you that you use a bootstrapping method to organise and conventionalise the way you set up your app.

### Bootstrapping Using a Directory of Files

The `BootFiles` bootstrap class is responsible for taking a bunch of PHP files and loading them alphabetically. Say we have a directory called `boot` that has all of our bootstrap files.

    use Europa\App\BootFiles;
    
    $boot = new BootFiles(__DIR__ . '/boot');
    $boot->boot();

This will load all `.php` files in the `boot` directory alphabetically. If we don't have `.php` files but want to load all `.inc` files that are prefixed with `boot_` (not sure why), then we can by specifying an optional regex as the second argument:

    $boot = new BootFiles(__DIR__ . '/boot', '/^boot_(.*?)\.inc$/');

### Bootstrapping Using an Object

The `BootClass` bootstrap abstract class is responsible for taking the child class and calling each public, non-inherited method in the order in which it is defined.

Take the following subclass:

    <?php
    
    namespace Boot;
    use Europa\App\BootClass;
    
    class MyBootstrapper extends BootClass
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

There may be instances where you need to use more than one bootstrapper. This is where you'd use the `BootChain` class.

    use Boot\MyBootstrapper;
    use Europa\App\BootChain;
    use Europa\App\BootFiles;
    
    $boot = new BootChain;
    $boot->add(new BootFiles(__DIR__ . '/boot'));
    $boot->add(new MyBootstrapper);
    $boot->boot();

### Completing Your Bootstrap File

If we put all this together, our `boot.php` file may look like the following:

    <?php
    
    use Boot\MyBootstrapper;
    use Europa\App\BootChain;
    use Europa\App\BootFiles;
    use Europa\Fs\Loader;
    
    include 'lib/Europa/Fs/Loader.php';
    
    $loader = new Loader;
    $loader->getLocator()->addPath(__DIR__ . '/classes');
    $loader->register();
    
    $boot = new BootChain;
    $boot->add(new BootFiles(__DIR__ . '/boot'));
    $boot->add(new MyBootstrapper);
    $boot->boot();

Now all you have to do when you want to setup your application is include the `boot.php` file.

For a complete example, see the default [boot.php](../../app/boot.php) file.

Running Your Application
------------------------

The `Europa\App\App` class was created so that there is an easy way to make the necessary components work together covering 99% of the use cases out there.

The default application ties together 5 components:

- [Controller](Controller)
- [Request](Request)
- [Response](Response)
- [Router](Router)
- [View](View)

Each component is required by the application and passed to the constructor. This should be done using a DI container.

By default, you are provided with a class called `Container\Europa` which contains the necessary components to easily run your app.

    <?php
    
    use Container\Europa;
    
    include 'boot.php';
    
    Europa::fetch()->get('app')->run();

If you were to set up the application class manually, you would have to set up each dependency. Using a container solves this problem and provides you with each component - including the application - ready to go out of the box.

More information on [Dependency Injection Containers](Di).

Specifying the Controller Request Parameter
-------------------------------------------

By default, the controller is resolved using the `controller` request parameter. This can be set by the router, or directly set on the request. If you need to change this to, say, `ctrl` or something else, you do so by telling the application.

    Europa::fetch()->get('app')->setKey('ctrl');
