Bootstrapping
=============

For those who don't know what bootstrapping is, it is the act of executing a chain of events to set up your application.

Europa comes with a default method of bootstrapping that allows you to define a class with a bunch of public methods that get executed in the order in which they are defined.

    <?php
    
    namespace My;
    
    class Bootstrapper extends Europa\Bootstrapper\BootstrapperAbstract
    {
        public function setErrorReportingLevels()
        {
            ...
        }
        
        public function setUpServiceContainer()
        {
            ...
        }
    }

Using general common sense, you should give your methods descriptive names so that your peers do not have to troll the logic to find out what it actually does.

Internally, the bootstrapper allows all non-magic, public methods, to be called automatically. This means you can still have private methods, or a constructor that accepts arguments.

    private $config = [
        'some' => 'default value'
    ];
    
    public function __construct($config = [])
    {
        $this->initConfig($config);
    }
    
    private function initConfig($config)
    {
        $this->config = new Europa\Config\Config($this->config, $config);
    }

In order to invoke bootstrapping, all you have to do is `__invoke()` the bootstrapper object.

    <?php
    
    $bootstrapper = new My\Bootstrapper;
    $bootstrapper();

Inherently, by design, this means that a bootstrapper is interchangeable with anything that is `callable`.