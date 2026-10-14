$mysql = 'D:\mysql\bin\mysql.exe'
$sql = "select k,v from pay_config where k in ('captcha_open','captcha_open_login','captcha_open_reg','reg_open','reg_pay','reg_pre_uid','reg_pre_price','verifytype','test_open','close_keylogin','totp_open');"
$out = & $mysql '-uepay' '-pEpay@2026' '-h127.0.0.1' 'epay' '-t' '-e' $sql 2>&1
Write-Output ($out -join "`n")
