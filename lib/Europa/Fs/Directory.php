<?php

namespace Europa\Fs;
use Europa\Fs\Directory\Exception;

/**
 * A general object for manipulating directories and multiple files.
 * 
 * @category Directory
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Directory extends Item implements \Countable, \Iterator
{
    /**
     * Holds all the items. If the items were filtered, then this array will
     * reflect that.
     * 
     * @var array
     */
    private $items = array();
    
    /**
     * Opens the directory an constructs the parent info object.
     * 
     * @param string $path The path to open.
     * 
     * @return \Europa\Fs\Directory
     */
    public function __construct($path)
    {
        $realpath = realpath($path);
        if (!$path || !$realpath) {
            throw new Exception('The path "' . $path . '" must be a valid directory.');
        }

        try {
            parent::__construct($realpath);
        } catch(\RuntimeException $e) {
            throw new Exception("Could not open directory {$path} with message: {$e->getMessage()}.");
        }

        $this->unflatten();
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
     * Returns all directory paths.
     * 
     * @return array
     */
    public function getDirectories()
    {
        $dirs = array();
        foreach ($this->getItems() as $item) {
            if (is_dir($item)) {
                $dirs[] = $item;
            }
        }
        return $dirs;
    }
    
    /**
     * Returns all file paths.
     * 
     * @return array
     */
    public function getFiles()
    {
        $files = array();
        foreach ($this->getItems() as $item) {
            if (is_file($item)) {
                $files[] = $item;
            }
        }
        return $files;
    }
    
    /**
     * Returns the raw items from the directory.
     * 
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }
    
    /**
     * Returns whether or not the specified directory exists in the current directory.
     * 
     * @param string $dir The directory to check for.
     * 
     * @return bool
     */
    public function hasDirectory($dir)
    {
        return is_dir($this->getPathname() . DIRECTORY_SEPARATOR . $dir);
    }
    
    /**
     * Returns whether or not the specified file exists in the current directory.
     * 
     * @param string $file The file to check for.
     * 
     * @return bool
     */
    public function hasFile($file)
    {
        return is_file($this->getPathname() . DIRECTORY_SEPARATOR . $file);
    }
    
    /**
     * Returns whether or not the current directory contains the specified file.
     * 
     * @param string $item The item to check for.
     * 
     * @return bool
     */
    public function hasItem($item)
    {
        return $this->hasDirectory($item) || $this->hasFile($item);
    }
    
    /**
     * Merges the current directory to the destination directory and leaves the
     * current directory alone.
     * 
     * @param \Europa\Fs\Directory $destination   The destination directory.
     * @param bool                 $fileOverwrite Whether or not to overwrite destination files.
     * 
     * @return \Europa\Fs\Directory
     */
    public function copy(Directory $destination, $fileOverwrite = true)
    {
        $self = $this->getPathname();
        $dest = $destination->getPathname() . DIRECTORY_SEPARATOR . $this->getBasename();
        foreach ($this->flatten() as $file) {
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
                    throw new Exception(
                        'File ' . $old . ' could not be copied to ' . $new . '.'
                    );
                }
            } elseif (is_file($new) && !$fileOverwrite) {
                throw new Exception(
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
     * @param \Europa\Fs\Directory $destination   The destination directory.
     * @param bool                 $fileOverwrite Whether or not to overwrite destination files.
     * 
     * @return \Europa\Fs\Directory
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
     * @return \Europa\Fs\Directory
     */
    public function rename($newName)
    {
        $oldPath = $this->getPathname();
        $newPath = dirname($oldPath) . DIRECTORY_SEPARATOR . basename($newName);
        if (!@rename($oldPath, $newPath)) {
            throw new Exception("Path {$oldPath} could not be renamed to {$newPath}.");
        }
        return self::open($newPath);
    }
    
    /**
     * Deletes the current directory and all of it's contents. Throws an exception
     * if the directory couldn't be removed.
     * 
     * @return void
     */
    public function delete()
    {
        // first empty the directory
        $this->clear();
        
        // then delete it
        if (!@rmdir($this->getPathname())) {
            throw new Exception("Could not remove directory {$this->getPathname()}.");
        }
    }
    
    /**
     * Empties the current directory.
     * 
     * @return \Europa\Fs\Directory
     */
    public function clear()
    {
        foreach ($this as $item) {
            $item->delete();
        }
        return $this;
    }
    
    /**
     * Returns the size taken up by the folder in bytes. If the structure is
     * flattened, it will return the total size of all files recursively. If
     * it is unflattened, then only the immediate children of the folder will
     * be accounted for.
     * 
     * @return int
     */
    public function size()
    {
        $size = 0;
        foreach ($this as $item) {
            if ($item->isDir()) {
                continue;
            }
            $size += $item->getSize();
        }
        return $size;
    }
    
    /**
     * Returns whether or not the current directory contains any items.
     * 
     * @param bool $all Whether or not to count recursively.
     * 
     * @return int
     */
    public function count($all = false)
    {
        return count($this->items);
    }
    
    /**
     * Returns whether or not the current directory is empty.
     * 
     * @return bool
     */
    public function isEmpty()
    {
        return $this->count() === 0;
    }
    
    /**
     * Returns a flat recursive file listing of the current directory.
     * 
     * @return array
     */
    public function flatten()
    {
        foreach ($this->items as $index => $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            if (is_dir($item)) {
                $this->items = array_merge($this->items, self::open($item)->flatten()->getItems());
            } else {
                $this->items[] = $item;
            }
        }
        return $this;
    }
    
    /**
     * Refreshes the file/directory listing.
     * 
     * @return \Europa\Fs\Directory
     */
    public function unflatten()
    {
        $this->items = array();
        foreach ($this->getIterator() as $item) {
            if ($item->isDot()) {
                continue;
            }
            $this->items[] = $item->getPathname();
        }
        return $this;
    }
    
    /**
     * Applies the filter to the current listing.
     * 
     * @param mixed $filter The callback filter to run against each item.
     * @param array $data   Any custom data to pass to the callback.
     * 
     * @return \Europa\Fs\Directory
     */
    public function filter($filter, array $data = array())
    {
        // only apply if it's callable
        if (!is_callable($filter, true)) {
            throw new Exception('The passed filter must be callable.');
        }
        
        // filter out each item
        foreach ($this->items as $index => $item) {
            if (call_user_func_array($filter, array_merge(array($item), $data)) === false) {
                unset($this->items[$index]);
            }
        }
        
        return $this;
    }
    
    /**
     * Sorts the items.
     * 
     * @return \Europa\Fs\Directory
     */
    public function sort()
    {
        sort($this->items);
        foreach ($this->items as $item) {
            if ($item instanceof Directory) {
                $item->sort();
            }
        }
        return $this;
    }
    
    /**
     * Searches for directories/files matching the pattern and returns them as a
     * flat array.
     * 
     * @param string $regex The pattern to match.
     * 
     * @return array
     */
    public function search($regex)
    {
        return $this->filter(function($item, $regex) {
            return preg_match($regex, $item) ? true : false;
        }, array($regex));
    }
    
    /**
     * Searches for matching text inside each file using the pattern and returns
     * each file containing matching text in a flat array.
     * 
     * @param string $regex The pattern to match.
     * 
     * @return array
     */
    public function searchIn($regex)
    {
        return $this->filter(function($item, $regex) {
            return is_file($item) && File::open($item)->search($regex);
        }, array($regex));
    }
    
    /**
     * Searches in each file for the matching pattern and replaces it with the
     * replacement pattern. Returns an array file/count if matching files were
     * found or false if no matching files were found.
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
     * Gets the raw directory iterator for the directory.
     * 
     * @return DirectoryIterator
     */
    public function getIterator()
    {
        return new \DirectoryIterator($this->getPathname());
    }
    
    /**
     * Gets the raw recursive iterator to iterate over each file.
     * 
     * @return RecursiveIteratorIterator
     */
    public function getRecursiveIterator()
    {
        return new \RecursiveDirectoryIterator($this->getPathname());
    }
    
    /**
     * Returns the current item in the iteration.
     * 
     * @return \Europa\Fs\Directory|\Europa\Fs\File
     */
    public function current()
    {
        $current = current($this->items);
        if (is_dir($current)) {
            return static::open($current);
        }
        return File::open($current);
    }
    
    /**
     * Returns the current key in the iteration.
     * 
     * @return int
     */
    public function key()
    {
        return key($this->items);
    }
    
    /**
     * Moves to the next item in the iteration.
     * 
     * @return void
     */
    public function next()
    {
        next($this->items);
    }
    
    /**
     * Auto-refreshes the directory, applying any filters, etc.
     * 
     * @return void
     */
    public function rewind()
    {
        reset($this->items);
    }
    
    /**
     * Returns whether or not the iteration is still valid.
     * 
     * @return bool
     */
    public function valid()
    {
        return current($this->items) ? true : false;
    }
    
    /**
     * Opens the specified directory. An exception is thrown if the directory doesn't exist.
     * 
     * @param string $path The path to the directory.
     * 
     * @return \Europa\Fs\Directory
     */
    public static function open($path)
    {
        if (!is_dir($path)) {
            throw new Exception("Could not open directory {$path}.");
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
     * @return \Europa\Fs\Directory
     */
    public static function create($path, $mask = 0777)
    {
        if (is_dir($path)) {
            throw new Exception("Directory {$path} already exists.");
        }
        
        if (!@mkdir($path, $mask, true)) {
            throw new Exception("Could not create directory {$path}.");
        }
        
        if (!@chmod($path, $mask)) {
            throw new Exception("Could not set file permissions on {$path} to {$mask}.");
        }
        
        return static::open($path);
    }
    
    /**
     * If the directory does not exist it is created and returned. Otherwise it is opened and returned.
     * 
     * @param string $path The path to the directory.
     * @param int    $mask The octal mask of the directory.
     * 
     * @return \Europa\Fs\Directory
     */
    public static function createIfNotExists($path, $mask = 0777)
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
     * @return \Europa\Fs\Directory
     */
    public static function overwrite($path, $mask = 0777)
    {
        if (is_dir($path)) {
            $dir = new static($path);
            $dir->delete();
        }
        return static::create($path, $mask);
    }
}