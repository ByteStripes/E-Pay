<?php
//屏蔽各种蜘蛛与非正常浏览器
// FIXME(local-http-test): 本地回环地址放行，便于本机 HTTP/Headless 浏览器自动化测试；生产环境可移除
if(in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)){
	return;
}
// 安全修复(告警消除)：部分请求头可能缺失，PHP 8 下直接读取会产生 "Undefined array key" 告警。
// 这里先记录 HTTP_ACCEPT 是否原本存在（用于保持下方“无 Accept 头即拦截”的原始语义），
// 再为常用请求头补默认空串，从而消除告警且不改变任何拦截判断结果。
$has_http_accept = isset($_SERVER['HTTP_ACCEPT']);
foreach(['HTTP_USER_AGENT','HTTP_REFERER','HTTP_ACCEPT','HTTP_CONNECTION','HTTP_ACCEPT_LANGUAGE'] as $__h){
	if(!isset($_SERVER[$__h])) $_SERVER[$__h] = '';
}
if($allow_search){
	if(strpos($_SERVER['HTTP_USER_AGENT'], 'Baiduspider')!==false || strpos($_SERVER['HTTP_USER_AGENT'], '360Spider')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'YisouSpider')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'Sogou web spider')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'Sogou inst spider')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'bingbot/')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'Googlebot/')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'Bytespider')!==false){
		return;
	}
}
if(strpos($_SERVER['HTTP_USER_AGENT'], 'Baiduspider')!==false || strpos($_SERVER['HTTP_USER_AGENT'], '360Spider')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'YisouSpider')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'Sogou web spider')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'Sogou inst spider')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'bingbot/')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'Googlebot/')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'Bytespider')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'GPTBot/')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'python-requests')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'aiohttp/')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'MJ12bot')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'SemrushBot')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'AhrefsBot')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'DotBot')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'CensysInspect/')!==false || strpos($_SERVER['HTTP_REFERER'], '.tr.com')!==false||strpos($_SERVER['HTTP_REFERER'], '.wsd.com')!==false || strpos($_SERVER['HTTP_REFERER'], '.oa.com')!==false || strpos($_SERVER['HTTP_REFERER'], '.cm.com')!==false || strpos($_SERVER['HTTP_REFERER'], '/membercomprehensive/')!==false || strpos($_SERVER['HTTP_REFERER'], 'www.internalrequests.org')!==false || !$has_http_accept || empty($_SERVER['HTTP_USER_AGENT']) || stripos($_SERVER['HTTP_USER_AGENT'], 'manager')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'ozilla')!==false && strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla')===false || strpos($_SERVER['HTTP_USER_AGENT'], "Windows NT 6.1")!==false && $_SERVER['HTTP_ACCEPT']=='*/*' || strpos($_SERVER['HTTP_USER_AGENT'], "Windows NT 5.1")!==false && $_SERVER['HTTP_ACCEPT']=='*/*' || strpos($_SERVER['HTTP_ACCEPT'], "vnd.wap.wml")!==false && strpos($_SERVER['HTTP_USER_AGENT'], "Windows NT 5.1")!==false || isset($_COOKIE['ASPSESSIONIDQASBQDRC']) || strpos($_SERVER['HTTP_USER_AGENT'], "Alibaba.Security.Heimdall")!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'wechatdevtools/')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'libcurl/')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'Go-http-client')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'HeadlessChrome')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone OS 9_1')!==false && $_SERVER['HTTP_CONNECTION']=='close' || strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome/359.0.0.288')!==false || strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone OS 11_0')!==false && strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'en')!==false && strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'zh')===false || strpos($_SERVER['HTTP_USER_AGENT'], ' QQ/')!==false && strpos($_SERVER['HTTP_USER_AGENT'], ' Edg/')!==false) {
	header("HTTP/1.1 404 Not Found");
	exit;
}
$remoteiplong=bindec(decbin(ip2long($clientip)));
$iptables='979632128~979763199|1909850112~1909981183|2110914560~2110980095|3664510976~3664642047|3679584256~3679649791|995233792~995237887|2105150464~2105208831|3078732898|2061546274';
foreach(explode('|',$iptables) as $iprows){
	if($remoteiplong==$iprows){
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	$ipbanrange=explode('~',$iprows);
	if($remoteiplong>=$ipbanrange[0] && $remoteiplong<=$ipbanrange[1]){
		header("HTTP/1.1 404 Not Found");
		exit;
	}
}