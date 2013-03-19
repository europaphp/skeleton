<?php

namespace Europa\Filter;

interface FilterAwareInterface
{
    public function setFilter(callable $filter);

    public function getFilter();
}