<?php

namespace Testes\Coverage;

class Line
{
    const UNEXECUTED = -1;
    
    const DEAD = -2;
    
    const IGNORED = -3;
    
    private $line;
    
    private $trimmed;
    
    private $num;
    
    private $status;
    
    private $count;
    
    public function __construct($line, $num, $status = self::UNEXECUTED)
    {
        $this->line    = (string) $line;
        $this->trimmed = trim($this->line);
        $this->num     = (int) $num;
        $this->status  = (int) $status;
        $this->count   = $this->status > 0 ? $this->status : 0;
        
        // detect the type of line
        $this->analyze();
    }
    
    public function __toString()
    {
        return $this->line;
    }
    
    public function count()
    {
        return $this->count;
    }
    
    public function getNumber()
    {
        return $this->num;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function isTested()
    {
        return $this->status > 0;
    }
    
    public function isUntested()
    {
        return $this->status === self::UNEXECUTED || $this->status === 0;
    }
    
    public function isDead()
    {
        return $this->status === self::DEAD;
    }
    
    public function isIgnored()
    {
        return $this->status === self::IGNORED;
    }
    
    private function analyze()
    {
        $this->analyzeIgnored();
    }
    
    private function analyzeIgnored()
    {
        // if the line is empty, ignore it
        if ($this->trimmed === '') {
            $this->status = self::IGNORED;
            return;
        }
        
        // characters that if at the beginning of a line make that line ignorable
        $startMatches = array(
            '<?',
            '?>',
            'namespace',
            'use',
            'abstract',
            'class',
            'interface',
            'trait',
            'const',
            'return',
            'final',
            'static',
            'public',
            'protected',
            'private',
            'function',
            '/',
            '*',
            '\'',
            '"',
            '.',
            '_',
            '[',
            ']'
        );
        
        foreach ($startMatches as $tok) {
            if (strpos($this->trimmed, $tok) === 0) {
                $this->status = self::IGNORED;
                return;
            }
        }
        
        $wholeMatches = array(
            '(',
            ')',
            ');',
            '{',
            '}',
            '[',
            ']'
        );
        
        foreach ($wholeMatches as $tok) {
            if ($this->trimmed === $tok) {
                $this->status = self::IGNORED;
                return;
            }
        }
    }
}