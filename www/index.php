<?php

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Europa\Di\Container;
$container->configure(new Europa\App\AppConfiguration);
$container->get('loader')->register();
$container->get('modules')
    ->add(new Europaphp\Help)
    ->add(new Europaphp\Main)
    ->add(new Europaphp\Tests([
        'global-analysis-paths' => [__DIR__ . '/../src']
    ]));

$container->get('app')->dispatch();