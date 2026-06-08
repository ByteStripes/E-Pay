$paths = @(
  'C:\Windows\System32\VCRUNTIME140.dll',
  'C:\Windows\System32\VCRUNTIME140_1.dll',
  'C:\Windows\SysWOW64\VCRUNTIME140.dll',
  'D:\phpstudy_pro\Extensions\php\php8.2.9nts\VCRUNTIME140.dll'
)
foreach ($p in $paths) {
  if (Test-Path $p) {
    '{0} => {1}' -f $p, (Get-Item $p).VersionInfo.FileVersion
  } else {
    '{0} => MISSING' -f $p
  }
}
