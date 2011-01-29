<?php

namespace Europa\Reflection;

interface DocTagInterface
{
	public function __construct($tagString = null);

	public function __toString();

	public function tag();

	public function parse($tagString);

	public function compile();
}