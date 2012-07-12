<?php

namespace Testes\Suite;
use Traversable;
use Testes\RunableInterface;

interface SuiteInterface extends RunableInterface
{
    public function addTest(RunableInterface $test);
    
    public function addTests(Traversable $tests);
}