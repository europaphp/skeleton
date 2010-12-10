<?php

class Europa_Install_Version
{
    const MAJOR = 'major';
    
    const MINOR = 'minor';
    
    const MAINTENANCE = 'maintenance';
    
    protected $versionParts = array();
    
    public function __construct($version)
    {
        // parse and set the version parts
        $this->versionParts = $this->parseVersion($version);
    }
    
    public function __toString()
    {
        return implode('.', $this->versionParts);
    }
    
    public function major()
    {
        return $this->versionParts[self::MAJOR];
    }
    
    public function minor()
    {
        return $this->versionParts[self::MINOR];
    }
    
    public function maintenance()
    {
        return $this->versionParts[self::MAINTENANCE];
    }
    
    protected function parseVersion($version)
    {
        // parse out the version
        $parts = explode('.', $version);
        
        // require at least a major version
        if (!isset($parts[0])) {
            throw new Europa_Install_Exception(
                'You must specify at least a major release number.'
            );
        }
        
        // default minor to 0
        if (!isset($parts[1])) {
            $parts[1] = 0;
        }
        
        // defult maintenance to 0
        if (!isset($parts[2])) {
            $parts[2] = 0;
        }
        
        // return the version parts
        return array(
            self::MAJOR       => (int) $parts[0],
            self::MINOR       => (int) $parts[1],
            self::MAINTENANCE => (int) $parts[2]
        );
    }
}