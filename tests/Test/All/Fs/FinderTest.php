<?php

namespace Test\All\Fs;
use Europa\Fs\Finder;
use Testes\Test\UnitAbstract;

class FinderTest extends UnitAbstract
{
    public function inIsAndCount()
    {
        $finder = new Finder;
        $finder->in(__DIR__);
        $finder->is('/\.php$/');

        $this->assert(count($finder) === 4);
    }
}