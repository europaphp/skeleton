<?php

namespace Europa;

/**
 * Class for ad-hoc application benchmarking.
 * 
 * @category Benchmarking
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Bench
{
    /**
     * The start time.
     * 
     * @var int
     */
    protected $startTime = 0;
    
    /**
     * The stop time.
     * 
     * @var int
     */
    protected $startMemory = 0;
    
    /**
     * The starting memory usage.
     * 
     * @var int
     */
    protected $stopTime = 0;
    
    /**
     * The stopping memory usage.
     * 
     * @var int
     */
    protected $stopMemory = 0;
    
    /**
     * Sets defaults.
     * 
     * @return \Europa\Bench
     */
    public function __construct($start = true)
    {
        if ($start) {
            $this->start();
        }
    }
    
    /**
     * Returns the time and memory as a parse-able string.
     * 
     * @return string
     */
    public function __toString()
    {
        if (!$this->isStopped()) {
            $this->stop();
        }
        return $this->getTime() . ' : ' . $this->getMemory();
    }
    
    /**
     * Sets the start point.
     * 
     * @return \Europa\Bench
     */
    public function start()
    {
        $this->startTime   = microtime(true);
        $this->startMemory = memory_get_peak_usage();
        $this->stopTime    = 0;
        $this->stopMemory  = 0;
        return $this;
    }
    
    /**
     * Sets the stop point.
     * 
     * @return \Europa\Bench
     */
    public function stop()
    {
        if (!$this->isStopped()) {
            $this->stopTime   = microtime(true);
            $this->stopMemory = memory_get_peak_usage();
        }
        return $this;
    }
    
    /**
     * Returns whether or not the benchmark is stopped.
     * 
     * @return bool
     */
    public function isStopped()
    {
        return $this->stopTime > 0;
    }
    
    /**
     * Returns the amount of time in seconds it took from the starting point to
     * the stopping point.
     * 
     * @return float
     */
    public function getTime($precision = 4)
    {
        $this->stop();
        return round($this->stopTime - $this->startTime, $precision);
    }
    
    /**
     * Returns the amount of memory in megabytes used from the starting point to
     * the stopping point.
     * 
     * @return float
     */
    public function getMemory($precision = 4)
    {
        $this->stop();
        return round(($this->stopMemory - $this->startMemory) / 1024 / 1024, $precision);
    }
}