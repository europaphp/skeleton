<?php

namespace Europa\Reflection\DocTag;
use Europa\Reflection\ClassReflector;
use Europa\Reflection\DocTag;
use Europa\Reflection\Exception;

/**
 * Represents a docblock filter tag.
 * 
 * @category Reflection
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class FilterTag extends DocTag
{
	/**
	 * Returns the name of the tag.
	 * 
	 * @return string
	 */
	public function tag()
	{
		return 'filter';
	}

	/**
	 * Returns the class name of the filter.
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->tagString;
	}

	/**
	 * Returns an instance of the filter.
	 * 
	 * @param array $args The arguments, if any, to pass to the filter's constructor.
	 * 
	 * @return \Europa\Controller\FilterInterface
	 */
	public function getInstance(array $args = array())
	{
		$reflector = new ClassReflector($this->getName());
		if ($reflector->hasMethod('__construct')) {
			return $reflector->newInstanceArgs($args);
		}
		return $reflector->newInstance();
	}

	/**
	 * Overridden to provide doc string validation.
	 * 
	 * @param string $tagString The tag string to parse.
	 * 
	 * @return void
	 */
	public function parse($tagString)
	{
        // use default parsing for generating the name and doc string
        parent::parse($tagString);

        // a filter class must be specified
        if (!$this->tagString) {
            throw new Exception('A filter must be specified.');
        }
	}
}