$src = 'C:\Users\Administrator\AppData\Local\Temp\cursor\screenshots'
$dst = 'd:\mydoc\project\E-Pay\_deploy\screenshots'
if (!(Test-Path $dst)) { New-Item -ItemType Directory -Path $dst | Out-Null }
$names = @('01_home.png','02_admin_login.png','03_admin_dashboard.png','04_admin_order.png','05_user_login.png','06_user_after_login.png','07_user_order.png')
foreach ($n in $names) {
  $s = Join-Path $src $n
  if (Test-Path $s) {
    Copy-Item $s (Join-Path $dst $n) -Force
    $sz = (Get-Item (Join-Path $dst $n)).Length
    Write-Output ("OK  {0}  ({1} bytes)" -f $n, $sz)
  } else {
    Write-Output ("MISS {0}" -f $n)
  }
}
Write-Output '--- listing source dir ---'
Get-ChildItem $src -ErrorAction SilentlyContinue | Select-Object Name,Length | ForEach-Object { Write-Output ($_.Name + '  ' + $_.Length) }
