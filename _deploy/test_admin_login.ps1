$ErrorActionPreference = 'Stop'
$base = 'http://127.0.0.1:8080'
# 1) 访问登录页，建立会话与 cookie
$r1 = Invoke-WebRequest -Uri "$base/admin/login.php" -SessionVariable sess -UseBasicParsing
Write-Output ('GET login.php => HTTP ' + $r1.StatusCode)

# 从页面提取注入的 CSRF token
$csrf = ''
if ($r1.Content -match "X-CSRF-TOKEN'\s*:\s*'([a-f0-9]+)'") { $csrf = $Matches[1] }
Write-Output ('CSRF token len=' + $csrf.Length)

# 2) 提交登录（明文 enc=0，验证码已关闭），带 Referer + CSRF 头 + 同一会话
$headers = @{ 'Referer' = "$base/admin/login.php"; 'X-CSRF-TOKEN' = $csrf }
$body = @{ username = 'admin'; password = '123456'; code = ''; enc = '0' }
try {
  $r2 = Invoke-WebRequest -Uri "$base/admin/login.php?act=login" -Method Post -WebSession $sess -Headers $headers -Body $body -UseBasicParsing
  Write-Output ('POST act=login => HTTP ' + $r2.StatusCode)
  Write-Output ('RESP: ' + $r2.Content)
  # 3) 用同一会话(含 admin_token cookie)访问后台首页，验证登录态保持
  $r3 = Invoke-WebRequest -Uri "$base/admin/" -WebSession $sess -Headers @{ 'Referer' = "$base/admin/login.php" } -UseBasicParsing
  Write-Output ('GET /admin/ => HTTP ' + $r3.StatusCode)
  if ($r3.Content -match "location\.href='\./login\.php'") {
    Write-Output 'ADMIN STATE: NOT_LOGGED_IN (redirected to login) FAIL'
  } elseif ($r3.Content -match 'href="\./order\.php"|dropdown-toggle') {
    Write-Output 'ADMIN STATE: LOGGED_IN (dashboard rendered) OK'
  } else {
    Write-Output 'ADMIN STATE: UNKNOWN'
  }
} catch {
  Write-Output ('POST ERROR: ' + $_.Exception.Message)
  if ($_.Exception.Response) {
    $sr = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
    Write-Output ('BODY: ' + $sr.ReadToEnd())
  }
}
