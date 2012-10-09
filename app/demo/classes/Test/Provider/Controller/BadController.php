<?php

namespace Test\Provider\Controller;
use Europa\Controller\RestController;

class BadController extends RestController
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