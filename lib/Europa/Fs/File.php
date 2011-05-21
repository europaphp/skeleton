<?php

namespace Europa\Fs;
use Europa\Fs\File\Exception;

/**
 * A general object for manipulating single files.
 * 
 * @category Directory
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class File extends Item
{
    /**
     * Returns the file contents instead of the file path.
     * 
     * @return string
     */
    public function __toString()
    {
        echo $this->getContents();
    }
    
    /**
     * Retrieves the contents of the file.
     * 
     * @return string
     */
    public function getContents()
    {
        return file_get_contents($this->getPathname());
    }
    
    /**
     * Sets the contents of the file.
     * 
     * @param string $contents The contents to set to the file.
     * 
     * @return \Europa\Fs\File
     */
    public function setContents($contents)
    {
        file_put_contents($this->getPathname(), $contents);
        return self::open($this->getPathname());
    }
    
    /**
     * Chunks the current file into base64 encoded chunks and returns them as an array.
     * 
     * @param int $maxSize The maximum size for each block.
     * 
     * @return array
     */
    public function chunk($maxSize = 1024)
    {
        return str_split(file_get_contents($this->getPathname()), $maxSize);
    }
    
    /**
     * Deletes the current file. Throws an exception if the file couldn't be deleted.
     * 
     * @return void
     */
    public function delete()
    {
        if (!@unlink($this->getPathname())) {
            throw new Exception(
                'Could not delete file ' . $this->getPathname() . '.'
            );
        }
        return $this;
    }
    
    /**
     * Copies the file to the destination directory.
     * 
     * @param \Europa\Fs\Directory $destination The destination directory.
     * @param bool                 $fileOverwrite Whether or not to overwrite the destination file if it exists.
     * 
     * @return \Europa\Fs\File
     */
    public function copy(Directory $destination, $fileOverwrite = true)
    {
        $source      = $this->getPathname();
        $destination = $destination->getPathname()
                     . DIRECTORY_SEPARATOR
                     . basename($source);
        
        // copy the file to the destination
        if ($fileOverwrite || !is_file($destination)) {
            if (!@copy($source, $destination)) {
                throw new File\Exception(
                    'Could not copy file ' . $source . ' to ' . $destination . '.'
                );
            }
        }
        
        // return the new file
        return new self($destination);
    }
    
    /**
     * Moves the current file to the specified directory.
     * 
     * @param \Europa\Fs\Directory $destination   The destination directory.
     * @param bool                 $fileOverwrite Whether or not to overwrite the destination file if it exists.
     * 
     * @return \Europa\Fs\File
     */
    public function move(Directory $destination, $fileOverwrite = true)
    {
        $destination = $this->copy($destination, $fileOverwrite);
        $this->delete();
        return $destination;
    }
    
    /**
     * Renames the current file to the new file and returns the new file.
     * 
     * @param string $newName The name to rename the current file to.
     * 
     * @return \Europa\Fs\File
     */
    public function rename($newPath)
    {
        $oldPath = $this->getPathname();
        if (!@rename($oldPath, $newPath)) {
            throw new Exception(
                'Could not rename file from ' . $oldPath . ' to ' . $newPath . '.'
            );
        }
        return new static($newPath);
    }
    
    /**
     * Returns whether or not the current file exists in the specified direcory.
     * 
     * @param \Europa\Fs\Directory $dir The directory to check in.
     * 
     * @return bool
     */
    public function existsIn(Directory $dir)
    {
        return $dir->hasFile($this);
    }
    
    /**
     * Searches the current file for the matching pattern and returs the all
     * matches that were found as an array using preg_match_all. If no matches
     * are found, false is returned.
     * 
     * @param string $regex The pattern to match.
     * 
     * @return false|array
     */
    public function search($regex)
    {
        preg_match_all($regex, file_get_contents($this->getPathname()), $matches);
        if (count($matches[0])) {
            return $matches;
        }
        return false;
    }
    
    /**
     * Searches the current file for the matching pattern and replaces it with
     * the replacement and returns the number of items that were replaced.
     * 
     * @param string $regex       The pattern to match.
     * @param string $replacement The replacement pattern.
     * 
     * @return int
     */
    public function searchAndReplace($regex, $replacement)
    {
        $pathname = $this->getPathname();
        $contents = file_get_contents($pathname);
        $contents = preg_replace($regex, $replacement, $contents, -1, $count);
        file_put_contents($pathname, $contents);
        return $count;
    }
    
    /**
     * Opens the specified file. If the file doesn't exist an exception is thrown.
     * 
     * @param string $file The file to open.
     * 
     * @return \Europa\Fs\Directory
     */
    public static function open($file)
    {
        if (!is_file($file)) {
            throw new File\Exception('Could not open file ' . $file . '.');
        }
        return new self($file);
    }
    
    /**
     * Creates the specified file. If the file already exists, an exception is
     * thrown.
     * 
     * @param string $file The file to create.
     * @param int    $mask The octal mask of the file.
     * 
     * @return \Europa\Fs\File
     */
    public static function create($file, $mask = 0777)
    {
        if (is_file($file)) {
            throw new Exception("The file {$file} already exists.");
        }
        file_put_contents($file, '');
        chmod($file, $mask);
        return self::open($file);
    }
    
    /**
     * Overwrites the specified file.
     * 
     * @param string $file The file to overwrite.
     * @param int    $mask The octal mask of the file.
     * 
     * @return \Europa\Fs\File
     */
    public static function overwrite($file, $mask = 0777)
    {
        if (is_file($file)) {
            self::open($file)->delete();
        }
        return self::create($file, $mask);
    }
}