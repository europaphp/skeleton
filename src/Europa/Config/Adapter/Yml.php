<?php

namespace Europa\Config\Adapter;
use Europa\Exception\Exception;

class Yml
{
    private $file;

    public function __construct($file)
    {
        if (!function_exists('yaml_parse_file')) {
            Exception::toss('In order to use the YAML config adapter you must install the PECL YAML extension. See http://php.net/yaml for more information.');
        }

        if (!is_file($this->file = $file)) {
            Exception::toss('The YAML configuration file "%s" does not exist.', $file);
        }
    }

    public function __invoke()
    {
        return yaml_parse_file($this->file);
    }
}