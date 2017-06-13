<?php
if(!defined("_HOME_")) exit();	

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


#app ip check 
$app->add(new RKA\Middleware\IpAddress());

#app run before run
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

#컨테이너 가져오기
$container = $app->getContainer();

#컨테이너에 custom 404 추가
$container['notFoundHandler'] = function($container){
	return function(Request $request,Response $response) use ($container) {
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
$mw	= function (Request $request,Response $response, $next) {	
	$header = $request->getHeaders();
	if(!isset($header['HTTP_AUTHENTICATION'])) return $response->withJson(['error' => 'AUTHENTICATION false'], 403);

	$response = $next($request, $response);
	return $response;
};

$app->add($mw);
