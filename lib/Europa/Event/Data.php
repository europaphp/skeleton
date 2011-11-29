<?php

namespace Europa\Event;

class Data implements DataInterface
{
	private $data = array();

	public function __construct($data = array())
	{
		if (is_object($data) || is_array($data)) {
			foreach ($data as $name => $value) {
				$this->data[$name] = $value;
			}
		}
	}

	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	public function __get($name)
	{
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}
		return null;
	}
}
