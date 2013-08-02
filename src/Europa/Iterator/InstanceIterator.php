<?php

namespace Europa\Iterator;
use Europa\Exception\Exception;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

class InstanceIterator implements IteratorAggregate
{
  private $instances;

  public function __construct(Traversable $instances, $instanceof = null)
  {
    foreach ($instances as $instance) {
      if (!is_object($instance)) {
        Exception::toss('The item at offset "%s" must be an object instance. Type of "%s" supplied.', $this->key(), gettype($instance));
      }

      if ($instanceof && !$instance instanceof $instanceof) {
        Exception::toss('The instance at offset "%s" must be an instance of "%s". Instance of "%s" supplied.', $this->key(), $this->instanceof, get_class($instance));
      }
    }

    $this->instances = $instances;
  }

  public function getIterator()
  {
    return new ArrayIterator($this->instances);
  }
}