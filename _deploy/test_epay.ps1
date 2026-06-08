$mysql = 'D:\mysql\bin\mysql.exe'
$out = & $mysql '-uepay' '-pEpay@2026' '-h127.0.0.1' '--connect-timeout=5' '-e' 'show databases; select current_user();' 2>&1
Write-Output ("EXIT=" + $LASTEXITCODE)
Write-Output ($out -join "`n")
