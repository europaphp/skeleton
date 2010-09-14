<?php

/**
 * A general object for manipulating directories and multiple files.
 * 
 * @category Directory
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Directory extends SplFileInfo implements Countable, Iterator
{
    /**
     * Holds all the items. If the items were filtered, then this array will
     * reflect that.
     * 
     * @var array
     */
    protected $_items = array();
    
    /**
     * The filters applied to the current listing.
     * 
     * @var array
     */
    protected $_filters = array();
    
    /**
     * Contains whether or not the current listing is flat.
     * 
     * @var bool
     */
    protected $_isFlat = false;
    
    /**
     * Opens the directory an constructs the parent info object.
     * 
     * @param string $path The path to open.
     * @return Europa_Directory
     */
    public function __construct($path)
    {
        $this->_path = realpath($path);
        if (!$path || !$this->_path) {
            throw new Europa_Directory_Exception(
                'The path ' . $path . ' must be a valid.'
            );
        }
        parent::__construct($this->_path);
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
     * Returns the raw items from the directory.
     * 
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }
    
    /**
     * Returns whether or not the current directory contains the specified file.
     * 
     * @param Europa_File $file The file to check for.
     * @return bool
     */
    public function hasFile(Europa_File $file)
    {
        $file = $file->getBasename();
        foreach ($this as $item) {
            if ($file === $item->getBasename()) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Merges the current directory to the destination directory and leaves the
     * current directory alone.
     * 
     * @param Europa_Directory $destination The destination directory.
     * @param bool $fileOverwrite Whether or not to overwrite destination files.
     * @return Europa_Directory
     */
    public function copy(Europa_Directory $destination, $fileOverwrite = true)
    {
        $self = $this->getPathname();
        $dest = $destination->getPathname() . DIRECTORY_SEPARATOR . $this->getBasename();
        foreach ($this->flatten() as $file) {
            $old  = $file->getPathname();
            $new  = substr($old, strlen($self));
            $new  = $dest . $new;
            $base = dirname($new);
            if (!is_dir($base)) {
                Europa_Directory::create($base);
            }
            if (!is_file($new) || $fileOverwrite) {
                if (!@copy($old, $new)) {
                    throw new Europa_Directory_Exception(
                        'File ' . $old . ' could not be copied to ' . $new . '.'
                    );
                }
            } elseif (is_file($new) && !$fileOverwrite) {
                throw new Europa_Directory_Exception(
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
     * @param Europa_Directory $destination The destination directory.
     * @param bool $fileOverwrite Whether or not to overwrite destination files.
     * @return Europa_Directory
     */
    public function move(Europa_Directory $destination, $fileOverwrite = true)
    {
        $this->copy($destination, $fileOverwrite);
        $this->delete();
        return $destination;
    }
    
    /**
     * Renames the current directory and returns it.
     * 
     * @param string $newName The new name of the directory.
     * @return Europa_Directory
     */
    public function rename($newName)
    {
        $oldPath = $this->getPathname();
        $newPath = dirname($oldPath) . DIRECTORY_SEPARATOR . basename($newName);
        if (!@rename($oldPath, $newPath)) {
            throw new Europa_Directory_Exception(
                'Path ' . $oldPath . ' could not be renamed to ' . $newPath . '.'
            );
        }
        return Europa_Directory::open($newPath);
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
            throw new Europa_Directory_Exception(
                'Could not remove directory ' . $this->getPathname() . '.'
            );
        }
    }
    
    /**
     * Empties the current directory.
     * 
     * @return Europa_Directory
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
     * @return int
     */
    public function count($all = false)
    {
        return count($this->_items);
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
     * Applies the filter to the current listing.
     * 
     * @param mixed $filter The callback filter to run against each item.
     * @return Europa_Directory
     */
    public function filter($filter)
    {
        if (is_callable($filter, true)) {
            $this->_filters[] = $filter;
        }
        return $this;
    }
    
    /**
     * Removes the last filter on the listing. If $all is set to true, then all
     * filters are removed.
     * 
     * @param bool $all Whether or not to move all filters.
     * @return Europa_Directory
     */
    public function unfilter($all = false)
    {
        if ($all) {
            $this->_filters = array();
        } elseif (count($this->_filters)) {
            array_pop($this->_filters);
        }
        return $this;
    }
    
    /**
     * Returns whether or not the current listing has filters applied to it.
     * 
     * @return bool
     */
    public function hasFilters()
    {
        return count($this->_filters) > 0;
    }
    
    /**
     * Flattens the directory structure so a single iteration is all that is
     * needed to iterate over every file.
     * 
     * @return array
     */
    public function flatten()
    {
        // mark as flat
        $this->_isFlat = true;
        
        // then flatten
        $this->_items = array();
        foreach ($this->getIterator() as $item) {
            
            if ($item->isDot()) {
                continue;
            }
            
            // convert to path
            $item = $item->getPathname();
            if (!$this->_filter($item)) {
                continue;
            }
            
            // if it's a directory, flatten it
            if (is_dir($item)) {
                
                // Add it to the items array
                $this->_items[] = $item;
                
                $item = self::open($item);
                foreach ($this->_filters as $filter) {
                    $item->filter($filter);
                }
                foreach ($item->flatten() as $sub) {
                    $this->_items[] = $sub->getPathname();
                }
            
            // if it's a file just add it
            } else {
                $this->_items[] = $item;
            }
        }
        
        return $this;
    }
    
    /**
     * Resets the directory listing back to the default.
     * 
     * @return Europa_Directory
     */
    public function unflatten()
    {
        // mark as un-flat
        $this->_isFlat = false;
        
        // then un-flatten
        $this->_items = array();
        foreach ($this->getIterator() as $item) {
            if ($item->isDot()) {
                continue;
            }
            $item = $item->getPathname();
            if ($this->_filter($item)) {
                $this->_items[] = $item;
            }
        }
        return $this;
    }
    
    /**
     * Returns whether or not the current listing is flat.
     * 
     * @return bool
     */
    public function isFlat()
    {
        return $this->_isFlat;
    }
    
    /**
     * Refreshes the current listing.
     * 
     * @return Europa_Directory
     */
    public function refresh()
    {
        if ($this->isFlat()) {
            return $this->flatten();
        }
        return $this->unflatten();
    }
    
    /**
     * Searches for directories/files matching the pattern and returns them as a
     * flat array.
     * 
     * @param string $regex The pattern to match.
     * @return array
     */
    public function search($regex, $basenameOnly = true)
    {
        $items = array();
        foreach ($this->_items as $item) {
            $name = $item;
            if ($basenameOnly) {
                $name = basename($name);
            }
            if (preg_match($regex, $name)) {
                $items[] = $item;
            }
        }
        $this->_items = $items;
        return $this;
    }
    
    /**
     * Searches for matching text inside each file using the pattern and returns
     * each file containing matching text in a flat array.
     * 
     * @param string $regex The pattern to match.
     * @return array
     */
    public function searchIn($regex)
    {
        $items = array();
        foreach ($this->_items as $item) {
            if (is_file($item) && Europa_File::open($item)->searchIn($regex)) {
                $items[] = $item;
            }
        }
        $this->_items = $items;
        return $this;
    }
    
    /**
     * Searches in each file for the matching pattern and replaces it with the
     * replacement pattern. Returns an array file/count if matching files were
     * found or false if no matching files were found.
     * 
     * @param string $regex The pattern to match.
     * @param string $replacement The replacement pattern.
     * @return array
     */
    public function searchAndReplace($regex, $replacement)
    {
        $items = array();
        foreach ($this->searchIn($regex) as $file) {
            $count = Europa_File::open($file)->searchAndReplace($regex, $replacement);
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
        return new DirectoryIterator($this->getPathname());
    }
    
    /**
     * Gets the raw recursive iterator to iterate over each file.
     * 
     * @return RecursiveIteratorIterator
     */
    public function getRecursiveIterator()
    {
        return new RecursiveDirectoryIterator($this->getPathname());
    }
    
    /**
     * Returns the current item in the iteration.
     * 
     * @return Europa_Directory|Europa_File
     */
    public function current()
    {
        $item = current($this->_items);
        if (!$item) {
            return;
        }
        if (is_dir($item)) {
            return self::open($item);
        }
        return new Europa_File($item);
    }
    
    /**
     * Returns the current key in the iteration.
     * 
     * @return int
     */
    public function key()
    {
        return key($this->_items);
    }
    
    /**
     * Moves to the next item in the iteration.
     * 
     * @return void
     */
    public function next()
    {
        next($this->_items);
    }
    
    /**
     * Auto-refreshes the directory, applying any filters, etc.
     * 
     * @return void
     */
    public function rewind()
    {
        reset($this->_items);
    }
    
    /**
     * Returns whether or not the iteration is still valid.
     * 
     * @return bool
     */
    public function valid()
    {
        return $this->current() instanceof SplFileInfo;
    }
    
    /**
     * Opens the specified directory. An exception is thrown if the directory
     * doesn't exist.
     * 
     * @param string $path The path to the directory.
     * @return Europa_Directory
     */
    public static function open($path)
    {
        if (!is_dir($path)) {
            throw new Europa_Directory_Exception(
                'Could not open directory ' . $path . '.'
            );
        }
        return new self($path);
    }
    
    /**
     * Creates the specified directory using the specified mask. An exception is
     * thrown if the directory already exists.
     * 
     * @param string $path The path to the directory.
     * @param int $mask The octal mask of the directory.
     * @return Europa_Directory
     */
    public static function create($path, $mask = 0777)
    {
        if (is_dir($path)) {
            throw new Europa_Directory_Exception(
                'Directory ' . $path . ' already exists.'
            );
        }
        $old = umask(0); 
        mkdir($path, $mask, true);
        chmod($path, $mask);
        umask($old);
        return self::open($path);
    }
    
    /**
     * Creates the specified directory. If the directory already exists, it is
     * overwritten by the new directory.
     * 
     * @param string $path The path to the directory.
     * @param int $mask The octal mask of the directory.
     * @return Europa_Directory
     */
    public static function overwrite($path, $mask = 0777)
    {
        if (is_dir($path)) {
            $dir = new Europa_Directory($path);
            $dir->delete();
        }
        return self::create($path, $mask);
    }
    
    /**
     * Applies the filters to the current item and returns whether the item is
     * valid or not based on the filter's return value.
     * 
     * @param string $item The path to the item.
     * @return bool
     */
    protected function _filter($item)
    {
        foreach ($this->_filters as $filter) {
            if (!call_user_func($filter, $item)) {
                return false;
            }
        }
        return true;
    }
}