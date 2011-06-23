<?php

namespace Helper;

class Css extends Script
{
    protected function compile($file)
    {
        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$file}.css\" />";
    }
}