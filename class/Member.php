<?php
namespace customClass;
use MongoDB;
use MongoDB\Driver as M_DB;

class Member{
	
	private $encObj = null;

	public function __construct(){
		$this->encObj = new customClass\Encrypt();
		
	}
	
	public function login_proc($param){
		$string 	= $param['userid'].hash('sha256', $param['password']).$param['userip'].$param['logindate'];
		$encstring	= $this->encObj->encrypt($string );
	}
	
}