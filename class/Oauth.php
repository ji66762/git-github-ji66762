<?php
namespace customClass;
use MongoDB;
use MongoDB\Driver as M_DB;

class Oauth{
	private $authorization_key	= "Oau7H_7Est!HanDasd@!59asd%jktl";
	private $dbs =  ['map_oauth' => '', 'map_authorization' => '', 'map_accesstoken' => ''];
	
	public function __construct(){
		$mongo = new MongoDB\Client();
		$db    = $mongo->map_data;
		foreach($this->dbs as $k=> $v) $this->dbs[$k] = $db->$k;
	}
	
	public function authorization($param){
		$bearer = $param['bearer'];	
	}
	
	public function client_check($find = [], $option = []){
	
		$cnt	= $this->dbs['map_oauth']->count($find);
		if($cnt == 0) return ['code'=>99];
		unset($cnt);	
		
		$result = $this->dbs['map_oauth']->find($find, $option);
			
		$authorization_code = $this->make_authorization($result);
		
		return $authorization_code;
	}
	
	private function make_authorization($param){
		
		$time	= time();
		$rand	= rand(1000, 9999);

		$hash	= hash("sha256", $rand.$param['client_id'].$time.$this->authorization_key.$param['client_secrect'].$param['redirect_url']);
		
		$chk_param	= ['time' => $time, 'rand' => $rand, 'authorization' => $hash];
		
		$bulk 		= new M_DB\BulkWrite;
		$manager 	= new M_DB\Manager();
	
		$bulk->insert($chk_param);
		unset($chk_param);
		
		$return_array = ['code' => 1];
		
		try {
			$manager->executeBulkWrite('map_data.map_authorization', $bulk);
			$return_array['authorization'] = $hash;
		} catch(MongoCursorException $e) {
			$return_array['code'] = -99;
		}
		unset($bulk,$manager);
		
		return $return_array;
	}
	
}
