<?php

class Europa_Install_Installer
{
    protected $data;
    
    public function __construct(Europa_Install_Data $data)
    {
        $this->data = $data;
    }
}