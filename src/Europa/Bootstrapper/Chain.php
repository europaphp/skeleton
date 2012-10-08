<?php

namespace Europa\Bootstrapper;

/**
 * Allows multiple bootstrappers to be chained together.
 * 
 * @category Boot
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class Chain implements BootstrapperInterface
{
    /**
     * The chain of bootstrappers to boot with.
     * 
     * @var array
     */
    private $bootstrappers = [];
    
    /**
     * Adds a bootstrapper to the chain.
     * 
     * @param BootstrapperInterface $boot The bootstrapper to add.
     * 
     * @return BootChain
     */
    public function add(BootstrapperInterface $bootstrapper)
    {
        $this->bootstrappers[] = $bootstrapper;
        return $this;
    }
    
    /**
     * Bootstraps the app.
     * 
     * @return BootstrapperInterface
     */
    public function bootstrap()
    {
        foreach ($this->bootstrappers as $bootstrapper) {
            $bootstrapper->bootstrap();
        }
        return $this;
    }
}