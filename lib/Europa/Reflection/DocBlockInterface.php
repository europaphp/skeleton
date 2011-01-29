<?php

namespace Europa\Reflection;

interface DocBlockInterface
{
	public function __construct($docString = null);

	public function __toString();

	public function setDescription($description);

	public function getDescription();

	public function addTag(DocTagAbstract $tag);

	public function getTag($name);

	public function compile();
	
	public function parse($docString);
}