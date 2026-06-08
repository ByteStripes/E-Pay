<?php
include("../includes/common.php");

if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");

$uid=intval($_GET['uid']);

$userrow=$DB->getRow("select * from pre_user where uid=:uid limit 1", [':uid'=>$uid]);
if(!$userrow)sysmsg('当前用户不存在！');

$DB->insert('log', ['uid'=>$uid, 'type'=>'管理员SSO登录', 'date'=>'NOW()', 'ip'=>$clientip]);
error_log(sprintf('[SSO] admin SSO login as uid=%d, ip=%s, time=%s', $uid, $clientip, date('Y-m-d H:i:s')));

$session=md5($uid.$userrow['key'].$password_hash);
$expiretime=time()+7200;
$token=authcode("{$uid}\t{$session}\t{$expiretime}", 'ENCODE', SYS_KEY);
setcookie("user_token", $token, ['expires'=>$expiretime, 'path'=>'/user', 'secure'=>isset($_SERVER['HTTPS']), 'httponly'=>true, 'samesite'=>'Strict']);

exit("<script language='javascript'>window.location.href='../user/';</script>");
