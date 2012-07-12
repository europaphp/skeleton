<?php

namespace Testes\Renderer;
use Testes\RunableInterface;

/**
 * Main interface for output renderers.
 * 
 * @category UnitTesting
 * @package  Testes
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2010 Trey Shugart http://europaphp.org/license
 */
interface RendererInterface
{
    /**
     * Renders the test results.
     * 
     * @param RunableInterface $finder The finder containing the tests.
     * 
     * @return string
     */
    public function render(RunableInterface $test);
}
