<?php

/**
 * RSA exceptions.
 * 
 * @category Exceptions
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Rsa_Exception extends Europa_Exception
{
    /**
     * Invalid private key.
     * 
     * @var int
     */
    const INVALID_PRIVATE_KEY = 1;
    
    /**
     * Invalid public key.
     * 
     * @var int
     */
    const INVALID_PUBLIC_KEY = 2;
}