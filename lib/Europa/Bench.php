<?php

namespace Europa;

/**
 * Simple benchmarking.
 * 
 * @category Benchmarking
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) Trey Shugart 2011 http://europaphp.org/license
 */
class Bench
{
    /**
     * The amount of time the last benchmark took.
     * 
     * @var int
     */
    private $time = 0;
    
    /**
     * The amount of memory the last benchmark took.
     * 
     * @var int
     */
    private $memory = 0;
    
    /**
     * The time the benchmark started.
     * 
     * @var int
     */
    private $startTime = 0;
    
    /**
     * The time the benchmark ended.
     * 
     * @var int
     */
    private $stopTime = 0;
    
    /**
     * The memory when the benchmark started.
     * 
     * @var int
     */
    private $startMemory = 0;
    
    /**
     * The memory when the benchmark ended.
     * 
     * @var int
     */
    private $stopMemory = 0;
    
    /**
     * The peak amount of memory used during the benchmark.
     * 
     * @var int
     */
    private $peakMemory = 0;
    
    /**
     * Returns simple information about the benchmark as a string.
     * 
     * @return string
     */
    public function __toString()
    {
        return sprintf('Time: %d, Memory: %d', $this->getTime(), $this->getMemory());
    }
    
    /**
     * Starts the benchmark.
     * 
     * @return Bench
     */
    public function start()
    {
        $this->startTime   = $this->getCurrentTime();
        $this->startMemory = $this->getCurrentMemory();
        return $this;
    }
    
    /**
     * Stops the benchmark.
     * 
     * @return Bench
     */
    public function stop()
    {
        $this->stopTime   = $this->getCurrentTime();
        $this->time       = $this->stopTime - $this->startTime;
        $this->stopMemory = $this->getCurrentMemory();
        $this->peakMemory = $this->getCurrentPeakMemory();
        $this->memory     = $this->peakMemory - $this->startMemory;
        return $this;
    }
    
    /**
     * Returns the number of milliseconds it took to run the tests.
     * 
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Returns the peak amount of memory that was used during the test.
     * 
     * @return int
     */
    public function getMemory()
    {
        return $this->memory;
    }
    
    /**
     * Returns the start time.
     * 
     * @return int
     */
    public function getStartTime()
    {
        return $this->startTime;
    }
    
    /**
     * Returns the current time.
     * 
     * @return int
     */
    public function getCurrentTime()
    {
        return microtime(true);
    }
    
    /**
     * Returns the current time difference.
     * 
     * @return int
     */
    public function getCurrentTimeDifference()
    {
        return $this->getCurrentTime() - $this->getStartTime();
    }
    
    /**
     * Returns the stop time.
     * 
     * @return int
     */
    public function getStopTime()
    {
        return $this->stopTime;
    }
    
    /**
     * Returns the start memory.
     * 
     * @return int
     */
    public function getStartMemory()
    {
        return $this->startMemory;
    }
    
    /**
     * Returns the current memory usage.
     * 
     * @return int
     */
    public function getCurrentMemory()
    {
        return memory_get_usage(true);
    }
    
    /**
     * Returns the current memory difference.
     * 
     * @return int
     */
    public function getCurrentMemoryDifferenc()
    {
        return $this->getCurrentMemory() - $this->getStartMemory();
    }
    
    /**
     * Returns the stop memory.
     * 
     * @return int
     */
    public function getStopMemory()
    {
        return $this->stopMemory;
    }
    
    /**
     * Returns the peak memory.
     * 
     * @return int
     */
    public function getPeakMemory()
    {
        return $this->peakMemory;
    }
    
    /**
     * Returns the current peak memory usage.
     * 
     * @return int
     */
    public function getCurrentPeakMemory()
    {
        return memory_get_peak_usage(true);
    }
}