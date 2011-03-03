<?php

use Europa\Crypt\Rsa;

class Test_Crypt_Rsa extends Testes_Test
{
    public function testPrivateKeyGeneration()
    {
        // generate a new key
        try {
            $private = new Rsa\PrivateKey;
        } catch (\Exception $e) {
            
        }
        
        // try and re-create the key from the generated key
        try {
            $private = new Rsa\PrivateKey($private);
        } catch (\Exception $e) {
            
        }
    }
}