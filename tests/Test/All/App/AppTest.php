<?php

namespace Test\All\App;
use Europa\App\App;
use Europa\Request\Http;

class AppTest extends \Testes\Test\UnitAbstract
{
    public function dispatch()
    {
        $app = new App([
            'modules' => [
                'tests/Test/Provider/Module/test-module' => [
                    'version' => '1.0.2'
                ]
            ],
            'defaultModuleConfig' => [
                'configs' => ['configs/config.json']
            ],
            'defaultModuleConfigs' => [
                'europaphp/test-module' => [
                    'version' => '1.0.1'
                ]
            ],
            'viewSuffix'       => 'phtml',
            'viewScriptFormat' => ':controller'
        ]);

        $app->request = new Http;
        $app->request->getUri()->setRequest('test');

        $app->router['test'] = [
            'pattern' => '(?<action>GET) (?<controller>test)'
        ];

        $app->save('app-test');

        $this->assert($app(true) === '1.0.2');
    }
}