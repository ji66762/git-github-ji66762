<?php
if(!defined("_HOME_")) exit();	

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/*
HTTP 인증 
200 : 성공
400 : bad request 
401 : 인증, 인가 실패
403 : 권한 없음
404 : 해당 리소스 없음
500 : 서버 사이드 에러

Response Body 에 에러 명시적 표현
error code 를 미리 설계 ex) 2000 ~ : 인증 관련, 3000 ~ : 주문 관련
에러 코드와 메시지는 별도의 파일로 만드는 것이 다국어 목적에 유용함

*/

#app ip check 
$app->add(new RKA\Middleware\IpAddress());

#app run before run
/*
$app->add( function( Request $request,Response $response, callable $next ){
	foreach(_IP_HEADER_ARRAY_ as $v){
		if(getenv($v)){
			$client_ip = getenv($v);
			if( mb_strpos($client_ip, ',', 0) > -1){
				$sp = explode(',',  $client_ip);
				$client_ip = $sp[0];
			}
			break;
		}
	}
	defined('_CLIENT_IP_')  	OR define('_CLIENT_IP_', $client_ip, True);
	return $next($request, $response);
});
*/

#컨테이너 가져오기
$container = $app->getContainer();

#컨테이너에 custom 404 추가
$container['notFoundHandler'] = function($container){
	return function( $request, $response) use ($container) {
		  return $container['response']
            ->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('not allowed');
	};
};

#컨테이너에 logger 추가
$container['logger'] = function($container) {
	$date 	= date('Ymd');
	$logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("./logs/app_".$date.".log");
    $logger->pushHandler($file_handler);
    return $logger;
};

#인젝션 체크 추가
$container['param_check']	= function($container){
	return function($param) {
		$value = $param;
		$value	= is_array($param) ? implode($value) : $value;
		$value = addslashes($value);
		//$value = preg_replace("/[^a-z0-9]/i", "\", $value);
		return $value;	
	};
};

#검색 필드 추가
$container['find_filed']	= function(){
	return ['id', 'name', 'gu', 'tag'];
};


#auth middleware 
/*
$mw	= function (Request $request,Response $response, $next) {	
	
	$apicode	= $request->getHeader('HTTP_APICODE'); 

	if( !isset($apicode[0]) || empty($apicode[0])) {
		$chk_array	= ['HTTP_AUTHORIZATION'=> '', 'PHP_AUTH_USER' => '', 'PHP_AUTH_PW'=> ''];
		$header_check = null;
		$for_check	= true;
		foreach($chk_array as $k=>$v){
			$header_check = $request->getHeader($v);
			if( !isset($heaer_check[0]) || empty($heaer_check[0] )) {
				$for_check = false;
				break;
			}
			$chk_array[$k] = $header_check[0];
			unset($header_check);
		}
		if($for_check !== true) return $response->withJson(['error' => _ERROR_MSG_['ENG']['403']], 403);
		
		try{
			$oauth		= new customClass\Oauth();
			$auth_array	= $oauth->get_apicode($chk_array);
			unset($oauth, $chk_array);	
		}
		catch( Exception $e ){
			return $response->withJson(['error' => _ERROR_MSG_['ENG']['500']], 500);
		}
		
		if($auth_array['code'] != 1) return $response->withJson(['error' => _ERROR_MSG_['ENG']['500']], 500);
		
		return $response->withJson($auth_array, 200);
	}
	else{
		
		$api_code   = $apicode[0];
	
		try{
			$lfile = file_get_contents(_AUTH_FILE_);	
		}
		catch( Exception $e ){
			return $response->withJson(['error' => _ERROR_MSG_['ENG']['500']], 500);
		}

		$lfile_array = json_decode($lfile, true);
		
		$time		= time();
		if( array_key_exists($api_code, $lfile_array) ) {
			$code_expire	= $lfile_array[$api_code];
			if($code_expire < $time) return $response->withJson(['error' => _ERROR_MSG_['ENG']['401']], 401);
		}
		else{
			return $response->withJson(['error' => _ERROR_MSG_['ENG']['403']], 403);
		}
		$method			= $request->getHeader('REQUEST_METHOD');
		$authorizations	= $lfile_array[$api_code]['authorizations'];
		
		if( !in_array($method, $authorizations) ) return $response->withJson(['error' => _ERROR_MSG_['ENG']['403']], 403);
		
		$next($request, $response);
	}
};
*/
/*
미들웨어 인증
*/
$mw	= function ( $request, $response, $next) {	
	
	foreach(_IP_HEADER_ARRAY_ as $v){
		if(getenv($v)){
			$client_ip = getenv($v);
			if( mb_strpos($client_ip, ',', 0) > -1){
				$sp = explode(',',  $client_ip);
				$client_ip = $sp[0];
			}
			break;
		}
	}
	defined('_CLIENT_IP_')  	OR define('_CLIENT_IP_', $client_ip, True);
	
	if(_PATH_ != 'webhook'){
	
		$authorization =  $request->getHeader('HTTP_AUTHORIZATION'); 	
		if( !isset($authorization[0]) || empty($authorization[0]) ) return $response->withJson(['error' => _ERROR_MSG_['ENG']['403']], 403); 

		$sp_auth		= explode(' ', $authorization[0]);
		if(sizeof($sp_auth) != 2)  return $response->withJson(['error' => _ERROR_MSG_['ENG']['404']], 404); 

		$auth_key		= strtoupper($sp_auth[0]);
		$auth_string	= $sp_auth[1];

		if( !in_array($auth_key, ['APITOKEN', 'BEARER', 'REFRESHTOKEN']) ) return $response->withJson(['error' => _ERROR_MSG_['ENG']['403']], 403); 

		if( $auth_key == 'BEARER' ){
			$requestUri		= $request->getHeader('REQUEST_URI');	
			$requestMethod	= $request->getHeader('REQUEST_METHOD');
			if ( $requestUri != '/login'  || $requestMethod != 'GET')  return $response->withJson(['error' => _ERROR_MSG_['ENG']['404']], 404); 
		}
		else if($auth_key == 'REFRESHTOKEN'){
			$requestUri		= $request->getHeader('REQUEST_URI');	
			$requestMethod	= $request->getHeader('REQUEST_METHOD');
			if ( $requestUri != '/request_token'  || $requestMethod != 'POST')  return $response->withJson(['error' => _ERROR_MSG_['ENG']['404']], 404); 	
		}
		else if($auth_key == 'APITOKEN'){
			if( $requestUri == '/login' ) return $response->withJson(['error' => _ERROR_MSG_['ENG']['404']], 404); 

			$oauth  = customClass/Member();
			$info	= $oauth->find_info(['api_token' => $auth_string]);

			if( !isset( $info['code'] ) || $info['code'] != 1 )  {
				$err_code	= $info['code'] == 99 ? 404 : 403;
				$err_msg	= _ERROR_MSG_['ENG'][$err_code];
				return $response->withJson(['error' =>$err_msg], $err_code); 
			}

			$time			= time();
			if( $info['expire'] >= $time ) return $response->withJson(['error' => _ERROR_MSG_['ENG']['401']], 401); 		

			unset($info, $oauth);
		}
		else{
			return $response->withJson(['error' => _ERROR_MSG_['ENG']['404']], 404); 	
		}
		
	}
	else{
		$ip_array = array('10.1.21.229');
		if(!in_array(_CLIENT_IP_, $ip_array)) return $response->withJson(['error' => _ERROR_MSG_['ENG']['404']], 404); 	
		
			//if()
	}
	
	return $next($request, $response);
	//$next($request, $response);
};

//$app->add($mw);
