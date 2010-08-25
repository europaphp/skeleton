<?php

/**
 * A general object for manipulating single files.
 * 
 * @category Directory
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_File extends SplFileObject
{
	/**
	 * Chunks the current file into base64 encoded chunks and returns them as an
	 * array.
	 * 
	 * @param int $maxSize The maximum size for each block.
	 * @return array
	 */
	public function chunk($maxSize = 1024)
	{
		return str_split(file_get_contents($this->getPathname()), $maxSize);
	}
	
	/**
	 * Delets the current file. Throws an exception if the file couldn't be
	 * deleted.
	 * 
	 * @return void
	 */
	public function delete()
	{
		if (!@unlink($this->getPathname())) {
			throw new Europa_File_Exception(
				'Could not delete file ' . $this->getPathname() . '.'
			);
		}
	}
	
	/**
	 * Copies the file to the destination directory.
	 * 
	 * @param Europa_Directory $destination The destination directory.
	 * @param bool $fileOverwrite Whether or not to overwrite the destination file
	 * if it exists.
	 * @return Europa_File
	 */
	public function copy(Europa_Directory $destination, $fileOverwrite = true)
	{
		$source      = $this->getPathname();
		$destination = $destination->getPathname()
		             . DIRECTORY_SEPARATOR
		             . basename($source);
		
		// copy the file to the destination
		if ($fileOverwrite || !is_file($destination)) {
			if (!@copy($source, $destination)) {
				throw new Europa_File_Exception(
					'Could not copy file ' . $source . ' to ' . $destination . '.'
				);
			}
		}
		
		// return the new file
		return new Europa_File($destination);
	}
	
	/**
	 * Moves the current file to the specified directory.
	 * 
	 * @param Europa_Directory $destination The destination directory.
	 * @param bool $fileOverwrite Whether or not to overwrite the destination file
	 * if it exists.
	 * @return Europa_File
	 */
	public function move(Europa_Directory $destination, $fileOverwrite = true)
	{
		$destination = $this->copy($destination, $fileOverwrite);
		$this->delete();
		return $destination;
	}
	
	/**
	 * Renames the current file to the new file and returns the new file.
	 * 
	 * @param string $newName The name to rename the current file to.
	 * @return Europa_File
	 */
	public function rename($newName)
	{
		$oldPath = $this->getPathname();
		$newPath = $this->getPath() . DIRECTORY_SEPARATOR . basename($newName);
		if (!@rename($oldPath, $newPath)) {
			throw new Europa_File_Exception(
				'Could not rename file from ' . $oldPath . ' to ' . $newPath . '.'
			);
		}
		return new Europa_File($newPath);
	}
	
	/**
	 * Returns whether or not the current file exists in the specified direcory.
	 * 
	 * param Europa_Directory $dir The directory to check in.
	 * @return bool
	 */
	public function existsIn(Europa_Directory $dir)
	{
		return $dir->hasFile($this);
	}
	
	/**
	 * Searches the current file for the matching pattern and returs the all
	 * matches that were found as an array using preg_match_all. If no matches
	 * are found, false is returned.
	 * 
	 * @param string $regex The pattern to match.
	 * @return false|array
	 */
	public function searchIn($regex)
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
	 * @param string $regex The pattern to match.
	 * @param string $replacement The replacement pattern.
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
	 * @return Europa_Directory
	 */
	public static function open($file)
	{
		if (!is_file($file)) {
			throw new Europa_File_Exception(
				'Could not open file ' . $file . '.'
			);
		}
		return new self($file);
	}
	
	/**
	 * Creates the specified file. If the file already exists, an exception is
	 * thrown.
	 * 
	 * @param string $file The file to create.
	 * @param int $mask The octal mask of the file.
	 * @return Europa_File
	 */
	public static function create($file, $mask = 0777)
	{
		if (is_file($file)) {
			throw new Europa_File_Exception(
				'File ' . $file . ' already exists.'
			);
		}
		file_put_contents($file, '');
		chmod($file, $mask);
		return self::open($file);
	}
	
	/**
	 * Overwrites the specified file.
	 * 
	 * @param string $file The file to overwrite.
	 * @param int $mask The octal mask of the file.
	 * @return Europa_File
	 */
	public static function overwrite($file, $mask = 0777)
	{
		if (is_file($file)) {
			$file = new Europa_File($file);
			$file->delete();
		}
		return self::create($file, $mask);
	}
}