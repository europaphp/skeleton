<?php

namespace Testes\Coverage;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class File implements Countable, IteratorAggregate
{
    private $file;
    
    private $lines;
    
    private $tested;
    
    private $untested;
    
    private $dead;
    
    private $ignored;
    
    private $result;
    
    public function __construct($file, CoverageResult $result)
    {
        $real = realpath($file);
        
        if (!$real) {
            throw new InvalidArgumentException('The file "' . $file . '" does not exist.');
        }
        
        $this->file     = $real;
        $this->lines    = new ArrayIterator;
        $this->tested   = new ArrayIterator;
        $this->untested = new ArrayIterator;
        $this->dead     = new ArrayIterator;
        $this->ignored  = new ArrayIterator;
        $this->result   = $result;
        
        $this->buildLines();
    }
    
    public function __toString()
    {
        return $this->file;
    }
    
    public function getIterator()
    {
        return new $this->lines;
    }
    
    public function count()
    {
        return $this->lines->count();
    }
    
    public function isTested()
    {
        return $this->untested->count() === 0;
    }
    
    public function isUntested()
    {
        return $this->untested->count() > 0;
    }
    
    public function isDead()
    {
        return $this->dead->count() === $this->line->count();
    }
    
    public function isIgnored()
    {
        return $this->ignored->count() === $this->line->count();
    }
    
    public function getTestedLines()
    {
        return $this->tested;
    }
    
    public function getUntestedLines()
    {
        return $this->untested;
    }
    
    public function getDeadLines()
    {
        return $this->dead;
    }
    
    public function getIgnoredLines()
    {
        return $this->ignored;
    }
    
    public function getTestedLineCount()
    {
        return $this->tested->count();
    }
    
    public function getUntestedLineCount()
    {
        return $this->untested->count();
    }
    
    public function getDeadLineCount()
    {
        return $this->dead->count();
    }
    
    public function getIgnoredLineCount()
    {
        return $this->ignored->count();
    }
    
    public function getPercentTested()
    {
        $tested   = $this->getTestedLineCount();
        $untested = $this->getUntestedLineCount();
        $total    = $tested + $untested;
        
        if (!$untested) {
            return 100;
        }
        
        if (!$tested) {
            return 0;
        }
        
        return $tested / $total * 100;
    }
    
    private function buildLines()
    {
        foreach (file($this->file) as $num => $line) {
            ++$num;
            
            $status = $this->result->line($this->file, $num);
            $line   = new Line($line, $num, $status);
            
            // add to all lines
            $this->lines->offsetSet($num, $line);
            
            // detect type of line
            if ($line->isTested()) {
                $this->tested->offsetSet(null, $line);
            } elseif ($line->isUntested()) {
                $this->untested->offsetSet(null, $line);
            } elseif ($line->isDead()) {
                $this->dead->offsetSet(null, $line);
            } elseif ($line->isIgnored()) {
                $this->ignored->offsetSet(null, $line);
            }
        }
    }
}