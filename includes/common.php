<?php
//error_reporting(0);
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);
if(defined('IN_CRONLITE'))return;
define('VERSION', '3097');
define('DB_VERSION', '2054');
define('IN_CRONLITE', true);
define('SYSTEM_ROOT', dirname(__FILE__).'/');
define('ROOT', dirname(SYSTEM_ROOT).'/');
define('PAYPAGE_ROOT', SYSTEM_ROOT.'pages/');
define('TEMPLATE_ROOT', ROOT.'template/');
define('PLUGIN_ROOT', ROOT.'plugins/');
date_default_timezone_set('Asia/Shanghai');
$date = date("Y-m-d H:i:s");

// Session 安全配置
ini_set('session.cookie_httponly', '1');
$cookieSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
ini_set('session.cookie_secure', $cookieSecure ? '1' : '0');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

// 安全响应头
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
// 安全修复(M2)：从 script-src 移除 'unsafe-eval'，禁止 eval/new Function 等动态执行，收敛 XSS 利用面；
// 暂保留 'unsafe-inline' 以兼容现有内联脚本（后续可迁移为 nonce/hash 后一并移除）。
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com;");

if(!isset($nosession) || !$nosession){
	session_start();

	// CSRF Token 生成
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	// CSRF 验证（POST请求，排除白名单）
	$csrf_whitelist = ['gateway.php', 'api.php', 'mapi.php', 'cron.php'];
	$current_script = basename($_SERVER['SCRIPT_FILENAME']);
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($current_script, $csrf_whitelist)) {
		$token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
		if (!hash_equals($_SESSION['csrf_token'], $token)) {
			http_response_code(403);
			exit('CSRF token验证失败');
		}
	}
}

if(!function_exists("is_https")){
	function is_https() {
		if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443){
			return true;
		}elseif(isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')){
			return true;
		}elseif(isset($_SERVER['HTTP_X_CLIENT_SCHEME']) && $_SERVER['HTTP_X_CLIENT_SCHEME'] == 'https'){
			return true;
		}elseif(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
			return true;
		}elseif(isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https'){
			return true;
		}elseif(isset($_SERVER['HTTP_EWS_CUSTOME_SCHEME']) && $_SERVER['HTTP_EWS_CUSTOME_SCHEME'] == 'https'){
			return true;
		}
		return false;
	}
}

$siteurl = (is_https() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].'/';

include_once(SYSTEM_ROOT."autoloader.php");
Autoloader::register();

if($is_defend){
	include_once(SYSTEM_ROOT."txprotect.php");
}

require ROOT.'config.php';
define('DBQZ', $dbconfig['dbqz']);

if(!$dbconfig['user']||!$dbconfig['pwd']||!$dbconfig['dbname'])//检测安装1
{
header('Content-type:text/html;charset=utf-8');
echo '你还没安装！<a href="/install/">点此安装</a>';
exit();
}

$DB = new \lib\PdoHelper($dbconfig);

if($DB->query("SELECT * FROM pre_config WHERE 1=1 LIMIT 1")==FALSE)//检测安装2
{
header('Content-type:text/html;charset=utf-8');
echo '你还没安装！<a href="/install/">点此安装</a>';
exit();
}


$CACHE=new \lib\Cache();
$conf=$CACHE->pre_fetch();
define('SYS_KEY', $conf['syskey']);
if(!$conf['localurl'])$conf['localurl'] = $siteurl;
if(!isset($conf['session_salt']) || empty($conf['session_salt'])){
	throw new \RuntimeException('安全配置 session_salt 未设置，请在系统设置中配置');
}
$password_hash = $conf['session_salt'];

if ($conf['version'] < DB_VERSION) {
    if (!$install) {
		header('Content-type:text/html;charset=utf-8');
        echo '请先完成网站升级！<a href="/install/update.php"><font color=red>点此升级</font></a>';
        exit;
    }
}

include_once(SYSTEM_ROOT."functions.php");
include_once(SYSTEM_ROOT."member.php");

require_once SYSTEM_ROOT."vendor/autoload.php";

if (!file_exists(ROOT.'install/install.lock') && file_exists(ROOT.'install/index.php')) {
	sysmsg('<h2>检测到无 install.lock 文件</h2><ul><li><font size="4">如果您尚未安装本程序，请<a href="/install/">前往安装</a></font></li><li><font size="4">如果您已经安装本程序，请手动放置一个空的 install.lock 文件到 /install 文件夹下，<b>为了您站点安全，在您完成它之前我们不会工作。</b></font></li></ul><br/><h4>为什么必须建立 install.lock 文件？</h4>它是安装保护文件，如果检测不到它，就会认为站点还没安装，此时任何人都可以安装/重装你的网站。<br/><br/>');exit;
}

if($conf['cdnpublic']==1){
	$cdnpublic = '//lib.baomitu.com/';
}elseif($conf['cdnpublic']==2){
	$cdnpublic = 'https://s4.zstatic.net/ajax/libs/';
}elseif($conf['cdnpublic']==4){
	$cdnpublic = 'https://cdnjs.snrat.com/ajax/libs/';
}else{
	$cdnpublic = '/assets/vendor/';
}

function csrf_field() {
	return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'] ?? '') . '">';
}
function csrf_token() {
	return $_SESSION['csrf_token'] ?? '';
}

if(empty($conf['public_key'])){
	$key_pair = generate_key_pair();
	if($key_pair){
		$conf['public_key'] = $key_pair['public_key'];
		$conf['private_key'] = $key_pair['private_key'];
		saveSetting('public_key', $conf['public_key']);
		saveSetting('private_key', $conf['private_key']);
		$CACHE->clear();
		unset($key_pair);
	}
}
?>