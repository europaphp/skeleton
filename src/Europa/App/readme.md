App
===

The application layer serves as a component responsible for eliminating unnecessary boilerplate code by bringing all of the necessary components for running your application together into one. It is designed to break apart your application into smaller chunks of code, or modules.

First, we create an instance of your application:

    $app = new Europa\App\App;

For your application to run, you need to add at least one module. By default a module named `main` is added. To run your application, all you need to do is invoke the app instance:

    $app();

Your application can also be configured pretty easily by passing a configuration array to the constructor:

    $app = new Europa\App\App([
        'basePath'         => null,
        'appPath'          => '{basePath}/app',
        'modules'          => [Europa\App\App::DEFAULT_MODULE],
        'defaultViewClass' => 'Europa\View\Php',
        'viewScriptFormat' => ':controller/:action',
        'viewSuffix'       => 'php'
    ]);

Since the `App` component uses `Europa\Config\Config`, you can pass a path to a file if you so desire:

    $app = new Europa\App\App('my/app/config.json');

Up to you. The following configuration options are used:

#### basePath

The `basePath` option tells your application what directory it is in. By default this is calculated to being the parent directory of the script that is running the application if the `basePath` is set to a falsy value.

#### appPath

The `appPath` option tells the application where to look for its modules.

#### modules

The modules specified in the `modules` option are added after the application is instantiated. This is just a shortcut to manually adding modules. Your values can either be a string or array. If it is a string, the key is ignored and the value is used as the module name and the default configuration is used. If your value is an array, your key is used as the module name and the array value is used as the module configuration.

#### defaultViewClass

Specifies the view class to use if a content type cannot be negotiated with the request. This defaults to using PHP views.

#### viewScriptFormat

Specifies the format in which a view script is resolved. Parameters are taken from the request to replace placeholders in the option value, so you can use any available request parameter. This defaults to `:controller/:action`.

#### viewSuffix

The default suffix to use for rendering a view script. Defaults to `php`.

Modules
-------

Modules are chunks of application code that are organised into their own directories. The default module structure is defined as follows:

- app
    - main
        - configs
            - config.json
            - routes.json
        - src
            - SomeNamespace
                - SomeClass.php
            - Controller
                - SomeController.php
        - views
            - controller
                - action.php

Every part of this structure can be customised by providing the module with a custom configuration:

    <?php

    $myModule = new Europa\App\Module('module-name', [
        'bootstrapperPrefix' => 'Bootstrapper\\',
        'bootstrapperSuffix' => '',
        'config'             => 'configs/config.json',
        'routes'             => 'configs/routes.json',
        'srcPaths'           => 'src',
        'viewPaths'          => 'views',
        'requiredModules'    => [],
        'requiredExtensions' => [],
        'requiredClasses'    => [],
        'requiredFunctions'  => []
    ]);

There are a few different ways to add your module to the application instance. The easiest, if you are using the default module configuration is to pass it as part of the application configuration. Failing this, you can append it using only its name:

    $app[] = 'module-name';

You can add it by name and pass it a configuration:

    $app['module-name'] = [
        // config
    ];
    
    $app['module-name'] = 'path/to/config.json';

Or you can add the instance:

    $app[] = new Europa\App\Module('module-name', 'path/to/config.json');

#### bootstrapperPrefix

By default, the bootsrapper class for the module is derived from a classname format of the module name. For example, `module-name` would become `ModuleName`. The prefix is prepended to this in order to allow the namespacing of module bootstrappers.

#### bootstrapperSuffix

Specifies the suffix to append to the module bootstrapper classname during resolution.

#### config

Specifies the user-configuration for the module. This can be anything that the `Europa\Config\Config` component can take in its constructor such as a raw array of configuration values. By default, this is a relative path to the module JSON configuration file.

#### routes

The module routes that get applied to the main router. This value can be anything that can be passed to `Europa\Router\Router->import()` such as a PHP array of routes. By default, this is a relative path to a routes JSON file.

#### srcPaths

The path, or paths, relative to the module directory that class files are autoloaded from. Defaults to `src`.

#### viewPaths

The path, or paths, relative to the module directory that view files are loaded from. Defaults to `views`.

#### requiredModules

Specifies the modules that this module requires in order to function. These modules are checked for existence and bootstrapped prior to bootstrapping the dependent module. If the required modules cannot be resolved, an exception is raised.

#### requiredExtensions

You can specify if your module requires any extensions in order to function. If any of the specified extensions are not found, an exception is raised.

#### requiredClasses

Like extensions, you can also require that some classes exist in order for your module to function. If they are not found… you get the point.

#### requiredFunctions

Aaaand like classes, you can require some functions be defined. Exceptions are thrown, etc.

### Bootstrapping

Modules are bootstrapped using a bootstrapper class. By default, the bootstrapper is contained under the `Bootstrapper` namespace and the class name is the same name as your module, but camel-capped. This means the bootstrapper for `module-name` would be `Bootstrapper\ModuleName`.

For more information on how bootstrappers work, see the documentation for the `Europa\Bootstrapper` component.

### Configuration

Module configuration is taken from the `configs/config.json` file. This can be changed by updating the `config` configuration option.

The options from this file are imported to the main configuration and organised in a namespace defined by the module name. No doubt you will need to access the configuration on your module.

You can get a single value from your module:

    $app['module-name']['my-config-option'];

Or you can get the whole config object:

    $app['module-name']->config();

### Routes

Routes configuration is taken from the `configs/routes.json` file. This can be changed by updating the `routes` configuration option.

The routes in this file are appended to the global route listing since these routes affect the application as a whole. Since that is the case, you can access the routes using the main router.

    $app->getServiceContainer()->router['module-name-route'];

### Autoload Paths

Autoload paths are taken from the `srcPaths` configuration option and defaults to `src`. This can either be a string or array of paths relative to the module install path.

### View Paths

View paths are taken from the `viewPaths` configuration option and defaults to `views`. This can either be a string or array of paths relative to the module install path.

Application Events
------------------

During the course of invoking your application some events will be triggered that you can bind handlers to. To bind handlers to the application service, you use the service container bound to it:

    $app->event->bind('route', function() {
        // do something
    });

#### route

Triggered prior to routing to a controller. The application instance is passed as the only argument to the event handler.

#### action

Triggered prior to actioning the controller. The application instance is passed as the first argument to the event handler and the controller as the second.

#### render

Triggered prior to rendering the view with the context returned from the action. The application instance is passed as the first argument and the context returned from the controller is the second.

#### send

Triggered prior to sending the response. The application instance is passed as the first argument and the rendered response as the second.

#### done

Triggered just before the dispatching completes. The application instance is the only passed argument.

Saving and Accessing Your Application Instance
----------------------------------------------

Since your application instance is the gateway to your application, it is likely that you'll need to access it quite often. You can save it for later use by calling the `save()` method and optionally passing it a name.

    $app->save();

Now you can get it later even when `$app` is out of scope:

    $app = Europa\App\App::get();

There may be cases where you have to access it by a specific name, or want to have multiple instances that are accessible. You can just pass a name you want to use:

    $app->save('my-instance');

And use the same name later:

    Europa\App\App::get('my-instance');

Accessing Services in the Container
-----------------------------------

Although you can use `getServiceContainer()` to access the applicaiton service container, if you only need to get a service from the container you can simply access the service you want as a property on the application instance.

    $app->router === $app->getServiceContainer()->router;

Overriding Application Services
-------------------------------

The application instance uses a service container to get everything it needs to run your app. This defaults to using an instance of `Europa\Di\ServiceContainer` configured with `Europa\App\AppConfiguration`. You can use any instance of `Europa\Di\ServiceContainer` as long as it provides the services defined by `Europa\App\AppConfigurationInterface`.

If you are writing your own configuration, you can extend the base configuration or write your own. Either way, the app instance will make sure that the services defined in `Europa\App\AppConfigurationInterface` are provided.