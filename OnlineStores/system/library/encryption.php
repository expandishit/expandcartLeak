<?php
final class Encryption {
	private $key;
	private $iv;
	
	public function __construct($key) {
        $this->key = hash('sha256', $key, true);
        if(function_exists('random_bytes')) {
            $this->iv = random_bytes(32);
        } else {
            $this->iv = mcrypt_create_iv(32, MCRYPT_RAND);
        }
	}
	
	public function encrypt($value) {
        if(function_exists('openssl_encrypt')) {
            return strtr(base64_encode(openssl_encrypt($value, 'aes-128-cbc', hash('sha256', $this->key, true))), '+/=', '-_,');
        } else {
            return strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->key, $value, MCRYPT_MODE_ECB, $this->iv)), '+/=', '-_,');
        }
	}
	
	public function decrypt($value) {
	    if(function_exists('openssl_decrypt')) {
            return trim(openssl_decrypt(base64_decode(strtr($value, '-_,', '+/=')), 'aes-128-cbc', hash('sha256', $this->key, true)));
        } else {
            return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->key, base64_decode(strtr($value, '-_,', '+/=')), MCRYPT_MODE_ECB, $this->iv));
        }
	}
}
?>