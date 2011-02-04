<?php

class ParamFilter extends \Europa\Controller\FilterAbstract
{
    public function params()
    {
        $params = parent::params();
        foreach ($params as &$param) {
            $param = \Europa\String::create($param)->cast();
        }
        return $params;
    }
}