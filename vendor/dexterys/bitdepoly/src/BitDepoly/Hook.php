<?php

namespace BitDepoly;

use Filipac\Ip;
use PHPushbullet\PHPushbullet;

class Hook
{
    public $server_name = 'node1';
    public $branch = 'master';
    public $repo_dir = '';
    public $web_root_dir = '';
    public $log_file = './deploy.log';

    public $git_bin_path = '/usr/bin/git';

    public $pushbullet_token = '';
    public $pushbullet_user = array('');

    private $visit_ip;
   // public $bitbucket_ip = array('104.192.143.0|104.192.143.255');
	 public $bitbucket_ip = array('1.1.1.1|255.255.255.255');
	
    public function __construct()
    {
        //$this->visit_ip = Ip::get();
		$ip_chk	 = Ip::get();
		
		if( strpos($ip_chk, ',') !== false){
			$sp = explode(',', $ip_chk );
			$this->visit_ip = trim( $sp[0] );
		}
		else{
			$this->visit_ip = $ip_chk;	
		}

		echo $this->visit_ip;
        if ($this->ip_check($this->bitbucket_ip, $this->visit_ip) == false) {
			$this->log_write(" Reject IP: " . $this->visit_ip);
        }
    }

    public function deployment()
    {
        // check branch
        if ($this->chk_branch() == false) {
         //   $this->log_write('The corresponding branch is not ' . $this->branch);
        }
		
	
		echo 'cd ' . $this->repo_dir . ' && ' . $this->git_bin_path . ' fetch';
		exit;
        // git
        exec('cd ' . $this->repo_dir . ' && ' . $this->git_bin_path . ' fetch');
        exec('cd ' . $this->repo_dir . ' && ' . $this->git_bin_path . '  --work-tree=' . $this->web_root_dir . '  checkout -f');

        // completed.
        $commit_hash = preg_replace('/\r?\n$/', ' ',
            shell_exec('cd ' . $this->repo_dir . ' && ' . $this->git_bin_path . ' rev-parse --short HEAD'));
		
		
        if (!empty($commit_hash)) {
            $msg = "Deployment {$this->server_name} has been completed. ( {$commit_hash} )";
            if (!empty($this->pushbullet_token)) {
                $pushbullet = new PHPushbullet($this->pushbullet_token);
                $pushbullet->user($this->pushbullet_user)->note($msg);
            }
            $this->log_write($msg);
        } else {
            $msg = "Error occurred during distribution to {$this->server_name}.";
            if (!empty($this->pushbullet_token)) {
                $pushbullet = new PHPushbullet($this->pushbullet_token);
                $pushbullet->user($this->pushbullet_user)->note($msg);
            }
            $this->log_write($msg);
        }
    }

    private function chk_branch()
    {
		$request_body = json_decode(file_get_contents('php://input'), true);
		
		if(is_array($request_body) ){
			foreach ($request_body['push']['changes'] as $changes) {
         	   	if ($changes['new']['name'] == $this->branch) {
                	return true;
            	}
        	}	
		}
        
        return false;
    }

    private function log_write($msg)
    {
        file_put_contents($this->log_file, date('Y/m/d H:i:s ') . $msg . "\n", FILE_APPEND);
        die($msg);
    }

    private function ip_check($webhook_ip = array(), $visit_ip)
    {
        $long_ip = ip2long($visit_ip);
		//echo $long_ip.'</br>';
        if ($long_ip != -1) {

            foreach ($webhook_ip AS $pri_addr) {
                list ($start, $end) = explode('|', $pri_addr);
				//echo ip2long( $start ).'</br>'.ip2long( $end );
                if ($long_ip >= ip2long($start) && $long_ip <= ip2long($end)) {
                    return true;
                }
            }
        }

        return false;
    }


}

