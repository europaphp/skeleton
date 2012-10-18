<?php

namespace Europa\Fs;
use Countable;
use IteratorAggregate;
use LogicException;
use RuntimeException;

/**
 * An object that represents a single directory.
 * 
 * @category Directory
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Directory extends Item implements IteratorAggregate, Countable
{
    /**
     * The default file mask.
     * 
     * @var int
     */
    const MASK = 0777;
    
    /**
     * Opens the directory an constructs the parent info object.
     * 
     * @param string $path The path to open.
     * 
     * @return Directory
     */
    public function __construct($path)
    {
        $path     = (string) $path;
        $realpath = realpath($path);
        
        if (!$path || !$realpath) {
            throw new LogicException('The path "' . $path . '" must be a valid directory.');
        }

        try {
            parent::__construct($realpath);
        } catch (RuntimeException $e) {
            throw new RuntimeException("Could not open directory {$path} with message: {$e->getMessage()}.");
        }
    }
    
    /**
     * Outputs the pathname of the directory.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->current()->getPathname();
    }
    
    /**
     * Returns the number of items in the current directory.
     * 
     * @return int
     */
    public function count()
    {
        return $this->getIterator()->count();
    }
    
    /**
     * Returns a finder instance representing the current directory.
     * 
     * @return Finder
     */
    public function getIterator()
    {
        $finder = new Finder;
        $finder->in($this->getPathname());
        return $finder;
    }
    
    /**
     * Merges the current directory to the destination directory and leaves the current directory alone.
     * 
     * @param Directory $destination   The destination directory.
     * @param bool                 $fileOverwrite Whether or not to overwrite destination files.
     * 
     * @return Directory
     */
    public function copy(Directory $destination, $fileOverwrite = true)
    {
        $self = $this->getPathname();
        $dest = $destination->getPathname() . DIRECTORY_SEPARATOR . $this->getBasename();
        
        foreach ($this->getIterator() as $file) {
            $old  = $file->getPathname();
            $new  = substr($old, strlen($self));
            $new  = $dest . $new;
            $base = dirname($new);
            
            if ($file instanceof Directory) {
                static::create($new);
                continue;
            }
            
            if (!is_file($new) || $fileOverwrite) {
                if (!@copy($old, $new)) {
                    throw new RuntimeException(
                        'File ' . $old . ' could not be copied to ' . $new . '.'
                    );
                }
            } elseif (is_file($new) && !$fileOverwrite) {
                throw new RuntimeException(
                    'File ' . $new . ' already exists.'
                );
            }
        }
        
        return $this;
    }
    
    /**
     * Moves the current directory to the destination directory, deletes the
     * current directory and returns the destination.
     * 
     * @param Directory $destination   The destination directory.
     * @param bool                 $fileOverwrite Whether or not to overwrite destination files.
     * 
     * @return Directory
     */
    public function move(Directory $destination, $fileOverwrite = true)
    {
        $this->copy($destination, $fileOverwrite);
        $this->delete();
        return $destination;
    }
    
    /**
     * Renames the current directory and returns it.
     * 
     * @param string $newName The new name of the directory.
     * 
     * @return Directory
     */
    public function rename($newName)
    {
        $oldPath = $this->getPathname();
        $newPath = dirname($oldPath) . DIRECTORY_SEPARATOR . basename($newName);
        if (!@rename($oldPath, $newPath)) {
            throw new RuntimeException("Path {$oldPath} could not be renamed to {$newPath}.");
        }
        return self::open($newPath);
    }
    
    /**
     * Deletes the current directory and all of it's contents. Throws an exception if the directory couldn't be
     * removed.
     * 
     * @return void
     */
    public function delete()
    {
        // first empty the directory
        $this->clear();
        
        // then delete it
        if (!@rmdir($this->getPathname())) {
            throw new RuntimeException("Could not remove directory {$this->getPathname()}.");
        }
    }
    
    /**
     * Empties the current directory.
     * 
     * @return Directory
     */
    public function clear()
    {
        foreach ($this->getIterator() as $item) {
            $item->delete();
        }
        return $this;
    }
    
    /**
     * Returns the size taken up by the folder in bytes.
     * 
     * @return int
     */
    public function size()
    {
        $size = 0;
        foreach ($this->getIterator() as $item) {
            $size += $item->getSize();
        }
        return $size;
    }
    
    /**
     * Returns whether or not the current directory is empty.
     * 
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getIterator()->count() === 0;
    }
    
    /**
     * Searches for matching text inside each file using the pattern and returns each file containing matching text in
     * a flat array.
     * 
     * @param string $regex The pattern to match.
     * 
     * @return array
     */
    public function searchIn($regex)
    {
        return $this->getIterator()->filter(function($item, $regex) {
            return is_file($item) && File::open($item)->search($regex);
        }, array($regex));
    }
    
    /**
     * Searches in each file for the matching pattern and replaces it with the replacement pattern. Returns an array
     * file/count if matching files were found or false if no matching files were found.
     * 
     * @param string $regex       The pattern to match.
     * @param string $replacement The replacement pattern.
     * 
     * @return array
     */
    public function searchAndReplace($regex, $replacement)
    {
        $items = array();
        foreach ($this->searchIn($regex)->getItems() as $file) {
            $count = File::open($file)->searchAndReplace($regex, $replacement);
            if ($count) {
                $items[] = $file;
            }
        }
        return $items;
    }
    
    /**
     * Opens the specified directory. An exception is thrown if the directory doesn't exist.
     * 
     * @param string $path The path to the directory.
     * 
     * @return Directory
     */
    public static function open($path)
    {
        if (!is_dir($path)) {
            throw new RuntimeException("Could not open directory {$path}.");
        }
        return new static($path);
    }
    
    /**
     * Creates the specified directory using the specified mask. An exception is thrown if the directory already
     * exists.
     * 
     * @param string $path The path to the directory.
     * @param int    $mask The octal mask of the directory.
     * 
     * @return Directory
     */
    public static function create($path, $mask = self::MASK)
    {
        if (is_dir($path)) {
            throw new RuntimeException("Directory {$path} already exists.");
        }
        
        if (!@mkdir($path, $mask, true)) {
            throw new RuntimeException("Could not create directory {$path}.");
        }
        
        if (!@chmod($path, $mask)) {
            throw new RuntimeException("Could not set file permissions on {$path} to {$mask}.");
        }
        
        return static::open($path);
    }
    
    /**
     * If the directory does not exist it is created and returned. Otherwise it is opened and returned.
     * 
     * @param string $path The path to the directory.
     * @param int    $mask The octal mask of the directory.
     * 
     * @return Directory
     */
    public static function createIfNotExists($path, $mask = self::MASK)
    {
        if (is_dir($path)) {
            return static::open($path, $mask);
        }
        return static::create($path, $mask);
    }
    
    /**
     * Creates the specified directory. If the directory already exists, it is overwritten by the new directory.
     * 
     * @param string $path The path to the directory.
     * @param int    $mask The octal mask of the directory.
     * 
     * @return Directory
     */
    public static function overwrite($path, $mask = self::MASK)
    {
        if (is_dir($path)) {
            $dir = new static($path);
            $dir->delete();
        }
        return static::create($path, $mask);
    }
}