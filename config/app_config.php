<?php
defined('_IP_HEADER_ARRAY_') OR define('_IP_HEADER_ARRAY_',  [
																'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR'
																,'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR'
																,'HTTP_FORWARDED', 'REMOTE_ADDR']);
defined('_HOME_') OR define('_HOME_', $_SERVER['DOCUMENT_ROOT'] );
defined('_AUTH_KEY_') OR define('_AUTH_KEY_', '1345lashdf57sjv8234752jkfnxvhre9046idfhlhklsfd8');

defined('_ERROR_MSG_') OR define('_ERROR_MSG_', [	
													'KR' 	=> []
													,'ENG'	=> [
																 '403' => 'auth false'
																,'404' => 'Not Found'
																,'500' => 'internal server error'
													]
						  ]);

$REQUEST_URI	= $_SERVER['REQUEST_URI'];
$SP_URI			= explode('/', $REQUEST_URI);

defined('_PATH_') OR define('_PATH_', $SP_URI[1]);

require _HOME_.'/vendor/autoload.php';

#error handler
$config	= [];
$config['displayErrorDetails'] = true; #상세 에러
$config['cookies.encrypt']	= true;
$config['cookies.secret_key'] = base64_encode("ak12k14k124k124k");
