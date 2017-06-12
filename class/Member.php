<?php
namespace customClass;
use MongoDB;
use MongoDB\Driver as M_DB;

class Member{
	
	public function login_proc($param){
		$string = $param['userid'].hash('sha256', $param['password']).$param['userip'].$param['logindate'])
		mcrypt_encrypt
	}
}