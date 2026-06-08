$mysql = 'D:\mysql\bin\mysql.exe'
$sql = @"
INSERT INTO pay_user (upid, ``key``, money, email, phone, addtime, pay, settle, keylogin, apply, status)
VALUES (0, MD5(RAND()), '0.00', 'merchant@test.local', '13800138000', NOW(), 1, 1, 1, 0, 1);
SET @uid = LAST_INSERT_ID();
UPDATE pay_user SET pwd = MD5(CONCAT(MD5('Test@123456'), MD5(@uid))) WHERE uid=@uid;
SELECT @uid AS new_uid, phone, email FROM pay_user WHERE uid=@uid;
"@
$out = & $mysql '-uepay' '-pEpay@2026' '-h127.0.0.1' 'epay' '-t' '-e' $sql 2>&1
Write-Output ("EXIT=" + $LASTEXITCODE)
Write-Output ($out -join "`n")
