<?php

namespace Test\View;
use Europa\Unit\Test\Test;
use Europa\View\Json as JsonView;

class Json extends Test
{
    function rendering()
    {
        $view          = new JsonView;
        $view->data    = array('val1' => 1, 'val2' => 2);
        $view->success = true;
        $this->assert($view->render() === '{"data":{"val1":1,"val2":2},"success":true}', 'The data was not rendered properly.');
    }
}