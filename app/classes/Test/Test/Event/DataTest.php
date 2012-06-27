<?php

namespace Test\Test\Event;
use Europa\Event\Data;
use Test\Provider\Event\CustomData;
use Testes\Test\Test;

class DataTest extends Test
{
    public function data()
    {
        $data = new Data(array(
            'construct' => true
        ));

        $data->set = true;

        $this->assert($data->construct, 'The data should have been set using the array passed to the constructor.');
        $this->assert($data->set, 'The data should be able to be modified by default.');
    }

    public function custom()
    {
        $data = new CustomData;
        $data->customData = false;
        $this->assert($data->customData, 'The data should not have been modified and should be true.');
    }
}