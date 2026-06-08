$urls = @(
  'http://127.0.0.1:8080/',
  'http://127.0.0.1:8080/admin/login.php',
  'http://127.0.0.1:8080/user/login.php'
)
foreach ($u in $urls) {
  Write-Output ('===== ' + $u + ' =====')
  try {
    $r = Invoke-WebRequest -Uri $u -UseBasicParsing -TimeoutSec 20
    Write-Output ('HTTP ' + $r.StatusCode + '  len=' + $r.Content.Length)
    $t = ($r.Content -replace '(?s)<script.*?</script>', ' ') -replace '<[^>]+>', ' '
    $t = ($t -replace '\s+', ' ').Trim()
    if ($t.Length -gt 300) { $t = $t.Substring(0,300) }
    Write-Output $t
    if ($r.Content -match 'Fatal error|Parse error|Uncaught|RuntimeException|Warning:|Notice:') {
      Write-Output '!!! 检测到 PHP 报错关键字 !!!'
    }
  } catch {
    Write-Output ('ERROR: ' + $_.Exception.Message)
    if ($_.Exception.Response) {
      Write-Output ('STATUS: ' + [int]$_.Exception.Response.StatusCode)
    }
  }
  Write-Output ''
}
