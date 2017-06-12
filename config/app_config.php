<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');

defined('_IP_HEADER_ARRAY_') OR define('_IP_HEADER_ARRAY_',  [
																'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR'
																,'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR'
																,'HTTP_FORWARDED', 'REMOTE_ADDR']);
defined('_HOME_') OR define('_HOME_', $_SERVER['DOCUMENT_ROOT'] );

require _HOME_.'/vendor/autoload.php';

#error handler
$config	= [];
$config['displayErrorDetails'] = true; #상세 에러
