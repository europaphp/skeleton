<?php

namespace Test\All\View;
use Europa\View\Json as JsonView;
use Testes\Test\UnitAbstract;

class Json extends UnitAbstract
{
    function rendering()
    {
        $view = new JsonView;
        $data = array(
            'data' => array(
                'val1' => 1,
                'val2' => 2
            ),
            'success' => true
        );
        
        $this->assert(
            $view->render($data) === '{"data":{"val1":1,"val2":2},"success":true}',
            'The data was not rendered properly.'
        );
    }
}