<?php

namespace Europa\Fs\Iterator;
use FilterIterator;
use Iterator;

class CallbackFilterIterator extends FilterIterator
{
  private $filters = [];

  public function __construct(Iterator $iterator, array $filters)
  {
    $this->filters = $filters;
    parent::__construct($iterator);
  }

  public function accept()
  {
    foreach ($this->filters as $filter) {
      if ($filter($this->current()) === false) {
        return false;
      }
    }
    return true;
  }
}