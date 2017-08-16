<?php
namespace customClass;
use MongoDB;
use MongoDB\Driver as M_DB;

class Member{
	
	private $mongo = null;
	private $collection	= null;
	
	public function __construct(){
		$this->mongo = new MongoDB\Client();
	}
	
	private function set_collection($dbname, $colname){
		$this->collection = $this->mongo->$dbname->$colname;
	}
	
	private function unset_collection(){unset( $this->collection );}
	
	private function enc_return($string = ''){
		if(empty($string)) return '';
		$encObj 	= new customClass\Encrypt();
		$encstring	= $encObj->encrypt($string );
		unset($encObj);
		return $encstring;
	}
	
	private function member_count($find = [], $unset = false){
		$this->set_cpllection('map_data','member_info');
		$cnt = $this->collection->find($find);
		if($unset !== false) $this->unset_collection();
		return $cnt;
	}
	
	public function member_login($find = []){
		
		$cnt	= $this->member_count($find);
		if($cnt == 0 ) return [];
		unset($cnt);
	
		$mem_info 	= $this->collection->find($find);
		
		$logindate	= date('Y-m-d H:i:s');
		$mem_uid	= $mem_info['_id'];
		unset($mem_info);
		
		$sid	= session_id();
		
		$string 	= $find['userid'].'|'.$find['userip'].'|'.$mem_uid.'|'.$logindate.'|'.$sid;
		$enc_string	= $this->enc_return($string);
		if(empty($enc_string)) return [];
 		
		$bulk 		= new M_DB\BulkWrite;
		$manager 	= new M_DB\Manager();
		
		$where	= [
					'_id' => $mem_uid
		];
			
		$update_param	= [
							'$set'	=> [
											'login_ip' 		=> $find['userip']
											,'login_date'	=> $logindate
											,'login_end'	=> $encstring
							]	
		];
		
		$return_array = ['code' => 1, 'authcode' => $enc_string];
		try {
			$bulk->update($where, $update_param);
			$manager->executeBulkWrite($dbname.'.'.$col_name, $bulk);
		} catch(MongoCursorException $e) {
			$return_array['code'] = -99;
		}
		
		$_SESSION['userid']		= $find['userid'];
		$_SESSION['userip']		= $find['userip'];
		$_SESSION['uid']		= $mem_uid;
		$_SESSION['logindate']	= $logindate;
		
		return $return_array;
	}
	
	public function member_join($find = []){
			
	}
}