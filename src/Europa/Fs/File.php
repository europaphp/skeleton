<?php

namespace Europa\Fs;
use LogicException;
use RuntimeException;

class File extends Item
{
  const MASK = 0777;

  public function __toString()
  {
    return $this->getContents();
  }

  public function setContents($contents)
  {
    file_put_contents($this->getPathname(), $contents);
    return static::open($this->getPathname());
  }

  public function getContents()
  {
    return file_get_contents($this->getPathname());
  }

  public function chunk($maxSize = 1024)
  {
    return str_split(file_get_contents($this->getPathname()), $maxSize);
  }

  public function delete()
  {
    if (!@unlink($this->getPathname())) {
      throw new RuntimeException(
        'Could not delete file ' . $this->getPathname() . '.'
      );
    }
    return $this;
  }

  public function copy(Directory $destination, $overwrite = true)
  {
    $source    = $this->getPathname();
    $destination = $destination->getPathname() . DIRECTORY_SEPARATOR . basename($source);

    // copy the file to the destination
    if ($overwrite || !is_file($destination)) {
      if (!@copy($source, $destination)) {
        throw new RuntimeException(
          'Could not copy file ' . $source . ' to ' . $destination . '.'
        );
      }
    }

    // return the new file
    return new static($destination);
  }

  public function move(Directory $destination, $overwrite = true)
  {
    $destination = $this->copy($destination, $overwrite);
    $this->delete();
    return $destination;
  }

  public function rename($newPath)
  {
    $oldPath = $this->getPathname();
    if (!@rename($oldPath, $newPath)) {
      throw new RuntimeException(
        'Could not rename file from ' . $oldPath . ' to ' . $newPath . '.'
      );
    }
    return new static($newPath);
  }

  public function existsIn(Directory $dir)
  {
    return $dir->hasFile($this);
  }

  public function find($regex)
  {
    preg_match_all($regex, file_get_contents($this->getPathname()), $matches, PREG_SET_ORDER);

    if (count($matches)) {
      $lines  = [];
      $contents = $this->getContents();

      foreach ($matches as $match) {
        $lines[substr_count($contents, "\n", 0, strpos($contents, $match[0])) + 1] = $match;
      }

      return $lines;
    }

    return [];
  }

  public function findAndReplace($regex, $replacement)
  {
    $pathname = $this->getPathname();
    $contents = file_get_contents($pathname);
    $contents = preg_replace($regex, $replacement, $contents, -1, $count);
    file_put_contents($pathname, $contents);
    return $count;
  }

  public function contains($pattern)
  {
    return count($this->find($pattern)) > 0;
  }

  public function getFilename()
  {
    return $this->getInfo('filename');
  }

  public function getExtension()
  {
    return $this->getInfo('extension');
  }

  public function getInfo($key = null)
  {
    $info = pathinfo($this->getRealpath());
    if ($key) {
      if (array_key_exists($key, $info)) {
        return $info[$key];
      } else {
        throw new LogicException('Information for "' . $this->getRealpath() . '" does not contain "' . $key . '".');
      }
    }
    return $info;
  }

  public static function open($file)
  {
    if (!is_file($file)) {
      throw new RuntimeException("Could not open file {$file}.");
    }
    return new static($file);
  }

  public static function create($file, $mask = self::MASK)
  {
    // the file must not exist
    if (is_file($file)) {
      throw new LogicException("The file {$file} already exists.");
    }

    // the directory it is supposed to be in
    $dir = dirname($file);

    // create the directory if it doesn't exist
    if (!is_dir($dir)) {
      Directory::create($dir);
    }

    // create the file in the directory
    file_put_contents($file, '');

    // set permissions
    chmod($file, $mask);

    // open and return
    return static::open($file);
  }

  public static function createIfNotExists($file, $mask = self::MASK)
  {
    if (is_file($path)) {
      return static::open($path, $mask);
    }
    return static::create($path, $mask);
  }

  public static function overwrite($file, $mask = self::MASK)
  {
    if (is_file($file)) {
      static::open($file)->delete();
    }
    return static::create($file, $mask);
  }
}