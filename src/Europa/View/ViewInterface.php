<?php

namespace Europa\View;

interface ViewInterface
{
    public function render(array $context = []);
}