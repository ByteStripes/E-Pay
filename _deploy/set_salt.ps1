$mysql = 'D:\mysql\bin\mysql.exe'
# 生成 64 位十六进制随机串作为 session_salt
$bytes = New-Object byte[] 32
[System.Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($bytes)
$salt = -join ($bytes | ForEach-Object { $_.ToString('x2') })
$sql = "INSERT INTO pay_config (k, v) VALUES ('session_salt', '$salt') ON DUPLICATE KEY UPDATE v=VALUES(v);"
$out = & $mysql '-uepay' '-pEpay@2026' '-h127.0.0.1' 'epay' '-e' $sql 2>&1
Write-Output ("EXIT=" + $LASTEXITCODE)
Write-Output ($out -join "`n")
Write-Output ("SALT=" + $salt)
# 校验
$chk = & $mysql '-uepay' '-pEpay@2026' '-h127.0.0.1' 'epay' '-N' '-e' "select k,length(v) from pay_config where k in ('session_salt','syskey','version','admin_user','admin_pwd');" 2>&1
Write-Output ($chk -join "`n")
