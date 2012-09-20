<?php

namespace Europa\Boot;
use DirectoryIterator;
use UnexpectedValueException;

/**
 * Boots the application.
 * 
 * @category App
 * @package  Rex
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  http://europaphp.org/license
 */
class BootFiles implements BootInterface
{
    /**
     * The regex to match loadable files with.
     * 
     * @var string
     */
    private $match;
    
    /**
     * The path containing the bootstrap files.
     * 
     * @var string
     */
    private $path;
    
    /**
     * Constructs a new bootstrapper.
     * 
     * @param string $path  The boot path.
     * @param string $match The regex to match loadable files with.
     * 
     * @return Boot
     */
    public function __construct($path, $match = '/\.php$/')
    {
        $this->match = $match;
        $this->path  = realpath($path);
        
        if (!$this->path) {
            throw new UnexpectedValueException('The boot path "' . $path . '" does not exist.');
        }
    }
    
    /**
     * Bootstraps the app.
     * 
     * @return void
     */
    public function boot()
    {
        foreach (new DirectoryIterator($this->path) as $file) {
            if (preg_match($this->match, $file->getBasename())) {
                include $file->getPathname();
            }
        }
        
        return $this;
    }
}