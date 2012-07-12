<?php

namespace Testes\Coverage;
use InvalidArgumentException;

class CoverageResult
{
    private $result;

    public function __construct(array $result)
    {
        $this->result = $result;
    }
    
    public function file($file)
    {
        if (isset($this->result[$file])) {
            return $this->result[$file];
        }
        return array();
    }
    
    public function line($file, $number)
    {
        $lines = $this->file($file);
        if (isset($lines[$number])) {
            return $lines[$number];
        }
        return 0;
    }
}
