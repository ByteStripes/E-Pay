$mysql = 'D:\mysql\bin\mysql.exe'
$out = & $mysql '-uepay' '-pEpay@2026' '-h127.0.0.1' 'epay' '-t' '-e' "select k,v from pay_config where k in ('cdnpublic','localurl','apiurl');" 2>&1
Write-Output ($out -join "`n")
