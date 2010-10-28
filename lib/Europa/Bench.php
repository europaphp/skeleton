<?php

/**
 * Class for ad-hoc application benchmarking.
 * 
 * @category  Benchmarking
 * @package   Europa
 * @author    Trey Shugart <treshugart@gmail.com>
 * @copyright (c) 2010 Trey Shugart
 * @link      http://europaphp.org/license
 */
class Europa_Bench
{
    /**
     * The start time.
     * 
     * @var int
     */
    protected $_startTime = 0;
    
    /**
     * The stop time.
     * 
     * @var int
     */
    protected $_startMemory = 0;
    
    /**
     * The starting memory usage.
     * 
     * @var int
     */
    protected $_stopTime = 0;
    
    /**
     * The stopping memory usage.
     * 
     * @var int
     */
    protected $_stopMemory = 0;
    
    /**
     * Sets defaults.
     * 
     * @return Europa_Bench
     */
    public function __construct()
    {
        $this->start();
    }
    
    /**
     * Returns the time and memory as a string.
     * 
     * @return string
     */
    public function __toString()
    {
        // if not stopped yet, stop
        if (!$this->isStopped()) {
            $this->stop();
        }
        return $this->getTime() . ' seconds';
    }
    
    /**
     * Sets the start point.
     * 
     * @return Europa_Bench
     */
    public function start()
    {
        $this->_startTime   = microtime(true);
        $this->_startMemory = memory_get_usage();
        return $this;
    }
    
    /**
     * Sets the stop point.
     * 
     * @return Europa_Bench
     */
    public function stop()
    {
        $this->_stopTime   = microtime(true);
        $this->_stopMemory = memory_get_usage();
        return $this;
    }
    
    /**
     * Returns whether or not the benchmark is stopped.
     * 
     * @return bool
     */
    public function isStopped()
    {
        return $this->_stopTime > 0;
    }
    
    /**
     * Returns the amount of time in seconds it took from the starting point to
     * the stopping point.
     * 
     * @return float
     */
    public function getTime($precision = 4)
    {
        return round($this->_stopTime - $this->_startTime, $precision);
    }
    
    /**
     * Returns the amount of memory in megabytes used from the starting point to
     * the stopping point.
     * 
     * @return float
     */
    public function getMemory($precision = 4)
    {
        return round(($this->_stopMemory - $this->_startMemory) / (1024 * 1024), $precision);
    }
}