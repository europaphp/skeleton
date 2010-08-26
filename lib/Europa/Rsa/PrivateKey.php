<?php

/**
 * Creates an object representing an RSA private key.
 * 
 * @category Rsa
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  (c) 2010 Trey Shugart
 * @link     http://europaphp.org/license
 */
class Europa_Rsa_PrivateKey
{
	/**
	 * The starting delineator for a PEM formatted key.
	 * 
	 * @var string
	 */
	const PEM_START = '-----BEGIN RSA PRIVATE KEY-----';
	
	/**
	 * The ending delineator for a PEM formatted key.
	 * 
	 * @var string
	 */
	const PEM_END = '-----END RSA PRIVATE KEY-----';

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
	 * Constructs a private key. If no key is provided, a new one is generated
	 * using the specified size.
	 * 
	 * @param string $key The public key.
	 * @param int $size The size of the private key to generate in bits.
	 * @return Europa_Rsa_PublicKey
	 */
	public function __construct($key = null, $size = 1024)
	{
		if ($key) {
			// format the key to PEM format
			if (strpos($key, self::PEM_START) === false) {
				$key = trim($key);
				$key = str_replace(PHP_EOL, '', $key);
				$key = str_split($key, self::PEM_LINE_LENGTH);
				$key = implode(PHP_EOL, $key);
				$key = PHP_EOL . $key . PHP_EOL;
				$key = self::PEM_START . $key . self::PEM_END . PHP_EOL;
			}
			$key = openssl_pkey_get_private($key);
		} else {
			$key = openssl_pkey_new(array(
				'private_key_bits' => $size,
				'private_key_type' => OPENSSL_KEYTYPE_RSA
			));
		}
		if (!$key) {
			throw new Europa_Rsa_Exception(
				'The provided key is not a valid RSA private key.',
				Europa_Rsa_Exception::INVALID_PRIVATE_KEY
			);
		}
		openssl_pkey_export($key, $key);
		$this->_key = $key;
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
	 * Returns an instance of Europa_Rsa_PublicKey representing the public key.
	 * 
	 * @return string
	 */
	public function getPublic()
	{
		$key = openssl_pkey_get_private($this->_key);
		$key = openssl_pkey_get_details($key);
		return new Europa_Rsa_PublicKey($key['key']);
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
	 * Encrypts a value using the private key and returns it.
	 * 
	 * @param string $value The value to encrypt.
	 * @return string
	 */
	public function encrypt($value, $padding = OPENSSL_PKCS1_PADDING)
	{
		openssl_private_encrypt($value, $value, $this->_key, $padding);
		return $value;
	}
	
	/**
	 * Decrypts a value using the private key and returns it.
	 * 
	 * @param string $value The value to decrypt.
	 * @return string
	 */
	public function decrypt($value, $padding = OPENSSL_PKCS1_PADDING)
	{
		openssl_private_decrypt($value, $value, $this->_key, $padding);
		return $value;
	}
}