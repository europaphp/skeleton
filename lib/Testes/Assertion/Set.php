<?php

namespace Testes\Assertion;
use Countable;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

class Set implements Countable, IteratorAggregate
{
    private $assertions;
    
    public function __construct(Traversable $assertions = null)
    {
        // the internal iterator
        $this->assertions = new ArrayIterator;
        
        // if constructed with assertions add them
        if ($assertions) {
            $this->addTraversable($assertions);
        }
    }
    
    public function add(AssertionInterface $assertion)
    {
        $this->assertions[] = $assertion;
        return $this;
    }
    
    public function addTraversable(Traversable $assertions)
    {
        foreach ($assertions as $assertion) {
            $this->add($assertion);
        }
        return $this;
    }
    
    public function isFailed()
    {
        return $this->getFailed()->count() > 0;
    }
    
    public function isPassed()
    {
        return $this->getFailed()->count() === 0;
    }
    
    public function getFailed()
    {
        $failed = new ArrayIterator;
        foreach ($this as $assertion) {
            if ($assertion->failed()) {
                $failed[] = $assertion;
            }
        }
        return $failed;
    }
    
    public function getPassed()
    {
        $passed = new ArrayIterator;
        foreach ($this as $assertion) {
            if ($assertion->passed()) {
                $passed[] = $assertion;
            }
        }
        return $passed;
    }
    
    public function count()
    {
        return $this->assertions->count();
    }
    
    public function getIterator()
    {
        return $this->assertions;
    }
}