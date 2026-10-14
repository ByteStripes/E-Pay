<?php
$ipType = (isset($conf['ip_type']) && $conf['ip_type'] !== '' && $conf['ip_type'] !== null) ? intval($conf['ip_type']) : 2;
$clientip = real_ip($ipType);
$islogin = 0;
$islogin2 = 0;
$userrow = [];

if(isset($_COOKIE["admin_token"]) && !empty($_COOKIE["admin_token"]))
{
	$token = authcode($_COOKIE['admin_token'], 'DECODE', SYS_KEY);
	if($token && strpos($token, "\t") !== false){
		$parts = explode("\t", $token);
		if(count($parts) === 3){
			list($user, $sid, $expiretime) = $parts;
			$session = md5($conf['admin_user'].$conf['admin_pwd'].$password_hash);
			if($user === $conf['admin_user'] && hash_equals($session, (string)$sid) && intval($expiretime) > time()) {
				$islogin = 1;
			}
		}
	}
}
if(isset($_COOKIE["user_token"]) && !empty($_COOKIE["user_token"]))
{
	$token = authcode($_COOKIE['user_token'], 'DECODE', SYS_KEY);
	if($token && strpos($token, "\t") !== false){
		$parts = explode("\t", $token);
		if(count($parts) === 3){
			list($uid, $sid, $expiretime) = $parts;
			$uid = intval($uid);
			$userrow = $DB->getRow("SELECT * FROM pre_user WHERE uid=:uid LIMIT 1", [':uid'=>$uid]);
			if($userrow && intval($userrow['status']) !== 0 && intval($expiretime) > time()){
				$session_v2 = md5($userrow['uid'].$userrow['key'].$userrow['pwd'].$password_hash);
				$session_v1 = md5($userrow['uid'].$userrow['key'].$password_hash);
				if(hash_equals($session_v2, (string)$sid) || hash_equals($session_v1, (string)$sid)) {
					$islogin2 = 1;
				}
			}
		}
	}
}
