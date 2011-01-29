<?php

namespace Europa\Reflection\DocTag;

class BehaviorTag extends \Europa\Reflection\DocTagAbstract
{
	protected $class;

	public function tag()
	{
		return 'behavior';
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