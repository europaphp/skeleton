<?php

namespace Test\Provider\Controller;
use Europa\Controller\AbstractController;

class BadController extends AbstractController
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