<?php

namespace Europa\Reflection;

class ClassReflector extends \ReflectionClass implements Reflectable
{
	public function getDocBlock()
	{
		return new DocBlock($this->getDocComment());
	}
}