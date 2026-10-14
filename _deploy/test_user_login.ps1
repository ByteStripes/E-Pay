$ErrorActionPreference = 'Stop'
$base = 'http://127.0.0.1:8080'
$r1 = Invoke-WebRequest -Uri "$base/user/login.php" -SessionVariable sess -UseBasicParsing
Write-Output ('GET user/login.php => HTTP ' + $r1.StatusCode)
$csrf = ''
if ($r1.Content -match 'name="csrf_token" value="([a-f0-9]+)"') { $csrf = $Matches[1] }
Write-Output ('CSRF len=' + $csrf.Length)
$headers = @{ 'Referer' = "$base/user/login.php"; 'X-CSRF-TOKEN' = $csrf }
$body = @{ type='1'; user='13800138000'; pass='Test@123456'; enc='0'; csrf_token=$csrf }
try {
  $r2 = Invoke-WebRequest -Uri "$base/user/ajax.php?act=login" -Method Post -WebSession $sess -Headers $headers -Body $body -UseBasicParsing
  Write-Output ('POST act=login => HTTP ' + $r2.StatusCode)
  Write-Output ('RESP: ' + $r2.Content)
} catch {
  Write-Output ('POST ERROR: ' + $_.Exception.Message)
  if ($_.Exception.Response) {
    $sr = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
    Write-Output ('BODY: ' + $sr.ReadToEnd())
  }
}
