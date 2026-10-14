<?php
function curl_get($url)
{
	global $conf;
	$ch=curl_init($url);
	if($conf['proxy'] == 1){
		$proxy_server = $conf['proxy_server'];
		$proxy_port = intval($conf['proxy_port']);
		if($conf['proxy_type'] == 'https'){
			$proxy_type = CURLPROXY_HTTPS;
		}elseif($conf['proxy_type'] == 'sock4'){
			$proxy_type = CURLPROXY_SOCKS4;
		}elseif($conf['proxy_type'] == 'sock5'){
			$proxy_type = CURLPROXY_SOCKS5;
		}elseif($conf['proxy_type'] == 'sock5h'){
			$proxy_type = CURLPROXY_SOCKS5_HOSTNAME;
		}else{
			$proxy_type = CURLPROXY_HTTP;
		}
		curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_PROXY, $proxy_server);
		curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
		if(!empty($conf['proxy_user']) && !empty($conf['proxy_pwd'])){
			$proxy_userpwd = $conf['proxy_user'].':'.$conf['proxy_pwd'];
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_userpwd);
		}
		curl_setopt($ch, CURLOPT_PROXYTYPE, $proxy_type);
	}
	$httpheader[] = "Accept: */*";
	$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
	$httpheader[] = "Connection: close";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36');
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	$content=curl_exec($ch);
	curl_close($ch);
	return $content;
}
function get_curl($url, $post=0, $referer=0, $cookie=0, $header=0, $ua=0, $nobaody=0, $addheader=0, $location=0)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	$httpheader[] = "Accept: */*";
	$httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
	$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
	$httpheader[] = "Connection: close";
	if($addheader){
		$httpheader = array_merge($httpheader, $addheader);
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
	if ($post) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	if ($header) {
		curl_setopt($ch, CURLOPT_HEADER, true);
	}
	if ($cookie) {
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	}
	if($referer){
		curl_setopt($ch, CURLOPT_REFERER, $referer);
	}
	if ($ua) {
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
	}
	else {
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; U; Android 4.0.4; es-mx; HTC_One_X Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0");
	}
	if ($nobaody) {
		curl_setopt($ch, CURLOPT_NOBODY, 1);
	}
	if ($location) {
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	}
	curl_setopt($ch, CURLOPT_ENCODING, "gzip");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$ret = curl_exec($ch);
	curl_close($ch);
	return $ret;
}
function real_ip($type=2){
	$ip = $_SERVER['REMOTE_ADDR'];
	if($type<=0 && isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		foreach ($ips as $xip) {
			$xip = trim($xip);
			if (filter_var($xip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
				$ip = $xip;
				break;
			}
		}
	} elseif ($type<=0 && isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ($type<=1 && isset($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
		$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
	} elseif ($type<=1 && isset($_SERVER['HTTP_X_REAL_IP']) && filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
		$ip = $_SERVER['HTTP_X_REAL_IP'];
	}
	return $ip;
}
function get_ip_city($ip)
{
    $url = 'https://www.bt.cn/api/panel/get_ip_info?ip=' . $ip;
    $response = get_curl($url);
    $result = json_decode($response, true);
	if(isset($result[$ip])){
		$data = $result[$ip];
		if($data['country'] == '中国'){
			return $data['province'].$data['city'];
		}else{
			return $data['country'].$data['province'].$data['city'];
		}
	}
	return false;
}
function send_mail($to, $sub, $msg) {
	global $conf;
	if($conf['mail_cloud']==1){
		$mail = new \lib\mail\Sendcloud($conf['mail_apiuser'], $conf['mail_apikey']);
		return $mail->send($to, $sub, $msg, $conf['mail_name2'], $conf['sitename']);
	}elseif($conf['mail_cloud']==2){
		$mail = new \lib\mail\Aliyun($conf['mail_apiuser'], $conf['mail_apikey']);
		return $mail->send($to, $sub, $msg, $conf['mail_name2'], $conf['sitename']);
	}else{
		if(!$conf['mail_name'] || !$conf['mail_port'] || !$conf['mail_smtp'] || !$conf['mail_pwd'])return false;
		$port = intval($conf['mail_port']);
		$mail = new \lib\mail\PHPMailer\PHPMailer(true);
		try{
			$mail->SMTPDebug = 0;
			$mail->CharSet = 'UTF-8';
			$mail->Timeout = 5;
			$mail->isSMTP();
			$mail->Host = $conf['mail_smtp'];
			$mail->SMTPAuth = true;
			$mail->Username = $conf['mail_name'];
			$mail->Password = $conf['mail_pwd'];
			if($port == 587) $mail->SMTPSecure = 'tls';
			else if($port >= 465) $mail->SMTPSecure = 'ssl';
			else $mail->SMTPAutoTLS = false;
			$mail->Port = intval($conf['mail_port']);
			$mail->setFrom($conf['mail_name'], $conf['sitename']);
			$mail->addAddress($to);
			$mail->addReplyTo($conf['mail_name'], $conf['sitename']);
			$mail->isHTML(true);
			$mail->Subject = $sub;
			$mail->Body = $msg;
			$mail->send();
			return true;
		} catch (Exception $e) {
			error_log('邮件发送失败: ' . $mail->ErrorInfo);
			return '邮件发送失败，请稍后再试';
		}
	}
}
function send_sms($phone, $code, $scene='reg'){
	global $conf;
	if($scene == 'reg'){
		$tpl_code = $conf['sms_tpl_reg'];
	}elseif($scene == 'login'){
		$tpl_code = $conf['sms_tpl_login'];
	}elseif($scene == 'find'){
		$tpl_code = $conf['sms_tpl_find'];
	}elseif($scene == 'edit'){
		$tpl_code = $conf['sms_tpl_edit'];
	}
	return send_sms_common($phone, $tpl_code, ['code'=>$code]);
}
function send_sms_common($phone, $tpl_code, $tpl_param){
	global $conf;
	if($conf['sms_api']==1){
		$sms = new \lib\sms\Qcloud($conf['sms_appid'], $conf['sms_appkey']);
		$arr = $sms->send($phone, $tpl_code, array_values($tpl_param), $conf['sms_sign']);
		if(isset($arr['result']) && $arr['result']==0){
			return true;
		}else{
			return $arr['errmsg'];
		}
	}elseif($conf['sms_api']==2){
		$sms = new \lib\sms\Aliyun($conf['sms_appid'], $conf['sms_appkey']);
		$arr = $sms->send($phone, $tpl_param, $tpl_code, $conf['sms_sign'], $conf['sitename']);
		if(isset($arr['Code']) && $arr['Code']=='OK'){
			return true;
		}else{
			return $arr['Message'];
		}
	}elseif($conf['sms_api']==3){
		$url = 'https://api.topthink.com/sms/send';
		$param = ['appCode'=>$conf['sms_appkey'], 'signId'=>$conf['sms_sign'], 'templateId'=>$tpl_code, 'phone'=>$phone, 'params'=>json_encode($tpl_param)];
		$data=get_curl($url, http_build_query($param));
		$arr=json_decode($data,true);
		if(isset($arr['code']) && $arr['code']==0){
			return true;
		}else{
			return $arr['message'];
		}
	}elseif($conf['sms_api']==4){
		$sms = new \lib\sms\SmsBao($conf['sms_appid'], $conf['sms_appkey']);
		return $sms->send($phone, $tpl_param, $tpl_code, $conf['sms_sign']);
	}else{
		$url = 'https://sms.php.gs/sms/send/yzm';
		$param = ['appkey'=>$conf['sms_appkey'], 'phone'=>$phone, 'moban'=>$tpl_code];
		$param = array_merge($param, $tpl_param);
		$data=get_curl($url, http_build_query($param));
		$arr=json_decode($data,true);
		if($arr['status']=='200'){
			return true;
		}else{
			return $arr['error_msg_zh'];
		}
	}
}
function daddslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = daddslashes($val);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

// 安全修复(CSV公式注入)：对以 = + - @ 或制表符/回车 开头的单元格值添加单引号前缀，
// 防止导出的 CSV 在 Excel/WPS 等表格软件中被当作公式执行（OWASP CSV Injection）。
// 仅在导出 CSV 时对用户可控文本字段调用，不影响数据库中的原始值。
function csv_safe($value) {
	$value = (string)$value;
	if($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)){
		return "'".$value;
	}
	return $value;
}

function strexists($string, $find) {
	return !(strpos($string, $find) === FALSE);
}

function dstrpos($string, $arr) {
	if(empty($string)) return false;
	foreach((array)$arr as $v) {
		if(strpos($string, $v) !== false) {
			return true;
		}
	}
	return false;
}

function checkmobile() {
	$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
	$ualist = array('android', 'midp', 'nokia', 'mobile', 'iphone', 'ipod', 'blackberry', 'windows phone');
	if((dstrpos($useragent, $ualist) || strexists($_SERVER['HTTP_ACCEPT'], "VND.WAP") || strexists($_SERVER['HTTP_VIA'],"wap")))
		return true;
	else
		return false;
}
function checkwechat(){
	if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger/') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'WindowsWechat') === false)
		return true;
	else
		return false;
}
function checkalipay(){
	if(strpos($_SERVER['HTTP_USER_AGENT'], 'AlipayClient/') !== false)
		return true;
	else
		return false;
}
function checkmobbileqq(){
	if(strpos($_SERVER['HTTP_USER_AGENT'], 'QQ/') !== false)
		return true;
	else
		return false;
}
function checkunionpay(){
	if(strpos($_SERVER['HTTP_USER_AGENT'], 'UnionPay/') !== false)
		return true;
	else
		return false;
}
function get_unionpay_ua(){
	if (preg_match('/UnionPay\/([0-9\.]+) ([a-zA-Z0-9]+)/', $_SERVER['HTTP_USER_AGENT'], $matches)) {
		return $matches[0];
	}
	return 'UnionPay/1.0 CloudPay';
}
function checkdouyin(){
	if(strpos($_SERVER['HTTP_USER_AGENT'], 'aweme‌') !== false)
		return true;
	else
		return false;
}
function getDevice(){
	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if(strpos($user_agent, 'iphone') !== false || strpos($user_agent, 'ipad') !== false || strpos($user_agent, 'ipod') !== false){
		return 'ios';
	}elseif(strpos($user_agent, 'android') !== false){
		return 'android';
	}else{
		return 'pc';
	}
}
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$derivedKey = sodium_crypto_generichash($key, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
	if($operation == 'DECODE') {
		$decoded = base64_decode($string);
		if($decoded === false || strlen($decoded) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
			return '';
		}
		$nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $derivedKey);
		if($plaintext === false) {
			return '';
		}
		$expireTime = (int)substr($plaintext, 0, 10);
		if($expireTime > 0 && $expireTime < time()) {
			return '';
		}
		return substr($plaintext, 10);
	} else {
		$expireStamp = $expiry > 0 ? sprintf('%010d', $expiry + time()) : '0000000000';
		$plaintext = $expireStamp . $string;
		$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $derivedKey);
		return base64_encode($nonce . $ciphertext);
	}
}

function random($length, $numeric = 0) {
	$seed = base_convert(md5(random_bytes(16)), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed[random_int(0, $max)];
	}
	return $hash;
}
function showmsg($content = '未知的异常',$type = 4,$back = false,$raw = false)
{
switch($type)
{
case 1:
	$panel="success";
break;
case 2:
	$panel="info";
break;
case 3:
	$panel="warning";
break;
case 4:
	$panel="danger";
break;
}
if(!$raw) $content = htmlspecialchars($content, ENT_QUOTES|ENT_HTML5, 'UTF-8');

echo '<div class="panel panel-'.$panel.'">
      <div class="panel-heading">
        <h3 class="panel-title">提示信息</h3>
        </div>
        <div class="panel-body">';
echo $content;

if ($back) {
	$back = htmlspecialchars($back, ENT_QUOTES|ENT_HTML5, 'UTF-8');
	echo '<hr/><a href="'.$back.'"><< 返回上一页</a>';
}
else
    echo '<hr/><a href="javascript:history.back(-1)"><< 返回上一页</a>';

echo '</div>
    </div>';
	exit;
}
function sysmsg($msg = '未知的异常',$title = '站点提示信息',$raw = false) {
    if(!$raw){
        $msg = htmlspecialchars($msg, ENT_QUOTES|ENT_HTML5, 'UTF-8');
        $title = htmlspecialchars($title, ENT_QUOTES|ENT_HTML5, 'UTF-8');
    }
    ?>  
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title?></title>
        <style type="text/css">
html{background:#eee}body{background:#fff;color:#333;font-family:"微软雅黑","Microsoft YaHei",sans-serif;margin:2em auto;padding:1em 2em;max-width:700px;-webkit-box-shadow:10px 10px 10px rgba(0,0,0,.13);box-shadow:10px 10px 10px rgba(0,0,0,.13);opacity:.8}h1{border-bottom:1px solid #dadada;clear:both;color:#666;font:24px "微软雅黑","Microsoft YaHei",sans-serif;margin:30px 0 0 0;padding:0;padding-bottom:7px}#error-page{margin-top:50px}h3{text-align:center}#error-page p{font-size:9px;line-height:1.5;margin:25px 0 20px}#error-page code{font-family:Consolas,Monaco,monospace}ul li{margin-bottom:10px;font-size:9px}a{color:#21759B;text-decoration:none;margin-top:-10px}a:hover{color:#D54E21}.button{background:#f7f7f7;border:1px solid #ccc;color:#555;display:inline-block;text-decoration:none;font-size:9px;line-height:26px;height:28px;margin:0;padding:0 10px 1px;cursor:pointer;-webkit-border-radius:3px;-webkit-appearance:none;border-radius:3px;white-space:nowrap;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;-webkit-box-shadow:inset 0 1px 0 #fff,0 1px 0 rgba(0,0,0,.08);box-shadow:inset 0 1px 0 #fff,0 1px 0 rgba(0,0,0,.08);vertical-align:top}.button.button-large{height:29px;line-height:28px;padding:0 12px}.button:focus,.button:hover{background:#fafafa;border-color:#999;color:#222}.button:focus{-webkit-box-shadow:1px 1px 1px rgba(0,0,0,.2);box-shadow:1px 1px 1px rgba(0,0,0,.2)}.button:active{background:#eee;border-color:#999;color:#333;-webkit-box-shadow:inset 0 2px 5px -3px rgba(0,0,0,.5);box-shadow:inset 0 2px 5px -3px rgba(0,0,0,.5)}table{table-layout:auto;border:1px solid #333;empty-cells:show;border-collapse:collapse}th{padding:4px;border:1px solid #333;overflow:hidden;color:#333;background:#eee}td{padding:4px;border:1px solid #333;overflow:hidden;color:#333}
        </style>
    </head>
    <body id="error-page">
        <?php echo '<h3>'.$title.'</h3>';
        echo $msg; ?>
    </body>
    </html>
    <?php
    exit;
}
function returnTemplate($return_url) {
	$url = htmlspecialchars(base64_encode($return_url), ENT_QUOTES|ENT_HTML5, 'UTF-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>支付成功跳转页面</title>
        <style type="text/css">
body{margin:0;padding:0}
p{position:absolute;left:50%;top:50%;height:35px;margin:-35px 0 0 -160px;padding:20px;font:bold 16px/30px "宋体",Arial;background:#f9fafc url(/assets/img/loading.gif) no-repeat 20px 20px;text-indent:40px;border:1px solid #c5d0dc}
#waiting{font-family:Arial}
        </style>
    </head>
    <body id="return-page">
        <p>支付成功，正在跳转请稍候...</p>
    </body>
	<script>window.location.href=window.atob("<?php echo $url?>");</script>
    </html>
    <?php
    exit;
}
function submitTemplate($html_text){
	$safe_html = htmlspecialchars($html_text, ENT_QUOTES|ENT_HTML5, 'UTF-8');
	if(substr($html_text, 0, 6) === '<form '){
		// 支付插件返回的表单HTML（可信来源），需保留原始HTML以便自动提交
		$safe_html = $html_text;
	}elseif(substr($html_text, 0, 8) === '<script>'){
		// 支付插件返回的跳转脚本（可信来源：由服务端支付插件生成，非用户输入）
		$safe_html = $html_text;
	}
	?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>正在为您跳转到支付页面，请稍候...</title>
	<style type="text/css">
body{margin:0;padding:0}
#waiting{position:absolute;left:50%;top:50%;height:35px;margin:-35px 0 0 -160px;padding:20px;font:16px/30px "Helvetica Neue",Helvetica,Arial,sans-serif;background:#f9fafc url(/assets/img/loading.gif) no-repeat 20px 20px;text-indent:40px;border:1px solid #c5d0dc}
	</style>
</head>
<body>
<p id="waiting">正在为您跳转到支付页面，请稍候...</p>
<?php echo $safe_html?>
</body>
</html>
	<?php
	exit;
}
function getSid() {
    return bin2hex(random_bytes(16));
}
function getMd5Pwd($pwd, $salt=null) {
    return md5(md5($pwd) . md5($salt));
}

function hashPassword($pwd) {
    return password_hash($pwd, PASSWORD_ARGON2ID);
}

function verifyPassword($pwd, $hash, $salt=null) {
    if (str_starts_with($hash, '$argon2id$') || str_starts_with($hash, '$2y$')) {
        return password_verify($pwd, $hash);
    }
    return hash_equals(getMd5Pwd($pwd, $salt), $hash);
}

// 生成安全的订单号
function generateTradeNo() {
    return date("YmdHis") . bin2hex(random_bytes(4));
}
function getMillisecond()
{
	list($s1, $s2) = explode(' ', microtime());
	return sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
}

/**
 * 取中间文本
 * @param string $str
 * @param string $leftStr
 * @param string $rightStr
 */
function getSubstr($str, $leftStr, $rightStr)
{
	$left = strpos($str, $leftStr);
	$start = $left+strlen($leftStr);
	$right = strpos($str, $rightStr, $start);
	if($left < 0) return '';
	if($right>0){
		return substr($str, $start, $right-$start);
	}else{
		return substr($str, $start);
	}
}
function isNullOrEmpty($str){
	return $str === null || $str === '';
}

function getSetting($k, $force = false){
	global $DB,$CACHE;
	if($force) return $DB->getColumn("SELECT v FROM pre_config WHERE k=:k LIMIT 1", [':k'=>$k]);
	$cache = $CACHE->get($k);
	return $cache[$k];
}
function saveSetting($k, $v){
	global $DB;
	return $DB->exec("REPLACE INTO pre_config SET v=:v,k=:k", [':v'=>$v, ':k'=>$k]);
}
function checkGroupSettings($str){
	foreach(explode(',',$str) as $row){
		if(!strpos($row,':'))return false;
	}
	return true;
}
function isEmpty($value)
{
	return $value === null || trim($value) === '';
}

function creat_callback($data){
	global $DB,$conf;
	$type=$DB->getColumn("SELECT name FROM pre_type WHERE id=:id LIMIT 1", [':id'=>$data['type']]);
	if($data['version'] == 1){
		$array=array('pid'=>$data['uid'],'trade_no'=>$data['trade_no'],'out_trade_no'=>$data['out_trade_no'],'type'=>$type,'name'=>$data['name'],'money'=>(float)$data['money'],'trade_status'=>'TRADE_SUCCESS');
		if(!empty($data['bill_trade_no']))$array['api_trade_no']=$data['bill_trade_no'];
		elseif(!empty($data['api_trade_no']))$array['api_trade_no']=$data['api_trade_no'];
		if(!empty($data['buyer']))$array['buyer']=$data['buyer'];
		if(!empty($data['param']))$array['param']=$data['param'];
		if($conf['notifyordername']==1)$array['name']='product';
		$array['timestamp'] = time().'';
		$array['sign_type'] = 'RSA';
		$array['sign'] = \lib\Payment::makeSign($array, '');
	}else{
		$key=$DB->getColumn("SELECT `key` FROM pre_user WHERE uid=:uid LIMIT 1", [':uid'=>$data['uid']]);
		$array=array('pid'=>$data['uid'],'trade_no'=>$data['trade_no'],'out_trade_no'=>$data['out_trade_no'],'type'=>$type,'name'=>$data['name'],'money'=>(float)$data['money'],'trade_status'=>'TRADE_SUCCESS');
		if(!empty($data['param']))$array['param']=$data['param'];
		if($conf['notifyordername']==1)$array['name']='product';
		$array['sign'] = \lib\Payment::makeSign($array, $key);
		$array['sign_type'] = 'MD5';
	}
	$query_str = http_build_query($array);
	if(strpos($data['notify_url'],'?'))
		$url['notify']=$data['notify_url'].'&'.$query_str;
	else
		$url['notify']=$data['notify_url'].'?'.$query_str;
	if(strpos($data['return_url'],'?'))
		$url['return']=$data['return_url'].'&'.$query_str;
	else
		$url['return']=$data['return_url'].'?'.$query_str;
	if($data['tid']>0){
		$url['return']=$data['return_url'];
	}
	return $url;
}

function getdomain($url){
	$arr=parse_url($url);
	$host = $arr['host'];
	if(isset($arr['port']) && $arr['port']!=80 && $arr['port']!=443)$host .= ':'.$arr['port'];
	return $host;
}
function get_host($url){
	$arr=parse_url($url);
	return $arr['host'];
}

function get_main_host($url){
	$arr=parse_url($url);
	$host = $arr['host'];
	if(filter_var($host, FILTER_VALIDATE_IP))return $host;
	if(substr_count($host, '.')>1){
		$host = substr($host, strpos($host, '.')+1);
	}
	return $host;
}

function is_safe_url($url) {
	$parsed = parse_url($url);
	if (!$parsed || !isset($parsed['host'])) return false;
	if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) return false;
	$host = $parsed['host'];
	$ip = gethostbyname($host);
	$forbidden = ['127.0.0.1', '0.0.0.0', 'localhost', '169.254.169.254'];
	if (in_array($ip, $forbidden)) return false;
	if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) return false;
	return true;
}

function do_notify($url){
	global $conf;
	if (!is_safe_url($url)) {
		return false;
	}
	if($conf['proxy'] == 2 && !empty($conf['proxy_apiurl']) && !empty($conf['proxy_apikey'])){
		$url = base64_encode($url);
		$timestamp = strval(time());
		$param = ['url' => $url, 'timestamp' => $timestamp, 'sign' => md5($url.$timestamp.$conf['proxy_apikey'])];
		$return = get_curl($conf['proxy_apiurl'], http_build_query($param));
	}else{
		$return = curl_get($url);
	}
	if(strpos($return,'success')!==false || strpos($return,'SUCCESS')!==false || strpos($return,'Success')!==false){
		return true;
	}else{
		return false;
	}
}

function checkBlockUser($openid, $trade_no){
	global $DB, $conf;
	$DB->update('order', ['buyer'=>$openid], ['trade_no'=>$trade_no]);
	$black = $DB->find('blacklist', '*', ['type'=>0, 'content'=>$openid], null, 1);
	if($black){
		return ['type'=>'error','msg'=>'系统异常无法完成付款'];
	}
	if($conf['pay_userlimit'] > 0){
		$usercount = $DB->getColumn("select count(*) from pre_order where `buyer`=:buyer and `date`=:today and status>0", [':buyer'=>$openid, ':today'=>date('Y-m-d')]);
		if($usercount >= $conf['pay_userlimit']){
			return ['type'=>'error','msg'=>'你今天已无法再发起支付，请明天再试'];
		}
	}
	if($conf['pay_daymoney'] > 0){
		$daymoney = $DB->getColumn("select sum(realmoney) from pre_order where `buyer`=:buyer and `date`=:today and status>0", [':buyer'=>$openid, ':today'=>date('Y-m-d')]);
		if($daymoney >= $conf['pay_daymoney']){
			return ['type'=>'error','msg'=>'你今天已累积支付金额超过'.$conf['pay_daymoney'].'元，请明天再试'];
		}
	}
	return false;
}

function moneyEquals($left, $right){
	if($left === null || $left === '' || $right === null || $right === '') return false;
	return intval(round((float)$left * 100)) === intval(round((float)$right * 100));
}

function processReturn($order, $api_trade_no=null, $buyer=null, $bill_trade_no = null, $bill_mch_trade_no = null, $end_time = null, $paid_amount = null){
	if($paid_amount !== null && !moneyEquals($paid_amount, $order['realmoney'])) return;
	\lib\Payment::processOrder(false, $order, $api_trade_no, $buyer, $bill_trade_no, $bill_mch_trade_no, $end_time);
}

function processNotify($order, $api_trade_no=null, $buyer=null, $bill_trade_no = null, $bill_mch_trade_no = null, $end_time = null, $paid_amount = null){
	if($paid_amount !== null && !moneyEquals($paid_amount, $order['realmoney'])) return;
	\lib\Payment::processOrder(true, $order, $api_trade_no, $buyer, $bill_trade_no, $bill_mch_trade_no, $end_time);
}

function processOrder(&$srow,$notify=true){
	global $DB,$CACHE,$conf,$channel;
	$addmoney = $srow['getmoney'];
	$reducemoney = round($srow['realmoney']-$srow['getmoney'], 2);
	if($reducemoney<0)$reducemoney=0;

	$profitmoney = $reducemoney;
	if(!empty($channel['costrate']) && $channel['costrate'] > 0){
		$profitmoney = round($profitmoney - $srow['realmoney'] * $channel['costrate'] / 100, 2);
	}
	$DB->update('order', ['profitmoney'=>$profitmoney], ['trade_no'=>$srow['trade_no']]);

	if(($srow['tid']==0 || $srow['tid']==3) && intval($conf['black_payact'])==2 && !empty($srow['buyer'])){
		$black = $DB->find('blacklist', '*', ['type'=>0, 'content'=>$srow['buyer']], null, 1);
		if($black){
			$srow['black'] = true;
			$DB->exec("UPDATE pre_order SET notify=-1 WHERE trade_no=:trade_no", [':trade_no'=>$srow['trade_no']]);
			$refund_no = date("YmdHis").random_int(11111,99999);
			\lib\Order::refund($refund_no, $srow['trade_no'], $srow['realmoney'], 1, 0, null, true);
			return;
		}
	}

	if($srow['tid']==1){ //商户注册
		changeUserMoney($srow['uid'], $addmoney, true, '订单收入', $srow['trade_no']);
		$info = json_decode($CACHE->read('reg_'.$srow['trade_no']), true);
		if($info){
			$key = random(32);
			$paystatus = $conf['user_review']==1?2:1;
			$sds=$DB->exec("INSERT INTO `pre_user` (`upid`, `key`, `money`, `email`, `phone`, `addtime`, `pay`, `settle`, `keylogin`, `apply`, `status`) VALUES (:upid, :key, '0.00', :email, :phone, NOW(), :paystatus, 1, 0, 0, 1)", [':upid'=>$info['upid'], ':key'=>$key, ':email'=>$info['email'], ':phone'=>$info['phone'], ':paystatus'=>$paystatus]);
			$uid=$DB->lastInsertId();
		$pwd = getMd5Pwd($info['pwd'], $uid);
		$DB->exec("UPDATE `pre_user` SET `pwd`=:pwd WHERE `uid`=:uid", [':pwd'=>$pwd, ':uid'=>$uid]);
			if($sds){
				if(!empty($info['email'])){
					$sub = $conf['sitename'].' - 注册成功通知';
					$msg = '<h2>商户注册成功通知</h2>感谢您注册'.$conf['sitename'].'！<br/>您的登录账号：'.$info['email'].'<br/>您的商户ID：'.$uid.'<br/>您的商户秘钥：'.$key.'<br/>'.$conf['sitename'].'官网：<a href="http://'.$_SERVER['HTTP_HOST'].'/" target="_blank">'.$_SERVER['HTTP_HOST'].'</a><br/>【<a href="http://'.$_SERVER['HTTP_HOST'].'/user/" target="_blank">商户管理后台</a>】';
					send_mail($info['email'], $sub, $msg);
				}
				if(isset($info['invitecodeid']) && $info['invitecodeid']>0){
					$DB->update('invitecode', ['status'=>1, 'uid'=>$uid, 'usetime'=>'NOW()'], ['id'=>$info['invitecodeid']]);
				}
				if($paystatus == 2){
					\lib\MsgNotice::send('regaudit', 0, ['uid'=>$uid, 'account'=>$info['email']?$info['email']:$info['phone']]);
				}
			}
		}
	}else if($srow['tid']==2){ //充值余额
		$param = json_decode($srow['param'], true);
		changeUserMoney($param['uid'], $addmoney, true, '余额充值', $srow['trade_no']);
	}else if($srow['tid']==3){ //聚合收款码
		if($channel['mode']==1){
			if($reducemoney>0)
				changeUserMoney($srow['uid'], $reducemoney, false, '在线收款服务费', $srow['trade_no']);
		}else{
			changeUserMoney($srow['uid'], $addmoney, true, '在线收款', $srow['trade_no']);
		}
		if($conf['black_payact'] > 0){
			$black = $DB->find('blacklist', '*', ['type'=>0, 'content'=>$srow['buyer']], null, 1);
			if($black){
				$srow['black'] = true;
				$DB->exec("UPDATE pre_order SET notify=-1 WHERE trade_no=:trade_no", [':trade_no'=>$srow['trade_no']]);
				return;
			}
		}
	}else if($srow['tid']==4){ //购买用户组
		$param = json_decode($srow['param'], true);
		changeUserGroup($param['uid'], $param['gid'], $param['endtime']);

		$upid = $DB->findColumn('user', 'upid', ['uid'=>$param['uid']]);
		if($upid > 0){
			$upgid = $DB->findColumn('user', 'gid', ['uid'=>$upid]);
			$groupconfig = getGroupConfig($upgid);
			$conf_n = array_merge($conf, $groupconfig);
			if($conf_n['invite_open'] == 1 && $conf_n['invite_groupbuy_rate'] > 0){
				$invite_money = round($srow['money'] * $conf_n['invite_groupbuy_rate'] / 100, 2);
				if($invite_money > 0){
					changeUserMoney($upid, $invite_money, true, '邀请购买会员');
				}
			}
		}
	}else if($srow['tid']==5){ //充值保证金
		$param = json_decode($srow['param'], true);
		$userrow = $DB->find('user', 'deposit', ['uid'=>$param['uid']]);
		$deposit = $userrow['deposit'] > 0 ? round($userrow['deposit'] + $srow['money'], 2) : $srow['money'];
		$DB->exec("UPDATE pre_user SET deposit=:deposit WHERE uid=:uid", [':deposit'=>$deposit, ':uid'=>$param['uid']]);
	}else{
		if($channel['mode']==1){
			if($reducemoney>0)
				changeUserMoney($srow['uid'], $reducemoney, false, '订单服务费', $srow['trade_no']);
		}else{
			changeUserMoney($srow['uid'], $addmoney, true, '订单收入', $srow['trade_no']);
		}
		if($conf['black_payact'] > 0){
			$black = $DB->find('blacklist', '*', ['type'=>0, 'content'=>$srow['buyer']], null, 1);
			if($black){
				$srow['black'] = true;
				$DB->exec("UPDATE pre_order SET notify=-1 WHERE trade_no=:trade_no", [':trade_no'=>$srow['trade_no']]);
				return;
			}
		}
		$url=creat_callback($srow);
		if(do_notify($url['notify'])){
			$DB->exec("UPDATE pre_order SET notify=0 WHERE trade_no=:trade_no", [':trade_no'=>$srow['trade_no']]);
		}elseif($notify==true){
			$DB->exec("UPDATE pre_order SET notify=1,notifytime=date_add(now(), interval 1 minute) WHERE trade_no=:trade_no", [':trade_no'=>$srow['trade_no']]);
		}
	}
	if($srow['tid']==0 || $srow['tid']==3){
		//发送订单通知
		\lib\MsgNotice::send('order', $srow['uid'], ['trade_no'=>$srow['trade_no'], 'out_trade_no'=>$srow['out_trade_no'], 'name'=>$srow['name'], 'money'=>$srow['money'], 'type'=>$srow['typeshowname'], 'time'=>date('Y-m-d H:i:s'), 'tid'=>$srow['tid'], 'remark'=>$srow['param']]);

		//邀请返现
		if(!$conf['invite_mode']){
			$upid = $DB->findColumn('user', 'upid', ['uid'=>$srow['uid']]);
			if($upid > 0){
				$upgid = $DB->findColumn('user', 'gid', ['uid'=>$upid]);
				$groupconfig = getGroupConfig($upgid);
				$conf_n = array_merge($conf, $groupconfig);
				if($conf_n['invite_open'] == 1 && !empty($conf_n['invite_rate'])){
					if($conf_n['invite_order_type']==2){
						$invite_money = round($profitmoney * $conf_n['invite_rate'] / 100, 2);
					}elseif($conf_n['invite_order_type']==1){
						$invite_money = round($reducemoney * $conf_n['invite_rate'] / 100, 2);
					}else{
						$invite_money = round($srow['money'] * $conf_n['invite_rate'] / 100, 2);
						if(!$conf_n['invite_order_fee']){
							if($invite_money > $reducemoney) $invite_money = $reducemoney;
						}
					}
					if($invite_money > 0){
						changeUserMoney($upid, $invite_money, true, '邀请返现', $srow['trade_no']);
					}
				}
			}
		}
	}
	if($channel['daytop']>0){
		$cachekey = 'daytop'.$channel['id'].date("Ymd");
		$nowmoney = $CACHE->read($cachekey);
		if(!$nowmoney)$nowmoney=0;
		$nowmoney=round($nowmoney+$srow['realmoney'], 2);
		$CACHE->save($cachekey, $nowmoney, 86400);
		if($nowmoney>=$channel['daytop']){
			$DB->exec("UPDATE pre_channel SET daystatus=1 WHERE id=:id", [':id'=>$channel['id']]);
		}
	}
	if($channel['daymaxorder'] > 0){
		$orders = $DB->getColumn("SELECT COUNT(*) FROM pre_order WHERE channel=:channel AND status>0 AND date=CURDATE()", [':channel'=>$channel['id']]);
		if($orders >= $channel['daymaxorder']){
			$DB->exec("UPDATE pre_channel SET daystatus=1 WHERE id=:id", [':id'=>$channel['id']]);
		}
	}
	if($srow['profits']>0){ //订单分账处理
		$psreceiver = \lib\ProfitSharing\CommUtil::getReceiver($srow['profits']);
		if($psreceiver){
			$status = in_array($srow['plugin'], \lib\ProfitSharing\CommUtil::$no_order_plugins) || (in_array($srow['plugin'], \lib\ProfitSharing\CommUtil::$mode_plugins) && $psreceiver['mode'] == 0) ? 2 : 0;
			$allpsmoney = 0;
			$rdata = [];
			foreach($psreceiver['info'] as $receiver){
				if(!empty($receiver['rate']) && $receiver['rate']>0){
					$psmoney = round(floor($srow['realmoney'] * $receiver['rate']) / 100, 2);
					$rdata[] = ['account'=>$receiver['account'], 'money'=>$psmoney];
					$allpsmoney += $psmoney;
				}
			}
			$delay = ($srow['plugin'] == 'wxpaynp' || $srow['plugin'] == 'alipayd') && $conf['direct_settle_time'] == 1 ? 1 : 0;
			if(($srow['plugin'] == 'wxpaynp' || $srow['plugin'] == 'alipayd') && $srow['combine'] == 1){
				$sub_orders = \lib\Payment::getSubOrders($srow['trade_no']);
				if(!empty($sub_orders)){
					foreach($sub_orders as $sub_order){
						$allpsmoney = 0;
						$rdata = [];
						foreach($psreceiver['info'] as $receiver){
							if(!empty($receiver['rate']) && $receiver['rate']>0){
								$psmoney = round(floor($sub_order['money'] * $receiver['rate']) / 100, 2);
								$rdata[] = ['account'=>$receiver['account'], 'money'=>$psmoney];
								$allpsmoney += $psmoney;
							}
						}
						$DB->insert('psorder', ['rid'=>$psreceiver['id'], 'trade_no'=>$srow['trade_no'], 'sub_trade_no'=>$sub_order['sub_trade_no'], 'api_trade_no'=>$sub_order['api_trade_no'], 'money'=>round($allpsmoney, 2), 'status'=>$status, 'addtime'=>'NOW()', 'delay'=>$delay, 'rdata'=>json_encode($rdata)]);
					}
				}
			}else{
				$DB->insert('psorder', ['rid'=>$psreceiver['id'], 'trade_no'=>$srow['trade_no'], 'api_trade_no'=>$srow['api_trade_no'], 'money'=>round($allpsmoney, 2), 'status'=>$status, 'addtime'=>'NOW()', 'delay'=>$delay, 'rdata'=>json_encode($rdata)]);
			}
		}
	}
}

function changeUserMoney($uid, $money, $add=true, $type=null, $orderid=null, $ownTransaction=true){
	global $DB;
	if($money<=0)return;
	if($ownTransaction) $DB->beginTransaction();
	$oldmoney = $DB->getColumn("SELECT money FROM pre_user WHERE uid=:uid LIMIT 1 FOR UPDATE", [':uid'=>$uid]);
	if($type=='代付退回' && !empty($orderid)){
		$isrefund = $DB->getColumn("SELECT id FROM pre_record WHERE uid=:uid AND type='代付退回' AND trade_no=:orderid LIMIT 1", [':uid'=>$uid, ':orderid'=>$orderid]);
		if($isrefund){
			if($ownTransaction) $DB->commit();
			return;
		}
	}
	if($add == true){
		$action = 1;
		$newmoney = round($oldmoney+$money, 2);
	}else{
		$action = 2;
		$newmoney = round($oldmoney-$money, 2);
		if($newmoney < 0){
			if($ownTransaction) $DB->rollBack();
			return false;
		}
	}
	$res = $DB->exec("UPDATE pre_user SET money=:money WHERE uid=:uid", [':money'=>$newmoney, ':uid'=>$uid]);
	$DB->insert('record', ['uid'=>$uid, 'action'=>$action, 'money'=>$money, 'oldmoney'=>$oldmoney, 'newmoney'=>$newmoney, 'type'=>$type, 'trade_no'=>$orderid, 'date'=>'NOW()']);
	if($ownTransaction) $DB->commit();
	return $res;
}

function changeUserMoney2($uid, $oldmoney, $money, $add=true, $type=null, $orderid=null){
	global $DB;
	if($money<=0)return;
	if($add == true){
		$action = 1;
		$newmoney = round($oldmoney+$money, 2);
	}else{
		$action = 2;
		$newmoney = round($oldmoney-$money, 2);
	}
	$res = $DB->exec("UPDATE pre_user SET money=:money WHERE uid=:uid", [':money'=>$newmoney, ':uid'=>$uid]);
	$DB->insert('record', ['uid'=>$uid, 'action'=>$action, 'money'=>$money, 'oldmoney'=>$oldmoney, 'newmoney'=>$newmoney, 'type'=>$type, 'trade_no'=>$orderid, 'date'=>'NOW()']);
	return $res;
}

function changeUserGroup($uid, $gid, $endtime = null){
	global $DB;
	return $DB->update('user', ['gid'=>$gid, 'endtime'=>$endtime?$endtime:null], ['uid'=>$uid]);
}

function checkIfActive($string) {
	$array=explode(',',$string);
	$php_self=substr($_SERVER['REQUEST_URI'],strrpos($_SERVER['REQUEST_URI'],'/')+1,strrpos($_SERVER['REQUEST_URI'],'.')-strrpos($_SERVER['REQUEST_URI'],'/')-1);
	if (in_array($php_self,$array)){
		return 'active';
	}else
		return null;
}

//通用转账
function transfer_do($type, $channel, $out_biz_no, $payee_account, $payee_real_name, $money){
	return \lib\Transfer::submit($type, $channel, $out_biz_no, $payee_account, $payee_real_name, $money);
}
//转账回调处理
function processTransfer($out_biz_no, $status, $errmsg = null){
	\lib\Transfer::processNotify($out_biz_no, $status, $errmsg);
}
function processProfitSharing($trade_no, $status, $errmsg = null, $settle_no = null){
	\lib\ProfitSharing\CommUtil::processNotify($trade_no, $status, $errmsg, $settle_no);
}

function ordername_replace($name,$oldname,$uid,$order,$outorder=null){
	global $DB;
	/*if(strpos($name, '|')){
		$names = explode('|', $name);
		$name = $names[array_rand($names)];
	}*/
	if(strpos($name,'[name]')!==false){
		$name = str_replace('[name]', $oldname, $name);
	}
	if(strpos($name,'[order]')!==false){
		$name = str_replace('[order]', $order, $name);
	}
	if(strpos($name,'[outorder]')!==false && $outorder){
		$name = str_replace('[outorder]', $outorder, $name);
	}
	if(strpos($name,'[qq]')!==false || strpos($name,'[phone]')!==false){
		$userrow = $DB->find('user', 'qq,phone', ['uid'=>$uid]);
		$name = str_replace('[qq]', $userrow['qq'], $name);
		$name = str_replace('[phone]', $userrow['phone'], $name);
	}
	if(strpos($name,'[time]')!==false){
		$name = str_replace('[time]', time(), $name);
	}
	return $name;
}

function is_idcard( $id )
{
	$id = strtoupper($id);
	$regx = "/(^\d{17}([0-9]|X)$)/";
	$arr_split = array();
	if(strlen($id)!=18 || !preg_match($regx, $id))
	{
		return false;
	}
	$regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
	@preg_match($regx, $id, $arr_split);
	$dtm_birth = $arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
	if(!strtotime($dtm_birth)) //检查生日日期是否正确
	{
		return false;
	}
	else
	{
		//检验18位身份证的校验码是否正确。
		//校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
		$arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
		$arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
		$sign = 0;
		for ( $i = 0; $i < 17; $i++ )
		{
			$b = (int) $id[$i];
			$w = $arr_int[$i];
			$sign += $b * $w;
		}
		$n = $sign % 11;
		$val_num = $arr_ch[$n];
		if ($val_num != substr($id,17, 1))
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}

function checkRefererHost(){
	if(!$_SERVER['HTTP_REFERER'])return false;
	$url_arr = parse_url($_SERVER['HTTP_REFERER']);
	$http_host = $_SERVER['HTTP_HOST'];
	if(strpos($http_host,':'))$http_host = substr($http_host, 0, strpos($http_host, ':'));
	return $url_arr['host'] === $http_host;
}
function randFloat($min=0, $max=1){
	return $min + random_int(0, PHP_INT_MAX)/PHP_INT_MAX * ($max-$min);
}

function check_cert($idcard, $name, $phone){
	global $conf;
	$appcode = $conf['cert_appcode'];
	$url = 'http://phone3.market.alicloudapi.com/phonethree';
	$post = ['idcard'=>$idcard, 'phone'=>$phone, 'realname'=>$name];
	$data = get_curl($url.'?'.http_build_query($post), 0,0,0,0,0,0, ['Authorization: APPCODE '.$appcode, 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8']);
	$arr=json_decode($data,true);
	if(isset($arr['code']) && $arr['code']==200){
		return ['code'=>0, 'msg'=>$arr['msg']];
	}elseif(isset($arr['msg'])){
		return ['code'=>-1, 'msg'=>$arr['msg']];
	}else{
		return ['code'=>-2, 'msg'=>'返回结果解析失败'];
	}
}
function check_corp_cert($companyName, $creditNo, $legalPerson){
	global $conf;
	$appcode = $conf['cert_appcode2'];
	$url = 'http://companythree.shumaidata.com/companythree/check';
	$post = ['companyName'=>$companyName, 'creditNo'=>$creditNo, 'legalPerson'=>$legalPerson];
	$data = get_curl($url.'?'.http_build_query($post), 0, 0,0,0,0,0, ['Authorization: APPCODE '.$appcode, 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8']);
	$arr=json_decode($data,true);
	if(array_key_exists('code',$arr) && $arr['code']==200){
		if($arr['data']['result']==0){
			return ['code'=>0, 'msg'=>$arr['data']['desc']];
		}else{
			return ['code'=>-1, 'msg'=>$arr['data']['desc']=='不一致'?'公司与法人信息不一致':$arr['data']['desc']];
		}
	}elseif(array_key_exists('msg',$arr)){
		return ['code'=>-1, 'msg'=>$arr['msg']];
	}else{
		return ['code'=>-2, 'msg'=>'返回结果解析失败'];
	}
}
function show_cert_type($certtype){
	if($certtype == 1){
		return '企业实名认证';
	}else{
		return '个人实名认证';
	}
}
function show_cert_method($certmethod){
	if($certmethod == 1){
		return '微信快捷认证';
	}elseif($certmethod == 2){
		return '手机号三要素认证';
	}elseif($certmethod == 3){
		return '人工审核认证';
	}else{
		return '支付宝快捷认证';
	}
}

function randomFloat($min = 0, $max = 1) {
	$num = $min + random_int(0, PHP_INT_MAX) / PHP_INT_MAX * ($max - $min);
	return sprintf("%.2f",$num);
}

function wx_get_access_token($appid, $secret) {
	global $DB;
	$row = $DB->getRow("SELECT id FROM pre_weixin WHERE appid=:appid LIMIT 1", [':appid'=>$appid]);
	if($row) return $row['id'];
	return false;
}

function wxminipay_jump_scheme($wid, $orderid){
	global $conf, $order, $siteurl;
	$path = 'pages/pay/pay';
	if($conf['wxminipay_path']) {
		$path = $conf['wxminipay_path'];
	}
	$jump_url = $siteurl.'pay/wxminipay/'.$orderid.'/';
	$query = 'money='.$order['realmoney'].'&url='.$jump_url;
	$wechat = new \lib\wechat\WechatAPI($wid);
	return $wechat->generate_scheme($path, $query);
}

function wxminipay_jump_path($orderid){
	global $conf, $order, $siteurl;
	$path = 'pages/pay/pay';
	if($conf['wxminipay_path']) {
		$path = $conf['wxminipay_path'];
	}
	$jump_url = $siteurl.'pay/wxminipay/'.$orderid.'/';
	$extraData = ['money'=>$order['realmoney'], 'url'=>$jump_url];
	return $path . '?' . http_build_query($extraData);
}

function wechat_oauth($wxinfo){
	global $conf;
	$oauth = new \lib\wechat\WechatOauth($wxinfo['appid'], $wxinfo['appsecret']);
	if (isset($_GET['code'])) {
		$code = $_GET['code'];
		return $oauth->GetOpenidFromMp($code);
	}else{
		if(!empty($conf['wx_open_url'])){
			$oauth->setOpenUrl($conf['wx_open_url']);
		}
		$oauth->login();
	}
}

function wechat_applet_oauth($code, $wxinfo){
	$oauth = new \lib\wechat\WechatOauth($wxinfo['appid'], $wxinfo['appsecret']);
	$openid = $oauth->AppGetOpenid($code);
	if(isset($_GET['phone_code']) && !empty($_GET['phone_code'])){
		$wechat = new \lib\wechat\WechatAPI($wxinfo['id']);
		$phone_info = $wechat->getPhoneNumber($_GET['phone_code']);
		if($phone_info && isset($phone_info['phoneNumber'])){
			global $DB;
			$DB->update('order', ['buyer'=>$openid, 'mobile'=>$phone_info['phoneNumber']], ['trade_no'=>TRADE_NO]);
			$black = $DB->find('blacklist', '*', ['type'=>0, 'content'=>$phone_info['phoneNumber']], null, 1);
			if($black){
				throw new Exception('系统异常无法完成付款');
			}
		}
	}
	return $openid;
}

function checkDomain($domain){
	if(empty($domain) || !preg_match('/^[-$a-z0-9:_*.]{2,512}$/i', $domain) || (stripos($domain, '.') === false) || substr($domain, -1) == '.' || substr($domain, 0 ,1) == '.' || substr($domain, 0 ,1) == '*' && substr($domain, 1 ,1) != '.' || substr_count($domain, '*')>1 || strpos($domain, '*')>0 || strlen($domain)<4) return false;
	return true;
}

//微信合单支付，返回所有子单金额
function combinepay_submoneys($money){
	global $conf;
	if(!$conf['wxcombine_open'] || !$conf['wxcombine_minmoney']) return false;
	if($money >= intval($conf['wxcombine_minmoney']*100)){
		$subnum = 3;
		$submoney = intval($money/$subnum);
		while($submoney > intval($conf['wxcombine_submoney']*100)){
			$subnum++;
			$submoney = intval($money/$subnum);
			if($subnum==50)break;
		}
		$submoneys = [];
		for($i=0;$i<$subnum;$i++){
			$submoneys[] = $submoney;
		}
		$mod = $money%$subnum;
		if($mod > 0){
			for($i=0;$i<$mod;$i++){
				$submoneys[$i] += 1;
			}
		}
		return $submoneys;
	}
	return false;
}

function get_invite_code($uid){
	return rtrim(strtr(base64_encode(hash_hmac('sha256', (string)$uid, SYS_KEY, true) . pack('N', $uid)), '+/', '-_'), '=');
}

function get_invite_uid($code){
	$code = base64_decode(strtr($code, '-_', '+/'));
	if (strlen($code) < 36) return 0;
	$uid = unpack('N', substr($code, 32, 4))[1];
	$expected = hash_hmac('sha256', (string)$uid, SYS_KEY, true) . pack('N', $uid);
	return hash_equals($code, $expected) ? $uid : 0;
}

function currency_convert($from, $to, $amount){
	$param = [
		'from' => $from,
		'to' => $to,
		'amount' => $amount
	];
	$url = 'https://api.exchangerate.host/convert?'.http_build_query($param);
	$data = get_curl($url);
	$arr = json_decode($data, true);
	if($arr['success']===true){
		return $arr['result'];
	}else{
		throw new Exception('汇率转换失败');
	}
}

function checkPayVerifyOpen($pid){
	global $DB, $conf, $clientip;
	if($conf['pay_verify'] == 3) return true;
	elseif($conf['pay_verify'] == 2){
		$uid_arr = explode('|', $conf['pay_verify_check_uid']);
		if(in_array($pid, $uid_arr)) return true;
	}
	elseif($conf['pay_verify'] == 1){
		$second = intval($conf['pay_verify_check_second']);
		$count = intval($conf['pay_verify_check_count']);
		$sucrate = floatval($conf['pay_verify_check_rate']);
		if($second>0 || $count>0 || $sucrate>0){
			$total_num=$DB->getColumn("SELECT count(*) FROM pre_order WHERE uid=:uid AND addtime>=DATE_SUB(NOW(), INTERVAL ".intval($second)." SECOND)", [':uid'=>$pid]);
			$succ_num=$DB->getColumn("SELECT count(*) FROM pre_order WHERE uid=:uid AND addtime>=DATE_SUB(NOW(), INTERVAL ".intval($second)." SECOND) AND status>0", [':uid'=>$pid]);
			if($total_num >= $count){
				$succ_rate = round($succ_num * 100 / $total_num, 2);
				if($succ_rate < $sucrate){
					return true;
				}
			}
		}
		$ipcheck = intval($conf['pay_verify_check_ip']);
		if($ipcheck>0){
			$orders = $DB->getAll("SELECT status FROM pre_order WHERE `ip`=:ip AND addtime>=DATE_SUB(NOW(), INTERVAL 3600 SECOND) ORDER BY addtime DESC LIMIT ".intval($ipcheck), [':ip'=>$clientip]);
			$fail_num = 0;
			foreach($orders as $row){
				if($row['status'] == 0) $fail_num++;
			}
			if($fail_num>=$ipcheck){
				return true;
			}
		}
	}
	return false;
}

function showPayVerifyPage($defend_key, $query_arr){
	global $conf, $cdnpublic;
	if($conf['pay_verify_type'] == 0){
		$key = time().$defend_key.random_int(111111,999999);
		include PAYPAGE_ROOT.'verify_jump.php';
	}elseif($conf['pay_verify_type'] == 1){
		include PAYPAGE_ROOT.'verify_invisible.php';
	}elseif($conf['pay_verify_type'] == 2){
		include PAYPAGE_ROOT.'verify_slide.php';
	}
	exit;
}

function getDefendKey($pid, $trade_no){
	return md5(SYS_KEY.$pid.'_'.$trade_no.SYS_KEY);
}

//极验3.0服务端验证
function verify_captcha($user_id = 'public'){
	global $conf, $clientip;
	$GtSdk = new \lib\GeetestLib($conf['captcha_id'], $conf['captcha_key']);
	if($conf['captcha_version'] == '1'){
		return $GtSdk->gt4_validate($_POST['captcha_id'], $_POST['lot_number'], $_POST['pass_token'], $_POST['gen_time'], $_POST['captcha_output']);
	}else{
		$data = array(
			'user_id' => $user_id,
			'client_type' => "web",
			'ip_address' => $clientip
		);
		if ($_SESSION['gtserver'] == 1) {   //服务器正常
			return $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
		}else{  //服务器宕机,走failback模式
			return $GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode']);
		}
	}
}

//极验4.0服务端验证
function verify_captcha4(){
    global $conf;
    if(!isset($_POST['captcha_id']) || !isset($_POST['lot_number']) || !isset($_POST['pass_token']) || !isset($_POST['gen_time']) || !isset($_POST['captcha_output'])) return false;
    // 安全修复(H-2)：原实现硬编码极验官方 demo 端点(gt4.geetest.com/demov4/demo/login)，
    // 攻击者只要走 demo 站即可获得 success，验证码形同虚设。现改为使用系统配置的极验正式参数
    // (captcha_id / captcha_key)，复用 GeetestLib::gt4_validate 走官方校验接口
    // (gcaptcha4.geetest.com/validate) 并以 captcha_key 做 HMAC 签名校验。
    // 配置缺失时直接判定校验不通过（fail-closed），不再回退 demo 端点放行。
    if(empty($conf['captcha_id']) || empty($conf['captcha_key'])) return false;
    $GtSdk = new \lib\GeetestLib($conf['captcha_id'], $conf['captcha_key']);
    return $GtSdk->gt4_validate($_POST['captcha_id'], $_POST['lot_number'], $_POST['pass_token'], $_POST['gen_time'], $_POST['captcha_output']) ? true : false;
}

function getGroupConfig($gid){
	global $DB;
	$input_key = ['settle_open', 'settle_type', 'settle_transfer', 'user_transfer'];
	$grouprow=$DB->getRow("SELECT config FROM pre_group WHERE gid=:gid LIMIT 1", [':gid'=>$gid]);
	if(!$grouprow)$grouprow=$DB->getRow("SELECT config FROM pre_group WHERE gid=0 LIMIT 1");
	$config = [];
	if(!$grouprow) return $config;
	if($grouprow['config']){
		$arr = json_decode($grouprow['config'], true);
		foreach($arr as $key=>$value){
			if(!isNullOrEmpty($value)){
				if(in_array($key, $input_key) && $value=='0') continue;
				if($key == 'settle_type') $value = $value-1;
				$config[$key] = $value;
			}
		}
	}
	return $config;
}

function get_alipay_userid(){
	global $conf;
	if($conf['alipay_web_login']==0) throw new Exception('未配置支付宝网页快捷登录通道');
	$channel = \lib\Channel::get($conf['alipay_web_login']);
	if(!$channel) throw new Exception('支付宝网页快捷登录通道信息不存在');
	$alipay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
	return alipay_oauth($alipay_config, true);
}

function alipay_oauth($alipay_config = null){
	global $conf;
	if($alipay_config && $conf['alipay_web_login_all'] == 1 && $conf['alipay_web_login'] > 0 || !$alipay_config && $conf['alipay_web_login'] > 0){
		$channel = \lib\Channel::get($conf['alipay_web_login']);
		if($channel) {
			$alipay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
		}
	}
	if(!$alipay_config) throw new Exception('未配置支付宝网页快捷登录通道');
	try{
		$oauth = new \Alipay\AlipayOauthService($alipay_config);
		if(isset($_GET['auth_code'])){
			$result = $oauth->getToken($_GET['auth_code']);
			if(!empty($result['user_id'])){
				$user_id = $result['user_id'];
				$user_type = 'userid';
			}else{
				$user_id = $result['open_id'];
				$user_type = 'openid';
			}
			if($conf['alipay_getmobile'] == 1 && defined('TRADE_NO')){
				$userinfo = $oauth->userinfo($result['access_token']);
				if(isset($userinfo['mobile'])){
					global $DB;
					$DB->update('order', ['buyer'=>$user_id, 'mobile'=>$userinfo['mobile']], ['trade_no'=>TRADE_NO]);
					$black = $DB->find('blacklist', '*', ['type'=>0, 'content'=>$userinfo['mobile']], null, 1);
					if($black){
						throw new Exception('系统异常无法完成付款');
					}
				}
			}
			return [$user_type, $user_id];
		}else{
			$redirect_uri = (is_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			if($conf['alipay_getmobile'] == 1 && defined('TRADE_NO')){
				$oauth->oauth($redirect_uri, null, 'auth_user');
			}else{
				$oauth->oauth($redirect_uri);
			}
		}
	}catch(Exception $e){
		throw new Exception('支付宝快捷登录失败！'.$e->getMessage());
	}
}

function alipay_mini_oauth($auth_code, $alipay_config = null){
	global $conf;
	if($conf['alipay_mini_login'] > 0){
		$channel = \lib\Channel::get($conf['alipay_mini_login']);
		if($channel) {
			$alipay_config = require(PLUGIN_ROOT.$channel['plugin'].'/inc/config.php');
		}
	}
	if(!$alipay_config) throw new Exception('未配置支付宝小程序快捷登录通道');
	try{
		$oauth = new \Alipay\AlipayOauthService($alipay_config);
		$result = $oauth->getToken($auth_code);
		if(!empty($result['user_id'])){
			$user_id = $result['user_id'];
			$user_type = 'userid';
		}else{
			$user_id = $result['open_id'];
			$user_type = 'openid';
		}
		if(!empty($_GET['phone_data']) && !empty($conf['alipay_aes_key'])){
			$phone_data = json_decode($_GET['phone_data'], true);
			if($phone_data){
				$mobile = $oauth->decryptMobile($phone_data, $conf['alipay_aes_key']);
				global $DB;
				$DB->update('order', ['buyer'=>$user_id, 'mobile'=>$mobile], ['trade_no'=>TRADE_NO]);
				$black = $DB->find('blacklist', '*', ['type'=>0, 'content'=>$mobile], null, 1);
				if($black){
					throw new Exception('系统异常无法完成付款');
				}
			}
		}
		return [$alipay_config['app_id'], $user_type, $user_id];
	}catch(Exception $e){
		throw new Exception('支付宝快捷登录失败！'.$e->getMessage());
	}
}

function alipaymini_jump_scheme($orderid, $appid = null){
	global $conf, $order, $siteurl;
	if($conf['alipay_mini_login'] > 0){
		$channel = \lib\Channel::get($conf['alipay_mini_login']);
		if($channel) {
			$appid = $channel['appid'];
		}
	}
	$jump_url = $siteurl.'pay/alipaymini/'.$orderid.'/';
	$path = 'pages/pay/pay';
	$param = ['money'=>$order['realmoney'], 'url'=>$jump_url];
	$page = $path.'?'.http_build_query($param);
	$scheme_url = 'alipays://platformapi/startapp?appId='.$appid.'&page='.urlencode($page);
	return $scheme_url;
}

function getBankCardInfo($cardno){
	$url = 'https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardNo='.$cardno.'&cardBinCheck=true';
	$data = get_curl($url);
	$arr = json_decode($data, true);
	if($arr['validated']){
		$bank_arr = json_decode(file_get_contents(SYSTEM_ROOT.'lib/bank.json'), true);
		return ['card_no'=>$cardno, 'bank_code'=>$arr['bank'], 'bank_name'=>$bank_arr[$arr['bank']] ? $bank_arr[$arr['bank']] : $arr['bank'], 'card_type'=>$arr['cardType']];
	}else{
		throw new Exception('银行卡号校验失败');
	}
}

function convert_channel_data(){
	global $DB;
	$data_list = $DB->getAll("SELECT * FROM pre_channel WHERE config IS NULL");
	foreach($data_list as $row){
		$config = [];
		if($row['appid']) $config['appid'] = $row['appid'];
		if($row['appkey']) $config['appkey'] = $row['appkey'];
		if($row['appsecret']) $config['appsecret'] = $row['appsecret'];
		if($row['appurl']) $config['appurl'] = $row['appurl'];
		if($row['appmchid']) $config['appmchid'] = $row['appmchid'];
		if($row['appswitch']) $config['appswitch'] = $row['appswitch'];
		$configstr = json_encode($config);
		$DB->update('channel', ['config'=>$configstr], ['id'=>$row['id']]);
	}
	if(file_exists(TEMPLATE_ROOT.'index1/doc.php')){
		unlink(TEMPLATE_ROOT.'index1/doc.php');
	}
}

function addon_update($name, $version){
	global $DB, $conf, $dbconfig, $CACHE;
	if(!$conf['addon_'.$name] || $conf['addon_'.$name] < $version){
		$sql = ROOT.'install/addon_'.$name.'.sql';
		if(file_exists($sql)){
			$sql = file_get_contents($sql);
			$sqls = explode(';', $sql);
			foreach($sqls as $value){
				$value = trim($value);
				if(empty($value)) continue;
				$value = str_replace('pre_',$dbconfig['dbqz'].'_',$value);
				$DB->exec($value);
			}
		}
		saveSetting('addon_'.$name, $version);
		$CACHE->clear();
	}
}

function generate_key_pair(){
	$config = [
		"private_key_bits" => 2048,
		'private_key_type' => OPENSSL_KEYTYPE_RSA,
	];
	$res = openssl_pkey_new($config);
	if(!$res) {
		touch(SYSTEM_ROOT.'openssl.cnf');
		$config = [
			"config" => SYSTEM_ROOT.'openssl.cnf',
			"private_key_bits" => 2048,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		];
		$res = openssl_pkey_new($config);
		if(!$res) return null;
	}
	$privateKey = '';
	openssl_pkey_export($res, $privateKey, null, $config);
	$pubKey = openssl_pkey_get_details($res);
	openssl_pkey_free($res);
	return ['public_key'=>pemToBase64($pubKey["key"]), 'private_key'=>pemToBase64($privateKey)];
}

function pemToBase64($data){
	$line = explode("\n", $data);
	$base64 = '';
	foreach($line as $row){
		if(empty($row) || strpos($row, '-----BEGIN')!==false || strpos($row, '-----END')!==false) continue;
		$base64 .= trim($row);
	}
	return $base64;
}

function base64ToPem($data, $type){
	if(empty($data) || strpos($data, '-----BEGIN')!==false) return $data;
	$pem = "-----BEGIN ".$type."-----\n" .
        wordwrap($data, 64, "\n", true) .
        "\n-----END ".$type."-----";
    return $pem;
}

function echojson($array){
	@header('Content-Type: application/json; charset=UTF-8');
	exit(json_encode($array, JSON_UNESCAPED_UNICODE));
}

function echojsonmsg($msg, $code = -1){
	echojson(['code'=>$code, 'msg'=>$msg]);
}

/**
 * 统一JSON返回方法（包含data层）
 * @param int $code 状态码，200表示成功，其他表示失败
 * @param string $msg 消息内容
 * @param mixed $data 返回数据
 */
function json_response($code = 200, $msg = 'success', $data = null){
	@header('Content-Type: application/json; charset=UTF-8');
	$response = [
		'code' => $code,
		'msg' => $msg
	];
	if($data !== null){
		$response['data'] = $data;
	}
	exit(json_encode($response, JSON_UNESCAPED_UNICODE));
}

function getScanPayType($authCode){
	$prefix = substr($authCode,0,2);
	$alipay_prefix = ['25', '26', '27', '28', '29', '30'];
	$wxpay_prefix = ['10', '11', '12', '13', '14', '15'];
	$qqpay_prefix = ['91'];
	$bank_prefix = ['62'];
	$ecny_prefix = ['01'];
	if(in_array($prefix, $alipay_prefix)){
		return 'alipay';
	}elseif(in_array($prefix, $wxpay_prefix)){
		return 'wxpay';
	}elseif(in_array($prefix, $qqpay_prefix)){
		return 'qqpay';
	}elseif(in_array($prefix, $bank_prefix)){
		return 'bank';
	}elseif(in_array($prefix, $ecny_prefix)){
		return 'ecny';
	}else{
		return 'unknown';
	}
}
function check_proxy($url)
{
	global $conf;
	$ch=curl_init($url);
	$proxy_server = $conf['proxy_server'];
	$proxy_port = intval($conf['proxy_port']);
	if($conf['proxy_type'] == 'https'){
		$proxy_type = CURLPROXY_HTTPS;
	}elseif($conf['proxy_type'] == 'sock4'){
		$proxy_type = CURLPROXY_SOCKS4;
	}elseif($conf['proxy_type'] == 'sock5'){
		$proxy_type = CURLPROXY_SOCKS5;
	}else{
		$proxy_type = CURLPROXY_HTTP;
	}
	curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_PROXY, $proxy_server);
	curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
	if(!empty($conf['proxy_user']) && !empty($conf['proxy_pwd'])){
		$proxy_userpwd = $conf['proxy_user'].':'.$conf['proxy_pwd'];
		curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_userpwd);
	}
	curl_setopt($ch, CURLOPT_PROXYTYPE, $proxy_type);
	$httpheader[] = "Accept: */*";
	$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
	$httpheader[] = "Connection: close";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36');
	curl_setopt($ch, CURLOPT_TIMEOUT, 3);
	curl_exec($ch);
	$errno = curl_errno($ch);
	if($errno){
		$errmsg = curl_error($ch);
		curl_close($ch);
		throw new Exception($errmsg);
	}
	$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	curl_close($ch);
	if($httpCode >= 200 && $httpCode < 400){
		return true;
	}else{
		throw new Exception('HTTP状态码异常：'.$httpCode);
	}
}

function showstar($num){
	$data = '';
	for($i=0;$i<$num;$i++){
		$data .= '*';
	}
	return $data;
}

function is_url($url){
	if (preg_match('/^(http|https):\/\/[^\s]+/', $url)) {
		return true;
	} else {
		return false;
	}
}

function is_self_url($url){
	global $conf, $siteurl;
	return strpos($url, $siteurl)===0 || !empty($conf['localurl_alipay']) && strpos($url, $conf['localurl_alipay'])===0 || !empty($conf['localurl_wxpay']) && strpos($url, $conf['localurl_wxpay'])===0;
}

function get_ip_region($ip){
	if(!class_exists('\\lib\\Ip2Region')) return false;
	$ipregion = new \lib\Ip2Region();
	$region = $ipregion->search($ip);
	if(!$region) return false;
	$region = explode('|',$region);
	if($region[1] == '中国'){
		return $region[2].$region[3];
	}else{
		return $region[1].$region[2].$region[3];
	}
}