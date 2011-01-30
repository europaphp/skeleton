<?php

namespace Europa\Reflection\DocTag;

class PreFilterTag extends \Europa\Reflection\DocTag
{
	protected $class;

	public function tag()
	{
		return 'preFilter';
	}

	public function setClass($class)
	{
		$this->class = $class;
		return $this;
	}

	public function getClass()
	{
		return $this->class;
	}

	public function parse($tagString)
	{
		parent::parse($tagString);
		$this->class = trim($this->tagString);
	}
}