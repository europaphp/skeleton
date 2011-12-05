<?php

namespace Test\Event;
use Testes\Test\Test;
use Europa\Event\Data;
use Europa\Event\DataInterface;
use Europa\Event\Dispatcher;
use Europa\Event\CallbackEvent;

class DispatcherTest extends Test
{
	public function single()
	{
		$dispatcher = new Dispatcher;

		$dispatcher->bind('test', new CallbackEvent(function(DataInterface $data) {
			$data->one = true;
		}));

		$data = new Data;
		$dispatcher->trigger('test', $data);

		$this->assert($data->one, 'The dispatcher did not trigger the custom event.');
	}

	public function double()
	{
		$dispatcher = new Dispatcher;

		$dispatcher->bind('test', new CallbackEvent(function(DataInterface $data) {
			$data->one = true;
		}));

		$dispatcher->bind('test', new CallbackEvent(function(DataInterface $data) {
			$data->two = true;
		}));

		$data = new Data;
		$dispatcher->trigger('test', $data);
		
		$this->assert($data->one && $data->two, 'The dispatcher did not trigger both events.');
	}

	public function namespaced()
	{
		$dispatcher = new Dispatcher;

		$dispatcher->bind('test.one', new CallbackEvent(function(DataInterface $data) {
			$data->one = true;
		}));

		$dispatcher->bind('test.two', new CallbackEvent(function(DataInterface $data) {
			$data->two = true;
		}));

		$data = new Data;
		$dispatcher->trigger('test', $data);

		$this->assert($data->one && $data->two, 'The dispatcher did not trigger both separately namespaced events.');
	}

	public function unbindSingleNamespaced()
	{
		$dispatcher = new Dispatcher;

		$dispatcher->bind('test.one', new CallbackEvent(function(DataInterface $data) {
			$data->one = true;
		}));

		$dispatcher->bind('test.two', new CallbackEvent(function(DataInterface $data) {
			$data->two = true;
		}));

		$data = new Data;
		$dispatcher->unbind('test.two');
		$dispatcher->trigger('test', $data);

		$this->assert($data->one && !$data->two, 'The dispatcher did not unbind the second event.');
	}

	public function unbindNamespacedUsingUnnamespacedName()
	{
		$dispatcher = new Dispatcher;

		$dispatcher->bind('test.one', new CallbackEvent(function(DataInterface $data) {
			$data->one = true;
		}));

		$dispatcher->bind('test.two', new CallbackEvent(function(DataInterface $data) {
			$data->two = true;
		}));

		$data = new Data;
		$dispatcher->unbind('test');
		$dispatcher->trigger('test', $data);

		$this->assert(!$data->one && !$data->two, 'The dispatcher did not unbind both events.');
	}
}
