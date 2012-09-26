<?php

namespace Europa\Boot;
use DirectoryIterator;
use UnexpectedValueException;

/**
 * Boots the application.
 * 
 * @category Boot
 * @package  Rex
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  http://europaphp.org/license
 */
class Files implements BootstrapperInterface
{
    /**
     * The files to use.
     * 
     * @var array
     */
    private $files = [];

    /**
     * Adds a single file.
     * 
     * @param string $file The file to add.
     * 
     * @return Files
     */
    public function addFile($file)
    {
        if (!is_file($file)) {
            throw new UnexpectedValueException(sprintf('The file "%s" does not exist.', $file));
        }
        $this->files[] = $file;
        return $this;
    }

    /**
     * Adds an array of files.
     * 
     * @param array $files The files to add.
     * 
     * @return Files
     */
    public function addFiles(array $files)
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }
        return $this;
    }
    
    /**
     * Bootstraps the app.
     * 
     * @return void
     */
    public function boot()
    {
        foreach ($this->files as $file) {
            include $file->getPathname();
        }
        return $this;
    }
}