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

if( is_file(_HOME_.'/'._PATH_.'/'._PATH_.'.php')){
	require_once _HOME_.'/'._PATH_.'/'._PATH_.'.php';
}
else{
	header('Status: 404', TRUE, 404);
	$err_msg	= json_encode(['error'=>_ERROR_MSG_['ENG'][404]]);
	exit($err_msg);
}
$app->run();
