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
    ->add(new Europaphp\Tests([
        'global-analysis-paths' => [__DIR__ . '/../src']
    ]));

$container->get('app')->dispatch();