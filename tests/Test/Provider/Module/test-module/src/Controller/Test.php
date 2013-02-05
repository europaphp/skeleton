<?php

namespace Controller;
use Europa\App\App;

class Test
{
    public function __invoke()
    {
        return [
            'test' => App::get('app-test')['europaphp/test-module']->version()
        ];
    }
}