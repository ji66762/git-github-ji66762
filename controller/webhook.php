<?php
use BitDepoly\Hook as BitHook;
if(!defined('_HOME_')) exit();


$app->get('/webhook[/]', function ( $request,  $response) {
	$depoly 					= new BitHook();
	$depoly->server_name		= '1';
	$depoly->branch				= 'master';
	$depoly->repo_dir			= _HOME_;//'https://ji667623:!!@@nq9408nc@bitbucket.org/ji667623/slimf_restful.git';
	$depoly->web_root_dir		= _HOME_;
	$depoly->log_file			= _HOME_.'/logs/deploy.log';
	$depoly->pushbullet_token 	= 'o.GONxlIU7JW7ZsmaUmFVKEYHQafpFab45'; 
	$depoly->pushbullet_user 	= array('ji6676@naver.com'); 
	$depoly->deployment();
});
