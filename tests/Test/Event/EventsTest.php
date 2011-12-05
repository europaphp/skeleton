<?php

namespace Test\Event;
use Testes\Test\Test;

class EventsTest extends Test
{
	public function callback()
	{
		$callback = new \Europa\Event\CallbackEvent(function(\Europa\Event\Data $data) {
			$data->triggered = true;
		});

		$data = new \Europa\Event\Data(array(
			'triggered' => false
		));
		
		$callback->trigger($data);
		
		$this->assert($data->triggered === true, 'The data was not modified during triggering.');
	}

	public function custom()
	{
		$event = new \Provider\Event\CustomEvent;

		$data = new \Europa\Event\Data(array(
			'triggered' => false
		));

		$event->trigger($data);
		
		$this->assert($data->triggered === true, 'The data was not modified during triggering.');
	}
}
