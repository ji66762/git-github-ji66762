<?php
namespace customClass;
use MongoDB;
use MongoDB\Driver as M_DB;

class Map{
	
	private $mongo = null;
	private $collection = null;
	private	$dbname		= 'map_data';
	private $collname	= 'map_info';
	
	public function __construct(){
		$this->mongo = new MongoDB\Client();
		$dbname  	= $this->dbname;
		$collname	= $this->collname;
		$this->collection = $this->mongo->$dbname->$collname;
	}	
	
	public function map_info($find = [], $option = []){
		
		$cnt	= $this->collection->count($find);
		$return_value = [];
		if($cnt > 0) {
			$result = $this->collection->find($find, $option);
			$return_value	= iterator_to_array($result);
		}
		
		$return_value['total_cnt']	= $cnt;

		return $return_value;
	}
	
	public function map_write($param = [] ){
		
		$bulk 		= new M_DB\BulkWrite;
		$manager 	= new M_DB\Manager();
		
		$bulk->insert($param);
		
		$return_array = ['code' => 1];
	
		try {
			$manager->executeBulkWrite($this->dbname.'.'.$this->collname, $bulk);
		} catch(MongoCursorException $e) {
			$return_array['code'] = -99;
		}
		unset($bulk,$manager);
		
		return $return_array;
	}
	
	public function map_update( $param = [] ){
		$bulk 		= new M_DB\BulkWrite;
		$manager 	= new M_DB\Manager();
		
		$where	= ['_id'=> $param['_id']];
		unset($param['_id']);
		
		$update_param	= [
							'$set'	=> $param	
		];
		
		$return_array = ['code' => 1];
		try {
			$bulk->update($where, $update_param);
			$manager->executeBulkWrite($this->dbname.'.'.$this->collname, $bulk);
		} catch(MongoCursorException $e) {
			$return_array['code'] = -99;
		}
		
		return $return_array;
	}
	
	public function __destruct(){
		unset($this->mongo);
		unset($this->collection);
	}
}