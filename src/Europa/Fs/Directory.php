<?php

namespace Europa\Fs;
use Countable;
use IteratorAggregate;
use LogicException;
use RuntimeException;

class Directory extends Item implements IteratorAggregate, Countable
{
  const MASK = 0777;

  public function __construct($path)
  {
    $path   = (string) $path;
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

  public function __toString()
  {
    return $this->current()->getPathname();
  }

  public function count()
  {
    return $this->getIterator()->count();
  }

  public function getIterator()
  {
    $finder = new Finder;
    $finder->in($this->getPathname());
    return $finder;
  }

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

  public function move(Directory $destination, $fileOverwrite = true)
  {
    $this->copy($destination, $fileOverwrite);
    $this->delete();
    return $destination;
  }

  public function rename($newName)
  {
    $oldPath = $this->getPathname();
    $newPath = dirname($oldPath) . DIRECTORY_SEPARATOR . basename($newName);

    if (!@rename($oldPath, $newPath)) {
      throw new RuntimeException("Path {$oldPath} could not be renamed to {$newPath}.");
    }

    return self::open($newPath);
  }

  public function delete()
  {
    // first empty the directory
    $this->clear();

    // then delete it
    if (!@rmdir($this->getPathname())) {
      throw new RuntimeException("Could not remove directory {$this->getPathname()}.");
    }
  }

  public function clear()
  {
    foreach ($this->getIterator() as $item) {
      $item->delete();
    }

    return $this;
  }

  public function size()
  {
    $size = 0;

    foreach ($this->getIterator() as $item) {
      $size += $item->getSize();
    }

    return $size;
  }

  public function isEmpty()
  {
    return $this->getIterator()->count() === 0;
  }

  public function searchIn($regex)
  {
    return $this->getIterator()->filter(function($item, $regex) {
      return is_file($item) && File::open($item)->search($regex);
    }, array($regex));
  }

  public function searchAndReplace($regex, $replacement)
  {
    $items = [];

    foreach ($this->searchIn($regex)->getItems() as $file) {
      $count = File::open($file)->searchAndReplace($regex, $replacement);

      if ($count) {
        $items[] = $file;
      }
    }

    return $items;
  }

  public static function open($path)
  {
    if (!is_dir($path)) {
      throw new RuntimeException("Could not open directory {$path}.");
    }

    return new static($path);
  }

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

  public static function createIfNotExists($path, $mask = self::MASK)
  {
    if (is_dir($path)) {
      return static::open($path, $mask);
    }

    return static::create($path, $mask);
  }

  public static function overwrite($path, $mask = self::MASK)
  {
    if (is_dir($path)) {
      $dir = new static($path);
      $dir->delete();
    }

    return static::create($path, $mask);
  }
}