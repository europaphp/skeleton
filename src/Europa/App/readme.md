App
===

The application layer serves as a component responsible for eliminating unnecessary boilerplate code by bringing all of the necessary components for running your application together into one. It is designed to break apart your application into smaller chunks of code, or modules. In order for your application to run, you need at least one module.

To add modules to your application, you would access the `modules` dependency and append items to it. At its most basic, all you need to specify is the module directory name:

    <?php

    $app = new Europa\App\App;
    $app->getServiceContainer()->modules[] = 'main';

To run your application, all you need to do is invoke the app instance:

    $app();

Modules
-------

Modules are chunks of application code that are organised into their own directories. The default module structure is defined as follows:

- app
    - module-name
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

    $app->getServiceContainer()->modules[] = new Europa\App\Module('module-name', [
        'bootstrapperNs' => 'Bootstrapper',
        'config'         => 'configs/config.json',
        'routes'         => 'configs/routes.json',
        'src'            => 'src',
        'views'          => 'views'
    ]);

By default, if you only specify a module name, the default module config shown above is used.

### Bootstrapping

Modules are bootstrapped using a bootstrapper class. By default, the bootstrapper is contained under the `Bootstrapper` namespace and the class name is the same name as your module, but camel-cased. This means the bootstrapper for `module-name` would be `Bootstrapper\ModuleName`.

### Configuration

Module configuration is taken from the `configs/config.json` file. This can be changed by updating the `config` configuration option.

The options from this file are imported to the main configuration and organised in a namespace defined by the module name. You can access it as follows:

    $app->getServiceContainer()->config->modules['module-name'];

### Routes

Routes configuration is taken from the `configs/routes.json` file. This can be changed by updating the `routes` configuration option.

The routes in this file are appended to the global route listing since these routes affect the application as a whole. Since that is the case, you can access the routes using the main router.

    $app->getServiceContainer()->router['module-name-route'];

### Autoload Paths

Autoload paths are taken from the `src` configuration option and defaults to `src`. This can either be a string or array of paths relative to the module install path.

### View Paths

View paths are taken from the `views` configuration option and defaults to `views`. This can either be a string or array of paths relative to the module install path.

### Requiring Other Modules

Sometimes one module's behavior relies on another module. If this is the case, you can tell one module to require another module. During bootstrapping, the module manager will attempt to bootstrap all dependencies before the dependant. If a required module does not exist, an exception is raised.

    <?php

    $modules   = new Europa\App\ModuleManager;
    $modules[] = 'some-module';
    $modules[] = 'some-other-module';

    $modules['some-module']->requires('some-other-module');

    $modules->bootstrap();

Customizing Application Behavior
--------------------------------

The default setup will be good for most people, however, you can modify parts of the application runner by passing in configuration options to the constructor:

    $app = new Europa\App\App([
        'appPath'          => '../app',
        'defaultViewClass' => 'Europa\View\Php',
        'viewScriptFormat' => ':controller/:action',
        'viewSuffix'       => 'php'
    ]);

Due to the nature of how the `Config` component works, you could even specify a file:

    $app = new Europa\App\App('/path/to/config/file.ini');

Or closure:

    $app = new Europa\App\App(function() {
        return [ ... ];
    });

Or custom configuration class that either defines public properties or is traversable:

    $app = new Europa\App\App(new My\Config);

If you need to access the configuration, you can do so by getting it from the service container just like we did with the modules:

    // Returns the application path.
    $app->getServiceContainer()->config->paths->app;

Application Configuration Options
---------------------------------

`appPath`

The path to the application folder where the modules are kept. This defaults to `../app` which means that, by default, the file running the application must also be the `cwd()`.

`defaultViewClass`

The default view class to use. This defaults to using `Europa\View\Php`, but this may not even be necessary.

`viewScriptFormat`

If using a view that implements `Europa\View\ViewScriptInterface`, then the `->setScript()` method will be passed this value. You can substitute request parameters by prefixing the request parameter name with a colon. For example, the default value is `:controller/:action`.

`viewSuffix`

If using a view that implements `Europa\View\ViewScriptInterface`, then the view suffix will be set to this value. The default suffix is `php`.

Application Events
------------------

During the course of invoking your application some events will be triggered that you can bind handlers to.

`route`

Triggered prior to routing to a controller.

`action`

Triggered prior to actioning the controller.

`render`

Triggered prior to rendering the view with the context returned from the action.