<?php

class Europa_Install_Upgrader
{
    protected $from;
    
    protected $to;
    
    public function __construct(Europa_Install_Version $from, Europa_Install_Version $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }
}