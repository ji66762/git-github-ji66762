<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use MongoDB\BSON\ObjectID;

require_once $_SERVER['DOCUMENT_ROOT'].'/config/app_config.php';

#app 생성
$app = new \Slim\App(['settings' => $config]);

#app 커스텀 설정
require_once _HOME_."/app_sub/app_custom.php";

#인덱스 접근 금지 (404)
$app->any('/', function(Request $request, Response $response)  {
	$this->logger->addInfo(_CLIENT_IP_." : ./index.php");
	return $this->get("notFoundHandler")($request, $response);
});

#mongodb map info
$app->get('/map[/{type}/{id}]', function (Request $request, Response $response, $args) {
	
	//$app->redirect('http://www.naver.com');
	
	$id		= isset($args["id"]) ? $this->get('param_check')( $args["id"] ) : '';
	$type	= isset($args["type"]) ? $this->get('param_check')( $args["type"] ) : '';
	$type_array	= $this->get("find_filed");	
	
	$log_string	= _CLIENT_IP_." : /map";
	$log_string .= !empty($id) ? "/".$id : "";
	$log_string .= !empty($type) ? "/".$type : "";
	
	$this->logger->addInfo($log_string.", connect");
	
	if( !empty($type) ){
		if( !in_array($type, $type_array) ) return $this->get("notFoundHandler")($request, $response);
	}
	
	$find = !empty($id) && !empty($type) ? ( $type == 'id' ?  [ '_id' => new ObjectID( $id ) ] : [$type => $id] ): array(); 
	$find["use_yn"] = "Y";
	#$option	= array( "projection"=>array( "name" => 1, "_id" => 0 ) );
	$option = array();
	
	$map	= new customClass\Map();
	$result	= $map->map_info($find, $option);
	unset($map);
	
	$this->logger->addInfo($log_string.", result : ".$result["total_cnt"]);
	unset($result["total_cnt"]);
	
	return json_encode($result);
});

#mongodb map write
$app->post('/map/', function (Request $request, Response $response) {

	$chk_param	= ['posx' => '', 'posy' => '', 'name' => '', 'gu' => '', 'phone' => '', 'address' => '', 'likecnt' => 0, 'dislikecnt' => 0, 'tag' => [] ];
	$paresBody	= $request->getParsedBody();
	$chk 		= true;
	foreach($chk_param as $k => $v){
		if(!isset($paresBody[$k])) {
			$chk = false;
			break;
		}
		
		if($k == 'tag'){ 
			if( !is_array( $paresBody[$k]) ) {
				$chk = false;
				break;
			}

			foreach($paresBody[$k] as $v){
				$chk_param[$k][] = $this->get('param_check')($v);
			}
		}
		else{
			$chk_param[$k]	= $this->get('param_check')( $paresBody[$k] );	
		}
	}
	if(!$chk)  return $this->get("notFoundHandler")($request, $response);

	$map	= new customClass\Map();
	
	$result	= $map->map_write($chk_param);
	unset($map);
	
	return json_encode($result);
});


#mongodb map update
$app->put('/map/{id}', function (Request $request, Response $response, $args) {
	
	if( !isset($args['id'])) return $this->get("notFoundHandler")($request, $response);
	
	$id			=  $this->get('param_check')($args['id']);

	$chk_param	= [ 'posx' => '', 'posy' => '', 'name' => '', 'gu' => '', 'phone' => '', 'address' => '', 'likecnt' => 0, 'dislikecnt' => 0, 'tag' => [] ];
	$paresBody	= $request->getParsedBody();
	$chk 		= true;
	foreach($chk_param as $k => $v){
		if(!isset($paresBody[$k])) {
			$chk = false;
			break;
		}
		
		if($k == 'tag'){ 
			if( !is_array( $paresBody[$k]) ) {
				$chk = false;
				break;
			}

			foreach($paresBody[$k] as $v){
				$chk_param[$k][] = $this->get('param_check')($v);
			}
		}
		else{
			$chk_param[$k]	= $this->get('param_check')( $paresBody[$k] );	
		}
	}
	if(!$chk)  return $this->get("notFoundHandler")($request, $response);

	$map	= new customClass\Map();
	
	$chk_param['_id']	= new ObjectID( $id ); 
	
	$result	= $map->map_update($chk_param);
	unset($map);
	
	return json_encode($result);
	
});


#지도 사용 플래그 변경
$app->delete('/map/{id}', function (Request $request, Response $response, $args) {
	if( !isset($args['id'])) return $this->get("notFoundHandler")($request, $response);
	$id			=  $this->get('param_check')($args['id']);
	
	$chk_param	= [];
	$chk_param['_id']		= new ObjectID( $id ); 
	$chk_param['use_yn']	= "N";
	
	$map	= new customClass\Map();
	
	$result	= $map->map_update($chk_param);
	unset($map);
	
	return json_encode($result);
});

#지도 좋아요/안좋아요
$app->put('/map/{id}/{type}', function (Request $request, Response $response, $args) {
	
	if( !isset($args['id'])) return $this->get("notFoundHandler")($request, $response);
	if( !isset($args['type'])) return $this->get("notFoundHandler")($request, $response);
	
	$id			=  $this->get('param_check')($args['id']);
	$type		=  $this->get('param_check')($args['type']);
	if($type != 'like' &&  $type != 'dislike') return $this->get("notFoundHandler")($request, $response);
	
	$filed_name	= $type == 'like' ? 'like_cnt' : 'dislike_cnt';
	
	$chk_param	= [];
	$chk_param['_id']		= new ObjectID( $id ); 
	$chk_param[$filed_name]	= $filed_name." + 1";
	
	$map	= new customClass\Map();
	$result	= $map->map_update($chk_param);
	unset($map);
	
	return json_encode($result);
});

#oauth authorization
$app->get('/auth[/]', function (Request $request, Response $response) {
	
	$paresBody	= $request->getParsedBody();
	
	//if(!isset($paresBody['response_type']) ) return $this->get("notFoundHandler")($request, $response);
	if(!isset($paresBody['client_id']) ) return $this->get("notFoundHandler")($request, $response);
	if(!isset($paresBody['state']) ) return $this->get("notFoundHandler")($request, $response);
	if(!isset($paresBody['redirect_url']) ) return $this->get("notFoundHandler")($request, $response);
	if(!isset($paresBody['scope']) ) return $this->get("notFoundHandler")($request, $response);
	
	//$response_type =  $this->get('param_check')($paresBody['response_type']);	
	$client_id 		=  $this->get('param_check')($paresBody['client_id']);	
	$state 			=  $this->get('param_check')($paresBody['state']);
	$redirect_url	=  $this->get('param_check')($paresBody['redirect_url']);
	$scope			=  $this->get('param_check')($paresBody['scope']);
	
	$oauth = new customClass/Oauth();
	$param	= ['client_id' => $client_id, 'state' => $state, 'redirect_url' => $redirect_url, 'scope' => $scope];
	
	$authcode	= $oauth->client_check($param);
	unset($param);	
	
	$query_string = '';
	foreach($authcode as $k => $v) $query_string .= $k.'='.$v;
		
	$redirect_url .= '?'.$query_string;
	
	return $response->withHeader('Location', $redirect_url);
});

#response access_token, refresh_token, expire 
$app->get('/auth/access_token', function(Request $request, Response $response)  use($app) {
	if(!isset($paresBody['client_id']) ) return $this->get("notFoundHandler")($request, $response);		
	if(!isset($paresBody['client_secret']) ) return $this->get("notFoundHandler")($request, $response);		
	if(!isset($paresBody['redirect_uri']) ) return $this->get("notFoundHandler")($request, $response);	
	if(!isset($paresBody['code']) ) return $this->get("notFoundHandler")($request, $response);	
	
	$client_id 		=  $this->get('param_check')($paresBody['client_id']);	
	$client_secret 	=  $this->get('param_check')($paresBody['client_secret']);
	$redirect_uri	=  $this->get('param_check')($paresBody['redirect_url']);
	$code			=  $this->get('param_check')($paresBody['code']);
	
	$oauth = new customClass/Oauth();
	$param	= ['client_id'=> $client_id, 'client_secret' =>$client_secret, 'redirect_uri' => $redirect_uri, 'code'	=> $code ];
	
	$result	= $oauth->access_token($param);
	
	return json_encode($result);
});


$app->run();
