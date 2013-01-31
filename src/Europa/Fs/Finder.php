<?php

namespace Europa\Fs;
use AppendIterator;
use ArrayIterator;
use DirectoryIterator;
use Europa\Exception\Exception;
use Europa\Fs\Directory;
use Europa\Fs\File;
use Europa\Fs\Iterator\CallbackFilterIterator;
use Europa\Fs\Iterator\DotFilterIterator;
use Europa\Fs\Iterator\FsIterator;
use Europa\Fs\Iterator\LimitIterator;
use Europa\Fs\Iterator\PathnameFilterIterator;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Traversable;

class Finder implements IteratorAggregate
{
    const WILDCARD = '*';

    private $dirs = [];

    private $notDirs = [];
    
    private $is = [];
    
    private $not = [];
    
    private $filters = [];
    
    private $depth = -1;

    private $offset = 0;

    private $limit = -1;
    
    private $prepend = [];
    
    private $append = [];
    
    public function getIterator()
    {
        // So we can merge results.
        $it = new AppendIterator;
        
        // Prepend paths.
        foreach ($this->prepend as $prepend) {
            $it->append($this->normalizeTraversable($prepend));
        }
        
        // Add directories recursively.
        foreach ($this->dirs as $dir) {
            if (!in_array($dir, $this->notDirs)) {
                $it->append($this->getRecursiveIterator($dir));
            }
        }

        // Limit results.
        $it = new LimitIterator($it);
        $it->setOffset($this->offset);
        $it->setLimit($this->limit);

        // Apply filters.
        $it = $this->applyFilters($it);

        // Append paths.
        foreach ($this->append as $append) {
            $it->append($this->normalizeTraversable($append));
        }

        return $it;
    }

    public function getFsIterator()
    {
        return new FsIterator($this->getIterator());
    }
    
    public function prepend($prepend)
    {
        $this->prepend[] = $prepend;
        return $this;
    }
    
    public function append($append)
    {
        $this->append[] = $append;
        return $this;
    }
    
    public function is($pattern)
    {
        $this->is[] = $pattern;
        return $this;
    }
    
    public function not($pattern)
    {
        $this->not[] = $pattern;
        return $this;
    }
    
    public function contains($pattern)
    {
        return $this->files()->filter(function($item) use ($pattern) {
            return (new File($item))->contains($pattern);
        });
    }
    
    public function files()
    {
        return $this->filter(function($item) {
            return $item->isFile();
        });
    }
    
    public function directories()
    {
        return $this->filter(function($item) {
            return $item->isDir();
        });
    }
    
    public function filter($filter)
    {
        if (!is_callable($filter)) {
            throw new InvalidArgumentException('The filter must be callable.');
        }

        $this->filters[] = $filter;

        return $this;
    }
    
    public function in($path)
    {
        if (is_dir($real = realpath($path))) {
            $this->dirs[] = $real;
        } elseif (strpos($path, self::WILDCARD)) {
            $paths = explode(self::WILDCARD, $path, 2);

            if (!is_dir($paths[0])) {
                return $this;
            }

            foreach (new DirectoryIterator($paths[0]) as $item) {
                if ($item->isDot()) {
                    continue;
                }

                $this->in($item->getRealpath() . $paths[1]);
            }
        }

        return $this;
    }

    public function notIn($path)
    {
        if (is_dir($real = realpath($path))) {
            $this->notDirs[] = $real;
        } elseif (strpos($path, self::WILDCARD)) {
            $paths = explode(self::WILDCARD, $path, 2);

            foreach (new DirectoryIterator($paths[0]) as $item) {
                if ($item->isDot()) {
                    continue;
                }

                $this->notIn($item->getRealpath() . $paths[1]);
            }
        }

        return $this;
    }
    
    public function depth($depth = null)
    {
        if (is_null($depth) || $depth < -1) {
            $depth = -1;
        }

        $this->depth = $depth;

        return $this;
    }

    public function offset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }

    public function page($page, $limit)
    {
        $page = $page ? $page : 1;

        $this->limit  = $limit;
        $this->offset = ($page * $limit) - $limit;

        return $this;
    }
    
    public function sort($cb)
    {
        $this->sort[] = $cb;
        return $this;
    }

    public function toArray()
    {
        $arr = [];

        foreach ($this->getIterator() as $item) {
            $arr[] = $item->getPathname();
        }

        return array_unique($arr);
    }
    
    private function applyFilters(Iterator $iterator)
    {
        $iterator = new DotFilterIterator($iterator);
        $iterator = new PathnameFilterIterator($iterator, $this->is, $this->not);
        $iterator = new CallbackFilterIterator($iterator, $this->filters);
        return $iterator;
    }
    
    private function getRecursiveIterator($dir)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        $iterator->setMaxDepth($this->depth);

        return $iterator;
    }
    
    private function normalizeTraversable($iterator)
    {
        if ($iterator instanceof IteratorAggregate) {
            $iterator = $iterator->getIterator();
        } elseif ($iterator instanceof Iterator) {
            $iterator = $iterator;
        } elseif ($iterator instanceof Traversable || is_array($iterator)) {
            $traversable = new ArrayIterator();

            foreach ($iterator as $item) {
                $traversable->append($item instanceof SplFileInfo ? $item : new SplFileInfo($item));
            }

            $iterator = $traversable;
        } else {
            $iterator = new ArrayIterator([new SplFileInfo($iterator)]);
        }
        
        return new FsIterator($iterator);
    }
}