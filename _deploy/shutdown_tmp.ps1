$adm = 'D:\mysql\bin\mysqladmin.exe'
$out = & $adm '-uepay' '-pEpay@2026' '-h127.0.0.1' 'shutdown' 2>&1
Write-Output ("EXIT=" + $LASTEXITCODE)
Write-Output ($out -join "`n")
