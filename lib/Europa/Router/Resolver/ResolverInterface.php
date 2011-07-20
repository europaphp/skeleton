<?php

namespace Europa\Router\Resolver;

/**
 * Interface for defining route resolvers.
 * 
 * @category Router
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
interface ResolverInterface
{
    /**
     * Performs route matching. The parameters are returned if matched.
     * 
     * @param string $subject The subject to match.
     * 
     * @return array|false
     */
    public function query($subject);
}