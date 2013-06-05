<?php

require_once __DIR__ . '/../vendor/autoload.php';

$container = new Europa\Di\Container;
$container->configure(new Europa\App\AppConfiguration);
$container('loader')->register();
$container('modules')
    ->add(new Europaphp\Help)
    ->add(new Europaphp\Main)
    ->add(new Europaphp\Tests([
        'global-analysis-paths' => [__DIR__ . '/../src']
    ]));

$app = $container('app');
$app();