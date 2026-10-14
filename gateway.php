<?php
$nosession = true;
require './includes/common.php';

@header('Content-Type: application/json; charset=UTF-8');

function voicemsg($msg){
    exit(json_encode(['cmd'=>'voice', 'msg'=>$msg], JSON_UNESCAPED_UNICODE));
}

$json = file_get_contents('php://input');
$arr = json_decode($json,true);
if(!$arr || !isset($arr['cmd'])) exit;
//file_put_contents('gateway.txt', $json);

if($arr['cmd'] == 'heartbeat'){
    $result = ['cmd'=>'heartbeat'];
    exit(json_encode($result, JSON_UNESCAPED_UNICODE));
}elseif($arr['cmd'] == 'qrcode'){
    $sn = $arr['sn'];
    if(!$sn) voicemsg('设备号不能为空');
    $userrow=$DB->getRow("SELECT `uid`,`gid`,`key`,`device_secret`,`money`,`mode`,`pay`,`cert`,`status`,`channelinfo`,`qq`,`ordername`,`keytype`,`publickey` FROM `pre_user` WHERE `voice_devid`=:voice_devid LIMIT 1", [':voice_devid'=>$sn]);
    if(!$userrow) voicemsg('设备关联的商户不存在');
    if($userrow['status']==0 || $userrow['pay']==0) voicemsg('当前商户已被封禁');
    $uid = $userrow['uid'];

    $auth_code = base64_decode($arr['data']);
    if(!$auth_code) voicemsg('二维码解析失败');

    $money = $arr['money'];
    if($money <= 0 || !is_numeric($money) || !preg_match('/^[0-9.]+$/', $money)) voicemsg('金额错误');
    if($conf['pay_maxmoney']>0 && $money>$conf['pay_maxmoney']) voicemsg('最大支付金额是'.$conf['pay_maxmoney'].'元');
	if($conf['pay_minmoney']>0 && $money<$conf['pay_minmoney']) voicemsg('最小支付金额是'.$conf['pay_minmoney'].'元');

    $trade_no = date("YmdHis").random_int(11111,99999);
    $out_trade_no = $arr['msgid'];
    if(!preg_match('/^[a-zA-Z0-9.\_\-|]+$/',$out_trade_no))voicemsg('订单号格式不正确');
    $method = 'scan';
    $name = '付款码收款';
    $type = getScanPayType($auth_code);
    if($type == 'unknown') voicemsg('未知的付款码类型');
    $typeshowname = $DB->findColumn('type', 'showname', ['name'=>$type]);

    // 安全修复(M-2)：设备上行接口此前无任何签名/防重放，任何人知道 sn 即可伪造收款下单。
    // 现已为设备引入独立密钥列 pre_user.device_secret（强鉴权），优先用它校验 HMAC：
    //  - 若设备已配置 device_secret：强制要求上送 sign，并用 device_secret 校验，缺签名或签名错误一律拒绝；
    //    设备密钥与商户业务 key 解耦，泄露商户 key 不再能伪造设备上行请求。
    //  - 若设备尚未配置 device_secret（旧设备/未生成密钥）：回退到原兼容逻辑（用商户 key 做“有则校验”），
    //    以免升级后立即中断存量设备。FIXME：存量设备应尽快在商户中心生成 device_secret 切换为强鉴权。
    // 其余防重放/频控加固保持不变：
    //  2) 时间戳窗口防重放：若上送 timestamp，则要求与服务器时间相差不超过 300 秒；
    //  3) msgid 防重放：同一商户下相同 out_trade_no 不允许重复下单；
    //  4) 频控：限制同一设备(商户)短时间内的下单频率，缓解重放/刷单。
    $now = time();
    $reqTs = isset($arr['timestamp']) ? intval($arr['timestamp']) : 0;
    if($reqTs <= 0 || abs($now - $reqTs) > 300) voicemsg('请求已过期或缺少时间戳');
    if(!isset($arr['sign']) || $arr['sign'] === '') voicemsg('缺少设备签名');

    $signStr = 'cmd='.$arr['cmd'].'&sn='.$sn.'&data='.$arr['data'].'&money='.$money.'&msgid='.$out_trade_no.'&timestamp='.$reqTs;
    $secretKey = !empty($userrow['device_secret']) ? (string)$userrow['device_secret'] : (string)$userrow['key'];
    if($secretKey === '') voicemsg('设备密钥未配置');
    $expectSign = hash_hmac('sha256', $signStr, $secretKey);
    if(!hash_equals($expectSign, (string)$arr['sign'])) voicemsg('签名校验失败');

    $dup = $DB->getRow("SELECT trade_no FROM pre_order WHERE out_trade_no=:o AND uid=:uid LIMIT 1", [':o'=>$out_trade_no, ':uid'=>$uid]);
    if($dup) voicemsg('订单号重复，请勿重复提交');

    $rc = $DB->getRow("SELECT COUNT(*) AS c FROM pre_order WHERE uid=:uid AND tid=3 AND addtime > (NOW() - INTERVAL '5 SECOND')", [':uid'=>$uid]);
    if($rc && intval($rc['c']) >= 5) voicemsg('请求过于频繁，请稍后再试');

    $return_url=$siteurl;
    $domain=getdomain($return_url);
    if(!$DB->exec("INSERT INTO `pre_order` (`trade_no`,`out_trade_no`,`uid`,`tid`,`addtime`,`name`,`money`,`notify_url`,`return_url`,`domain`,`ip`,`status`) VALUES (:trade_no, :out_trade_no, :uid, 3, NOW(), :name, :money, :notify_url, :return_url, :domain, :clientip, 0)", [':trade_no'=>$trade_no, ':out_trade_no'=>$out_trade_no, ':uid'=>$uid, ':name'=>$name, ':money'=>$money, ':notify_url'=>$return_url, ':return_url'=>$return_url, ':domain'=>$domain, ':clientip'=>$clientip]))voicemsg('创建订单失败');

    $submitData = \lib\Channel::submit($type, $userrow['uid'], $userrow['gid'], $money);
    if(!$submitData) voicemsg('没有可用的支付通道');
    $realmoney = sprintf("%.2f", $money);
    $getmoney = round($money*$submitData['rate']/100,2);

    if($submitData['mode']==1 && $realmoney-$getmoney>$userrow['money']){
        voicemsg('当前商户余额不足，无法完成支付，请商户登录用户中心充值余额');
    }

    $DB->update('order', ['type'=>$submitData['typeid'], 'channel'=>$submitData['channel'], 'subchannel'=>$submitData['subchannel'], 'realmoney'=>$realmoney, 'getmoney'=>$getmoney], ['trade_no'=>$trade_no]);
    
    $order['trade_no'] = $trade_no;
    $order['out_trade_no'] = $out_trade_no;
    $order['uid'] = $uid;
    $order['tid'] = 3;
    $order['addtime'] = date('Y-m-d H:i:s');
    $order['name'] = $name;
    $order['realmoney'] = $realmoney;
    $order['getmoney'] = $getmoney;
    $order['money'] = $money;
    $order['type'] = $submitData['typeid'];
    $order['channel'] = $submitData['channel'];
    $order['subchannel'] = $submitData['subchannel'];
    $order['typename'] = $submitData['typename'];
    $order['profits'] = \lib\Payment::updateOrderProfits($order, $submitData['plugin']);
    $order['auth_code'] = $auth_code;

    try{
        $res = \lib\Plugin::loadForSubmit($submitData['plugin'], $trade_no, true);
    }catch(Exception $e){
        voicemsg($e->getMessage());
    }
    if($res['type'] == 'error') voicemsg(preg_replace('/\[[^\]]*\]/', '', $res['msg']));
    if($res['type'] != 'scan') voicemsg('当前支付插件不支持付款码支付');
    $result = ['cmd'=>'voice', 'msg'=>$typeshowname.'收款'.$money.'元', 'money'=>$money];
    exit(json_encode($result, JSON_UNESCAPED_UNICODE));
}