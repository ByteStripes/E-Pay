<?php
$ipType = (isset($conf['ip_type']) && $conf['ip_type'] !== '' && $conf['ip_type'] !== null) ? intval($conf['ip_type']) : 2;
$clientip=real_ip($ipType);

if(isset($_COOKIE["admin_token"]))
{
	// 安全修复(M5)：token 为 authcode 加密串，后续仅做 DECODE 解密、字段拆分与参数化查询，
	// 此处 daddslashes 无任何 SQL 防护价值，反而可能污染密文导致解密异常，故移除。
	$token=authcode($_COOKIE['admin_token'], 'DECODE', SYS_KEY);
	list($user, $sid, $expiretime) = explode("\t", $token);
	$session=md5($conf['admin_user'].$conf['admin_pwd'].$password_hash);
	if($session===$sid && $expiretime>time()) {
		$islogin=1;
	}
}
if(isset($_COOKIE["user_token"]))
{
	// 安全修复(M5)：同上，token 解密路径上的 daddslashes 冗余且可能污染密文，移除。
	$token=authcode($_COOKIE['user_token'], 'DECODE', SYS_KEY);
	list($uid, $sid, $expiretime) = explode("\t", $token);
	$uid = intval($uid);
	$userrow=$DB->getRow("SELECT * FROM pre_user WHERE uid=:uid limit 1", [':uid'=>$uid]);
	$session=md5($userrow['uid'].$userrow['key'].$password_hash);
	if($userrow && $session===$sid && $expiretime>time()) {
		$islogin2=1;
	}
}
?>