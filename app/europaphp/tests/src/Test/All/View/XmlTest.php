<?php

namespace Test\All\View;
use Europa\View\Xml;
use Testes\Test\UnitAbstract;

class XmlTest extends UnitAbstract
{
    function rendering()
    {
        $view = new Xml;
        $data = [
            'data' => [
                'val1' => 1,
                'val2' => 2
            ],
            'success' => true
        ];

        $test = '<?xml version="1.0" encoding="UTF-8" ?>
<xml>
  <data>
    <val1>1</val1>
    <val2>2</val2>
  </data>
  <success>true</success>
</xml>';
        
        $this->assert(
            $view($data) === $test,
            'The data was not rendered properly.'
        );
    }
}