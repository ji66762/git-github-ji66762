<?php
require_once __DIR__ . '/../vendor/autoload.php';
use BitDepoly\Hook;


$depoly = new Hook();

$depoly->server_name = 'node1';
$depoly->branch = 'master';
$depoly->repo_dir = '/home/git/xxxxxx.git';
$depoly->web_root_dir = '/home/node1/public_html';
$depoly->log_file = '/home/node1/deploy.log';
$depoly->pushbullet_token = '';
$depoly->pushbullet_user = array('xxxx@wbp.co.kr');

$depoly->deployment();
