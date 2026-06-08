$ErrorActionPreference = 'Stop'
$base = 'http://127.0.0.1:8080/install/index.php'

# Step 3: 写入 config.php
$body = @{
  host     = '127.0.0.1'
  port     = '3306'
  user     = 'epay'
  pwd      = 'Epay@2026'
  database = 'epay'
  dbqz     = 'pay'
}
Write-Output '===== STEP 3: 保存数据库配置 ====='
$r3 = Invoke-WebRequest -Uri "$base`?step=3" -Method Post -Body $body -UseBasicParsing
# 提取关键提示文本
$t3 = ($r3.Content -replace '<[^>]+>', ' ') -replace '\s+', ' '
Write-Output $t3.Substring(0, [Math]::Min(600, $t3.Length))

Write-Output ''
Write-Output '===== STEP 4: 安装数据表 ====='
$r4 = Invoke-WebRequest -Uri "$base`?step=4" -Method Get -UseBasicParsing
$t4 = ($r4.Content -replace '<[^>]+>', ' ') -replace '\s+', ' '
Write-Output $t4.Substring(0, [Math]::Min(800, $t4.Length))
