<?php

namespace Europa\Reflection\DocTag;

class ReturnTag extends \Europa\Reflection\DocTagAbstract
{
	private $type;

	private $description;

	public function tag()
	{
		return 'return';
	}

	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function parse($tagString)
	{
		// use default parsing for generating the name and doc string
		parent::parse($tagString);

		// a doc string must be specified
		if (!$this->tagString) {
			throw new \Europa\Reflection\Exception('A valid return type must be specified. None given.');
		}

		// split in to type/description parts (only two parts are allowed);
		$parts = explode(' ', $this->tagString, 2);

		// set type and description if it exists
		$this->type = $parts[0];
		if (isset($parts[1])) {
			$this->description = $parts[1];
		}
	}
}