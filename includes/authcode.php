<?php
$_authcode_value = getenv('EPAY_AUTHCODE');
if(empty($_authcode_value) && defined('ROOT') && file_exists(ROOT.'config.php')){
	$_authcode_config = ROOT.'.authcode';
	if(file_exists($_authcode_config)){
		$_authcode_value = trim(file_get_contents($_authcode_config));
	}
}
if(empty($_authcode_value)){
	$_authcode_value = bin2hex(random_bytes(16));
	error_log('警告: authcode 未配置，请设置环境变量 EPAY_AUTHCODE 或创建 .authcode 文件');
}
define('authcode', $_authcode_value);
unset($_authcode_value, $_authcode_config);

?>