<?php

namespace Test\Provider\Controller;
use Europa\Controller\ControllerAbstract;

class BadController extends ControllerAbstract
{
    /**
     * @filter Some\Non\Existent\Filter
     */
    public function get()
    {

    }

    public function post($id, array $data)
    {

    }
}