<?php

namespace Test\All\View;
use Europa\View\Json;
use Testes\Test\UnitAbstract;

class JsonTest extends UnitAbstract
{
    function rendering()
    {
        $view = new Json;
        $data = array(
            'data' => array(
                'val1' => 1,
                'val2' => 2
            ),
            'success' => true
        );
        
        $this->assert(
            $view($data) === '{"data":{"val1":1,"val2":2},"success":true}',
            'The data was not rendered properly.'
        );
    }
}