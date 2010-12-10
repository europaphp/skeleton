<?php

/**
 * Creates an object representing an RSA public key.
 * 
 * @category Rsa
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart http://europaphp.org/license
 */
class Europa_Crypt_Rsa_PublicKey
{
    /**
     * The starting delineator for a PEM formatted key.
     * 
     * @var string
     */
    const PEM_START = '-----BEGIN PUBLIC KEY-----';
    
    /**
     * The ending delineator for a PEM formatted key.
     * 
     * @var string
     */
    const PEM_END = '-----END PUBLIC KEY-----';
    
    /**
     * The number of characters (maximum) per line in a PEM key.
     * 
     * @var int
     */
    const PEM_LINE_LENGTH = 64;
    
    /**
     * The PEM formatted key.
     * 
     * @var string
     */
    private $_key;
    
    /**
     * The size of the private key used in bits.
     * 
     * @var int
     */
    private $_size;
    
    /**
     * Constructs a public key. A key must be provided to the constructor since a
     * public key can only be generated with a private key.
     * 
     * @param string $key The public key.
     * 
     * @return Europa_Crypt_Rsa_PublicKey
     */
    public function __construct($key)
    {
        // make sure the pem parts are removed
        if (strpos($key, self::PEM_START) === false) {
            $key = trim($key);
            $key = str_replace(PHP_EOL, '', $key);
            $key = str_split($key, self::PEM_LINE_LENGTH);
            $key = implode(PHP_EOL, $key);
            $key = PHP_EOL . $key . PHP_EOL;
            $key = self::PEM_START . $key . self::PEM_END;
        }
        
        // generate public key from the private key
        $key = openssl_pkey_get_public($key);
        
        // if the key is not valid, throw an exception
        if (!$key) {
            throw new Europa_Crypt_Rsa_Exception(
                'The provided key is not a valid RSA public key.'
            );
        }
        
        // get key details
        $key = openssl_pkey_get_details($key);
        $this->_key  = $key['key'];
        $this->_size = $key['bits'];
    }
    
    /**
     * Returns the non-PEM formatted key.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getKey();
    }
    
    /**
     * Returns the non-PEM formatted key.
     * 
     * @return string
     */
    public function getKey()
    {
        $key = str_replace(self::PEM_START, '', $this->_key);
        $key = str_replace(self::PEM_END, '', $key);
        $key = str_replace(PHP_EOL, '', $key);
        $key = trim($key);
        return $key;
    }
    
    /**
     * Returns the PEM formatted key.
     * 
     * @return string
     */
    public function getPemKey()
    {
        return $this->_key;
    }
    
    /**
     * Encrypts a value using the public key and returns it.
     * 
     * @param string $value The value to encrypt.
     * 
     * @return string
     */
    public function encrypt($value, $padding = OPENSSL_PKCS1_PADDING)
    {
        openssl_public_encrypt($value, $value, $this->_key, $padding);
        return $value;
    }
    
    /**
     * Decrypts a value using the public key and returns it.
     * 
     * @param string $value The value to decrypt.
     * 
     * @return string
     */
    public function decrypt($value, $padding = OPENSSL_PKCS1_PADDING)
    {
        openssl_public_decrypt($value, $value, $this->_key, $padding);
        return $value;
    }
}