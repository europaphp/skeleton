<?php

namespace Europa\Reflection;

abstract class DocBlockAbstract implements DocBlockInterface
{
	protected $description = null;

	protected $tags = array();

	public function __construct($docString = null)
	{
		if ($docString) {
			$this->parse($docString);
		}
	}

	/**
	 * Returns the compiled doc block.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->compile();
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

	public function addTag(DocTagAbstract $tag)
	{
		// used multiple times
		$name = $tag->tag();

		// if the tag is already set, we create multiple of the same one
		// otherwise we just set it
		if (isset($this->tags[$name])) {
			if (!is_array($this->tags[$name])) {
				$this->tags[$name] = array($this->tags[$name]);
			}
			$this->tags[$name][] = $tag;
		} else {
			$this->tags[$name] = $tag;
		}

		return $this;
	}

	/**
	 * Returns the specified tag. If $asArray is true, then even if the
	 * tag is not an array of tags, it is made into one.
	 * 
	 * @param string $name    The tag name to get.
	 * @param bool   $asArray Whether or not to force an array.
	 * 
	 * @return mixed
	 */
	public function getTag($name, $asArray = false)
	{
		if (isset($this->tags[$name])) {
			$tag = $this->tags[$name];
			if ($asArray && !is_array($tag)) {
				return array($tag);
			}
			return $tag;
		}
		return $asArray ? array() : null;
	}

	/**
	 * Reverses the doc block parsing.
	 * 
	 * @return string The compiled doc block.
	 */
	public function compile()
	{
		$str = '/**' . PHP_EOL 
		     . ' * ' . $this->description . PHP_EOL
		     . ' * '. PHP_EOL;
		
		$last = null;
		foreach ($this->tags as $tag) {
			if ($last === $tag->tag()) {
				$str .= ' * ' . PHP_EOL;
			}
			$str .= $tag->__toString() . PHP_EOL;
			$last = $tag->tag();
		}
		return $str . ' */';
	}

	public function parse($docString)
	{
		$lines = $this->_parseTags($docString);
		$lines = $this->_getDocTagsFromLines($lines);
		foreach ($lines as $line) {
			$this->addTag($line);
		}
	}

	private function _parseTags($docString)
	{
		// the unparsed doc lines
		$lines = array();

		// parse out the specified tags
		$tok = strtok($docString, '@');
		while ($tok !== false) {
			$tok     = strtok('@');
			if ($tok) {
				$lines[] = $tok;
			}
		}

		return $lines;
	}

	private function _getDocTagsFromLines(array $lines)
	{
		foreach ($lines as &$line) {
			$line = $this->_getDocTagFromLine($line);
		}
		return $lines;
	}

	private function _getDocTagFromLine($line)
	{
		$parts  = explode(' ', $line, 2);
		$name   = $parts[0];
		$string = isset($parts[1]) ? $parts[1] : null;
		$class  = __NAMESPACE__ . '\\DocTag\\' . ucfirst($name) . 'Tag';
		return new $class($string);
	}
}