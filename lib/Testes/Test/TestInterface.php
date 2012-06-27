<?php

namespace Testes\Test;
use Testes\RunableInterface;

interface TestInterface extends RunableInterface
{
    /**
	 * Creates an assertion.
	 * 
	 * @param bool   $expression  The expression to test.
	 * @param string $description The description of the assertion.
	 * @param int    $code        A code if necessary.
	 * 
	 * @return TypeInterface
	 */
	public function assert($expression, $description = null, $code = Assertion::DEFAULT_CODE);
}