<?php

namespace Europa\Module;
use UnexpectedValueException;

class Module implements ModuleInterface
{
    const BOOTSTRAP = 'bootstrap.php';

    private $name;

    private $path;

    public function __construct($path)
    {
        if (!$this->path = realpath($path)) {
            throw new UnexpectedValueException(sprintf('The path "%s" does not exist.', $path));
        }

        $this->name = basename($this->path);
    }

    public function name()
    {
        return $this->name;
    }

    public function path()
    {
        return $this->path;
    }

    public function bootstrap()
    {
        if (file_exists($this->path . '/' . self::BOOTSTRAP)) {
            require_once $this->path . '/' . self::BOOTSTRAP;
        }

        return $this;
    }
}