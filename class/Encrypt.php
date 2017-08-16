<?php
namespace customClass;

class Encrypt {
	
	private $key	= null;
	private $iv		= null;

	public function __construct() {
		$this->key			= $this->hex2bin('1ae49m1k1zb1207673f07f1260b1452232k');
		$this->iv			= $this->hex2bin('2811ua21570m415ctio04f25drd47d9a');
	}

	private function hex2bin($hexdata) {
		$bindata="";

		for ($i=0;$i<strlen($hexdata);$i+=2) $bindata.=chr(hexdec(substr($hexdata,$i,2)));
		
		return $bindata;	
	}
	
	private function toPkcs7($value) {
		if ( is_null ($value) )$value = "" ;

		$padSize = 16 - (strlen ($value) % 16) ;
		return $value . str_repeat (chr ($padSize), $padSize) ;
	}
	
	
	private function fromPkcs7($value) {
		$valueLen = strlen ($value) ;
		
		if ( $valueLen % 16 > 0 ) $value = "";
		
		$padSize = ord ($value{$valueLen - 1}) ;
		
		if ( ($padSize < 1) or ($padSize > 16) )$value = "";
		
		// Check padding.
		for ($i = 0; $i < $padSize; $i++){
			if ( ord ($value{$valueLen - $i - 1}) != $padSize ) $value = "";
		}
		
		return substr ($value, 0, $valueLen - $padSize) ;
	}
	

	public function encrypt ( $value, $isId = false) {
		if ( is_null ($value) ) $value = "" ;
		$value = $this->toPkcs7 ($value) ;
	
		$output = mcrypt_encrypt (MCRYPT_RIJNDAEL_128, $this->key, $value, MCRYPT_MODE_CBC, $this->iv) ;
		return base64_encode( base64_encode ($output) );
	}

	public function decrypt ($value, $isId = false) {
		if ( is_null ($value) ) $value = "" ;   
		$value = base64_decode( base64_decode ($value) );
		
		$output = mcrypt_decrypt (MCRYPT_RIJNDAEL_128, $this->key, $value, MCRYPT_MODE_CBC, $this->iv);
		return $this->fromPkcs7($output) ;
	}
}	
?>