<?php

use Europa\App\AppConfiguration;
use Europa\Di\Container;

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Container;
$container->configure(new AppConfiguration);
$container->get('loader')->register();
$container->get('modules')
    ->add(new Europaphp\Help)
    ->add(new Europaphp\Main)
    ->add(new Europaphp\Tests);

$container->get('app')->dispatch();