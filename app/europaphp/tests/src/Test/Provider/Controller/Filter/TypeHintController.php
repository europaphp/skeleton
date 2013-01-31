<?php

namespace Test\Provider\Controller\Filter;
use DateTime;
use Europa\Controller\ControllerAbstract;

class TypeHintController extends ControllerAbstract
{
    public $requiredParameter;

    public $optionalParameter;

    /**
     * @filter Europa\Controller\Filter\TypeHint
     */
    public function test(
        DateTime $requiredParameter,
        DateTime $optionalParameter = null,
        $some = 'normal value'
    ) {
        $this->requiredParameter = $requiredParameter;
        $this->optionalParameter = $optionalParameter;
    }
}