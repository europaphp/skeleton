<?php

namespace Helper;

class Js extends Script
{
    protected function compile($file)
    {
        return "<script type=\"text/javascript\" src=\"{$file}.js\"></script>";
    }
}