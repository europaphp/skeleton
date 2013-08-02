<?php

namespace Europa\Config;
use ArrayAccess;
use Countable;
use Iterator;
use Serializable;

interface ConfigInterface extends ArrayAccess, Countable, Iterator, Serializable
{
  public function import($config);

  public function export();

  public function clear();

  public function setParent(ConfigInterface $config);

  public function getParent();

  public function getRoot();
}