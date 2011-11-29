<?php

namespace Provider\Event;
use Europa\Event\DataInterface;
use Europa\Event\EventInterface;

class CustomEvent implements \Europa\Event\EventInterface
{
	public function trigger(DataInterface $data)
	{
		$data->triggered = true;
	}
}
