$mysql = 'D:\mysql\bin\mysql.exe'
$pwds = @('', 'root', '123456', 'password', 'mysql', 'admin', '123456789', 'root123', 'phpstudy', 'P@ssw0rd')
foreach ($p in $pwds) {
  if ($p -eq '') {
    $args = @('-uroot', '--connect-timeout=5', '-e', 'select 1;')
    $label = '(empty)'
  } else {
    $args = @('-uroot', "-p$p", '--connect-timeout=5', '-e', 'select 1;')
    $label = $p
  }
  $out = & $mysql @args 2>&1
  if ($LASTEXITCODE -eq 0) {
    Write-Output ("SUCCESS with password: " + $label)
    break
  } else {
    Write-Output ("FAIL [" + $label + "]: " + ($out -join ' '))
  }
}
