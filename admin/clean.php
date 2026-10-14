<?php
/**
 * 系统数据清理
**/
include("../includes/common.php");
$title='系统数据清理';
include './head.php';
if($islogin==1){}else exit("<script language='javascript'>window.location.href='./login.php';</script>");
?>
  <div class="container" style="padding-top:70px;">
    <div class="col-xs-12 col-sm-10 col-lg-8 center-block" style="float: none;">
<?php
if(!isset($_SESSION['csrf_token'])){
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$mod=isset($_POST['mod'])?$_POST['mod']:(isset($_GET['mod'])?$_GET['mod']:null);
if($mod && $mod!='cleancache' && !isset($_GET['mod'])){
	if(!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])){
		showmsg('CSRF验证失败，请重试',3);
	}
}
if($mod=='cleancache' && $_SERVER['REQUEST_METHOD']==='POST'){
$CACHE->clear();
if(function_exists("opcache_reset"))@opcache_reset();
showmsg('清理系统设置缓存成功！',1);
}elseif($mod=='cleanorder' && $_SERVER['REQUEST_METHOD']==='POST'){
$cutoff = date("Y-m-d H:i:s",strtotime("-30 days"));
$DB->exec("DELETE FROM `pre_order` WHERE addtime<:cutoff", [':cutoff'=>$cutoff]);
$DB->exec("VACUUM pre_order");
showmsg('删除30天前订单记录成功！',1);
}elseif($mod=='cleansettle' && $_SERVER['REQUEST_METHOD']==='POST'){
$cutoff = date("Y-m-d H:i:s",strtotime("-30 days"));
$DB->exec("DELETE FROM `pre_settle` WHERE addtime<:cutoff", [':cutoff'=>$cutoff]);
$DB->exec("VACUUM pre_settle");
showmsg('删除30天前结算记录成功！',1);
}elseif($mod=='cleanrecord' && $_SERVER['REQUEST_METHOD']==='POST'){
$cutoff = date("Y-m-d H:i:s",strtotime("-30 days"));
$DB->exec("DELETE FROM `pre_record` WHERE date<:cutoff", [':cutoff'=>$cutoff]);
$DB->exec("VACUUM pre_record");
showmsg('删除30天前资金明细成功！',1);
}elseif($mod=='cleanorderi' && $_POST['do']=='submit'){
$days = intval($_POST['days']);
if($days<=0)showmsg('请确保每项不能为空',3);
$cutoff = date("Y-m-d H:i:s",strtotime("-{$days} days"));
$DB->exec("DELETE FROM `pre_order` WHERE addtime<:cutoff", [':cutoff'=>$cutoff]);
$DB->exec("VACUUM pre_order");
showmsg('删除订单记录成功！',1);
}elseif($mod=='cleansettlei' && $_POST['do']=='submit'){
$days = intval($_POST['days']);
if($days<=0)showmsg('请确保每项不能为空',3);
$cutoff = date("Y-m-d H:i:s",strtotime("-{$days} days"));
$DB->exec("DELETE FROM `pre_settle` WHERE addtime<:cutoff", [':cutoff'=>$cutoff]);
$DB->exec("VACUUM pre_settle");
showmsg('删除结算记录成功！',1);
}elseif($mod=='cleanrecordi' && $_POST['do']=='submit'){
$days = intval($_POST['days']);
if($days<=0)showmsg('请确保每项不能为空',3);
$cutoff = date("Y-m-d H:i:s",strtotime("-{$days} days"));
$DB->exec("DELETE FROM `pre_record` WHERE date<:cutoff", [':cutoff'=>$cutoff]);
$DB->exec("VACUUM pre_record");
showmsg('删除资金明细成功！',1);
}elseif($mod=='cleantransferi' && $_POST['do']=='submit'){
$days = intval($_POST['days']);
if($days<=0)showmsg('请确保每项不能为空',3);
$cutoff = date("Y-m-d H:i:s",strtotime("-{$days} days"));
$DB->exec("DELETE FROM `pre_transfer` WHERE paytime<:cutoff", [':cutoff'=>$cutoff]);
$DB->exec("VACUUM pre_transfer");
showmsg('删除付款记录成功！',1);
}elseif($mod=='cleanpsorderi' && $_POST['do']=='submit'){
$days = intval($_POST['days']);
if($days<=0)showmsg('请确保每项不能为空',3);
$cutoff = date("Y-m-d H:i:s",strtotime("-{$days} days"));
$DB->exec("DELETE FROM `pre_psorder` WHERE addtime<:cutoff", [':cutoff'=>$cutoff]);
$DB->exec("VACUUM pre_psorder");
showmsg('删除分账记录成功！',1);
}elseif($mod=='cleanlogi' && $_POST['do']=='submit'){
$days = intval($_POST['days']);
if($days<=0)showmsg('请确保每项不能为空',3);
$cutoff = date("Y-m-d H:i:s",strtotime("-{$days} days"));
$DB->exec("DELETE FROM `pre_log` WHERE `date`<:cutoff", [':cutoff'=>$cutoff]);
$DB->exec("VACUUM pre_log");
showmsg('删除登录记录成功！',1);
}else{
?>
<div class="panel panel-primary">
<div class="panel-heading"><h3 class="panel-title">系统数据清理</h3></div>
<div class="panel-body">
<form method="post" style="display:inline"><input type="hidden" name="mod" value="cleancache"/><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"/><button type="submit" class="btn btn-block btn-default">清理设置缓存</button></form><br/>
<form method="post" style="display:inline" onsubmit="return confirm('你确实要删除30天前的订单记录吗？');"><input type="hidden" name="mod" value="cleanorder"/><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"/><button type="submit" class="btn btn-block btn-default">删除30天前订单记录</button></form><br/>
<form method="post" style="display:inline" onsubmit="return confirm('你确实要删除30天前的结算记录吗？');"><input type="hidden" name="mod" value="cleansettle"/><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"/><button type="submit" class="btn btn-block btn-default">删除30天前结算记录</button></form><br/>
<form method="post" style="display:inline" onsubmit="return confirm('你确实要删除30天前的资金明细吗？');"><input type="hidden" name="mod" value="cleanrecord"/><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"/><button type="submit" class="btn btn-block btn-default">删除30天前资金明细</button></form><br/>
<h4>自定义清理：</h4>
<form action="./clean.php?mod=cleanorderi" method="post" role="form"><input type="hidden" name="do" value="submit"/><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"/>
<b>订单记录</b>：<input type="text" name="days" value="" placeholder="天数"/>天前的订单记录&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
<form action="./clean.php?mod=cleansettlei" method="post" role="form"><input type="hidden" name="do" value="submit"/><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"/>
<b>结算记录</b>：<input type="text" name="days" value="" placeholder="天数"/>天前的结算记录&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
<form action="./clean.php?mod=cleanrecordi" method="post" role="form"><input type="hidden" name="do" value="submit"/><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"/>
<b>资金明细</b>：<input type="text" name="days" value="" placeholder="天数"/>天前的订单记录&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
<form action="./clean.php?mod=cleantransferi" method="post" role="form"><input type="hidden" name="do" value="submit"/><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"/>
<b>付款记录</b>：<input type="text" name="days" value="" placeholder="天数"/>天前的付款记录&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
<form action="./clean.php?mod=cleanpsorderi" method="post" role="form"><input type="hidden" name="do" value="submit"/><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"/>
<b>分账记录</b>：<input type="text" name="days" value="" placeholder="天数"/>天前的分账记录&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
<form action="./clean.php?mod=cleanlogi" method="post" role="form"><input type="hidden" name="do" value="submit"/><input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>"/>
<b>登录记录</b>：<input type="text" name="days" value="" placeholder="天数"/>天前的登录记录&nbsp;<input type="submit" name="submit" value="立即删除" class="btn btn-sm btn-danger" onclick="return confirm('删除后无法恢复，确定继续吗？');"/>
</form><br/>
</div>
<div class="panel-footer">
<span class="glyphicon glyphicon-info-sign"></span>
定期清理数据有助于提升网站访问速度
</div>
</div>
<?php }?>
 </div>
</div>