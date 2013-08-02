<?php

namespace Europa\Filter;

trait FilterAware
{
  private $filter;

  public function setFilter(callable $filter)
  {
    $this->filter = $filter;
    return $this;
  }

  public function getFilter()
  {
    return $this->filter;
  }
}