<?php

namespace Europa\Fs;

interface LocatorAwareInterface
{
    public function setLocator(LocatorInterface $locator);

    public function getLocator();
}