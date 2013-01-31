<?php

namespace Europa\Version;

interface VersionInterface
{
    public function __toString();
    public function set($version);
    public function is($version);
}